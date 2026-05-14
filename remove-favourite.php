<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$allowedRole = 'user';
require 'auth_guard.php';
require 'db.php';

header('Content-Type: application/json');

$userID = $_SESSION['id'];
$recipeID = isset($_POST['recipeID']) ? (int) $_POST['recipeID'] : 0;

if ($recipeID <= 0) {
    echo json_encode(false);
    exit();
}

$stmt = $conn->prepare("DELETE FROM favourites WHERE userID = ? AND recipeID = ?");
$stmt->bind_param("ii", $userID, $recipeID);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(true);
} else {
    echo json_encode(false);
}

$stmt->close();
?>