<?php
$allowedRole = 'user';
require 'auth_guard.php';
include 'db.php';

if (!isset($_GET['id'])) {
    echo json_encode(false);
    exit();
}

$id = $_GET['id'];
$userID = $_SESSION['id'];

// check ownership
$result = $conn->query("SELECT * FROM recipe WHERE id=$id AND userID=$userID");

if ($result->num_rows == 0) {
   echo json_encode(false);
    exit();
}

$recipe = $result->fetch_assoc();

// delete image
if (!empty($recipe['photoFileName'])) {
    $path1 = "uploads/" . $recipe['photoFileName'];
    $path2 = "images/" . $recipe['photoFileName'];

    if (file_exists($path1)) unlink($path1);
    elseif (file_exists($path2)) unlink($path2);
}

// delete video
if (!empty($recipe['videoFilePath']) && !filter_var($recipe['videoFilePath'], FILTER_VALIDATE_URL)) {
    $videoPath = "uploads/" . $recipe['videoFilePath'];
    if (file_exists($videoPath)) {
        unlink($videoPath);
    }
}

// delete related data
$conn->query("DELETE FROM ingredients WHERE recipeID=$id");
$conn->query("DELETE FROM instructions WHERE recipeID=$id");
$conn->query("DELETE FROM comment WHERE recipeID=$id");
$conn->query("DELETE FROM likes WHERE recipeID=$id");
$conn->query("DELETE FROM favourites WHERE recipeID=$id");
$conn->query("DELETE FROM report WHERE recipeID=$id");

// delete recipe
$conn->query("DELETE FROM recipe WHERE id=$id AND userID=$userID");

// redirect
echo json_encode(true);
exit();
?>