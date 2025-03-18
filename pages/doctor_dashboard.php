<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}

// Get doctor data
$doctorId = $_SESSION['profile_id'];
$stmt = $conn->prepare("
    SELECT d.*, u.email, u.username
    FROM doctor_profiles d
    JOIN users u ON d.user_id = u.id
    WHERE d.id = ?
");
$stmt->execute([$doctorId]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent patients
$stmt = $conn->prepare("
    SELECT DISTINCT p.*, u.email
    FROM patient_profiles p
    JOIN users u ON p.user_id = u.id
    JOIN medical_records mr ON p.id = mr.patient_id
    WHERE mr.doctor_id = ?
    ORDER BY mr.visit_date DESC
    LIMIT 5
");
$stmt->execute([$doctorId]);
$recentPatients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <!-- Doctor Profile Card -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo translate('profile'); ?></h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th><?php echo translate('name'); ?>:</th>
                        <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo translate('email'); ?>:</th>
                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo translate('specialization'); ?>:</th>
                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo translate('license_number'); ?>:</th>
                        <td><?php echo htmlspecialchars($doctor['license_number']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo translate('phone'); ?>:</th>
                        <td><?php echo htmlspecialchars($doctor['phone_number'] ?? translate('not_provided')); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- QR Code Scanner -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h4><?php echo translate('scan_patient_qr'); ?></h4>
            </div>
            <div class="card-body">
                <div id="reader"></div>
                <div id="result" class="mt-3"></div>
            </div>
        </div>
        
        <!-- Recent Patients -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><?php echo translate('recent_patients'); ?></h4>
                <a href="index.php?page=patients" class="btn btn-primary btn-sm">
                    <?php echo translate('view_all'); ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentPatients)): ?>
                    <p class="text-center"><?php echo translate('no_recent_patients'); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo translate('name'); ?></th>
                                    <th><?php echo translate('email'); ?></th>
                                    <th><?php echo translate('phone'); ?></th>
                                    <th><?php echo translate('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPatients as $patient): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['phone_number'] ?? translate('not_provided')); ?></td>
                                        <td>
                                            <a href="index.php?page=patient_details&id=<?php echo $patient['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <?php echo translate('view_details'); ?>
                                            </a>
                                            <a href="index.php?page=add_record&patient_id=<?php echo $patient['id']; ?>" 
                                               class="btn btn-sm btn-success">
                                                <?php echo translate('add_record'); ?>
                                            </a>
                                        </td>
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
                        <a href="index.php?page=patients" class="btn btn-outline-primary w-100 mb-2">
                            <?php echo translate('manage_patients'); ?>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php?page=reports" class="btn btn-outline-primary w-100 mb-2">
                            <?php echo translate('view_reports'); ?>
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

<script>
function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning
    html5QrcodeScanner.clear();
    
    // Show result
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = `
        <div class="alert alert-info">
            ${translate('scanning_complete')}
        </div>
    `;
    
    // Process QR code data
    fetch('api/process_qr.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ qr_data: decodedText })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `index.php?page=patient_details&id=${data.patient_id}`;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                ${translate('error_processing_qr')}
            </div>
        `;
    });
}

function onScanFailure(error) {
    // Handle scan failure
    console.warn(`QR code scanning failed: ${error}`);
}

// Initialize QR code scanner
const html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { 
        fps: 10,
        qrbox: {width: 250, height: 250},
        aspectRatio: 1.0
    }
);
html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script> 