<?php
$allowedRole = 'user';
require 'auth_guard.php';
include 'db.php';

$userID = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['id'];
    $recipeName = $_POST['recipeName'];
    $categoryID = $_POST['category'];
    $description = $_POST['description'];

    $videoUrl = trim($_POST['videoUrl'] ?? '');
    $hasVideoFile = !empty($_FILES['videoFile']['name']);
    $hasVideoUrl = !empty($videoUrl);

    if ($hasVideoFile && $hasVideoUrl) {
        $error = urlencode("Please enter either a video file or a video URL, not both.");
        header("Location: edit-recipe.php?id=$id&error=$error");
        exit();
    }

    $sql = "UPDATE recipe
            SET name='$recipeName', description='$description', categoryID=$categoryID
            WHERE id=$id AND userID=$userID";
    $conn->query($sql);

    if (!empty($_FILES['photo']['name'])) {
        $oldPhotoResult = $conn->query("SELECT photoFileName FROM recipe WHERE id=$id AND userID=$userID");
        $oldPhoto = $oldPhotoResult->fetch_assoc()['photoFileName'];

        $photoName = $_FILES['photo']['name'];
        $photoTmp = $_FILES['photo']['tmp_name'];
        $newPhotoName = uniqid() . "_" . basename($photoName);

        move_uploaded_file($photoTmp, "uploads/" . $newPhotoName);

        $conn->query("UPDATE recipe SET photoFileName='$newPhotoName' WHERE id=$id AND userID=$userID");

        $oldPhotoPath1 = "uploads/" . $oldPhoto;
        $oldPhotoPath2 = "images/" . $oldPhoto;

        if (file_exists($oldPhotoPath1)) {
            unlink($oldPhotoPath1);
        } elseif (file_exists($oldPhotoPath2)) {
            unlink($oldPhotoPath2);
        }
    }

    if (!empty($_FILES['videoFile']['name'])) {
        $oldVideoResult = $conn->query("SELECT videoFilePath FROM recipe WHERE id=$id AND userID=$userID");
        $oldVideo = $oldVideoResult->fetch_assoc()['videoFilePath'];

        $videoName = $_FILES['videoFile']['name'];
        $videoTmp = $_FILES['videoFile']['tmp_name'];
        $newVideoName = uniqid() . "_" . basename($videoName);

        move_uploaded_file($videoTmp, "uploads/" . $newVideoName);

        $conn->query("UPDATE recipe SET videoFilePath='$newVideoName' WHERE id=$id AND userID=$userID");

        if (!empty($oldVideo) && !filter_var($oldVideo, FILTER_VALIDATE_URL)) {
            $oldVideoPath = "uploads/" . $oldVideo;
            if (file_exists($oldVideoPath)) {
                unlink($oldVideoPath);
            }
        }

    } elseif ($videoUrl != "") {
        $oldVideoResult = $conn->query("SELECT videoFilePath FROM recipe WHERE id=$id AND userID=$userID");
        $oldVideo = $oldVideoResult->fetch_assoc()['videoFilePath'];

        $conn->query("UPDATE recipe SET videoFilePath='$videoUrl' WHERE id=$id AND userID=$userID");

        if (!empty($oldVideo) && !filter_var($oldVideo, FILTER_VALIDATE_URL)) {
            $oldVideoPath = "uploads/" . $oldVideo;
            if (file_exists($oldVideoPath)) {
                unlink($oldVideoPath);
            }
        }
    }

    $conn->query("DELETE FROM ingredients WHERE recipeID=$id");
    $conn->query("DELETE FROM instructions WHERE recipeID=$id");

    for ($i = 0; $i < count($_POST['ingredientName']); $i++) {
        $ingredientName = $_POST['ingredientName'][$i];
        $ingredientQty = $_POST['ingredientQty'][$i];

        $conn->query("INSERT INTO ingredients (recipeID, ingredientName, ingredientQuantity)
                      VALUES ($id, '$ingredientName', '$ingredientQty')");
    }

    for ($i = 0; $i < count($_POST['stepText']); $i++) {
        $step = $_POST['stepText'][$i];
        $stepOrder = $i + 1;

        $conn->query("INSERT INTO instructions (recipeID, step, stepOrder)
                      VALUES ($id, '$step', $stepOrder)");
    }

    header("Location: my-recipes.php");
    exit();
}
?>
