<?php
session_start();
include 'db_config.php';

// Check if user is logged in and session is valid
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Verify session token
$stmt = $conn->prepare("SELECT session_token FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['session_token'] !== $_SESSION['session_token']) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(['error' => 'Invalid session']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['item_id'], $input['shop_id'], $input['user_id'], $input['item_name'], $input['item_price'], $input['quantity'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

// Verify user_id matches session
if ($input['user_id'] !== $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'User ID mismatch']);
    exit();
}

$item_id = $input['item_id'];
$shop_id = $input['shop_id'];
$user_id = $input['user_id'];
$item_name = $input['item_name'];
$item_price = $input['item_price'];
$quantity = $input['quantity'];

// Start transaction
$conn->begin_transaction();

try {
    // Check if item exists and has sufficient stock
    $stmt = $conn->prepare("SELECT quantity, stock_status FROM Food_Items WHERE item_id = ? AND shop_id = ? FOR UPDATE");
    $stmt->bind_param("ii", $item_id, $shop_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if (!$item || $item['quantity'] < $quantity) {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['error' => 'Item not available or insufficient stock']);
        exit();
    }

    // Update Food_Items quantity
    $new_quantity = $item['quantity'] - $quantity;
    $new_stock_status = ($new_quantity > 0) ? 'in' : 'out';
    $stmt = $conn->prepare("UPDATE Food_Items SET quantity = ?, stock_status = ? WHERE item_id = ? AND shop_id = ?");
    $stmt->bind_param("isii", $new_quantity, $new_stock_status, $item_id, $shop_id);
    $stmt->execute();
    $stmt->close();

    // Check if item is already in cart
    $stmt = $conn->prepare("SELECT quantity FROM Cart WHERE user_id = ? AND item_id = ? AND shop_id = ?");
    $stmt->bind_param("iii", $user_id, $item_id, $shop_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_item = $result->fetch_assoc();
    $stmt->close();

    if ($cart_item) {
        // Update cart quantity
        $new_cart_quantity = $cart_item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE Cart SET quantity = ?, item_price = ? WHERE user_id = ? AND item_id = ? AND shop_id = ?");
        $stmt->bind_param("idiii", $new_cart_quantity, $item_price, $user_id, $item_id, $shop_id);
    } else {
        // Insert new cart item
        $stmt = $conn->prepare("INSERT INTO Cart (user_id, item_id, shop_id, item_name, item_price, quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisdi", $user_id, $item_id, $shop_id, $item_name, $item_price, $quantity);
    }

    if ($stmt->execute()) {
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Item added to cart']);
    } else {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add item to cart']);
    }
    $stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>