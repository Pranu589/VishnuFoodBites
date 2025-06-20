<?php
ob_start(); // Start output buffering
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

include 'db_config.php';

if ($conn->connect_error) {
    error_log("save_orders.php - Database connection failed: " . $conn->connect_error);
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$raw_input = file_get_contents('php://input');
error_log("save_orders.php - Raw input: " . $raw_input);
$data = json_decode($raw_input, true);

if (!isset($data['user_id']) || empty($data['user_id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$user_id = (int)$data['user_id'];
$conn->begin_transaction();

try {
    // Create order
    $stmt = $conn->prepare("INSERT INTO Orders (user_id) VALUES (?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO Order_Items (order_id, item_id, quantity) 
                            SELECT ?, item_id, quantity FROM Cart WHERE user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM Cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
} catch (Exception $e) {
    $conn->rollback();
    error_log("save_orders.php - Error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()]);
}

$conn->close();
?>