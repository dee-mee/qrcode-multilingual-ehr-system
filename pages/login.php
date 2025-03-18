<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = translate('error_empty_fields');
    } else {
        if ($auth->login($username, $password)) {
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error = translate('error_invalid_credentials');
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center"><?php echo translate('login'); ?></h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?page=login">
                    <div class="mb-3">
                        <label for="username" class="form-label"><?php echo translate('username'); ?></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label"><?php echo translate('password'); ?></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"><?php echo translate('login'); ?></button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p><?php echo translate('no_account'); ?> <a href="index.php?page=register"><?php echo translate('register'); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</div> 