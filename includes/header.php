<?php
if (!defined('SITE_NAME')) {
    die('Direct access not permitted');
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=dashboard"><?php echo translate('dashboard'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=profile"><?php echo translate('profile'); ?></a>
                        </li>
                        <?php if ($isLoggedIn && $currentUser && $currentUser['role'] === 'patient'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=medical_history"><?php echo translate('medical_history'); ?></a>
                            </li>
                        <?php elseif ($isLoggedIn && $currentUser && $currentUser['role'] === 'doctor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=scan_qr"><?php echo translate('scan_qr'); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=login"><?php echo translate('login'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=register"><?php echo translate('register'); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Language Selector -->
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown">
                        <?php echo $supported_languages[getCurrentLanguage()]; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach ($supported_languages as $code => $name): ?>
                            <li>
                                <a class="dropdown-item" href="index.php?lang=<?php echo $code; ?>">
                                    <?php echo $name; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <?php if ($isLoggedIn): ?>
                    <a href="index.php?page=logout" class="btn btn-light ms-2"><?php echo translate('logout'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4"> 