<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Get user data based on role
if ($_SESSION['role'] === 'patient') {
    $stmt = $conn->prepare("
        SELECT p.*, u.email, u.username
        FROM patient_profiles p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
} else {
    $stmt = $conn->prepare("
        SELECT d.*, u.email, u.username
        FROM doctor_profiles d
        JOIN users u ON d.user_id = u.id
        WHERE d.id = ?
    ");
}

$stmt->execute([$_SESSION['profile_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $phoneNumber = $_POST['phone_number'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Role-specific fields
    if ($_SESSION['role'] === 'patient') {
        $dateOfBirth = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
    } else {
        $specialization = $_POST['specialization'] ?? '';
        $licenseNumber = $_POST['license_number'] ?? '';
    }
    
    // Validate required fields
    if (empty($fullName)) {
        $error = translate('error_name_required');
    } else {
        try {
            // Update profile based on role
            if ($_SESSION['role'] === 'patient') {
                $stmt = $conn->prepare("
                    UPDATE patient_profiles 
                    SET full_name = ?, phone_number = ?, address = ?, date_of_birth = ?, gender = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $fullName,
                    $phoneNumber,
                    $address,
                    $dateOfBirth,
                    $gender,
                    $_SESSION['profile_id']
                ]);
            } else {
                $stmt = $conn->prepare("
                    UPDATE doctor_profiles 
                    SET full_name = ?, phone_number = ?, address = ?, specialization = ?, license_number = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $fullName,
                    $phoneNumber,
                    $address,
                    $specialization,
                    $licenseNumber,
                    $_SESSION['profile_id']
                ]);
            }
            
            $success = translate('profile_updated_success');
            
            // Refresh profile data
            if ($_SESSION['role'] === 'patient') {
                $stmt = $conn->prepare("
                    SELECT p.*, u.email, u.username
                    FROM patient_profiles p
                    JOIN users u ON p.user_id = u.id
                    WHERE p.id = ?
                ");
            } else {
                $stmt = $conn->prepare("
                    SELECT d.*, u.email, u.username
                    FROM doctor_profiles d
                    JOIN users u ON d.user_id = u.id
                    WHERE d.id = ?
                ");
            }
            $stmt->execute([$_SESSION['profile_id']]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error = translate('error_updating_profile');
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4><?php echo translate('profile_settings'); ?></h4>
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
                        <label for="full_name" class="form-label"><?php echo translate('full_name'); ?> *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($profile['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label"><?php echo translate('email'); ?></label>
                        <input type="email" class="form-control" id="email" 
                               value="<?php echo htmlspecialchars($profile['email']); ?>" disabled>
                    </div>
                    
                    <?php if ($_SESSION['role'] === 'patient'): ?>
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label"><?php echo translate('date_of_birth'); ?></label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                   value="<?php echo $profile['date_of_birth']; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="gender" class="form-label"><?php echo translate('gender'); ?></label>
                            <select class="form-select" id="gender" name="gender">
                                <option value=""><?php echo translate('select_gender'); ?></option>
                                <option value="M" <?php echo $profile['gender'] === 'M' ? 'selected' : ''; ?>>
                                    <?php echo translate('male'); ?>
                                </option>
                                <option value="F" <?php echo $profile['gender'] === 'F' ? 'selected' : ''; ?>>
                                    <?php echo translate('female'); ?>
                                </option>
                                <option value="O" <?php echo $profile['gender'] === 'O' ? 'selected' : ''; ?>>
                                    <?php echo translate('other'); ?>
                                </option>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label for="specialization" class="form-label"><?php echo translate('specialization'); ?></label>
                            <input type="text" class="form-control" id="specialization" name="specialization" 
                                   value="<?php echo htmlspecialchars($profile['specialization']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="license_number" class="form-label"><?php echo translate('license_number'); ?></label>
                            <input type="text" class="form-control" id="license_number" name="license_number" 
                                   value="<?php echo htmlspecialchars($profile['license_number']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="phone_number" class="form-label"><?php echo translate('phone_number'); ?></label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                               value="<?php echo htmlspecialchars($profile['phone_number'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label"><?php echo translate('address'); ?></label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo translate('save_changes'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div> 