<?php
$servername = "localhost";
$username = "root";
$password = "root";
$database = "recipes_project";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>