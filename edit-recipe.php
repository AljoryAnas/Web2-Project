<?php
$allowedRole = 'user';
require 'auth_guard.php';
include 'db.php';

$id = $_GET['id'];
$userID = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['id'];
    $recipeName = $_POST['recipeName'];
    $categoryID = $_POST['category'];
    $description = $_POST['description'];

    // update recipe table
    $sql = "UPDATE recipe
            SET name='$recipeName', description='$description', categoryID=$categoryID
            WHERE id=$id AND userID=$userID";
    $conn->query($sql);

        // update photo if new one uploaded
    if (!empty($_FILES['photo']['name'])) {
    $oldPhotoResult = $conn->query("SELECT photoFileName FROM recipe WHERE id=$id AND userID=$userID");
    $oldPhoto = $oldPhotoResult->fetch_assoc()['photoFileName'];

    $photoName = $_FILES['photo']['name'];
    $photoTmp = $_FILES['photo']['tmp_name'];

    move_uploaded_file($photoTmp, "uploads/" . $photoName);

    $conn->query("UPDATE recipe SET photoFileName='$photoName' WHERE id=$id AND userID=$userID");

    $oldPhotoPath1 = "uploads/" . $oldPhoto;
    $oldPhotoPath2 = "images/" . $oldPhoto;

    if (file_exists($oldPhotoPath1)) {
        unlink($oldPhotoPath1);
    } elseif (file_exists($oldPhotoPath2)) {
        unlink($oldPhotoPath2);
    }
}

    // update video if new one uploaded
   $videoUrl = trim($_POST['videoUrl']);

    if (!empty($_FILES['videoFile']['name'])) {
        $oldVideoResult = $conn->query("SELECT videoFilePath FROM recipe WHERE id=$id AND userID=$userID");
        $oldVideo = $oldVideoResult->fetch_assoc()['videoFilePath'];

        $videoName = $_FILES['videoFile']['name'];
        $videoTmp = $_FILES['videoFile']['tmp_name'];

        move_uploaded_file($videoTmp, "uploads/" . $videoName);

        $conn->query("UPDATE recipe SET videoFilePath='$videoName' WHERE id=$id AND userID=$userID");

        if (!empty($oldVideo) && !filter_var($oldVideo, FILTER_VALIDATE_URL)) {
            $oldVideoPath = "uploads/" . $oldVideo;
            if (file_exists($oldVideoPath)) {
                unlink($oldVideoPath);
            }
        }

    } elseif ($videoUrl != "") {
        $oldVideoResult = $conn->query("SELECT videoFilePath FROM recipe WHERE id=$id AND userID=$userID");
        $oldVideo = $oldVideoResult->fetch_assoc()['videoFilePath'];

        $conn->query("UPDATE recipe SET videoFilePath='$videoUrl' WHERE id=$id AND userID=$userID");

        if (!empty($oldVideo) && !filter_var($oldVideo, FILTER_VALIDATE_URL)) {
            $oldVideoPath = "uploads/" . $oldVideo;
            if (file_exists($oldVideoPath)) {
                unlink($oldVideoPath);
            }
        }
    }

    // delete old ingredients and instructions
    $conn->query("DELETE FROM ingredients WHERE recipeID=$id");
    $conn->query("DELETE FROM instructions WHERE recipeID=$id");

    // insert ingredients again
    for ($i = 0; $i < count($_POST['ingredientName']); $i++) {
        $ingredientName = $_POST['ingredientName'][$i];
        $ingredientQty = $_POST['ingredientQty'][$i];

        $conn->query("INSERT INTO ingredients (recipeID, ingredientName, ingredientQuantity)
                      VALUES ($id, '$ingredientName', '$ingredientQty')");
    }

    // insert instructions again
    for ($i = 0; $i < count($_POST['stepText']); $i++) {
        $step = $_POST['stepText'][$i];
        $stepOrder = $i + 1;

        $conn->query("INSERT INTO instructions (recipeID, step, stepOrder)
                      VALUES ($id, '$step', $stepOrder)");
    }

    header("Location: my-recipes.php");
    exit();
}

// load recipe data
$result = $conn->query("SELECT * FROM recipe WHERE id=$id AND userID=$userID");

if ($result->num_rows == 0) {
    die("Recipe not found."); 
}

$recipe = $result->fetch_assoc();
$ingredients = $conn->query("SELECT * FROM ingredients WHERE recipeID=$id");
$steps = $conn->query("SELECT * FROM instructions WHERE recipeID=$id ORDER BY stepOrder");
$categoryResult = $conn->query("SELECT * FROM recipecategory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Recipe - KiddoBites</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>

<body class="edit-recipe-page">

  <header>
<a href = "user.php" style = "text-decoration: none;">
        <h2><span class="brand">Kiddo</span>Bites</h2>
      </a>    <h2 class="welcome">Edit Recipe</h2>

    <nav>
      <a href="my-recipes.php" class="my-recipes-link">My Recipes</a>
      &nbsp; | &nbsp;
      <a href="logout.php">Log-out</a>
    </nav>
  </header>

  <main class="container">

    <section>
      <h3>Edit Recipe</h3>
      <p>Update the recipe details below, then click <strong>Save Changes</strong>.</p>

      <form id="editRecipeForm" action="edit-recipe.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <p>
          <label for="recipeName">Recipe Name</label><br>
          <input type="text" id="recipeName" name="recipeName" value="<?php echo $recipe['name']; ?>" required>
        </p>

        <p>
          <label for="category">Category</label><br>
          <select id="category" name="category" required>
            <?php while($category = $categoryResult->fetch_assoc()) { ?>
              <option value="<?php echo $category['id']; ?>"
                <?php if($recipe['categoryID'] == $category['id']) echo "selected"; ?>>
                <?php echo $category['categoryName']; ?>
            </option>
          <?php } ?>
          </select>
        </p>

        <p>
          <label for="description">Description</label><br>
          <textarea id="description" name="description" rows="4" required><?php echo $recipe['description']; ?></textarea>
        </p>

        <p>
          <label for="photo">Change Recipe Photo</label><br>
          <input type="file" id="photo" name="photo" accept="image/*">
          <br><small>Current photo: <?php echo $recipe['photoFileName']; ?></small>
        </p>

        <hr>

        <h3>Ingredients</h3>
        <p class="hint">Edit ingredient name + quantity, or add more.</p>

        <div id="ingredientsContainer">
          <?php while($ing = $ingredients->fetch_assoc()) { ?>
            <div class="row ingredient-row">
              <div class="col">
                <label>Ingredient Name</label><br>
              <input type="text" name="ingredientName[]" value="<?php echo $ing['ingredientName']; ?>" required>
            </div>

            <div class="col">
              <label>Quantity</label><br>
              <input type="text" name="ingredientQty[]" value="<?php echo $ing['ingredientQuantity']; ?>" required>
             </div>
          </div>
        <?php } ?>
       </div>

        <p>
          <button type="button" id="addIngredientBtn" class="btn-secondary">+ Add another ingredient</button>
        </p>

        <hr>

        <h3>Instructions</h3>
        <p class="hint">Edit steps, or add new ones.</p>

        <div id="stepsContainer">
          <?php while($step = $steps->fetch_assoc()) { ?>
            <div class="row step-row">
             <div class="col wide">
              <label>Step</label><br>
              <input type="text" name="stepText[]" value="<?php echo $step['step']; ?>" required>
            </div>
          </div>
        <?php } ?>
     </div>

        <p>
          <button type="button" id="addStepBtn" class="btn-secondary">+ Add another step</button>
        </p>

        <hr>

        <h3>Video (Optional)</h3>

        <p>
          <label for="videoFile">Change Video File (optional)</label><br>
          <input type="file" id="videoFile" name="videoFile" accept="video/*">
        </p>

        <p>
          <label for="videoUrl">Video URL (optional)</label><br>
          <input type="url" id="videoUrl" name="videoUrl"
            value="<?php echo filter_var($recipe['videoFilePath'], FILTER_VALIDATE_URL) ? $recipe['videoFilePath'] : ''; ?>"
            placeholder="https://youtube.com/...">
        </p>

        <p>
          <button type="submit">Save Changes</button>
          <a href="my-recipes.php" class="btn-cancel">Cancel</a>
        </p>

      </form>
    </section>

  </main>

  <footer>
    <p>© 2026 KiddoBites — Healthy Yummies for Tiny Tummies</p>
  </footer>

  <script src="script.js"></script>

</body>
</html>
