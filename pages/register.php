<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $fullName = $_POST['full_name'] ?? '';
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role) || empty($fullName)) {
        $error = translate('error_empty_fields');
    } elseif ($password !== $confirm_password) {
        $error = translate('error_password_mismatch');
    } else {
        // Prepare additional data based on role
        $additionalData = [];
        if ($role === 'patient') {
            $additionalData = [
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'phone_number' => $_POST['phone_number'] ?? '',
                'address' => $_POST['address'] ?? ''
            ];
        } else {
            $additionalData = [
                'specialization' => $_POST['specialization'] ?? '',
                'license_number' => $_POST['license_number'] ?? '',
                'phone_number' => $_POST['phone_number'] ?? ''
            ];
        }
        
        if ($auth->register($username, $email, $password, $role, $fullName, $additionalData)) {
            $success = translate('registration_success');
        } else {
            $error = translate('error_registration_failed');
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center"><?php echo translate('register'); ?></h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label"><?php echo translate('username'); ?></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label"><?php echo translate('email'); ?></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label"><?php echo translate('password'); ?></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label"><?php echo translate('confirm_password'); ?></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label"><?php echo translate('full_name'); ?></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label"><?php echo translate('role'); ?></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value=""><?php echo translate('select_role'); ?></option>
                                <option value="patient"><?php echo translate('patient'); ?></option>
                                <option value="doctor"><?php echo translate('doctor'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Patient-specific fields -->
                    <div id="patient-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label"><?php echo translate('date_of_birth'); ?></label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label"><?php echo translate('gender'); ?></label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value=""><?php echo translate('select_gender'); ?></option>
                                    <option value="M"><?php echo translate('male'); ?></option>
                                    <option value="F"><?php echo translate('female'); ?></option>
                                    <option value="Other"><?php echo translate('other'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Doctor-specific fields -->
                    <div id="doctor-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="specialization" class="form-label"><?php echo translate('specialization'); ?></label>
                                <input type="text" class="form-control" id="specialization" name="specialization">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="license_number" class="form-label"><?php echo translate('license_number'); ?></label>
                                <input type="text" class="form-control" id="license_number" name="license_number">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Common fields -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone_number" class="form-label"><?php echo translate('phone_number'); ?></label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label"><?php echo translate('address'); ?></label>
                            <textarea class="form-control" id="address" name="address" rows="1"></textarea>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"><?php echo translate('register'); ?></button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p><?php echo translate('already_have_account'); ?> <a href="index.php?page=login"><?php echo translate('login'); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    const patientFields = document.getElementById('patient-fields');
    const doctorFields = document.getElementById('doctor-fields');
    
    patientFields.style.display = this.value === 'patient' ? 'block' : 'none';
    doctorFields.style.display = this.value === 'doctor' ? 'block' : 'none';
});
</script> 