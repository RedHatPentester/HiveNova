<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Admin') {
    header("Location: normal_login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "vulnuser", "vulnpassword", "hivenova");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch patient data
$patients = $conn->query("SELECT * FROM patient_records");

// Fetch staff data
$staff = $conn->query("SELECT * FROM staff");

// Fetch appointment schedules
$appointments = $conn->query("SELECT * FROM appointment_schedules");

// Fetch lab assignments
$lab_assignments = $conn->query("SELECT * FROM lab_assignments");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HiveNova Medical</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        h1, h2, h3 {
            color: #333;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #007c91;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        .empty-message {
            text-align: center;
            color: #888;
            font-style: italic;
        }
        select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .logout {
            margin-top: 20px;
            text-align: center;
        }
        .logout a {
            padding: 10px 20px;
            background-color: #007c91;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .logout a:hover {
            background-color: #005f6b;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <h1>Welcome, Admin <?php echo $_SESSION['user']; ?></h1>

    <h2>Patient Management</h2>
    <div class="search-bar">
        <input type="text" placeholder="Search patients..." onkeyup="filterTable('patientTable', this.value)">
    </div>
    <table id="patientTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Contact</th>
                <th>Medical History</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $patients->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo $row['contact']; ?></td>
                    <td><?php echo $row['medical_history']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Staff Management</h2>
    <h3>Staff Scheduling</h3>
    <div class="search-bar">
        <input type="text" placeholder="Search staff..." onkeyup="filterTable('staffTable', this.value)">
    </div>
    <?php if ($staff && $staff->num_rows > 0): ?>
        <table id="staffTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Shift</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $staff->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['UUID']); ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Role']); ?></td>
                        <td><?php echo htmlspecialchars($row['Department']); ?></td>
                        <td>
                            <select>
                                <option value="Morning">Morning</option>
                                <option value="Afternoon">Afternoon</option>
                                <option value="Night">Night</option>
                            </select>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="empty-message">No staff data available.</p>
    <?php endif; ?>

    <h2>Operational Metrics</h2>
    <h3>Appointment Schedules</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient Name</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $appointments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['doctor']; ?></td>
                    <td><?php echo $row['appointment_date']; ?></td>
                    <td><?php echo $row['appointment_time']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3>Lab Assignments</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <!-- <th>Patient Name</th> -->
                <th>Lab Test</th>
                <th>Assigned By</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $lab_assignments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <!-- <td><?php echo $row['patient_id']; ?></td> -->
                    <td><?php echo $row['lab_test']; ?></td>
                    <td><?php echo $row['assigned_by']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <p class="logout"><a href="logout.php">Logout</a></p>
</div>

<script>
    function filterTable(tableId, searchValue) {
        const table = document.getElementById(tableId);
        const rows = table.getElementsByTagName('tr');
        searchValue = searchValue.toLowerCase();

        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j].innerText.toLowerCase().includes(searchValue)) {
                    match = true;
                    break;
                }
            }

            rows[i].style.display = match ? '' : 'none';
        }
    }
</script>
</body>
</html>
