<?php
require_once 'auth_guard.php';
require_once 'db.php';

$userID = (int) $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$recipeID = isset($_POST['recipeID']) ? (int) $_POST['recipeID'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : "";

if ($recipeID <= 0 || empty($comment)) {
    die("Invalid comment data.");
}

$stmt = $conn->prepare(
    "INSERT INTO comment (comment, date, userID, recipeID) 
     VALUES (?, NOW(), ?, ?)"
);
$stmt->bind_param("sii", $comment, $userID, $recipeID);

if ($stmt->execute()) {
    header("Location: view-recipe.php?id=" . $recipeID);
    exit();
} else {
    die("Error adding comment: " . $conn->error);
}
?>