<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Nurse') {
    header("Location: index.php");
    exit();
}

// Dummy data for patient prescriptions
$prescriptions = [
    ['id' => 1, 'patient' => 'John Doe', 'medication' => 'Amoxicillin', 'status' => 'Pending'],
    ['id' => 2, 'patient' => 'Jane Smith', 'medication' => 'Ibuprofen', 'status' => 'Dispensed'],
    ['id' => 3, 'patient' => 'Alice Johnson', 'medication' => 'Paracetamol', 'status' => 'Pending'],
];

// Handle marking medication as dispensed (vulnerable to CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dispense_id'])) {
    $dispense_id = $_POST['dispense_id'];
    // Vulnerable: no CSRF token verification
    foreach ($prescriptions as &$prescription) {
        if ($prescription['id'] == $dispense_id) {
            $prescription['status'] = 'Dispensed';
            $message = "Medication marked as dispensed for prescription ID: " . htmlspecialchars($dispense_id);
            break;
        }
    }
}

// Handle CSV inventory upload (vulnerable to CSV injection)
$upload_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_inventory'])) {
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $file = $_FILES['csv_inventory'];
    $target_file = $upload_dir . basename($file['name']);
    // No sanitization of CSV content or filename (CSV injection vulnerability)
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $upload_message = "CSV inventory uploaded successfully: " . htmlspecialchars($file['name']);
    } else {
        $upload_message = "Failed to upload CSV inventory.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pharmacist Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
</head>
<body>
<div class="dashboard-container">
    <h1>Welcome, Pharmacist <?php echo htmlspecialchars($_SESSION['user']); ?></h1>

    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <h2>Patient Prescriptions</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Medication</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($prescriptions as $prescription): ?>
        <tr>
            <td><?php echo $prescription['id']; ?></td>
            <td><?php echo htmlspecialchars($prescription['patient']); ?></td>
            <td><?php echo htmlspecialchars($prescription['medication']); ?></td>
            <td><?php echo htmlspecialchars($prescription['status']); ?></td>
            <td>
                <?php if ($prescription['status'] !== 'Dispensed'): ?>
                <form method="POST" action="">
                    <input type="hidden" name="dispense_id" value="<?php echo $prescription['id']; ?>" />
                    <input type="submit" value="Mark as Dispensed" />
                </form>
                <?php else: ?>
                Dispensed
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Upload CSV Inventory</h2>
    <?php if ($upload_message): ?>
        <p style="color: green;"><?php echo $upload_message; ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" action="">
        <input type="file" name="csv_inventory" accept=".csv" required />
        <input type="submit" value="Upload CSV" />
    </form>

    <p><a href="logout.php">Logout</a></p>
</div>
</body>
</html>
