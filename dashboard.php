<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}
echo "<h2>Welcome, " . $_SESSION["user"] . "!</h2>";
$conn = new mysqli("localhost", "root", "", "hivenova");
$sql = "SELECT * FROM patients";
$result = $conn->query($sql);
echo "<h3>Patients List</h3><ul>";
while($row = $result->fetch_assoc()) {
    echo "<li>" . $row["Name"] . " - " . $row["Illness"] . "</li>";
}
echo "</ul>";
?>
<a href='logout.php'>Logout</a>
