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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = translate('error_all_fields_required');
    } elseif ($newPassword !== $confirmPassword) {
        $error = translate('error_passwords_dont_match');
    } elseif (strlen($newPassword) < 8) {
        $error = translate('error_password_too_short');
    } else {
        try {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($currentPassword, $user['password'])) {
                $error = translate('error_current_password_incorrect');
            } else {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                
                $success = translate('password_updated_success');
            }
        } catch (Exception $e) {
            $error = translate('error_updating_password');
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4><?php echo translate('change_password'); ?></h4>
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
                        <label for="current_password" class="form-label"><?php echo translate('current_password'); ?> *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label"><?php echo translate('new_password'); ?> *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text"><?php echo translate('password_min_length'); ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label"><?php echo translate('confirm_password'); ?> *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo translate('update_password'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div> 