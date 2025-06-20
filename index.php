<?php
session_start();
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, password, role FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Generate session token
                $session_token = bin2hex(random_bytes(16));
                
                // Update session token in Users table
                $stmt = $conn->prepare("UPDATE Users SET session_token = ? WHERE user_id = ?");
                $stmt->bind_param("si", $session_token, $user['user_id']);
                $stmt->execute();
                $stmt->close();
                
                // Clear existing cart
                $stmt = $conn->prepare("DELETE FROM Cart WHERE user_id = ?");
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $stmt->close();
                
                // Set session variables
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['session_token'] = $session_token;
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: a1.php");
                } else {
                    header("Location: homepage.php");
                }
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with that username.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vishnu Food Bites</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #1e3a5f, #1e3a5f);
            background-size: cover;
            background-position: center;
            color: white;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 40px 60px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .login-container:hover {
            transform: scale(1.05);
        }

        h1 {
            font-family: 'Pacifico', cursive;
            color: #1e3a5f;
            font-size: 48px;
            margin-bottom: 30px;
        }

        .input-field {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            border: 2px solid #1e3a5f;
            border-radius: 5px;
            font-size: 16px;
            background-color: #fff;
        }

        .input-field:focus {
            outline: none;
            border-color: #1e3a5f;
        }

        .button {
            background-color: #1e3a5f;
            color: white;
            font-size: 18px;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        .button:hover {
            background-color: #16324a;
        }

        .footer {
            margin-top: 30px;
            color: #1e3a5f;
            font-size: 18px;
        }

        .footer a {
            color: #1e3a5f;
            text-decoration: none;
            font-weight: bold;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .role-toggle {
            margin-bottom: 20px;
            text-align: left;
        }

        .role-toggle label {
            margin-right: 20px;
            font-size: 18px;
            color: #1e3a5f;
            font-weight: bold;
        }

        .error {
            color: #d32f2f;
            font-size: 16px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 id="login-title">Login to Vishnu Food Bites</h1>

        <div class="role-toggle">
            <label>
                <input type="radio" name="role" value="admin" checked onclick="toggleRole()"> Admin
            </label>
            <label>
                <input type="radio" name="role" value="user" onclick="toggleRole()"> User
            </label>
        </div>

        <form method="POST" action="">
            <input type="text" name="username" class="input-field" placeholder="Username" required>
            <input type="password" name="password" class="input-field" placeholder="Password" required>
            <button type="submit" class="button">Login</button>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </form>

        <div class="footer">
            <p><b>Don't have an account? <a href="signup.php">Sign up here</a></b></p>
        </div>
    </div>

    <script>
        function toggleRole() {
            const role = document.querySelector('input[name="role"]:checked').value;
            const loginTitle = document.getElementById('login-title');
            loginTitle.textContent = role === 'admin' ? 'Admin Login' : 'User Login';
        }

        // Clear localStorage cart for new user
        localStorage.removeItem('cart');
    </script>
</body>
</html>
<?php $conn->close(); ?>