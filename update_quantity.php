<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vishnu_food_bites";

$conn = new mysqli($servername, $username, $password, $dbname, 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$delta = $data['delta'];

if ($delta == -1) {
    $conn->query("UPDATE cart SET quantity = quantity - 1 WHERE id = $id AND quantity > 1");
} else if ($delta == 1) {
    $conn->query("UPDATE cart SET quantity = quantity + 1 WHERE id = $id");
}

echo "Quantity updated";
$conn->close();
?>
