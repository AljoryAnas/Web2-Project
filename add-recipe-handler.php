<?php
$allowedRole = 'user';
require 'auth_guard.php';
include 'db.php';

$userID = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $recipeName = $_POST['recipeName'];
    $categoryID = $_POST['category'];
    $description = $_POST['description'];

    // photo
    $photoName = $_FILES['photo']['name'];
    $photoTmp = $_FILES['photo']['tmp_name'];
    $newPhotoName = uniqid() . "_" . basename($photoName);

    // video (optional)
    $videoName = "";
    $videoTmp = "";
    $videoUrl = trim($_POST['videoUrl']);

    $hasVideoFile = !empty($_FILES['videoFile']['name']);
    $hasVideoUrl = !empty($videoUrl);

    if ($hasVideoFile && $hasVideoUrl) {
        $error = urlencode("Please enter either a video file or a video URL, not both.");
        header("Location: add-recipe.php?error=$error");
        exit();
    }

    $videoValue = "";

    if ($hasVideoFile) {
        $videoName = $_FILES['videoFile']['name'];
        $videoTmp = $_FILES['videoFile']['tmp_name'];

        $newVideoName = uniqid() . "_" . basename($videoName);
        move_uploaded_file($videoTmp, "uploads/" . $newVideoName);
        $videoValue = $newVideoName;

    } elseif ($hasVideoUrl) {
        $videoValue = $videoUrl;
    }

    move_uploaded_file($photoTmp, "uploads/" . $newPhotoName);

    $sql = "INSERT INTO recipe (userID, categoryID, name, description, photoFileName, videoFilePath)
            VALUES ($userID, $categoryID, '$recipeName', '$description', '$newPhotoName', '$videoValue')";

    if ($conn->query($sql) === TRUE) {

        $recipeID = $conn->insert_id;

        for ($i = 0; $i < count($_POST['ingredientName']); $i++) {
            $ingredientName = $_POST['ingredientName'][$i];
            $ingredientQty = $_POST['ingredientQty'][$i];

            $conn->query("INSERT INTO ingredients (recipeID, ingredientName, ingredientQuantity)
                          VALUES ($recipeID, '$ingredientName', '$ingredientQty')");
        }

        for ($i = 0; $i < count($_POST['stepText']); $i++) {
            $step = $_POST['stepText'][$i];
            $stepOrder = $i + 1;

            $conn->query("INSERT INTO instructions (recipeID, step, stepOrder)
                          VALUES ($recipeID, '$step', $stepOrder)");
        }

        header("Location: my-recipes.php");
        exit();

    } else {
        echo "Insert failed: " . $conn->error;
    }
}
?>