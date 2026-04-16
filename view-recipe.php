<?php
require_once 'auth_guard.php';
require_once 'db.php';

$viewerID = (int) $_SESSION['id'];
$viewerType = $_SESSION['userType'];

$recipeID = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 1;

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

/* =========================
   1) Recipe + creator + category
   ========================= */
$sql = "SELECT 
            recipe.id,
            recipe.userID,
            recipe.categoryID,
            recipe.name,
            recipe.description,
            recipe.photoFileName,
            recipe.videoFilePath,
            recipecategory.categoryName,
            user.firstName,
            user.lastName,
            user.photoFileName AS creatorPhoto
        FROM recipe
        INNER JOIN recipecategory ON recipe.categoryID = recipecategory.id
        INNER JOIN user ON recipe.userID = user.id
        WHERE recipe.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipeID);
$stmt->execute();
$recipeResult = $stmt->get_result();

if ($recipeResult->num_rows === 0) {
    die("Recipe not found.");
}

$recipe = $recipeResult->fetch_assoc();
$creatorID = (int) $recipe['userID'];

$recipePhotoPath = resolveFilePath($recipe['photoFileName'], 'images', 'uploads');
$creatorPhotoPath = resolveFilePath($recipe['creatorPhoto'], 'images', 'uploads');

/* =========================
   2) Ingredients
   ========================= */
$ingredientsStmt = $conn->prepare(
    "SELECT ingredientName, ingredientQuantity
     FROM ingredients
     WHERE recipeID = ?
     ORDER BY id ASC"
);
$ingredientsStmt->bind_param("i", $recipeID);
$ingredientsStmt->execute();
$ingredientsResult = $ingredientsStmt->get_result();

/* =========================
   3) Instructions
   ========================= */
$instructionsStmt = $conn->prepare(
    "SELECT step, stepOrder
     FROM instructions
     WHERE recipeID = ?
     ORDER BY stepOrder ASC, id ASC"
);
$instructionsStmt->bind_param("i", $recipeID);
$instructionsStmt->execute();
$instructionsResult = $instructionsStmt->get_result();

/* =========================
   4) Comments
   ========================= */
$commentsStmt = $conn->prepare(
    "SELECT 
        comment.comment,
        comment.date,
        user.firstName,
        user.lastName
     FROM comment
     INNER JOIN user ON comment.userID = user.id
     WHERE comment.recipeID = ?
     ORDER BY comment.date DESC"
);
$commentsStmt->bind_param("i", $recipeID);
$commentsStmt->execute();
$commentsResult = $commentsStmt->get_result();

/* =========================
   5) Top buttons
   ========================= */
$showTopButtons = ($viewerType !== 'admin' && $viewerID !== $creatorID);

$isFavourite = false;
$isLiked = false;
$isReported = false;
$topReference = "admin.php";
$topText = "Admin Page";

if ($viewerType !== 'admin') {
    $topReference = "user.php";
    $topText = "User Page";
}

