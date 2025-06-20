<?php
session_start();
include 'db_config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token']) || $_SESSION['role'] !== 'admin') {
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

// Fetch accepted items
$stmt = $conn->prepare("
    SELECT ai.accepted_item_id, ai.order_id, ai.user_id, ai.item_id, ai.quantity, ai.accepted_date,
           u.username, f.item_name, f.price, s.shop_name
    FROM Accepted_Items ai
    JOIN Users u ON ai.user_id = u.user_id
    JOIN Food_Items f ON ai.item_id = f.item_id
    JOIN Shops s ON f.shop_id = s.shop_id
    ORDER BY ai.accepted_date DESC
");
$stmt->execute();
$result = $stmt->get_result();
$accepted_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accepted Orders - Vishnu Food Bites</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }
        header {
            background-color: #001f3d;
            display: flex;
            align-items: center;
            position: relative;
            height: 80px;
        }
        .header-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }
        .header-center h1 {
            color: white;
            font-size: 24px;
            margin: 0;
        }
        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            margin-right: 20px;
        }
        .header-right a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            margin-left: 20px;
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
            color: #001f3d;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #001f3d;
            color: white;
        }
        .no-items {
            text-align: center;
            font-size: 18px;
            color: #777;
            margin-top: 20px;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 18px;
            color: white;
            background-color: #001f3d;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #ff6347;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-center">
            <h1>Vishnu Food Bites</h1>
        </div>
        <div class="header-right">
            <a href="a1.php">Back to Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>
    <div class="container">
        <h2>Accepted Orders</h2>
        <?php if (empty($accepted_items)): ?>
            <div class="no-items">No orders have been accepted yet.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Accepted Item ID</th>
                        <th>Order ID</th>
                        <th>Username</th>
                        <th>Item Name</th>
                        <th>Shop</th>
                        <th>Quantity</th>
                        <th>Price (₹)</th>
                        <th>Total (₹)</th>
                        <th>Accepted Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accepted_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['accepted_item_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['order_id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($item['username']); ?></td>
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
        <a href="a1.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>