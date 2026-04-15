<?php
$allowedRole = 'user';
require 'auth_guard.php';
require 'db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $recipeID = (int) $_GET['id'];
    $userID = (int) $_SESSION['id'];

 
    $stmt = $conn->prepare("DELETE FROM Favourites WHERE userID = ? AND recipeID = ?");
    $stmt->bind_param("ii", $userID, $recipeID);

    if ($stmt->execute()) {
        header("Location: user.php?msg=Removed from favourites");
    } else {
        header("Location: user.php?error=Could not remove recipe");
    }
    
    $stmt->close();
} else {
    header("Location: user.php");
}

exit();
?>