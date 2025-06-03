<?php
session_start();
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "vulnuser", "vulnpassword", "hivenova");
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = "user";

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } else if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            // Insert new user (Note: Password stored in plain text, should be hashed in production)
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $role);
            if ($stmt->execute()) {
                $success = "Registration successful! You can now <a href='index.php'>login</a>.";
            } else {
                $error = "Error during registration. Please try again.";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HiveNova Medical Registration</title>
    <style>
        /* Reset and base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #00bfa5 0%, #ffffff 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #004d40;
        }

        .register-wrapper {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 191, 165, 0.3);
            width: 400px;
            max-width: 90%;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow 0.3s ease;
        }

        .register-wrapper:hover {
            box-shadow: 0 12px 40px rgba(0, 191, 165, 0.5);
        }

        .register-wrapper h2 {
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 2rem;
            color: #007c91;
            text-align: center;
        }

        form {
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #004d40;
        }

        input[type="text"],
        input[type="password"] {
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 2px solid #00bfa5;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #007c91;
            box-shadow: 0 0 8px #007c91;
        }

        .btn-register {
            background: #00bfa5;
            color: white;
            padding: 14px 0;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-register:hover {
            background: #007c91;
            box-shadow: 0 0 15px #007c91;
        }

        .message {
            margin-top: 15px;
            text-align: center;
            font-weight: 600;
        }

        .error {
            color: #ff4d4d;
        }

        .success {
            color: #00bfa5;
        }

        .login-link {
            margin-top: 20px;
            text-align: center;
            font-weight: 600;
            color: #007c91;
        }

        .login-link a {
            color: #00bfa5;
            text-decoration: underline;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #007c91;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .register-wrapper {
                width: 90%;
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="register-wrapper" role="main" aria-labelledby="registerTitle">
        <h2 id="registerTitle">Join HiveNova Medical Center</h2>
        <?php if ($error): ?>
            <p class="message error" role="alert"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($success): ?>
            <p class="message success" role="alert"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="post" class="register-form" aria-describedby="registerInstructions">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required aria-required="true" />

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required aria-required="true" />

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required aria-required="true" />

            <input type="submit" value="Register" class="btn-register" />
        </form>
        <p class="login-link">Already have an account? <a href="index.php">Login</a></p>
    </div>
</body>

</html>
