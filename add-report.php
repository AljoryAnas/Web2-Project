<?php
require_once 'auth_guard.php';
require_once 'db.php';

$userID = (int) $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo 'false';
    exit();
}

$recipeID = isset($_POST['recipeID']) ? (int) $_POST['recipeID'] : 0;

if ($recipeID <= 0) {
    echo 'false';
    exit();
}

$checkStmt = $conn->prepare("SELECT * FROM report WHERE userID = ? AND recipeID = ?");
$checkStmt->bind_param("ii", $userID, $recipeID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows == 0) {
    $insertStmt = $conn->prepare("INSERT INTO report (userID, recipeID) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $userID, $recipeID);
    if (!$insertStmt->execute()) {
        echo 'false';
        exit();
    }
}

echo 'true';
exit();
?>