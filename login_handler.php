<?php

session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $emailAddress = trim($_POST['emailAddress'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($emailAddress) || empty($password)) {
        header("Location: login.php?error=Please fill in all required fields");
        exit();
    }

    // Check if blocked
    $stmt = $conn->prepare("SELECT * FROM blockeduser WHERE emailAddress = ?");
    $stmt->bind_param("s", $emailAddress);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        header("Location: login.php?error=Your account is blocked");
        exit();
    }
    $stmt->close();

    // Get user data
    $stmt = $conn->prepare("SELECT * FROM user WHERE emailAddress = ?");
    $stmt->bind_param("s", $emailAddress);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        header("Location: login.php?error=Incorrect email or password");
        exit();
    }

    $userRow = $result->fetch_assoc();
    $storedPassword = $userRow['password'];

    if (!password_verify($password, $storedPassword)) {
        $stmt->close();
        header("Location: login.php?error=Incorrect email or password");
        exit();
    }

    $stmt->close();

    $_SESSION['id'] = $userRow['id'];
    $_SESSION['userType'] = $userRow['userType'];

    if ($userRow['userType'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: user.php");
    }
    exit();
}
?>