<?php
session_start();

$default_username = "admin";
$default_password = "password123"; // Easily hackable default password

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $default_username && $password === $default_password) {
        $_SESSION['user'] = $username;
        $_SESSION['role'] = 'Admin';
        header("Location: ../admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - HiveNova Medical</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #222;
            color: #eee;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .honeypot-container {
            background-color: #333;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px #00bfa5;
            text-align: center;
            width: 300px;
        }
        h1 {
            color: #00bfa5;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 1em;
        }
        input[type="submit"] {
            background-color: #00bfa5;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1em;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #008c7e;
        }
        p.note {
            margin-top: 20px;
            font-size: 0.9em;
            color: #aaa;
        }
        p.error {
            color: #ff4444;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="honeypot-container">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" autocomplete="off" />
            <input type="password" name="password" placeholder="Password" autocomplete="off" />
            <input type="submit" value="Login" />
        </form>
        <p class="note">Ctrl + U</p>
        <!-- <p></p>This is a honeypot login page. The real admin login is at <code>landing_login.php</code>.</p> -->
    </div>
</body>
</html>
