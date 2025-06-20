<?php
session_start();
include 'db_config.php';

// Check if user is logged in and session is valid
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
    header("Location: index.php");
    exit();
}

// Verify session token
$stmt = $conn->prepare("SELECT session_token, username FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['session_token'] !== $_SESSION['session_token']) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Log user access for debugging
error_log("users_ordered_items.php - Accessed by User ID: {$_SESSION['user_id']}, Username: {$user['username']}");

// Fetch pending orders for the logged-in user
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, u.username
    FROM Orders o
    JOIN Users u ON o.user_id = u.user_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$pending_orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch pending order items
$pending_order_items = [];
foreach ($pending_orders as $order) {
    $stmt = $conn->prepare("
        SELECT oi.quantity, f.item_name, f.price, s.shop_name
        FROM Order_Items oi
        JOIN Food_Items f ON oi.item_id = f.item_id
        JOIN Shops s ON f.shop_id = s.shop_id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order['order_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $pending_order_items[$order['order_id']] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch accepted orders for the logged-in user
$stmt = $conn->prepare("
    SELECT ai.accepted_item_id, ai.order_id, ai.quantity, ai.accepted_date,
           f.item_name, f.price, s.shop_name
    FROM Accepted_Items ai
    JOIN Food_Items f ON ai.item_id = f.item_id
    JOIN Shops s ON f.shop_id = s.shop_id
    WHERE ai.user_id = ?
    ORDER BY ai.accepted_date DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$accepted_orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ordered Items - Vishnu Food Bites</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f1;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        header {
            background-color: #1e3a5f;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            box-sizing: border-box;
        }
        .header-left h1 {
            margin: 0;
            font-size: 28px;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .header-right a {
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
        }
        .header-right a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        h2 {
            color: #1e3a5f;
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e4d8b7;
        }
        th {
            background-color: #1e3a5f;
            color: white;
            font-size: 18px;
        }
        td {
            color: #333;
            font-size: 16px;
        }
        .no-orders {
            text-align: center;
            font-size: 20px;
            color: #ee4747;
            margin: 20px 0;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 18px;
            color: white;
            background-color: #1e3a5f;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #16324a;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <h1>Vishnu Food Bites</h1>
        </div>
        <div class="header-right">
            <span>User: <?php echo htmlspecialchars($user['username']); ?> (ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?>)</span>
            <a href="homepage.php">HOME</a>
            <a href="logout.php">LOGOUT</a>
        </div>
    </header>

    <div class="container">
        <h2>Your Pending Orders</h2>
        <?php if (empty($pending_orders)): ?>
            <div class="no-orders">You have no pending orders.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Items</th>
                        <th>Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td>
                                <?php
                                $total = 0;
                                foreach ($pending_order_items[$order['order_id']] as $item) {
                                    $subtotal = $item['quantity'] * $item['price'];
                                    $total += $subtotal;
                                ?>
                                    <div>
                                        <?php echo htmlspecialchars($item['item_name']); ?> (<?php echo htmlspecialchars($item['shop_name']); ?>)
                                        - Qty: <?php echo htmlspecialchars($item['quantity']); ?>
                                        - ₹<?php echo number_format($subtotal, 2); ?>
                                    </div>
                                <?php } ?>
                            </td>
                            <td>₹<?php echo number_format($total, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Your Accepted Orders</h2>
        <?php if (empty($accepted_orders)): ?>
            <div class="no-orders">You have no accepted orders.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Accepted Item ID</th>
                        <th>Order ID</th>
                        <th>Item Name</th>
                        <th>Shop</th>
                        <th>Quantity</th>
                        <th>Price (₹)</th>
                        <th>Total (₹)</th>
                        <th>Accepted Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accepted_orders as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['accepted_item_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['order_id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['shop_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($item['accepted_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="homepage.php" class="back-btn">Back to Homepage</a>
    </div>
</body>
</html>