<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/AuthHandler.php';
require_once 'includes/QRCodeHandler.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Initialize handlers
$auth = new AuthHandler($conn);
$qrHandler = new QRCodeHandler($conn);

// Handle language change
if (isset($_GET['lang']) && setLanguage($_GET['lang'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Check if user is logged in
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $isLoggedIn ? $auth->getCurrentUser() : null;

// Determine which page to show
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Handle logout first
if ($page === 'logout') {
    if ($isLoggedIn) {
        $auth->logout();
    }
    header('Location: index.php');
    exit;
}

// Header
include 'includes/header.php';

// Main content
switch ($page) {
    case 'login':
        if ($isLoggedIn) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        include 'pages/login.php';
        break;
        
    case 'register':
        if ($isLoggedIn) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        include 'pages/register.php';
        break;
        
    case 'dashboard':
        if (!$isLoggedIn) {
            header('Location: index.php?page=login');
            exit;
        }
        if ($currentUser['role'] === 'patient') {
            include 'pages/patient_dashboard.php';
        } else {
            include 'pages/doctor_dashboard.php';
        }
        break;
        
    case 'profile':
        if (!$isLoggedIn) {
            header('Location: index.php?page=login');
            exit;
        }
        include 'pages/profile.php';
        break;
        
    case 'medical_history':
        if (!$isLoggedIn || $currentUser['role'] !== 'patient') {
            header('Location: index.php');
            exit;
        }
        include 'pages/medical_history.php';
        break;
        
    case 'scan_qr':
        if (!$isLoggedIn || $currentUser['role'] !== 'doctor') {
            header('Location: index.php');
            exit;
        }
        include 'pages/scan_qr.php';
        break;
        
    default:
        include 'pages/home.php';
        break;
}

// Footer
include 'includes/footer.php';
?> 