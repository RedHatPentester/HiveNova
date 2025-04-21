-- SQL script to create lab_tests and lab_assignments tables for HiveNova Medical

-- Table to store available lab tests
CREATE TABLE IF NOT EXISTS lab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lab_test_name VARCHAR(255) NOT NULL UNIQUE
);

-- Table to store lab test assignments to patients
CREATE TABLE IF NOT EXISTS lab_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    lab_test VARCHAR(255) NOT NULL,
    assigned_by VARCHAR(255) NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_records(id) ON DELETE CASCADE
);
