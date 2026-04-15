<?php

session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $emailAddress = trim($_POST['emailAddress'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($firstName) || empty($lastName) || empty($emailAddress) || empty($password)) {
        header("Location: signup.php?error=Please fill in all required fields");
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE emailAddress = ?");
    $stmt->bind_param("s", $emailAddress);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        header("Location: signup.php?error=Email already exists");
        exit();
    }
    $stmt->close();

    // Check if blocked
    $stmt = $conn->prepare("SELECT * FROM blockeduser WHERE emailAddress = ?");
    $stmt->bind_param("s", $emailAddress);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        header("Location: signup.php?error=Blocked users cannot sign up");
        exit();
    }
    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $userType = "user";

    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === 0) {
        $fileName = $_FILES['profileImage']['name'];
        $tmpName = $_FILES['profileImage']['tmp_name'];

        $newName = uniqid() . "_" . basename($fileName);
        $uploadPath = "uploads/" . $newName;

        if (move_uploaded_file($tmpName, $uploadPath)) {
            $profileImage = $newName;
        } else {
            $profileImage = "default.jpg";
        }
    } else {
        $profileImage = "default.jpg";
    }

    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO user (userType, firstName, lastName, emailAddress, password, photoFileName)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssssss", $userType, $firstName, $lastName, $emailAddress, $hashedPassword, $profileImage);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        $_SESSION['id'] = $userId;
        $_SESSION['userType'] = 'user';
        $stmt->close();
        header("Location: user.php");
        exit();
    } else {
        $stmt->close();
        header("Location: signup.php?error=Failed to create account");
        exit();
    }
}
?>