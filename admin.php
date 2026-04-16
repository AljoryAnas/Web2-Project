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
        <table class="admin-table">
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
              <tr>
                <td>
                  <a href="view-recipe.php?id=<?php echo $report['recipeID']; ?>">
                    <?php echo htmlspecialchars($report['recipeName']); ?>
                  </a>
                </td>
                <td>
                  <?php echo htmlspecialchars($report['firstName'] . ' ' . $report['lastName']); ?><br>
                  <img src="<?php echo htmlspecialchars($creatorPhotoPath); ?>" alt="Creator" class="table-avatar">
                </td>
                <td>
                  <form action="handle-report.php" method="POST" class="admin-actions-form">
                    <input type="hidden" name="reportID" value="<?php echo $report['reportID']; ?>">
                    <input type="hidden" name="recipeID" value="<?php echo $report['recipeID']; ?>">
                    <button type="submit" name="action" value="block" class="btn-block">Confirm &amp; Block</button>
                    <button type="submit" name="action" value="dismiss" class="btn-dismiss">Dismiss</button>
                  </form>
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
        <table class="admin-table">
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
        <p>No blocked users.</p>
      <?php } ?>
    </section>

  </main>

  <footer>
    <p>© 2026 KiddoBites — Healthy Yummies for Tiny Tummies</p>
  </footer>

</body>
</html>