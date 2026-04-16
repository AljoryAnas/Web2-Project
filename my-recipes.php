<?php
$allowedRole = 'user';
require 'auth_guard.php';
include 'db.php';

$userID = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Recipes - KiddoBites</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>

<body class="my-recipes-page">

  <header>
<a href = "user.php" style = "text-decoration: none;">
        <h2><span class="brand">Kiddo</span>Bites</h2>
      </a>
    <h2 class="welcome">My Recipes</h2>

    <nav>
      <a href="user.php" class="my-recipes-link">User Page</a>
      &nbsp; | &nbsp;
      <a href="logout.php">Log-out</a>
    </nav>
  </header>

  <main class="container">

    <section>
      <div class="my-recipes-top">
        <div>
          <h3>Recipes Added By Me</h3>
          <p>
            Here you can manage all recipes you have shared in <strong>KiddoBites</strong>.
          </p>
        </div>

        <div>
          <a href="add-recipe.php" class="add-new-link">+ Add New Recipe</a>
        </div>
      </div>
    </section>

    <section>
      <h3>My Recipes List</h3>

      <table class="my-recipes-table">
        <thead>
          <tr class = "left">
            <th>Recipe</th>
            <th>Ingredients</th>
            <th>Instructions</th>
            <th>Video</th>
            <th>Likes</th>
            <th>Edit</th>
            <th>Delete</th>
          </tr>
        </thead>
      <tbody>

<?php

$sql = "SELECT * FROM recipe WHERE userID = $userID";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows == 0) {
    echo "<tr><td colspan='7'>No recipes found.</td></tr>";
} else {

    while ($row = $result->fetch_assoc()) {
        $recipeID = $row['id'];

        // Ingredients
        $ingSQL = "SELECT * FROM ingredients WHERE recipeID = $recipeID";
        $ingRes = $conn->query($ingSQL);

        $ingredients = "<ul>";
        while ($ing = $ingRes->fetch_assoc()) {
            $ingredients .= "<li>{$ing['ingredientName']} - {$ing['ingredientQuantity']}</li>";
        }
        $ingredients .= "</ul>";

        // Instructions
        $stepSQL = "SELECT * FROM instructions WHERE recipeID = $recipeID ORDER BY stepOrder";
        $stepRes = $conn->query($stepSQL);

        $steps = "<ol>";
        while ($step = $stepRes->fetch_assoc()) {
            $steps .= "<li>{$step['step']}</li>";
        }
        $steps .= "</ol>";

        // Likes
        $likeSQL = "SELECT COUNT(*) as total FROM likes WHERE recipeID = $recipeID";
        $likeRes = $conn->query($likeSQL);
        $likes = $likeRes->fetch_assoc()['total'];

        echo "<tr>";

        $photoPath = "images/" . $row['photoFileName'];
        if (!file_exists($photoPath)) { //COME BACK HERE IF PICS DON'T WORK
            $photoPath = "uploads/" . $row['photoFileName'];
        }

        echo "<td>
        <a href='view-recipe.php?id=$recipeID' class='recipe-link'>
          <div class='recipe-cell'>
            <img src='$photoPath' alt='Recipe photo'>
            <span>{$row['name']}</span>
          </div>
        </a>
      </td>";

        echo "<td>$ingredients</td>";
        echo "<td>$steps</td>";

        if ($row['videoFilePath'] != "") {

          if (filter_var($row['videoFilePath'], FILTER_VALIDATE_URL)) {
              echo "<td><a href='{$row['videoFilePath']}' target='_blank'>Watch video</a></td>";
          } else {
              $videoPath = "uploads/" . $row['videoFilePath'];

              if (file_exists($videoPath)) {
                  echo "<td><a href='$videoPath' target='_blank'>Watch video</a></td>";
              } else {
                  echo "<td>No video for recipe</td>";
              }
          }

} else {
    echo "<td>No video for recipe</td>";
}

        echo "<td>$likes</td>";
        echo "<td><a href='edit-recipe.php?id=$recipeID'>Edit</a></td>";
        echo "<td><a href='delete-recipe.php?id=$recipeID'>Delete</a></td>";

        echo "</tr>";
    }
}
?>
</tbody>
</table>
</section>
</main>

  <footer>
    <p>© 2026 KiddoBites — Healthy Yummies for Tiny Tummies</p>
  </footer>

</body>
</html>
