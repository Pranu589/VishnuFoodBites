<?php
session_start();
include 'db_config.php';

// Check if user is logged in and session is valid
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
    header("Location: index.php");
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
    header("Location: index.php");
    exit();
}

// Handle clear cart request
if (isset($_POST['clear_cart'])) {
    $stmt = $conn->prepare("DELETE FROM Cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Cart cleared successfully!";
    header("Location: cart.php");
    exit();
}

// Fetch cart items
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT c.item_id, c.quantity, c.item_name, c.item_price, s.shop_name 
                        FROM Cart c 
                        JOIN Food_Items f ON c.item_id = f.item_id
                        JOIN Shops s ON f.shop_id = s.shop_id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Vishnu Food Bites</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #1e3a5f;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            box-sizing: border-box;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .header h2 {
            margin: 0;
            font-size: 28px;
        }
        .nav-links {
            display: flex;
            gap: 18px;
            align-items: center;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 20px;
        }
        h1 {
            text-align: center;
            margin-top: 100px;
            font-size: 36px;
            color: #1e3a5f;
        }
        .cart-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #1e3a5f;
            color: white;
        }
        .quantity-controls button {
            background-color: #16324a;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .quantity-controls button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .total {
            text-align: right;
            font-size: 20px;
            margin-top: 20px;
        }
        .checkout-btn, .clear-cart-btn {
            background-color: #16324a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            margin: 10px;
            display: inline-block;
        }
        .clear-cart-btn {
            background-color: #dc3545;
        }
        .empty-cart {
            text-align: center;
            font-size: 24px;
            color: #777;
            margin-top: 50px;
        }
        .message, .error {
            text-align: center;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Your Cart 🛒</h2>
        <div class="nav-links">
            <span>User ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
            <a href="homepage.php">Home</a>
            <a href="fooditems3.php">Food Items</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h1>Your Cart</h1>
    <div class="cart-container">
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">Your cart is empty.</div>
        <?php else: ?>
            <form method="post">
                <button type="submit" name="clear_cart" class="clear-cart-btn">Clear Cart</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Shop</th>
                        <th>Item</th>
                        <th>Price (₹)</th>
                        <th>Quantity</th>
                        <th>Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total = 0;
                    foreach ($cart_items as $item): 
                        $item_total = $item['item_price'] * $item['quantity'];
                        $grand_total += $item_total;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['shop_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo number_format($item['item_price'], 2); ?></td>
                            <td class="quantity-controls">
                                <button onclick="updateQuantity('<?php echo htmlspecialchars($item['item_id']); ?>', '<?php echo htmlspecialchars($item['item_name']); ?>', -1)" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
                                <span><?php echo $item['quantity']; ?></span>
                                <button onclick="updateQuantity('<?php echo htmlspecialchars($item['item_id']); ?>', '<?php echo htmlspecialchars($item['item_name']); ?>', 1)">+</button>
                            </td>
                            <td><?php echo number_format($item_total, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total">Grand Total: ₹<?php echo number_format($grand_total, 2); ?></div>
            <button class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</button>
        <?php endif; ?>
    </div>

    <script>
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
        const cartKey = `cart_user_${userId}`;

        // Clear stale localStorage keys
        for (let key in localStorage) {
            if (key.startsWith('cart_user_') && key !== cartKey || key === 'cart') {
                localStorage.removeItem(key);
            }
        }

        // Sync localStorage with server-side cart
        fetch('get_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                localStorage.setItem(cartKey, JSON.stringify(data.cart || []));
            }
        })
        .catch(err => console.error('Error syncing cart:', err));

        async function updateQuantity(itemId, itemName, change) {
            const row = event.target.closest('tr');
            const quantitySpan = row.querySelector('.quantity-controls span');
            const currentQuantity = parseInt(quantitySpan.textContent);
            const newQuantity = currentQuantity + change;

            if (newQuantity < 1) {
                if (confirm('Remove this item from cart?')) {
                    try {
                        const response = await fetch('removefromcart.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ item_name: itemName, user_id: userId })
                        });
                        const data = await response.json();
                        if (data.success) {
                            row.remove();
                            updateGrandTotal();
                            updateLocalStorage(itemName, true);
                            alert(data.message);
                        } else {
                            alert(data.message);
                        }
                    } catch (error) {
                        alert('Error removing item: ' + error.message);
                    }
                }
                return;
            }

            try {
                const response = await fetch('addtocart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        item_id: itemId,
                        user_id: userId,
                        item_name: itemName,
                        item_price: parseFloat(row.querySelector('td:nth-child(3)').textContent.replace(',', '')),
                        quantity: change
                    })
                });
                const data = await response.json();
                if (data.success) {
                    quantitySpan.textContent = newQuantity;
                    const price = parseFloat(row.querySelector('td:nth-child(3)').textContent.replace(',', ''));
                    row.querySelector('td:nth-child(5)').textContent = (price * newQuantity).toFixed(2);
                    updateGrandTotal();
                    row.querySelector('button:first-child').disabled = newQuantity <= 1;
                    updateLocalStorage(itemName, false);
                    alert(data.message);
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error updating quantity: ' + error.message);
            }
        }

        async function proceedToCheckout() {
            try {
                const response = await fetch('save_orders.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                });
                const data = await response.json();
                if (data.success) {
                    localStorage.setItem(cartKey, '[]');
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error processing order: ' + error.message);
            }
        }

        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('tbody tr').forEach(row => {
                total += parseFloat(row.querySelector('td:nth-child(5)').textContent.replace(',', ''));
            });
            document.querySelector('.total').textContent = 'Grand Total: ₹' + total.toFixed(2);
            if (total === 0) {
                document.querySelector('.cart-container').innerHTML = '<div class="empty-cart">Your cart is empty.</div>';
                localStorage.setItem(cartKey, '[]');
            }
        }

        function updateLocalStorage(itemName, remove = false) {
            let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
            if (remove) {
                cart = cart.filter(item => item.name !== itemName);
            }
            localStorage.setItem(cartKey, JSON.stringify(cart));
        }

        <?php if (isset($_POST['clear_cart'])): ?>
            localStorage.setItem(cartKey, '[]');
        <?php endif; ?>
    </script>
</body>
</html>