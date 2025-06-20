```php
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

include 'db_config.php';

if ($conn->connect_error) {
    error_log("get_cart.php - Database connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT c.item_id, c.item_name, c.item_price, c.quantity, f.shop_id
                        FROM Cart c
                        JOIN Food_Items f ON c.item_id = f.item_id
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
while ($row = $result->fetch_assoc()) {
    $cart[] = [
        'item_id' => $row['item_id'],
        'name' => $row['item_name'],
        'price' => $row['item_price'],
        'shop_id' => $row['shop_id'],
        'quantity' => $row['quantity']
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'cart' => $cart]);
?>