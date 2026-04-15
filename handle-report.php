<?php
$allowedRole = 'admin';
require_once 'auth_guard.php';
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$reportID = isset($_POST['reportID']) ? (int) $_POST['reportID'] : 0;
$recipeID = isset($_POST['recipeID']) ? (int) $_POST['recipeID'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($reportID <= 0 || $recipeID <= 0 || ($action !== 'block' && $action !== 'dismiss')) {
    die("Invalid data.");
}

/* =========================
   Dismiss report
   ========================= */
if ($action === 'dismiss') {
    $dismissStmt = $conn->prepare("DELETE FROM report WHERE id = ?");
    if (!$dismissStmt) {
        die("Prepare dismiss failed: " . $conn->error);
    }

    $dismissStmt->bind_param("i", $reportID);

    if (!$dismissStmt->execute()) {
        die("Dismiss failed: " . $conn->error);
    }

    header("Location: admin.php");
    exit();
}

/* =========================
   Get recipe creator
   ========================= */
$userSql = "SELECT 
                recipe.userID,
                user.firstName,
                user.lastName,
                user.emailAddress,
                user.photoFileName
            FROM recipe
            INNER JOIN user ON recipe.userID = user.id
            WHERE recipe.id = ?";

$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $recipeID);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    die("Recipe creator not found.");
}

$userData = $userResult->fetch_assoc();
$blockedUserID = (int) $userData['userID'];

/* =========================
   Add to blockeduser
   ========================= */
$checkBlockedStmt = $conn->prepare("SELECT id FROM blockeduser WHERE emailAddress = ?");
$checkBlockedStmt->bind_param("s", $userData['emailAddress']);
$checkBlockedStmt->execute();
$checkBlockedResult = $checkBlockedStmt->get_result();

if ($checkBlockedResult->num_rows === 0) {
    $insertBlockedStmt = $conn->prepare(
        "INSERT INTO blockeduser (firstName, lastName, emailAddress)
         VALUES (?, ?, ?)"
    );
    $insertBlockedStmt->bind_param(
        "sss",
        $userData['firstName'],
        $userData['lastName'],
        $userData['emailAddress']
    );

    if (!$insertBlockedStmt->execute()) {
        die("Insert blocked user failed: " . $conn->error);
    }
}

/* =========================
   Delete recipe files
   ========================= */
$recipesStmt = $conn->prepare("SELECT photoFileName, videoFilePath FROM recipe WHERE userID = ?");
$recipesStmt->bind_param("i", $blockedUserID);
$recipesStmt->execute();
$recipesResult = $recipesStmt->get_result();

while ($fileRow = $recipesResult->fetch_assoc()) {

    if (!empty($fileRow['photoFileName'])) {
        $photoInImages = __DIR__ . "/images/" . $fileRow['photoFileName'];
        $photoInUploads = __DIR__ . "/uploads/" . $fileRow['photoFileName'];

        if (file_exists($photoInImages)) {
            unlink($photoInImages);
        } elseif (file_exists($photoInUploads)) {
            unlink($photoInUploads);
        }
    }

    if (!empty($fileRow['videoFilePath'])) {
        $videoPath = __DIR__ . "/videos/" . $fileRow['videoFilePath'];
        if (file_exists($videoPath)) {
            unlink($videoPath);
        }
    }
}

/* =========================
   Delete profile image
   ========================= */
if (!empty($userData['photoFileName'])) {
    $profileInImages = __DIR__ . "/images/" . $userData['photoFileName'];
    $profileInUploads = __DIR__ . "/uploads/" . $userData['photoFileName'];

    if (file_exists($profileInImages)) {
        unlink($profileInImages);
    } elseif (file_exists($profileInUploads)) {
        unlink($profileInUploads);
    }
}

/* =========================
    Delete recipes FIRST
   ========================= */
$deleteRecipesStmt = $conn->prepare("DELETE FROM recipe WHERE userID = ?");
if (!$deleteRecipesStmt) {
    die("Prepare delete recipes failed: " . $conn->error);
}

$deleteRecipesStmt->bind_param("i", $blockedUserID);

if (!$deleteRecipesStmt->execute()) {
    die("Delete recipes failed: " . $conn->error);
}

/* =========================
   Delete user AFTER recipes
   ========================= */
$deleteUserStmt = $conn->prepare("DELETE FROM user WHERE id = ?");
if (!$deleteUserStmt) {
    die("Prepare delete user failed: " . $conn->error);
}

$deleteUserStmt->bind_param("i", $blockedUserID);

if (!$deleteUserStmt->execute()) {
    die("Delete user failed: " . $conn->error);
}

/* =========================
   Delete current report
   ========================= */
$deleteCurrentReportStmt = $conn->prepare("DELETE FROM report WHERE id = ?");
$deleteCurrentReportStmt->bind_param("i", $reportID);
$deleteCurrentReportStmt->execute();

header("Location: admin.php");
exit();
?>