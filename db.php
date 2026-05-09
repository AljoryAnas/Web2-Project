<?php
$servername = "localhost";
$username = "root";
$password = "root";
$database = "recipes_project";

<<<<<<< HEAD
$conn = new mysqli($servername, $username, $password, $database, '8889');
=======
$conn = new mysqli($servername, $username, $password, $database);
//$conn = new mysqli("sql208.infinityfree.com", "if0_41867996", "AKLTM445", "if0_41867996_recipes_project");
>>>>>>> f83b6bb963500788ddc4b70e754d508f41a50cfd

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>