if ($showTopButtons) {
    $favStmt = $conn->prepare(
        "SELECT 1 FROM favourites WHERE userID = ? AND recipeID = ?"
    );
    $favStmt->bind_param("ii", $viewerID, $recipeID);
    $favStmt->execute();
    $favResult = $favStmt->get_result();
    $isFavourite = $favResult->num_rows > 0;

    $likeStmt = $conn->prepare(
        "SELECT 1 FROM likes WHERE userID = ? AND recipeID = ?"
    );
    $likeStmt->bind_param("ii", $viewerID, $recipeID);
    $likeStmt->execute();
    $likeResult = $likeStmt->get_result();
    $isLiked = $likeResult->num_rows > 0;

    $reportStmt = $conn->prepare(
        "SELECT 1 FROM report WHERE userID = ? AND recipeID = ?"
    );
    $reportStmt->bind_param("ii", $viewerID, $recipeID);
    $reportStmt->execute();
    $reportResult = $reportStmt->get_result();
    $isReported = $reportResult->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Recipe - KiddoBites</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>
<body class="view-recipe-page">

  <header>
      <a href = "<?php echo $topReference; ?>" style = "text-decoration: none;">
        <h2><span class="brand">Kiddo</span>Bites</h2>
      </a>
       <nav>
      <a href="<?php echo $topReference; ?>"  class="my-recipes-link"><?php echo $topText; ?></a>
      &nbsp; | &nbsp;
      <a href="logout.php">Log-out</a>
    </nav>
  </header>

  <main class="container">
    <div class="recipe-card">

      <?php if ($showTopButtons) { ?>
      <div class="recipe-top-actions">

        <form action="add-favourite.php" method="POST" style="display:inline;">
          <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
          <button type="submit" class="btn-fav" <?php echo $isFavourite ? 'disabled' : ''; ?>>
            ⭐ <?php echo $isFavourite ? 'Already in Favourites' : 'Add to Favourites'; ?>
          </button>
        </form>

        <form action="add-like.php" method="POST" style="display:inline;">
          <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
          <button type="submit" class="btn-like" <?php echo $isLiked ? 'disabled' : ''; ?>>
            ❤️ <?php echo $isLiked ? 'Liked' : 'Like'; ?>
          </button>
        </form>

        <form action="add-report.php" method="POST" style="display:inline;">
          <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
          <button type="submit" class="btn-report" <?php echo $isReported ? 'disabled' : ''; ?>>
            🚩 <?php echo $isReported ? 'Reported' : 'Report'; ?>
          </button>
        </form>

      </div>
      <?php } ?>

      <section class="recipe-hero">
        <h1 class="recipe-title"><?php echo htmlspecialchars($recipe['name']); ?></h1>

        <img
          src="<?php echo htmlspecialchars($recipePhotoPath); ?>"
          alt="Recipe"
          class="recipe-main-img"
        >

        <div class="creator-badge">
          <img
            src="<?php echo htmlspecialchars($creatorPhotoPath); ?>"
            alt="Creator"
            class="creator-avatar"
          >
          <span>
            Created by:
            <strong>
              <?php echo htmlspecialchars($recipe['firstName'] . ' ' . $recipe['lastName']); ?>
            </strong>
          </span>
        </div>
      </section>

      <section class="recipe-intro">
        <span class="recipe-category">
          <?php echo htmlspecialchars($recipe['categoryName']); ?>
        </span>

        <p class="recipe-desc">
          <?php echo htmlspecialchars($recipe['description']); ?>
        </p>
      </section>

      <section class="recipe-details">
        <h3>Ingredients</h3>
        <ul class="ingredients-list">
          <?php if ($ingredientsResult->num_rows > 0) { ?>
            <?php while ($ingredient = $ingredientsResult->fetch_assoc()) { ?>
              <li>
                <?php echo htmlspecialchars($ingredient['ingredientName']); ?>
                —
                <?php echo htmlspecialchars($ingredient['ingredientQuantity']); ?>
              </li>
            <?php } ?>
          <?php } else { ?>
            <li>No ingredients available.</li>
          <?php } ?>
        </ul>
      </section>

      <section class="recipe-details">
        <h3>Instructions</h3>
        <ol class="instructions-list">
          <?php if ($instructionsResult->num_rows > 0) { ?>
            <?php while ($step = $instructionsResult->fetch_assoc()) { ?>
              <li><?php echo htmlspecialchars($step['step']); ?></li>
            <?php } ?>
          <?php } else { ?>
            <li>No instructions available.</li>
          <?php } ?>
        </ol>
      </section>

<section class="recipe-video-section">
  <h3>Watch How to Make It</h3>
  <div class="video-placeholder">
    <?php if (!empty($recipe['videoFilePath'])) { ?>

<?php if (filter_var($recipe['videoFilePath'], FILTER_VALIDATE_URL)) { ?>
  <?php
    $videoUrl = $recipe['videoFilePath'];
    $youtubeEmbed = '';

    // youtube.com/watch?v=...
    if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $videoUrl, $matches)) {
        $youtubeEmbed = "https://www.youtube.com/embed/" . $matches[1];
    }
    // youtu.be/...
    elseif (preg_match('/youtu\.be\/([^?&]+)/', $videoUrl, $matches)) {
        $youtubeEmbed = "https://www.youtube.com/embed/" . $matches[1];
    }
  ?>

  <?php if ($youtubeEmbed !== '') { ?>
    <iframe
      width="100%"
      height="400"
      src="<?php echo htmlspecialchars($youtubeEmbed); ?>"
      title="YouTube video player"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen>
    </iframe>
  <?php } else { ?>
    <p>
      <a href="<?php echo htmlspecialchars($videoUrl); ?>" target="_blank">
        Watch video
      </a>
    </p>
  <?php } ?>

<?php } else { ?>
        <?php
          $videoPath = "uploads/" . $recipe['videoFilePath'];
          $videoServerPath = __DIR__ . "/uploads/" . $recipe['videoFilePath'];
        ?>

        <?php if (file_exists($videoServerPath)) { ?>
          <video width="100%" controls>
            <source src="<?php echo htmlspecialchars($videoPath); ?>">
            Your browser does not support the video tag.
          </video>
        <?php } else { ?>
          <p>📺 No video available for this recipe.</p>
        <?php } ?>
      <?php } ?>

    <?php } else { ?>
      <p>📺 No video available for this recipe.</p>
    <?php } ?>
  </div>
</section>

      <section class="comments-area">
        <h3>Comments</h3>

        <form class="comment-form" action="add-comment.php" method="POST">
          <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
          <textarea name="comment" placeholder="Tell us what you think..." required></textarea>
          <button type="submit" class="btn-main">Post Comment</button>
        </form>

        <div class="comments-list">
          <?php if ($commentsResult->num_rows > 0) { ?>
            <?php while ($comment = $commentsResult->fetch_assoc()) { ?>
              <div class="comment-item">
                <p>
                  <strong>
                    <?php echo htmlspecialchars($comment['firstName'] . ' ' . $comment['lastName']); ?>:
                  </strong>
                  <?php echo htmlspecialchars($comment['comment']); ?>
                  <span class="comment-date">
                    (<?php echo htmlspecialchars($comment['date']); ?>)
                  </span>
                </p>
              </div>
            <?php } ?>
          <?php } else { ?>
            <div class="comment-item">
              <p><strong>No comments yet.</strong></p>
            </div>
          <?php } ?>
        </div>
      </section>

    </div>
  </main>

  <footer>
    <p>© 2026 KiddoBites — Healthy Yummies for Tiny Tummies</p>
  </footer>

</body>
</html>
