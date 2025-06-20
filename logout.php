<?php
session_start();
include 'db_config.php';

if (isset($_SESSION['user_id'])) {
    // Clear cart
    $stmt = $conn->prepare("DELETE FROM Cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    // Clear session token in Users table
    $stmt = $conn->prepare("UPDATE Users SET session_token = NULL WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - Vishnu Food Bites</title>
</head>
<body>
    <script>
        // Clear localStorage cart
        localStorage.removeItem('cart');
        // Redirect to index.php
        window.location.href = 'index.php';
    </script>
</body>
</html>
<?php $conn->close(); ?>