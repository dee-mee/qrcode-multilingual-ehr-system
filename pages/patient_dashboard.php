<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}

// Get patient data
$patientId = $_SESSION['profile_id'];
$stmt = $conn->prepare("
    SELECT p.*, u.email, u.username
    FROM patient_profiles p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$patientId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Generate QR code if not exists
if (!$patient['qr_code']) {
    $qrHandler->generateQRCode($patientId);
}

// Get recent medical records
$stmt = $conn->prepare("
    SELECT mr.*, d.full_name as doctor_name
    FROM medical_records mr
    JOIN doctor_profiles d ON mr.doctor_id = d.id
    WHERE mr.patient_id = ?
    ORDER BY mr.visit_date DESC
    LIMIT 5
");
$stmt->execute([$patientId]);
$recentRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <!-- Patient Profile Card -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo translate('profile'); ?></h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php if ($patient['qr_code']): ?>
                        <img src="<?php echo $patient['qr_code']; ?>" alt="QR Code" class="img-fluid mb-2" style="max-width: 200px;">
                        <div>
                            <a href="<?php echo $patient['qr_code']; ?>" class="btn btn-sm btn-primary" download>
                                <?php echo translate('download_qr'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
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
    
    <!-- Recent Medical Records -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><?php echo translate('recent_medical_records'); ?></h4>
                <a href="index.php?page=medical_history" class="btn btn-primary btn-sm">
                    <?php echo translate('view_all'); ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentRecords)): ?>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRecords as $record): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($record['visit_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($record['doctor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['diagnosis']); ?></td>
                                        <td><?php echo htmlspecialchars($record['prescription'] ?? translate('none')); ?></td>
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

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><?php echo translate('quick_actions'); ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="index.php?page=profile" class="btn btn-outline-primary w-100 mb-2">
                            <?php echo translate('edit_profile'); ?>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php?page=medical_history" class="btn btn-outline-primary w-100 mb-2">
                            <?php echo translate('view_history'); ?>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo $patient['qr_code']; ?>" class="btn btn-outline-primary w-100 mb-2" download>
                            <?php echo translate('download_qr'); ?>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php?page=change_password" class="btn btn-outline-primary w-100 mb-2">
                            <?php echo translate('change_password'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 