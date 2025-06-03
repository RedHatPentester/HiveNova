<?php
session_start();
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "vulnuser", "vulnpassword", "hivenova");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $username = $_POST['username'];
    $password = $_POST['password'];
    $login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'user';

    $staff_roles = ['doctor', 'nurse', 'receptionist', 'technician'];

    if (in_array($login_type, $staff_roles)) {
        // Simple CAPTCHA for staff login to prevent brute force
        if (!isset($_POST['captcha']) || strtolower($_POST['captcha']) !== 'blue') {
            $error = "CAPTCHA failed. Please enter the color 'blue'.";
        } else {
            $sql = "SELECT * FROM staff WHERE username = '$username' AND password = '$password' AND LOWER(role) = '" . strtolower($login_type) . "'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $_SESSION["user"] = $username;
                $_SESSION["role"] = ucfirst($login_type);
                if (strtolower($login_type) === 'nurse') {
                    header("Location: nurse_dashboard.php");
                } else {
                    header("Location: " . $login_type . "_dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid " . ucfirst($login_type) . " login!";
            }
        }
    } else if ($login_type === 'user') {
        // Vulnerable user login but with delay to slow brute force
        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $_SESSION["user"] = $username;
            $_SESSION["role"] = "User";
            header("Location: user_dashboard.php");
            exit();
        } else {
            $error = "Invalid user login!";
            // Delay to slow brute force attacks
            sleep(2);
        }
    } else {
        $error = "Invalid login type!";
    }
}
?>
<!DOCTYPE html>
<<<<<<< HEAD
<html>

<head>
    <title>HiveNova Medical Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
</head>

<body>
    <div class="page-container">
        <div class="left-panel">
            <h1>Welcome to HiveNova Medical Center</h1>
            <p>Your health, our priority.</p>
            <p>Access your appointments, records, and more with ease.</p>
            <div class="hospital-logo"></div>
        </div>
        <div class="right-panel">
            <div class="login-container">
                <h2>Login to HiveNova Medical Center</h2>
                <form method="post" class="login-form">
                    <label for="login_type">Login as:</label>
                    <select name="login_type" id="login_type">
                        <option value="user" <?php if (isset($_POST['login_type']) && $_POST['login_type'] == 'user')
                            echo 'selected'; ?>>User</option>
                        <option value="doctor" <?php if (isset($_POST['login_type']) && $_POST['login_type'] == 'doctor')
                            echo 'selected'; ?>>Doctor</option>
                        <option value="nurse" <?php if (isset($_POST['login_type']) && $_POST['login_type'] == 'nurse')
                            echo 'selected'; ?>>Nurse</option>
                        <option value="technician" <?php if (isset($_POST['login_type']) && $_POST['login_type'] == 'technician')
                            echo 'selected'; ?>>Technician</option>
                    </select><br /><br />
                    Username: <input type="text" name="username" required /><br />
                    Password: <input type="text" name="password" required /><br />
                    <?php if (isset($_POST['login_type']) && in_array($_POST['login_type'], $staff_roles)): ?>
                        <label for="captcha">What color is the sky on a clear day? (type 'blue')</label><br />
                        <input type="text" name="captcha" id="captcha" required /><br />
                        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <input type="submit" value="Login" />
                </form>
                <p style="margin-top: 15px; color: #00bfa5  
                ;">Don't have an account? <a href="register.php"
                        style="color: #00f2fe; text-decoration: underline;">Register</a></p>
                <?php if (!empty($error) && (!isset($_POST['login_type']) || !in_array($_POST['login_type'], $staff_roles))): ?>
                    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <footer
        style="text-align:center; padding: 15px 0; color: #00bfa5; font-size: 0.9em; background: rgba(0,0,0,0.3); position: fixed; width: 100%; bottom: 0;">
        &copy; Property of Hive Consult
    </footer>
</body>

</html>
=======
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HiveNova Medical Login</title>
    <style>
        /* Reset and base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

    body {
        background: url('login.png') no-repeat center center fixed;
        background-size: cover;
        height: 100vh;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        color: #004d40;
        padding-right: 50px;
    }


        .login-wrapper {
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

        .login-wrapper:hover {
            box-shadow: 0 12px 40px rgba(0, 191, 165, 0.5);
        }

        .login-wrapper h2 {
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

        select,
        input[type="text"],
        input[type="password"] {
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 2px solid #00bfa5;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        select:focus,
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #007c91;
            box-shadow: 0 0 8px #007c91;
        }

        .show-password {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-weight: 600;
            color: #004d40;
            cursor: pointer;
            user-select: none;
        }

        .show-password input {
            margin-right: 10px;
            cursor: pointer;
        }

        .btn-login {
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

        .btn-login:hover {
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

        .register-link {
            margin-top: 20px;
            text-align: center;
            font-weight: 600;
            color: #007c91;
        }

        .register-link a {
            color: #00bfa5;
            text-decoration: underline;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #007c91;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-wrapper {
                width: 90%;
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper" role="main" aria-labelledby="loginTitle">
        <h2 id="loginTitle">Login to HiveNova Medical Center</h2>
        <form method="post" class="login-form" aria-describedby="loginInstructions">
            <label for="login_type">Login as:</label>
            <select name="login_type" id="login_type" aria-required="true">
                <option value="user" <?php if (isset($_POST['login_type']) && $_POST['login_type'] == 'user')
                    echo 'selected'; ?>>User</option>
                <option value="doctor" <?php if (isset($_POST['login_type']) && $_POST['login_type'] == 'doctor')
                    echo 'selected'; ?>>Doctor</option>
                <option value="nurse" <?php if (isset($_POST['login_type']) && $_POST['login_type'] == 'nurse')
                    echo 'selected'; ?>>Nurse</option>
                <option value="technician" <?php if (isset($_POST['login_type']) && $_POST['login_type'] == 'technician')
                    echo 'selected'; ?>>Technician</option>
            </select>

            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required aria-required="true" />

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required aria-required="true" />

            <label class="show-password" for="showPassword">
                <input type="checkbox" id="showPassword" aria-describedby="showPasswordDesc" />
                Show Password
            </label>

            <?php if (isset($_POST['login_type']) && in_array($_POST['login_type'], $staff_roles)): ?>
                <label for="captcha">What color is the sky on a clear day? (type 'blue')</label>
                <input type="text" name="captcha" id="captcha" required aria-required="true" />
                <p class="message error" role="alert"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <input type="submit" value="Login" class="btn-login" />

            <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>

            <?php if (!empty($error) && (!isset($_POST['login_type']) || !in_array($_POST['login_type'], $staff_roles))): ?>
                <p class="message error" role="alert"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (!empty($success)): ?>
                <p class="message success" role="alert"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('showPassword').addEventListener('change', function() {
                var pwd = document.getElementById('password');
                if (this.checked) {
                    pwd.type = 'text';
                } else {
                    pwd.type = 'password';
                }
            });
        });
    </script>
</body>

</html>
>>>>>>> db2302c (Fixed some Issues)
