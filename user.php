<?php
$allowedRole = 'user';
require 'auth_guard.php';
require 'db.php';

$userID = (int) $_SESSION['id'];

$userStmt = $conn->prepare("SELECT firstName, lastName, emailAddress, photoFileName FROM user WHERE id = ?");
$userStmt->bind_param("i", $userID);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    die("User not found.");
}

$user = $userResult->fetch_assoc();

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

$userPhotoPath = resolveFilePath($user['photoFileName'], 'images', 'uploads');

$countRecipes = $conn->query("SELECT COUNT(*) as total FROM Recipe WHERE userID = $userID")->fetch_assoc()['total'];
$countLikes = $conn->query("SELECT COUNT(*) as total FROM Likes INNER JOIN Recipe ON Likes.recipeID = Recipe.id WHERE Recipe.userID = $userID")->fetch_assoc()['total'];

// Filter recipes by category
$categoriesResult = $conn->query("SELECT * FROM RecipeCategory");
$selectedCategory = isset($_POST['category']) ? (int)$_POST['category'] : 0;

$recipeSql = "SELECT Recipe.*, RecipeCategory.categoryName, User.firstName, User.lastName, User.photoFileName AS creatorPhoto,
              (SELECT COUNT(*) FROM Likes WHERE recipeID = Recipe.id) as totalLikes
              FROM Recipe 
              INNER JOIN RecipeCategory ON Recipe.categoryID = RecipeCategory.id
              INNER JOIN User ON Recipe.userID = User.id";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $selectedCategory > 0) {
    $recipeSql .= " WHERE Recipe.categoryID = $selectedCategory";
}
$allRecipes = $conn->query($recipeSql);

// Fetch favourite recipes
$favSql = "SELECT Recipe.id, Recipe.name, Recipe.photoFileName 
           FROM Favourites 
           INNER JOIN Recipe ON Favourites.recipeID = Recipe.id 
           WHERE Favourites.userID = $userID";
$favRecipes = $conn->query($favSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Page - KiddoBites</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>
<body class="user-page">

  <header> 
      <h2><span class="brand">Kiddo</span>Bites</h2>
      <h2 class="welcome">Welcome <?php echo htmlspecialchars($user['firstName']); ?>!</h2>
      <a href="logout.php">Log-out</a>
  </header>
            <?php if (isset($_GET['error'])): ?>
  <div class="error-box">
    <?php echo htmlspecialchars($_GET['error']); ?>
  </div>
<?php endif; ?>
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

      <form method="POST" action="user.php">
        <select name="category">
          <option value="0">All Categories</option>
          <?php while($cat = $categoriesResult->fetch_assoc()): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo ($selectedCategory == $cat['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['categoryName']); ?>
            </option>
          <?php endwhile; ?>
        </select>
        <button type="submit">Filter</button>
      </form>

      <?php if ($allRecipes->num_rows > 0): ?>
      <table>
        <tr>
          <th>Recipe Name</th>
          <th>Photo</th>
          <th>Creator</th>
          <th>Likes</th>
          <th>Category</th>
        </tr>
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
      </table>
      <?php else: ?>
        <p>No recipes found.</p>
      <?php endif; ?>
    </section>

    <section>
      <h3>My Favourite Recipes <img class="favourite" src="images/heart.png" alt="heart"></h3>
      <?php if ($favRecipes->num_rows > 0): ?>
      <table>
        <tr>
          <th>Recipe Name</th>
          <th>Photo</th>
          <th>Action</th>
        </tr>
        <?php while($fav = $favRecipes->fetch_assoc()): ?>
        <tr>
          <td><a href="view-recipe.php?id=<?php echo $fav['id']; ?>"><?php echo htmlspecialchars($fav['name']); ?></a></td>
          <td><img src="<?php echo resolveFilePath($fav['photoFileName']); ?>" alt="Recipe"></td>
          <td><a href="remove-favourite.php?id=<?php echo $fav['id']; ?>">Remove</a></td>
        </tr>
        <?php endwhile; ?>
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