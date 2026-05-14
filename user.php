<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'auth_guard.php'; 
require_once 'db.php';

if ($_SESSION['userType'] !== 'user') {
    header("Location: Login.php?error=Access Denied");
    exit();
}

$userID = (int) $_SESSION['id'];


$userStmt = $conn->prepare("SELECT firstName, lastName, emailAddress, photoFileName FROM user WHERE id = ?");
$userStmt->bind_param("i", $userID);
$userStmt->execute();
$userQueryResult = $userStmt->get_result();

if ($userQueryResult->num_rows === 0) {
    die("User not found.");
}
$user = $userQueryResult->fetch_assoc();


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


$countRecipes = $conn->query("SELECT COUNT(*) as total FROM recipe WHERE userID = $userID")->fetch_assoc()['total'];
$countLikes = $conn->query("SELECT COUNT(*) as total FROM likes INNER JOIN recipe ON likes.recipeID = recipe.id WHERE recipe.userID = $userID")->fetch_assoc()['total'];

// Filter recipes by category
$categoriesResult = $conn->query("SELECT * FROM recipecategory");
$selectedCategory = isset($_POST['category']) ? (int)$_POST['category'] : 0;

$recipeSql = "SELECT recipe.*, recipecategory.categoryName, user.firstName, user.lastName, user.photoFileName AS creatorPhoto,
              (SELECT COUNT(*) FROM likes WHERE recipeID = recipe.id) as totalLikes
              FROM recipe 
              INNER JOIN recipecategory ON recipe.categoryID = recipecategory.id
              INNER JOIN user ON recipe.userID = user.id";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $selectedCategory > 0) {
    $recipeSql .= " WHERE recipe.categoryID = $selectedCategory";
}
$allRecipes = $conn->query($recipeSql);

// Fetch favourite recipes
$favSql = "SELECT recipe.id, recipe.name, recipe.photoFileName 
           FROM favourites 
           INNER JOIN recipe ON favourites.recipeID = recipe.id 
           WHERE favourites.userID = $userID";
$favRecipes = $conn->query($favSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Page - KiddoBites</title>
  <link rel="stylesheet" href="stylesheet.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <script>
  $(document).ready(function () {

    $("#categorySelect").change(function () {
        var categoryID = $(this).val();

        $.ajax({
            url: "filter-recipes.php",
            type: "POST",
            data: { categoryID: categoryID },
            dataType: "json",
            success: function (data) {
                var tableBody = $("#recipesTable tbody");
                tableBody.empty(); 

                if (data.length > 0) {
                    $.each(data, function (index, recipe) {
                        var row = "<tr>" +
                            "<td><a href='view-recipe.php?id=" + recipe.id + "'>" + recipe.name + "</a></td>" +
                            "<td><img src='" + recipe.photo + "' alt='Recipe'></td>" +
                            "<td>" + recipe.creatorName + "<br><img src='" + recipe.creatorPhoto + "' alt='Creator' class='table-avatar'></td>" +
                            "<td>" + recipe.likes + "</td>" +
                            "<td>" + recipe.category + "</td>" +
                            "</tr>";
                        tableBody.append(row);
                    });
                } else {
                    tableBody.append("<tr><td colspan='5'>No recipes found in this category.</td></tr>");
                }
            },
            error: function () {
                alert("Error fetching recipes.");
            }
        });
    });
    $(".remove-favourite").click(function (event) {
      event.preventDefault();  
      var link = $(this);
      var recipeID = link.data("id");

      $.ajax({
        url: "remove-favourite.php",
        type: "POST",
        data: { recipeID: recipeID },
        dataType: "json",
        success: function (response) {
          if (response === true) {
            link.closest("tr").remove();
            if ($("#favouritesTable tr").length === 1) {
              $("#favouritesTable").replaceWith("<p>No favourites added yet.</p>");
            }
          } else {
            alert("Could not remove from favourites.");
          }
        }
      });
    });

  });
  </script>
</head>

<body class="user-page">

  <header> 
      <h2><span class="brand">Kiddo</span>Bites</h2>
      <h2 class="welcome">Welcome <?php echo htmlspecialchars($user['firstName']); ?>!</h2>
      <a href="logout.php">Log-out</a>
  </header>          

  <div class="container">
    
    <section>
      <h3>My Information</h3>
      <div class="user-info">
        <div>
          <p><strong>Name:</strong> <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($user['emailAddress']); ?></p>
        </div>
        <img src="<?php echo resolveFilePath($user['photoFileName']); ?>" alt="User Photo">
      </div>
    </section>

    <section>
      <a class="my-recipes-link" href="my-recipes.php"><h3>My Recipes</h3></a>
      <p>Total Recipes: <?php echo $countRecipes; ?></p>
      <p>Total Likes: <?php echo $countLikes; ?></p>
    </section>

    <section>
      <h3>All Available Recipes</h3>
      <select id="categorySelect">
        <option value="0">All Categories</option>
        <?php while($cat = $categoriesResult->fetch_assoc()): ?>
          <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['categoryName']); ?></option>
        <?php endwhile; ?>
      </select>

      <table id="recipesTable">
        <thead>
          <tr>
            <th>Recipe Name</th>
            <th>Photo</th>
            <th>Creator</th>
            <th>Likes</th>
            <th>Category</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $allRecipes->fetch_assoc()): ?>
          <tr>
            <td><a href="view-recipe.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></a></td>
            <td><img src="<?php echo resolveFilePath($row['photoFileName']); ?>" alt="Recipe"></td>
            <td>
              <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?><br>
              <img src="<?php echo resolveFilePath($row['creatorPhoto']); ?>" alt="Creator" class="table-avatar">
            </td>
            <td><?php echo $row['totalLikes']; ?></td>
            <td><?php echo htmlspecialchars($row['categoryName']); ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>

    <section>
      <h3>My Favourite Recipes <img class="favourite" src="images/heart.png" alt="heart"></h3>
      <?php if ($favRecipes->num_rows > 0): ?>
      <table id='favouritesTable'>
        <thead>
          <tr>
            <th>Recipe Name</th>
            <th>Photo</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while($fav = $favRecipes->fetch_assoc()): ?>
          <tr>
            <td><a href="view-recipe.php?id=<?php echo $fav['id']; ?>"><?php echo htmlspecialchars($fav['name']); ?></a></td>
            <td><img src="<?php echo resolveFilePath($fav['photoFileName']); ?>" alt="Recipe"></td>
            <td><a href="#" class="remove-favourite" data-id="<?php echo $fav['id']; ?>">Remove</a></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p>No favourites added yet.</p>
      <?php endif; ?>
    </section>

  </div>
  
  <footer>
    <p>© 2026 KiddoBites — Healthy Yummies for Tiny Tummies</p>
  </footer>

</body>
</html>
