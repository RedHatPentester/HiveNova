<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Nurse') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nurse Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
</head>
<body>
<div class="dashboard-container">
    <h1>Welcome, Nurse <?php echo htmlspecialchars($_SESSION['user']); ?></h1>
    <p>This is the Nurse Dashboard.</p>
    <p>Here you can manage patient care, view schedules, and update patient notes.</p>
    <a href="logout.php">Logout</a>
</div>
</body>
</html>
