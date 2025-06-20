<?php
$servername = "localhost"; 
$username = "root";  // Default username in XAMPP
$password = "";      // Default password is empty
$database = "vishnu_food_bites"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database,3306);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
