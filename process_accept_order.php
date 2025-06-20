<?php
ob_start();
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token']) || $_SESSION['role'] !== 'admin') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit;
}

include 'db_config.php';

if ($conn->connect_error) {
    error_log("process_accept_order.php - Database connection failed: " . $conn->connect_error);
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
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
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid session']);
    exit;
}

$raw_input = file_get_contents('php://input');
error_log("process_accept_order.php - Raw input: $raw_input");
$data = json_decode($raw_input, true);

if (!isset($data['order_id']) || empty($data['order_id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit;
}

$order_id = (int)$data['order_id'];
$conn->begin_transaction();

try {
    // Fetch order and user_id
    $stmt = $conn->prepare("SELECT user_id FROM Orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        new Exception("Order not found");
    }
    $order = $result->fetch_assoc();
    $user_id = $order['user_id'];
    $stmt->close();

    // Copy items to Accepted_Items
    $stmt = $conn->prepare("
        INSERT INTO Accepted_Items (order_id, user_id, item_id, quantity)
        SELECT ?, ?, item_id, quantity
        FROM Order_Items
        WHERE order_id = ?
    ");
    $stmt->bind_param("iii", $order_id, $user_id, $order_id);
    $stmt->execute();
    $stmt->close();

    // Delete order items
    $stmt = $conn->prepare("DELETE FROM Order_Items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Delete order
    $stmt = $conn->prepare("DELETE FROM Orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Order accepted and moved to Accepted_Items']);
} catch (Exception $e) {
    $conn->rollback();
    error_log("process_accept_order.php - Error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Failed to accept order: ' . $e->getMessage()]);
}

$conn->close();
?>