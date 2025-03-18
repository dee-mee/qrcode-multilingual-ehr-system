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
$patientId = $_GET['patient_id'] ?? null;
if (!$patientId) {
    header('Location: index.php?page=doctor_patients');
    exit;
}

// Get patient information
$stmt = $conn->prepare("
    SELECT p.*, u.email
    FROM patient_profiles p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$patientId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: index.php?page=doctor_patients');
    exit;
}

$success = '';
$error = '';

// Handle form submission
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
            
            // Redirect to patient details page after successful addition
            header("Location: index.php?page=patient_details&id=$patientId");
            exit;
        } catch (Exception $e) {
            $error = translate('error_adding_record');
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4><?php echo translate('add_medical_record'); ?></h4>
            </div>
            <div class="card-body">
                <!-- Patient Information -->
                <div class="alert alert-info">
                    <h5><?php echo translate('patient_information'); ?></h5>
                    <p class="mb-1"><strong><?php echo translate('name'); ?>:</strong> <?php echo htmlspecialchars($patient['full_name']); ?></p>
                    <p class="mb-1"><strong><?php echo translate('email'); ?>:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                    <p class="mb-1"><strong><?php echo translate('date_of_birth'); ?>:</strong> <?php echo date('Y-m-d', strtotime($patient['date_of_birth'])); ?></p>
                    <p class="mb-0"><strong><?php echo translate('gender'); ?>:</strong> <?php echo $patient['gender'] === 'M' ? translate('male') : ($patient['gender'] === 'F' ? translate('female') : translate('other')); ?></p>
                </div>
                
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
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=patient_details&id=<?php echo $patientId; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> <?php echo translate('back'); ?>
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo translate('save_record'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 