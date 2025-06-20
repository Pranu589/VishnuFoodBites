<?php
include 'db_config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['item_id'], $input['shop_id'], $input['quantity'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

$item_id = $input['item_id'];
$shop_id = $input['shop_id'];
$quantity = $input['quantity'];

// Check stock
$stmt = $conn->prepare("SELECT quantity FROM Food_Items WHERE item_id = ? AND shop_id = ?");
$stmt->bind_param("ii", $item_id, $shop_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item || $item['quantity'] < $quantity) {
    http_response_code(400);
    echo json_encode(['error' => 'Insufficient stock']);
    exit();
}

http_response_code(200);
echo json_encode(['message' => 'Stock available']);
$conn->close();
?>