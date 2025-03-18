<?php
require_once 'config/database.php';
require_once 'config/config.php';

try {
    // Initialize database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Read and execute schema.sql
    $schema = file_get_contents('config/schema.sql');
    $conn->exec($schema);
    
    // Create a test doctor user
    $username = 'doctor';
    $password = password_hash('doctor123', PASSWORD_DEFAULT, ['cost' => HASH_COST]);
    $email = 'doctor@example.com';
    $role = 'doctor';
    
    // Insert doctor user
    $stmt = $conn->prepare("
        INSERT INTO users (username, password, email, role)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$username, $password, $email, $role]);
    $userId = $conn->lastInsertId();
    
    // Insert doctor profile
    $stmt = $conn->prepare("
        INSERT INTO doctor_profiles (user_id, full_name, specialization, license_number)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        'Test Doctor',
        'General Medicine',
        'DOC123456'
    ]);
    
    // Create a test patient user
    $username = 'patient';
    $password = password_hash('patient123', PASSWORD_DEFAULT, ['cost' => HASH_COST]);
    $email = 'patient@example.com';
    $role = 'patient';
    
    // Insert patient user
    $stmt = $conn->prepare("
        INSERT INTO users (username, password, email, role)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$username, $password, $email, $role]);
    $userId = $conn->lastInsertId();
    
    // Insert patient profile
    $stmt = $conn->prepare("
        INSERT INTO patient_profiles (user_id, full_name, date_of_birth, gender)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        'Test Patient',
        '1990-01-01',
        'M'
    ]);
    
    echo "Database setup completed successfully!\n";
    echo "Test accounts created:\n";
    echo "Doctor - Username: doctor, Password: doctor123\n";
    echo "Patient - Username: patient, Password: patient123\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 