<?php
session_start();
include_once("dbconnect.php");


if (isset($_POST['login'])) {
    $username = $_POST['user'];
    $password = $_POST['password'];

    
    $query = "SELECT * FROM user_account WHERE Username = '$username' AND Password = '$password'";
    $result = mysqli_query($conn, $query);

    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['username'] = $username;
        echo "<script>window.alert('Login successful!');</script>";
        header('Location: dashboard.php');
        exit(); 
    } else {
        echo "<script>window.alert('Login failed. Please check your credentials.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calbee's Cafe - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('coffee2.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

    
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 0;
        }

        .title-container {
            text-align: center;
            margin-top: -180px;
            margin-right: 80px;
            margin-bottom: 15px;
            z-index: 1;
            position: relative;
            margin-top: 38px;
        }

        
        .title-container h1 {
    		font-size: 4em;
    		color: #ffdb58;
    		font-family: 'Pacifico', cursive;
   			padding-bottom: 5px;
   			letter-spacing: 2px;
    		text-shadow: 0 0 10px rgba(255, 219, 88, 0.7), 0 0 20px rgba(255, 219, 88, 0.6);
   			animation: neon 1.5s ease-in-out infinite alternate;
    		margin-bottom: -25px;
}

        @keyframes neon {
            from {
                text-shadow: 0 0 10px rgba(255, 219, 88, 0.7), 0 0 20px rgba(255, 219, 88, 0.6), 0 0 30px rgba(255, 219, 88, 0.5);
            }
            to {
                text-shadow: 0 0 20px rgba(255, 219, 88, 0.9), 0 0 40px rgba(255, 219, 88, 0.8), 0 0 50px rgba(255, 219, 88, 1);
            }
        }

        .title-container p {
    		font-size: 1.2em;
    		color: #f8f9fa;
    		margin-right: 50px;
    		padding-bottom: 250px;
    		font-style: italic;
    		text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    		margin-top: 1px;
    		text-align: left;
}

        .container {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px 50px;
            max-width: 350px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(15px);
            text-align: center;
            z-index: 1;
            position: relative;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        label {
            font-size: 14px;
            color: #f8f9fa;
            display: block;
            margin-bottom: 5px;
            text-align: left;
        }

        
        .input-container {
            position: relative;
            width: 100%;
        }

        .input-container i {
            position: absolute;
            left: 15px;
            top: 62%;
            transform: translateY(-50%);
            color: #999;
        }

        input[type="text"], input[type="password"] {
            width: 75%;
            padding: 12px 40px;
            margin: 10px 0;
            border: none;
            border-radius: 25px;
            background-color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            color: #333;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        
        input[type="submit"] {
            background: linear-gradient(90deg, #ffdb58, #ffc107);
            color: #333;
            padding: 15px 20px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            width: 98%;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        input[type="submit"]:hover {
            background: linear-gradient(90deg, #ffc107, #ffdb58);
            transform: translateY(-3px);
        }

        .footer-text {
            text-align: center;
            color: #f8f9fa;
            margin-top: 10px;
            font-size: 14px;
        }

        a {
            color: #ffdb58;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="title-container">
        <h1>Calbee's Cafe & Diner</h1>
       <p>Inventory Management System!</p>
    </div>

    <div class="container">
        <form action="" method="POST">
            <div class="input-container">
                <i class="fas fa-user"></i>
                <label for="user">Username</label>
                <input type="text" id="user" name="user" required>
            </div>

            <div class="input-container">
                <i class="fas fa-lock"></i>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <input type="submit" value="Login" name="login">
        </form>

        <div class="footer-text">
            <p>Don't have an account? <a href="registration.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
