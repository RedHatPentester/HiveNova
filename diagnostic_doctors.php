<?php
// Diagnostic script to test fetching doctors from the staff table

$conn = new mysqli("localhost", "vulnuser", "vulnpassword", "hivenova");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT Name, Role, Department FROM staff";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo "<h1>All Staff Records</h1>";
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr><th>Name</th><th>Role</th><th>Department</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Role']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Department']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No staff records found.";
}

echo "<h2>Doctors Only</h2>";
$sql_doctors = "SELECT Name, Department FROM staff WHERE LOWER(TRIM(Role)) = 'doctor'";
$result_doctors = $conn->query($sql_doctors);

if (!$result_doctors) {
    die("Doctor query failed: " . $conn->error);
}

if ($result_doctors->num_rows > 0) {
    echo "<ul>";
    while ($doc = $result_doctors->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($doc['Name']) . " (" . htmlspecialchars($doc['Department']) . ")</li>";
    }
    echo "</ul>";
} else {
    echo "No doctors found.";
}

$conn->close();
?>
