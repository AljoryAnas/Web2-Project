<?php
$allowedRole = 'admin';
require_once 'auth_guard.php';
require_once 'db.php';

$adminID = (int) $_SESSION['id'];

$adminStmt = $conn->prepare("SELECT * FROM user WHERE id = ? AND userType = 'admin'");
$adminStmt->bind_param("i", $adminID);
$adminStmt->execute();
$adminResult = $adminStmt->get_result();

if ($adminResult->num_rows === 0) {
    die("Admin not found.");
}

$admin = $adminResult->fetch_assoc();

$reportsSql = "SELECT 
                  report.id AS reportID,
                  recipe.id AS recipeID,
                  recipe.name AS recipeName,
                  user.id AS creatorID,
                  user.firstName,
                  user.lastName,
                  user.emailAddress,
                  user.photoFileName
               FROM report
               INNER JOIN recipe ON report.recipeID = recipe.id
               INNER JOIN user ON recipe.userID = user.id
               ORDER BY report.id ASC";

$reportsResult = $conn->query($reportsSql);

$blockedResult = $conn->query("SELECT * FROM blockeduser ORDER BY id ASC");

function resolveFilePath($fileName, $primaryFolder = 'images', $secondaryFolder = 'uploads') {
    if (empty($fileName)) {
        return $primaryFolder . '/default.jpg';
    }

    $primaryPath = __DIR__ . '/' . $primaryFolder . '/' . $fileName;
    if (file_exists($primaryPath)) {
        return $primaryFolder . '/' . $fileName;
    }

    $secondaryPath = __DIR__ . '/' . $secondaryFolder . '/' . $fileName;
    if (file_exists($secondaryPath)) {
        return $secondaryFolder . '/' . $fileName;
    }

    return $primaryFolder . '/default.jpg';
}

$adminPhotoPath = resolveFilePath($admin['photoFileName'], 'images', 'uploads');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - KiddoBites</title>
  <link rel="stylesheet" href="stylesheet.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="admin-page">

  <header>
      <h2><span class="brand">Kiddo</span>Bites</h2>
      <h2 class="welcome">Welcome Admin!</h2>
      <a href="logout.php">Log-out</a>
  </header>

  <main class="container">
    <?php if (isset($_GET['error'])): ?>
  <div class="error-box">
    <?php echo htmlspecialchars($_GET['error']); ?>
  </div>
<?php endif; ?>
    <section class="admin-profile">
      <h3>Admin Information</h3>
      <div class="user-info">
        <div class="admin-details">
          <p><strong>Name:</strong> <?php echo htmlspecialchars($admin['firstName'] . ' ' . $admin['lastName']); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['emailAddress']); ?></p>
        </div>
        <img src="<?php echo htmlspecialchars($adminPhotoPath); ?>" alt="Admin" class="admin-logo-img">
      </div>
    </section>

    <section class="reports-section">
      <h3 class="alert-title">Pending Recipe Reports 🚩</h3>

      <?php if ($reportsResult && $reportsResult->num_rows > 0) { ?>
        <table class="admin-table" id="reports-table">
          <thead>
            <tr>
              <th>Recipe Name</th>
              <th>Creator</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($report = $reportsResult->fetch_assoc()) { ?>
              <?php $creatorPhotoPath = resolveFilePath($report['photoFileName'], 'images', 'uploads'); ?>
              <tr id="report-row-<?php echo $report['reportID']; ?>">
                <td>
                  <a href="view-recipe.php?id=<?php echo $report['recipeID']; ?>">
                    <?php echo htmlspecialchars($report['recipeName']); ?>
                  </a>
                </td>
                <td>
                  <?php echo htmlspecialchars($report['firstName'] . ' ' . $report['lastName']); ?><br>
                  <img src="<?php echo htmlspecialchars($creatorPhotoPath); ?>" alt="Creator" class="table-avatar">
                </td>
                <td class="admin-actions-form">
                  <button class="btn-block report-action"
                      data-report-id="<?php echo $report['reportID']; ?>"
                      data-recipe-id="<?php echo $report['recipeID']; ?>"
                      data-action="block"
                      data-email="<?php echo htmlspecialchars($report['emailAddress']); ?>">
                    Confirm &amp; Block
                  </button>
                  <button class="btn-dismiss report-action"
                      data-report-id="<?php echo $report['reportID']; ?>"
                      data-recipe-id="<?php echo $report['recipeID']; ?>"
                      data-action="dismiss"
                      data-email="<?php echo htmlspecialchars($report['emailAddress']); ?>">
                    Dismiss
                  </button>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      <?php } else { ?>
        <p>No pending reports.</p>
      <?php } ?>
    </section>

    <section class="blocked-section">
      <h3>Blocked Users 🚫</h3>

      <?php if ($blockedResult && $blockedResult->num_rows > 0) { ?>
        <table class="admin-table" id="blocked-table">
          <thead>
            <tr>
              <th>User Name</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($blocked = $blockedResult->fetch_assoc()) { ?>
              <tr>
                <td><?php echo htmlspecialchars($blocked['firstName'] . ' ' . $blocked['lastName']); ?></td>
                <td><?php echo htmlspecialchars($blocked['emailAddress']); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      <?php } else { ?>
        <p id="no-blocked-msg">No blocked users.</p>
      <?php } ?>
    </section>

  </main>

  <footer>
    <p>© 2026 KiddoBites — Healthy Yummies for Tiny Tummies</p>
  </footer>
  <script>
    $(document).ready(function () {

      $(document).on('click', '.report-action', function () {
        var btn = $(this);
        var reportID = btn.data('report-id');
        var recipeID = btn.data('recipe-id');
        var action = btn.data('action');

        // Disable both buttons in the row to prevent double clicks
        btn.closest('tr').find('.report-action').prop('disabled', true);

        $.ajax({
          url:  'handle-report.php',
          type: 'POST',
          data: {
            reportID: reportID,
            recipeID: recipeID,
            action:   action
          },
          success: function (response) {
            if (response.trim() === 'true') {
              var row = $('#report-row-' + reportID);

              if (action === 'block') {
                var name = row.find('td:nth-child(2)').contents().filter(function() {
                  return this.nodeType === 3;
                }).text().trim();
                var email = btn.data('email');

                var newRow = '<tr><td>' + name + '</td><td>' + email + '</td></tr>';

                if ($('#blocked-table tbody').length > 0) {
                  $('#blocked-table tbody').append(newRow);
                } else {
                  $('#no-blocked-msg').replaceWith(
                    '<table class="admin-table" id="blocked-table"><thead><tr><th>User Name</th><th>Email</th></tr></thead><tbody>' + newRow + '</tbody></table>'
                  );
                }
              
            }

              row.fadeOut(400, function () {
                row.remove();
                // If no rows left, replace the table with a message
                if ($('#reports-table tbody tr').length === 0) {
                  $('#reports-table').replaceWith('<p>No pending reports.</p>');
                }
              });
            } else {
              alert('Action failed. Please try again.');
              btn.closest('tr').find('.report-action').prop('disabled', false);
            }
          },
          error: function () {
            alert('An error occurred. Please try again.');
            btn.closest('tr').find('.report-action').prop('disabled', false);
          }
        });
      });

    });
  </script>
</body>
</html>