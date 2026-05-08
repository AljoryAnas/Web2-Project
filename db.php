<?php
$servername = "localhost";
$username = "root";
$password = "root";
$database = "recipes_project";

$conn = new mysqli($servername, $username, $password, $database);
//$conn = new mysqli("sql208.infinityfree.com", "if0_41867996", "AKLTM445", "if0_41867996_recipes_project");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>