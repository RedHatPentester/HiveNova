<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Technician') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "vulnuser", "vulnpassword", "hivenova");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assign_message = '';

// Handle lab test assignment with access control and prepared statement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_id']) && isset($_POST['lab_test'])) {
    if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Technician') {
        header("HTTP/1.1 403 Forbidden");
        exit("Access denied.");
    }

    $assigned_patient = $_POST['patient_id'];
    $assigned_test = $_POST['lab_test'];

    // Basic input validation
    if (empty($assigned_patient) || empty($assigned_test)) {
        $assign_message = "Patient and lab test must be selected.";
    } else {
        $stmt = $conn->prepare("INSERT INTO lab_assignments (patient_id, lab_test, assigned_by, assigned_at) VALUES (?, ?, ?, NOW())");
        $assigned_by = $_SESSION['user'];
        $stmt->bind_param("iss", $assigned_patient, $assigned_test, $assigned_by);
        if ($stmt->execute()) {
            $assign_message = "Lab test '" . htmlspecialchars($assigned_test) . "' assigned to patient ID " . htmlspecialchars($assigned_patient) . ".";
        } else {
            $assign_message = "Error assigning lab test: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch users from users table with role 'user' to list all users who create accounts
$patients = [];
$patient_result = $conn->query("SELECT id, username FROM users WHERE LOWER(TRIM(role)) = 'user'");
if ($patient_result && $patient_result->num_rows > 0) {
    while ($row = $patient_result->fetch_assoc()) {
        $patients[] = $row;
    }
}

// Fetch distinct lab tests from database if available, else fallback
$lab_tests = [];
try {
    $lab_test_result = $conn->query("SELECT DISTINCT lab_test_name FROM lab_tests");
    if ($lab_test_result !== false && $lab_test_result->num_rows > 0) {
        while ($row = $lab_test_result->fetch_assoc()) {
            $lab_tests[] = $row['lab_test_name'];
        }
    } else {
        // Fallback lab tests
        $lab_tests = ['Blood Test', 'X-Ray', 'MRI', 'Urine Test'];
    }
} catch (mysqli_sql_exception $e) {
    // Fallback lab tests if table does not exist or query fails
    $lab_tests = ['Blood Test', 'X-Ray', 'MRI', 'Urine Test'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_assignment_id'])) {
    $delete_id = intval($_POST['delete_assignment_id']);
    $del_stmt = $conn->prepare("DELETE FROM lab_assignments WHERE id = ?");
    $del_stmt->bind_param("i", $delete_id);
    $del_stmt->execute();
    $del_stmt->close();
    header("Location: technician_dashboard.php");
    exit();
}

// Fetch assigned lab tests to display with patient username from users table
$assignments = [];
$assignment_result = $conn->query("SELECT la.id, u.username as patient_name, la.lab_test, la.assigned_by, la.assigned_at FROM lab_assignments la JOIN users u ON la.patient_id = u.id ORDER BY la.assigned_at DESC");
if ($assignment_result && $assignment_result->num_rows > 0) {
    while ($row = $assignment_result->fetch_assoc()) {
        $assignments[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lab Technician Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
    <style>
        .dashboard-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px 40px;
            background: #ffffffcc;
            border-radius: 15px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        h1 {
            color: #007c91;
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 2.4rem;
            letter-spacing: 0.05em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 1rem;
        }
        th, td {
            padding: 14px 18px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #00bfa5;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        tr:hover {
            background-color: #f0f9f8;
            transition: background-color 0.3s ease;
        }
        .assign-message {
            margin-top: 15px;
            font-weight: 600;
            color: green;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 28px;
            background-color: #00bfa5;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 6px 15px rgba(0, 191, 165, 0.4);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #008c7e;
            box-shadow: 0 8px 20px rgba(0, 140, 126, 0.6);
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <h1>Welcome, Lab Technician <?php echo htmlspecialchars($_SESSION['user']); ?></h1>

    <h2>Assign Lab Tests to Patients</h2>
    <?php if (!empty($assign_message)): ?>
        <p class="assign-message"><?php echo $assign_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="patient_id">Select Patient:</label>
        <select name="patient_id" id="patient_id" required>
            <?php foreach ($patients as $patient): ?>
        <option value="<?php echo $patient['id']; ?>"><?php echo htmlspecialchars($patient['username']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="lab_test">Select Lab Test:</label>
        <select name="lab_test" id="lab_test" required>
            <?php foreach ($lab_tests as $test): ?>
                <option value="<?php echo htmlspecialchars($test); ?>"><?php echo htmlspecialchars($test); ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Assign Lab Test" />
    </form>

    <h2>Assigned Lab Tests</h2>
    <?php if (!empty($assignments)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Lab Test</th>
                    <th>Assigned By</th>
                    <th>Assigned At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['id']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['lab_test']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['assigned_by']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['assigned_at']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                <input type="hidden" name="delete_assignment_id" value="<?php echo htmlspecialchars($assignment['id']); ?>" />
                                <button type="submit" style="background-color: #ff4d4d; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No lab tests assigned yet.</p>
    <?php endif; ?>

    <h2>Upload Lab Results</h2>
    <form method="POST" enctype="multipart/form-data" action="upload.php">
        <input type="file" name="patient_file" required />
        <input type="submit" value="Upload Lab Result" />
    </form>

    <a href="logout.php" class="logout-btn">Logout</a>
</div>
</body>
</html>
