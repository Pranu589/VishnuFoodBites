<?php
session_start();
include 'db_config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Verify session token
$stmt = $conn->prepare('SELECT session_token, username FROM Users WHERE user_id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user || $user['session_token'] !== $_SESSION['session_token']) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

$username = htmlspecialchars($user['username']);
echo "Welcome Admin, {$username}! 🎉";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vishnu Food Bites</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
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

        .banner {
            position: relative;
            text-align: center;
        }

        .banner img {
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            margin-top: 30px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .button {
            padding: 10px 20px;
            font-size: 18px;
            color: white;
            background-color: #001f3d;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .button:hover {
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
        <a href="logout.php">LOGOUT</a>
        <a href="signup.php">Login</a>
    </div>
</header>

<div class="banner">
    <img src="https://t3.ftcdn.net/jpg/02/52/38/80/360_F_252388016_KjCnB9vglSCuUJAumCDNbmMzGdzPAucK.jpg" alt="Delicious Food">
</div>

<div class="button-container">
    <button class="button" onclick="navigateToPage('orderplaced.php')">Order Placed</button>
    <button class="button" onclick="navigateToPage('admin1.php')">Update</button>
    <button class="button" onclick="navigateToPage('accept_order.php')">Accepted Orders</button>
</div>

<script>
    function navigateToPage(page) {
        window.location.href = page;
    }
</script>

</body>
</html>