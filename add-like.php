<?php
require_once 'auth_guard.php';
require_once 'db.php';

$userID = (int) $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$recipeID = isset($_POST['recipeID']) ? (int) $_POST['recipeID'] : 0;

if ($recipeID <= 0) {
    die("Invalid recipe ID.");
}

$checkStmt = $conn->prepare("SELECT * FROM likes WHERE userID = ? AND recipeID = ?");
$checkStmt->bind_param("ii", $userID, $recipeID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows == 0) {
    $insertStmt = $conn->prepare("INSERT INTO likes (userID, recipeID) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $userID, $recipeID);

    if (!$insertStmt->execute()) {
        die("Error adding like: " . $conn->error);
    }
}

header("Location: view-recipe.php?id=" . $recipeID);
exit();
?>