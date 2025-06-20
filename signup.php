<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']); // Not used in table now, maybe useful later
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo "<script>alert('All fields are required!');</script>";
    } elseif ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $conn = new mysqli("localhost", "root", "", "vishnu_food_bites",3306);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if username already exists
        $checkQuery = "SELECT * FROM Users WHERE username = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Username already taken!');</script>";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertQuery = "INSERT INTO Users (username, password, role) VALUES (?, ?, 'user')";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ss", $username, $hashedPassword);

            if ($stmt->execute()) {
                echo "<script>
                    alert('Signup successful!');
                    window.location.href = 'index.php';
                </script>";
            } else {
                echo "<script>alert('Signup failed. Try again later!');</script>";
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - Food Stall</title>
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
            color: white;
        }

        .signup-container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 40px 60px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            text-align: center;
        }

        .signup-container:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }

        h1 {
            font-family: 'Pacifico', cursive;
            color: #1e3a5f;
            font-size: 45px;
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
            border-radius: 5px;
            cursor: pointer;
            width: 106%;
            font-weight: bold;
        }

        .button:hover {
            background-color: #163050;
        }

        .footer {
            margin-top: 30px;
            color: #1e3a5f;
            font-size: 14px;
        }

        .footer a {
            color: #1e3a5f;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h1>Signup to Food Stall</h1>

    <form method="POST" action="">
        <input type="text" name="username" class="input-field" placeholder="Username" required>
        <input type="email" name="email" class="input-field" placeholder="Email" required>
        <input type="password" name="password" class="input-field" placeholder="Password" required>
        <input type="password" name="confirmPassword" class="input-field" placeholder="Confirm Password" required>
        <button type="submit" class="button">Sign Up</button>
    </form>

    <div class="footer">
        <p>Already have an account? <a href="index.php">Login here</a></p>
    </div>
</div>

</body>
</html>
