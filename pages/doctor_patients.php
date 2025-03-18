<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}

// Check if user is logged in and is a doctor
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'doctor') {
    header('Location: index.php');
    exit;
}

// Get search parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'visit_date';
$order = $_GET['order'] ?? 'DESC';

// Build query
$query = "
    SELECT DISTINCT p.*, u.email,
           (SELECT COUNT(*) FROM medical_records WHERE patient_id = p.id AND doctor_id = ?) as visit_count,
           (SELECT MAX(visit_date) FROM medical_records WHERE patient_id = p.id AND doctor_id = ?) as last_visit
    FROM patient_profiles p
    JOIN users u ON p.user_id = u.id
    JOIN medical_records mr ON p.id = mr.patient_id
    WHERE mr.doctor_id = ?
";

$params = [$_SESSION['profile_id'], $_SESSION['profile_id'], $_SESSION['profile_id']];

if (!empty($search)) {
    $query .= " AND (p.full_name LIKE ? OR u.email LIKE ? OR p.phone_number LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

$query .= " GROUP BY p.id ORDER BY $sort $order";

// Get patients
$stmt = $conn->prepare($query);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4><?php echo translate('my_patients'); ?></h4>
        <form method="GET" class="d-flex">
            <input type="hidden" name="page" value="doctor_patients">
            <input type="text" name="search" class="form-control me-2" 
                   placeholder="<?php echo translate('search_patients'); ?>"
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> <?php echo translate('search'); ?>
            </button>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($patients)): ?>
            <p class="text-center"><?php echo translate('no_patients_found'); ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="?page=doctor_patients&sort=full_name&order=<?php echo $sort === 'full_name' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo translate('name'); ?>
                                    <?php if ($sort === 'full_name'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th><?php echo translate('email'); ?></th>
                            <th><?php echo translate('phone'); ?></th>
                            <th>
                                <a href="?page=doctor_patients&sort=visit_count&order=<?php echo $sort === 'visit_count' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo translate('visits'); ?>
                                    <?php if ($sort === 'visit_count'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?page=doctor_patients&sort=last_visit&order=<?php echo $sort === 'last_visit' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo translate('last_visit'); ?>
                                    <?php if ($sort === 'last_visit'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th><?php echo translate('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                <td><?php echo htmlspecialchars($patient['phone_number'] ?? translate('not_provided')); ?></td>
                                <td><?php echo $patient['visit_count']; ?></td>
                                <td><?php echo $patient['last_visit'] ? date('Y-m-d', strtotime($patient['last_visit'])) : translate('no_visits'); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="index.php?page=patient_details&id=<?php echo $patient['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> <?php echo translate('view'); ?>
                                        </a>
                                        <a href="index.php?page=add_medical_record&patient_id=<?php echo $patient['id']; ?>" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-plus"></i> <?php echo translate('add_record'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div> 