<?php
require 'db.php';

$categoryID = isset($_POST['categoryID']) ? (int)$_POST['categoryID'] : 0;

$sql = "SELECT recipe.*, recipecategory.categoryName, user.firstName, user.lastName, user.photoFileName AS creatorPhoto,
        (SELECT COUNT(*) FROM likes WHERE recipeID = recipe.id) as totalLikes
        FROM recipe 
        INNER JOIN recipecategory ON recipe.categoryID = recipecategory.id
        INNER JOIN user ON recipe.userID = user.id";

if ($categoryID > 0) {
    $sql .= " WHERE recipe.categoryID = $categoryID";
}


$result = $conn->query($sql);
$recipes = [];

function getPath($fileName) {
    if (empty($fileName)) return 'images/default.jpg';
    return (file_exists('uploads/' . $fileName)) ? 'uploads/' . $fileName : 'images/' . $fileName;
}

while ($row = $result->fetch_assoc()) {
    $recipes[] = [
        "id" => $row['id'],
        "name" => htmlspecialchars($row['name']),
        "photo" => getPath($row['photoFileName']),
        "creatorName" => htmlspecialchars($row['firstName'] . ' ' . $row['lastName']),
        "creatorPhoto" => getPath($row['creatorPhoto']),
        "likes" => $row['totalLikes'],
        "category" => htmlspecialchars($row['categoryName'])
    ];
}

echo json_encode($recipes); 
?>
