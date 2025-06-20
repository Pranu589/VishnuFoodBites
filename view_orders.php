<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vishnu_food_bites";

$conn = new mysqli($servername, $username, $password, $dbname, 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = 1; // Dummy user

$sql = "SELECT * FROM orders WHERE user_id = $user_id AND order_time = (
            SELECT MAX(order_time) FROM orders WHERE user_id = $user_id
        )";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Ordered Items</title>
    <style>
        table {
            width: 90%;
            margin: 50px auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 10px 15px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
<h2 style="text-align:center;">📦 Your Ordered Items</h2>

<?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Item Name</th>
            <th>Item Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['item_name']) ?></td>
                <td>₹<?= number_format($row['item_price'], 2) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>₹<?= number_format($row['item_price'] * $row['quantity'], 2) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p style="text-align:center;">No ordered items found.</p>
<?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>
