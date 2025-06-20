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

// Fetch food items for shop_id = 4
$shop_id = 4;
$sql = "SELECT item_id, item_name, item_image, price, stock_status FROM Food_Items WHERE shop_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $shop_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juicy Drinks - Vishnu Food Bites</title>
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
        main {
            margin-top: 60px;
        }
        .header h2 {
            margin: 0;
            font-size: 28px;
        }
        .header .search-bar {
            flex-grow: 1;
            display: flex;
            justify-content: center;
        }
        .header .search-bar input {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            width: 500px;
        }
        .nav-links {
            display: flex;
            gap: 18px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 20px;
        }
        .add-to-cart {
            background-color: #16324a;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 16px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            transform: translateY(-10px);
        }
        .added {
            background-color: green;
            color: black;
        }
        .tick {
            margin-left: 5px;
            font-size: 1.2em;
            color: black;
        }
        h1 {
            text-align: center;
            margin-top: 80px;
            font-size: 36px;
            color: #1e3a5f;
        }
        h3 {
            text-align: center;
            margin-top: 40px;
            font-size: 28px;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px 40px;
            justify-items: center;
            margin-top: 50px;
        }
        .product-card {
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            border-radius: 12px;
            overflow: hidden;
            text-align: center;
            padding: 20px;
            transition: transform 0.3s ease;
        }
        .product-card img:hover {
            transform: scale(1.2);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .product-card h3 {
            font-size: 20px;
            color: #333;
            padding: 10px 0;
            margin: 0;
        }
        .product-card p.price {
            font-size: 16px;
            color: green;
            padding: 5px 0;
        }
        .product-card p.availability {
            font-size: 18px;
            padding: 5px 0;
            color: #777;
        }
        .in-stock {
            color: green;
            animation: continuousPopUp 1s ease-in-out infinite;
        }
        .out-of-stock {
            color: red;
            animation: continuousPopUp 1s ease infinite;
        }
        @keyframes continuousPopUp {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        @media (max-width: 900px) {
            .container { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Juicy Drinks 🍹</h2>
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="Search for drinks...">
        </div>
        <div class="nav-links">
            <span>User ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
            <a href="homepage.php">Home</a>
            <a href="logout.php">Logout</a>
            <a href="cart.php">Cart 🛒</a>
        </div>
    </div>

    <h1>JUICY DRINKS</h1>
    <h3>Timings: 11:00AM - 7:00PM (Mon-Sat)</h3>

    <div class="container" id="product-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product-card" data-name="<?php echo strtolower(htmlspecialchars($row['item_name'])); ?>">
                <img src="<?php echo htmlspecialchars($row['item_image']); ?>" alt="<?php echo htmlspecialchars($row['item_name']); ?>">
                <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                <p class="price"><strong>₹<?php echo number_format($row['price'], 2); ?></strong></p>
                <p class="availability <?php echo $row['stock_status'] === 'in' ? 'in-stock' : 'out-of-stock'; ?>">
                    <?php echo $row['stock_status'] === 'in' ? 'In Stock' : 'Out of Stock'; ?>
                </p>
                <button class="add-to-cart"
                        data-id="<?php echo $row['item_id']; ?>"
                        data-name="<?php echo htmlspecialchars($row['item_name']); ?>"
                        data-price="<?php echo $row['price']; ?>"
                        data-shop="<?php echo $shop_id; ?>"
                        <?php echo $row['stock_status'] === 'in' ? '' : 'disabled style="background-color: #ccc; color: #666; cursor: not-allowed;"'; ?>>
                    <?php echo $row['stock_status'] === 'in' ? 'Add to Cart' : 'Out of Stock'; ?>
                </button>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function updateButtonState(button, isAdded, inStock) {
            if (!inStock) {
                button.disabled = true;
                button.textContent = "Out of Stock";
                button.style.backgroundColor = "#ccc";
                button.style.color = "#666";
                button.style.cursor = "not-allowed";
                button.classList.remove('added');
            } else if (isAdded) {
                button.disabled = false;
                button.classList.add('added');
                button.textContent = "Added to Cart ✔";
                button.style.backgroundColor = "green";
                button.style.color = "black";
                button.style.cursor = "pointer";
            } else {
                button.disabled = false;
                button.classList.remove('added');
                button.textContent = "Add to Cart";
                button.style.backgroundColor = "#16324a";
                button.style.color = "white";
                button.style.cursor = "pointer";
            }
        }

        document.querySelectorAll('.add-to-cart').forEach(button => {
            const itemName = button.getAttribute('data-name');
            const itemPrice = button.getAttribute('data-price');
            const itemId = button.getAttribute('data-id');
            const shopId = button.getAttribute('data-shop');
            const inStock = !button.disabled;

            let isAdded = cart.some(item => item.name === itemName);
            updateButtonState(button, isAdded, inStock);

            button.addEventListener('click', () => {
                if (button.disabled) return;

                const index = cart.findIndex(item => item.name === itemName);

                if (index === -1) {
                    cart.push({ name: itemName, price: itemPrice, shop_id: shopId, item_id: itemId });
                    updateButtonState(button, true, true);

                    fetch('addtocart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            item_name: itemName,
                            item_price: itemPrice,
                            item_id: itemId,
                            shop_id: shopId,
                            user_id: userId,
                            quantity: 1
                        })
                    })
                    .then(res => res.text())
                    .then(data => console.log("Server response:", data))
                    .catch(err => console.error("Fetch error:", err));
                } else {
                    cart.splice(index, 1);
                    updateButtonState(button, false, true);

                    fetch('removefromcart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            item_name: itemName,
                            user_id: userId
                        })
                    })
                    .then(res => res.text())
                    .then(data => console.log("Removed:", data))
                    .catch(err => console.error("Remove error:", err));
                }

                localStorage.setItem('cart', JSON.stringify(cart));
            });
        });

        document.getElementById('search-input').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.getAttribute('data-name');
                card.style.display = name.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
<?php $stmt->close(); $conn->close(); ?>