<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/AuthHandler.php';
require_once '../includes/QRCodeHandler.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Initialize handlers
$auth = new AuthHandler($conn);
$qrHandler = new QRCodeHandler($conn);

// Check if user is logged in and is a doctor
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => translate('unauthorized_access')]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$qrData = $data['qr_data'] ?? '';

if (empty($qrData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => translate('invalid_qr_data')]);
    exit;
}

// Validate QR code
if (!$qrHandler->validateQRCode($qrData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => translate('invalid_qr_code')]);
    exit;
}

// Process QR code
$patient = $qrHandler->scanQRCode($qrData);

if (!$patient) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => translate('patient_not_found')]);
    exit;
}

// Return success response with patient ID
echo json_encode([
    'success' => true,
    'patient_id' => $patient['id'],
    'message' => translate('qr_code_processed')
]);
?> 