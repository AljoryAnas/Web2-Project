<?php
//$servername = "localhost";
//$username = "root";
//$password = "root";
//$database = "recipes_project";
$servername = "sql208.infinityfree.com";
$username = "if0_41867996";
$password = "AKLTM445";
$database = "if0_41867996_recipes_project";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>