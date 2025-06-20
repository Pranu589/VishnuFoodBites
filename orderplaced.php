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

// Fetch orders
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, u.username
    FROM Orders o
    JOIN Users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch order items
$order_items = [];
foreach ($orders as $order) {
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
    $order_items[$order['order_id']] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Placed - Vishnu Food Bites</title>
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
        .items-list {
            margin-top: 10px;
        }
        .items-list div {
            margin: 5px 0;
        }
        .no-orders {
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
        .accept-btn {
            padding: 8px 16px;
            font-size: 16px;
            color: white;
            background-color: #001f3d;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .accept-btn:hover {
            background-color: #28a745;
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
        <h2>Orders Placed</h2>
        <?php if (empty($orders)): ?>
            <div class="no-orders">No orders have been placed yet.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Username</th>
                        <th>Order Date</th>
                        <th>Items</th>
                        <th>Total (₹)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr data-order-id="<?php echo htmlspecialchars($order['order_id']); ?>">
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td class="items-list">
                                <?php
                                $total = 0;
                                foreach ($order_items[$order['order_id']] as $item) {
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
                            <td>
                                <button class="accept-btn" onclick="acceptOrder(<?php echo htmlspecialchars($order['order_id']); ?>)">Accept</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="a1.php" class="back-btn">Back to Dashboard</a>
    </div>

    <script>
        async function acceptOrder(orderId) {
            if (!confirm('Accept this order? It will be moved to Accepted Items and removed from this list.')) {
                return;
            }

            try {
                const response = await fetch('process_accept_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });
                const text = await response.text();
                console.log('Raw response:', text);
                const data = JSON.parse(text);

                if (data.success) {
                    document.querySelector(`tr[data-order-id="${orderId}"]`).remove();
                    if (document.querySelectorAll('tbody tr').length === 0) {
                        document.querySelector('table').outerHTML = '<div class="no-orders">No orders have been placed yet.</div>';
                    }
                    alert(data.message);
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error accepting order: ' + error.message);
            }
        }
    </script>
</body>
</html>