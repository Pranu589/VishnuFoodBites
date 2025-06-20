<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vishnu_food_bites";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['success_message'] = '';
    $_SESSION['error_message'] = '';

    foreach ($_POST['food'] as $id => $data) {
        $price = (float)$data['price'];
        $quantity = (int)$data['quantity'];
        $stock_status = $quantity < 5 ? 'out' : 'in';

        $stmt = $conn->prepare("UPDATE Food_Items SET price = ?, quantity = ?, stock_status = ? WHERE item_id = ? AND shop_id = 2");
        if ($stmt === false) {
            die('MySQL prepare error: ' . $conn->error);
        }

        $stmt->bind_param("diss", $price, $quantity, $stock_status, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] .= "Item ID $id updated successfully!<br>";
        } else {
            $_SESSION['error_message'] .= "Failed to update Item ID $id.<br>";
        }

        $stmt->close();
    }

    header("Location: adminfood2.php");
    exit;
}

$sql = "SELECT item_id, item_name, price, quantity, stock_status FROM Food_Items WHERE shop_id = 2";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vishnu Food Bytes - Central Square Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        header { background-color: #001f3d; color: white; display: flex; justify-content: space-between; align-items: center; padding: 20px; font-size: 24px; }
        .header-left a, .header-right a { color: white; text-decoration: none; font-size: 18px; }
        .header-left a:hover, .header-right a:hover { color: #ff6347; }
        .header-center { flex-grow: 1; text-align: center; font-size: 28px; font-weight: bold; }
        .food-list { width: 80%; margin: auto; padding: 20px 0; }
        .food-list li { background-color: #fff; border: 1px solid #ddd; margin: 10px 0; padding: 20px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .submit-container { text-align: center; margin-top: 20px; }
        .submit-container button { background-color: #001f3d; color: white; padding: 10px 20px; font-size: 18px; border: none; border-radius: 5px; cursor: pointer; }
        .submit-container button:hover { background-color: #ff6347; }
        .success-message, .error-message { text-align: center; font-size: 18px; margin-top: 20px; }
        .success-message { color: green; }
        .error-message { color: red; }
        .out-of-stock { color: red; font-weight: bold; }
        input[type=number] { width: 80px; margin-left: 10px; }
    </style>
</head>
<body>

<header>
    <div class="header-left">
        <a href="admin1.php">&#8592; Back</a>
    </div>
    <div class="header-center">Central Square - Admin Panel</div>
    <div class="header-right">
        <a href="index.php">Logout</a>
    </div>
</header>

<?php if (!empty($_SESSION['success_message'])): ?>
    <p class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
<?php endif; ?>

<form method="POST">
    <ul class="food-list">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()):
                $fid = $row['item_id'];
                $name = htmlspecialchars($row['item_name']);
                $price = $row['price'];
                $quantity = $row['quantity'];
                $stockStatus = $quantity < 5 ? "<span class='out-of-stock'>(Out of Stock)</span>" : "";
        ?>
        <li>
            <span><?php echo $name . " " . $stockStatus; ?></span>
            <div>
                ₹ <input type="number" name="food[<?php echo $fid; ?>][price]" value="<?php echo $price; ?>" step="0.01" required>
                Qty: <input type="number" name="food[<?php echo $fid; ?>][quantity]" value="<?php echo $quantity; ?>" required>
            </div>
        </li>
        <?php
            endwhile;
        } else {
            echo "<li>No food items found.</li>";
        }
        $conn->close();
        ?>
    </ul>
    <div class="submit-container">
        <button type="submit">Save Changes</button>
    </div>
</form>

</body>
</html>
