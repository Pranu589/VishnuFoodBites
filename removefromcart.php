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
if (!$input || !isset($input['item_name'], $input['user_id'])) {
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

$item_name = $input['item_name'];
$user_id = $input['user_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Get cart item details (to restore quantity)
    $stmt = $conn->prepare("SELECT item_id, shop_id, quantity FROM Cart WHERE user_id = ? AND item_name = ?");
    $stmt->bind_param("is", $user_id, $item_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_item = $result->fetch_assoc();
    $stmt->close();

    if (!$cart_item) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['error' => 'Item not found in cart']);
        exit();
    }

    $item_id = $cart_item['item_id'];
    $shop_id = $cart_item['shop_id'];
    $cart_quantity = $cart_item['quantity'];

    // Restore quantity to Food_Items
    $stmt = $conn->prepare("UPDATE Food_Items SET quantity = quantity + ?, stock_status = IF(quantity + ? > 0, 'in', 'out') WHERE item_id = ? AND shop_id = ?");
    $stmt->bind_param("iiii", $cart_quantity, $cart_quantity, $item_id, $shop_id);
    $stmt->execute();
    $stmt->close();

    // Remove item from cart
    $stmt = $conn->prepare("DELETE FROM Cart WHERE user_id = ? AND item_name = ?");
    $stmt->bind_param("is", $user_id, $item_name);
    if ($stmt->execute()) {
        $conn->commit();
        http_response_code(200);
        echo json_encode(['message' => 'Item removed from cart']);
    } else {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to remove item from cart']);
    }
    $stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>