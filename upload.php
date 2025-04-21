<?php
// Vulnerable file upload handler

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['patient_file'])) {
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file = $_FILES['patient_file'];
    $target_file = $upload_dir . basename($file['name']);

    // No validation or sanitization of file type or name (vulnerable)
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        echo "File uploaded successfully: " . htmlspecialchars($file['name']);
    } else {
        echo "Failed to upload file.";
    }
} else {
    echo "No file uploaded.";
}
?>
