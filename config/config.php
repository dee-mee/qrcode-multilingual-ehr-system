<?php
// Application configuration
define('SITE_NAME', 'EHR System');
define('SITE_URL', 'http://localhost/ehr-system');

// Security configuration
define('SECURE_SESSION', true);
define('SESSION_LIFETIME', 3600); // 1 hour
define('HASH_COST', 12); // For password_hash()

// QR Code configuration
define('QR_CODE_SIZE', 300);
define('QR_CODE_LEVEL', 'H'); // High error correction level
define('QR_CODE_MARGIN', 10);

// Supported languages
$supported_languages = [
    'en' => 'English',
    'sw' => 'Swahili',
    'ki' => 'Kikuyu',
    'luo' => 'Luo',
    'kln' => 'Kalenjin'
];

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('Africa/Nairobi');

// Configure session settings before starting session
if (SECURE_SESSION) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to get current language
function getCurrentLanguage() {
    return isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
}

// Function to set language
function setLanguage($lang) {
    if (isset($GLOBALS['supported_languages'][$lang])) {
        $_SESSION['language'] = $lang;
        return true;
    }
    return false;
}

// Function to translate text
function translate($key) {
    global $conn;
    $lang = getCurrentLanguage();
    
    $stmt = $conn->prepare("SELECT translation_value FROM translations WHERE language_code = ? AND translation_key = ?");
    $stmt->execute([$lang, $key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['translation_value'] : $key;
}
?> 