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

// Fetch all shops
$sql = "SELECT * FROM Shops";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vishnu Food Bites</title>
    <link rel="stylesheet" href="styles.css">
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
            overflow: hidden;
        }
        a {
            text-decoration: none;
            color: inherit;
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
        .header-center {
            flex: 1;
            text-align: center;
        }
        .header-center input {
            padding: 10px 20px;
            font-size: 16px;
            border: 2px solid #1e3a5f;
            border-radius: 6px;
            width: 500px;
        }
        .header-right {
            display: flex;
            align-items: center;
        }
        .header-right a {
            color: white;
            font-size: 18px;
            font-weight: bold;
            margin-left: 35px;
        }
        main {
            display: flex;
            gap: 40px;
            justify-content: center;
            align-items: center;
            margin: auto;
            flex-wrap: wrap;
            padding-top: 5px;
        }
        .stall {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: white;
            border: 1px solid #e4d8b7;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 320px;
            height: 380px;
            gap: 20px;
        }
        .stall:hover {
            transform: scale(1.05);
        }
        .stall img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .stall-details {
            padding: 10px;
            font-size: 18px;
            color: #1e3a5f;
            font-weight: bold;
            text-align: center;
        }
        #no-results {
            display: none;
            text-align: center;
            font-size: 20px;
            color: #ee4747;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <h1>Vishnu Food Bites</h1>
        </div>
        <div class="header-center">
            <input type="text" id="search-bar" placeholder="Search..." onkeyup="searchStalls()">
        </div>
        <div class="header-right">
            <span>User ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
            <a href="logout.php">LOGOUT</a>
            <a href="signup.php">SIGNUP</a>
            <a href="cart.php" class="cart-icon">CART 🛒</a>
            <a href="users_ordered_items.php">ORDERED ITEMS</a>
        </div>
    </header>

    <main id="stall-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($stall = $result->fetch_assoc()): ?>
                <a href="fooditems<?php echo $stall['shop_id']; ?>.php">
                    <div class="stall" data-name="<?php echo strtoupper(htmlspecialchars($stall['shop_name'])); ?>">
                        <img src="<?php echo htmlspecialchars($stall['shop_image']); ?>" alt="<?php echo htmlspecialchars($stall['shop_name']); ?>">
                        <div class="stall-details"><?php echo htmlspecialchars($stall['shop_name']); ?></div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No shops found 😢</p>
        <?php endif; ?>
    </main>

    <div id="no-results">No results found.</div>

    <script>
        function searchStalls() {
            const searchInput = document.getElementById('search-bar').value.toUpperCase();
            const stalls = document.querySelectorAll('.stall');
            const noResultsMessage = document.getElementById('no-results');
            let hasResults = false;

            stalls.forEach(stall => {
                const stallName = stall.getAttribute('data-name');
                if (stallName.includes(searchInput)) {
                    stall.style.display = 'flex';
                    hasResults = true;
                } else {
                    stall.style.display = 'none';
                }
            });

            noResultsMessage.style.display = hasResults ? 'none' : 'block';
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>