<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Stall - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #1f366d, #234673);
            background-size: cover;
            background-position: right;
        }

        .container {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 50px 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            margin-right: 530px;
            width: 100%;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: scale(1.03);
        }

        h1 {
            font-family: 'Pacifico', cursive;
            color: #1e3a5f;
            font-size: 46px;
            margin-bottom: 30px;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .button {
            background-color: #1e3a5f;
            color: white;
            font-size: 18px;
            padding: 15px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 45%;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #162d52;
        }

        .footer p {
            padding-top: 20px;
            font-size: 20px;
            color: #1e3a5f;
            transition: transform 0.3s ease;
        }

        .footer p:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome Foodies!</h1>
        
        <div class="button-container">
            <button class="button" onclick="window.location.href='login.php'">Login</button>
            <button class="button" onclick="window.location.href='signup.php'">Signup</button>
        </div>

        <div class="footer">
            <p><b>Explore the best food stalls around you!</b></p>
        </div>
    </div>
</body>
</html>
