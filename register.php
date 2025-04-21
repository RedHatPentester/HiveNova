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
<html>
<head>
    <title>HiveNova Medical Registration</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
</head>
<body>
<div class="page-container">
    <div class="left-panel">
        <h1>Join HiveNova Medical Center</h1>
        <p>Create your account to access appointments, records, and more.</p>
        <div class="hospital-logo"></div>
    </div>
    <div class="right-panel">
        <div class="login-container">
            <h2>Register</h2>
            <?php if ($error): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif ($success): ?>
                <p style="color: #00bfa5;"><?php echo $success; ?></p>
            <?php endif; ?>
            <form method="post" class="login-form">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required /><br/>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required /><br/>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required /><br/>
                <input type="submit" value="Register" />
            </form>
            <p style="margin-top: 15px; color: #00bfa5;">Already have an account? <a href="index.php" style="color: #00f2fe; text-decoration: underline;">Login</a></p>
        </div>
    </div>
</div>
<footer style="text-align:center; padding: 15px 0; color: #00bfa5; font-size: 0.9em; background: rgba(0,0,0,0.3); position: fixed; width: 100%; bottom: 0;">
    &copy; Property of Hive Consult
</footer>
</body>
</html>
