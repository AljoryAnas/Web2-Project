<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['userType'])) {
    header("Location: login.php?error=Please log in first");
    exit();
}

// role check
if (isset($allowedRole) && $_SESSION['userType'] !== $allowedRole) {
    header("Location: login.php?error=Access denied");
    exit();
}
?>

