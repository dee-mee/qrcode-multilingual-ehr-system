<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}

// Check if user is logged in and is a patient
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'patient') {
    header('Location: index.php');
    exit;
}

// Get patient's medical records
$stmt = $conn->prepare("
    SELECT mr.*, d.full_name as doctor_name, d.specialization
    FROM medical_records mr
    JOIN doctor_profiles d ON mr.doctor_id = d.id
    WHERE mr.patient_id = ?
    ORDER BY mr.visit_date DESC
");
$stmt->execute([$_SESSION['profile_id']]);
$medicalRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient's profile information
$stmt = $conn->prepare("
    SELECT p.*, u.email
    FROM patient_profiles p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$_SESSION['profile_id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);
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
    
    <!-- Medical Records -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><?php echo translate('medical_history'); ?></h4>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> <?php echo translate('print_history'); ?>
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($medicalRecords)): ?>
                    <p class="text-center"><?php echo translate('no_medical_records'); ?></p>
                <?php else: ?>
                    <?php foreach ($medicalRecords as $record): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo date('Y-m-d', strtotime($record['visit_date'])); ?></h5>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($record['specialization']); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong><?php echo translate('doctor'); ?>:</strong> <?php echo htmlspecialchars($record['doctor_name']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo translate('visit_date'); ?>:</strong> <?php echo date('Y-m-d H:i', strtotime($record['visit_date'])); ?></p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><?php echo translate('diagnosis'); ?></h6>
                                    <p><?php echo nl2br(htmlspecialchars($record['diagnosis'])); ?></p>
                                </div>
                                
                                <?php if (!empty($record['prescription'])): ?>
                                    <div class="mb-3">
                                        <h6><?php echo translate('prescription'); ?></h6>
                                        <p><?php echo nl2br(htmlspecialchars($record['prescription'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($record['notes'])): ?>
                                    <div>
                                        <h6><?php echo translate('notes'); ?></h6>
                                        <p><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn-primary {
        display: none;
    }
    .card {
        border: none;
        box-shadow: none;
    }
    .card-header {
        background: none;
        border-bottom: 1px solid #ddd;
    }
    .card-body {
        padding: 0;
    }
}
</style> 