<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}

// Check if user is logged in and is a doctor
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'doctor') {
    header('Location: index.php');
    exit;
}

// Get patient ID from URL
$patientId = $_GET['id'] ?? null;
if (!$patientId) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Get patient data
$stmt = $conn->prepare("
    SELECT p.*, u.email, u.username
    FROM patient_profiles p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$patientId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Get patient's medical records
$stmt = $conn->prepare("
    SELECT mr.*, d.full_name as doctor_name
    FROM medical_records mr
    JOIN doctor_profiles d ON mr.doctor_id = d.id
    WHERE mr.patient_id = ?
    ORDER BY mr.visit_date DESC
");
$stmt->execute([$patientId]);
$medicalRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for adding medical record
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosis = $_POST['diagnosis'] ?? '';
    $prescription = $_POST['prescription'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($diagnosis)) {
        $error = translate('error_diagnosis_required');
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO medical_records (patient_id, doctor_id, diagnosis, prescription, notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $patientId,
                $_SESSION['profile_id'],
                $diagnosis,
                $prescription,
                $notes
            ]);
            $success = translate('record_added_success');
            
            // Refresh medical records
            $stmt = $conn->prepare("
                SELECT mr.*, d.full_name as doctor_name
                FROM medical_records mr
                JOIN doctor_profiles d ON mr.doctor_id = d.id
                WHERE mr.patient_id = ?
                ORDER BY mr.visit_date DESC
            ");
            $stmt->execute([$patientId]);
            $medicalRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error = translate('error_adding_record');
        }
    }
}
?>

<div class="row">
    <!-- Patient Information -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo translate('patient_information'); ?></h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th><?php echo translate('name'); ?>:</th>
                        <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo translate('email'); ?>:</th>
                        <td><?php echo htmlspecialchars($patient['email']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo translate('date_of_birth'); ?>:</th>
                        <td><?php echo date('Y-m-d', strtotime($patient['date_of_birth'])); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo translate('gender'); ?>:</th>
                        <td><?php echo $patient['gender'] === 'M' ? translate('male') : ($patient['gender'] === 'F' ? translate('female') : translate('other')); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo translate('phone'); ?>:</th>
                        <td><?php echo htmlspecialchars($patient['phone_number'] ?? translate('not_provided')); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo translate('address'); ?>:</th>
                        <td><?php echo htmlspecialchars($patient['address'] ?? translate('not_provided')); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Medical Record Form -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo translate('add_medical_record'); ?></h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="diagnosis" class="form-label"><?php echo translate('diagnosis'); ?> *</label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="prescription" class="form-label"><?php echo translate('prescription'); ?></label>
                        <textarea class="form-control" id="prescription" name="prescription" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label"><?php echo translate('notes'); ?></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo translate('add_record'); ?></button>
                </form>
            </div>
        </div>
        
        <!-- Medical History -->
        <div class="card">
            <div class="card-header">
                <h4><?php echo translate('medical_history'); ?></h4>
            </div>
            <div class="card-body">
                <?php if (empty($medicalRecords)): ?>
                    <p class="text-center"><?php echo translate('no_medical_records'); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo translate('date'); ?></th>
                                    <th><?php echo translate('doctor'); ?></th>
                                    <th><?php echo translate('diagnosis'); ?></th>
                                    <th><?php echo translate('prescription'); ?></th>
                                    <th><?php echo translate('notes'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medicalRecords as $record): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($record['visit_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($record['doctor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['diagnosis']); ?></td>
                                        <td><?php echo htmlspecialchars($record['prescription'] ?? translate('none')); ?></td>
                                        <td><?php echo htmlspecialchars($record['notes'] ?? translate('none')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> 