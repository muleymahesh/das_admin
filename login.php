<?php
include('server.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user exists in the user_master table
    $query = "SELECT * FROM user_master WHERE username = ? AND password = ? AND role_id = 5";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        session_start();
        // User exists, set session variables
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user;
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
    } else {
        // User does not exist, show error message
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #dc3545;

        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #dc3545;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color:rgb(190, 41, 56);
        }
        .login-container .links {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .login-container .links a {
            color: #dc3545;
            text-decoration: none;
        }
        .login-container .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="text-align: center;">Login</h2>
        <form action="#" method="post" style="padding: 0 20px;">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" style="margin-top: 20px;">Login</button>
            <?php if (!empty($error)): ?>
                <p style="color: red; text-align: center;"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
        <div class="links" style="margin-top: 20px;">
            <!-- <a href="signup.php">Sign Up</a> -->
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>