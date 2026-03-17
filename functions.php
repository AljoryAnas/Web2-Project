<?php
require_once "db.php";

function getCategories() {
    global $conn;

    $sql = "SELECT * FROM RecipeCategory";
    $result = $conn->query($sql);

    return $result;
}

function countLikes($recipeId) {
    global $conn;

    $sql = "SELECT COUNT(*) AS total FROM Likes WHERE recipeID = $recipeId";
    $result = $conn->query($sql);

    $row = $result->fetch_assoc();
    return $row['total'];
}

function getRecipes() {
    global $conn;

    $sql = "SELECT * FROM Recipe";
    return $conn->query($sql);
}
?>