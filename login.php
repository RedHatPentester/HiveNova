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