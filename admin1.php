<?php
session_start();

// DB connection
$servername = "localhost";
$username = "root"; // update if needed
$password = "";     // update if needed
$dbname = "vishnu_food_bites";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vishnu Food Bytes - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #001f3d;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            font-size: 24px;
        }
        .header-left, .header-right {
            display: flex;
            gap: 20px;
        }
        .header-left a, .header-right a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }
        .header-left a:hover, .header-right a:hover {
            color: #ff6347;
        }
        .header-center {
            flex-grow: 1;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
        }
        .shop-list {
            width: 100%;
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .shop-list li {
            background-color: #fff;
            border: 1px solid #ddd;
            margin: 10px 0;
            padding: 30px;
            border-radius: 5px;
            font-size: 25px;
            text-align: center;
            width: 80%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .shop-list li:hover {
            background-color: #e6f0ff;
        }
        .arrow {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-left: 5px solid #001f3d;
            border-top: 5px solid #001f3d;
            transform: rotate(135deg);
        }
        .arrow:hover {
            border-left-color: #000;
            border-top-color: #000;
        }
    </style>
</head>
<body>
<header>
    <div class="header-left">
        <a href="a1.php">&#8592; Back</a>
    </div>
    <div class="header-center">
        Vishnu Food Bites - Admin Panel
    </div>
    <div class="header-right">
        <a href="index.php">Logout</a>
    </div>
</header>

<ul class="shop-list">
<?php
$sql = "SELECT shop_id, shop_name FROM Shops";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $shopId = $row['shop_id'];
        $shopName = htmlspecialchars($row['shop_name']);
        echo "
            <li onclick=\"window.location.href='adminfood$shopId.php'\">
                <span>$shopName</span>
                <span class='arrow'></span>
            </li>
        ";
    }
} else {
    echo "<li>No shops found 😔</li>";
}
$conn->close();
?>
</ul>

</body>
</html>
