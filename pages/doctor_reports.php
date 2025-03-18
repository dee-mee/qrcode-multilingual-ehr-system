<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}

// Check if user is logged in and is a doctor
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'doctor') {
    header('Location: index.php');
    exit;
}

// Get date range parameters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month

// Get total patients
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT patient_id) as total_patients
    FROM medical_records
    WHERE doctor_id = ?
");
$stmt->execute([$_SESSION['profile_id']]);
$totalPatients = $stmt->fetch(PDO::FETCH_ASSOC)['total_patients'];

// Get total visits in date range
$stmt = $conn->prepare("
    SELECT COUNT(*) as total_visits
    FROM medical_records
    WHERE doctor_id = ? AND visit_date BETWEEN ? AND ?
");
$stmt->execute([$_SESSION['profile_id'], $startDate, $endDate]);
$totalVisits = $stmt->fetch(PDO::FETCH_ASSOC)['total_visits'];

// Get visits by month for chart
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(visit_date, '%Y-%m') as month,
           COUNT(*) as visit_count
    FROM medical_records
    WHERE doctor_id = ?
    GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
$stmt->execute([$_SESSION['profile_id']]);
$monthlyVisits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get most common diagnoses
$stmt = $conn->prepare("
    SELECT diagnosis, COUNT(*) as count
    FROM medical_records
    WHERE doctor_id = ? AND visit_date BETWEEN ? AND ?
    GROUP BY diagnosis
    ORDER BY count DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['profile_id'], $startDate, $endDate]);
$commonDiagnoses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent patients
$stmt = $conn->prepare("
    SELECT p.full_name, p.phone_number, mr.visit_date, mr.diagnosis
    FROM medical_records mr
    JOIN patient_profiles p ON mr.patient_id = p.id
    WHERE mr.doctor_id = ?
    ORDER BY mr.visit_date DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['profile_id']]);
$recentPatients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <!-- Date Range Filter -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="page" value="doctor_reports">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label"><?php echo translate('start_date'); ?></label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo $startDate; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label"><?php echo translate('end_date'); ?></label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo $endDate; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?php echo translate('apply_filter'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo translate('total_patients'); ?></h5>
                <h2 class="card-text"><?php echo $totalPatients; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo translate('total_visits'); ?></h5>
                <h2 class="card-text"><?php echo $totalVisits; ?></h2>
            </div>
        </div>
    </div>
    
    <!-- Monthly Visits Chart -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo translate('monthly_visits'); ?></h5>
            </div>
            <div class="card-body">
                <canvas id="visitsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Common Diagnoses -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo translate('common_diagnoses'); ?></h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($commonDiagnoses as $diagnosis): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo htmlspecialchars($diagnosis['diagnosis']); ?></span>
                            <span class="badge bg-primary rounded-pill"><?php echo $diagnosis['count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Patients -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo translate('recent_patients'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo translate('name'); ?></th>
                                <th><?php echo translate('phone'); ?></th>
                                <th><?php echo translate('visit_date'); ?></th>
                                <th><?php echo translate('diagnosis'); ?></th>
                                <th><?php echo translate('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPatients as $patient): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($patient['phone_number'] ?? translate('not_provided')); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($patient['visit_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($patient['diagnosis']); ?></td>
                                    <td>
                                        <a href="index.php?page=patient_details&id=<?php echo $patient['patient_id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> <?php echo translate('view'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('visitsChart').getContext('2d');
    const data = <?php echo json_encode(array_reverse($monthlyVisits)); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('<?php echo translate('locale'); ?>', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: '<?php echo translate('visits'); ?>',
                data: data.map(item => item.visit_count),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script> 