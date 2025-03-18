<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Color\Color;

class QRCodeHandler {
    private $conn;
    private $qrDirectory = 'uploads/qrcodes/';
    
    public function __construct($conn) {
        $this->conn = $conn;
        
        // Create QR code directory if it doesn't exist
        if (!file_exists($this->qrDirectory)) {
            mkdir($this->qrDirectory, 0755, true);
        }
    }
    
    /**
     * Generate QR code for a patient
     * @param int $patientId Patient ID
     * @return string|false QR code file path or false on failure
     */
    public function generateQRCode($patientId) {
        try {
            // Generate unique data for QR code
            $qrData = $this->generateQRData($patientId);
            
            // Create QR code
            $qrCode = QrCode::create($qrData)
                ->setSize(300)
                ->setMargin(10)
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255));
            
            // Create writer
            $writer = new PngWriter();
            
            // Generate file name
            $fileName = 'patient_' . $patientId . '_' . time() . '.png';
            $filePath = $this->qrDirectory . $fileName;
            
            // Save QR code
            $result = $writer->write($qrCode);
            $result->saveToFile($filePath);
            
            // Update patient profile with QR code path
            $stmt = $this->conn->prepare("UPDATE patient_profiles SET qr_code = ? WHERE id = ?");
            $stmt->execute([$fileName, $patientId]);
            
            return $fileName;
        } catch (Exception $e) {
            error_log('QR Code Generation Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate unique data for QR code
     * @param int $patientId Patient ID
     * @return string Encrypted QR data
     */
    private function generateQRData($patientId) {
        // Get patient information
        $stmt = $this->conn->prepare("
            SELECT p.id, p.full_name, u.email
            FROM patient_profiles p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$patientId]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Create data array
        $data = [
            'id' => $patient['id'],
            'timestamp' => time(),
            'checksum' => hash('sha256', $patient['id'] . $patient['email'] . time())
        ];
        
        // Encrypt data
        return base64_encode(json_encode($data));
    }
    
    /**
     * Validate QR code data
     * @param string $qrData Encrypted QR data
     * @return bool Whether the QR code is valid
     */
    public function validateQRCode($qrData) {
        try {
            // Decode data
            $data = json_decode(base64_decode($qrData), true);
            
            if (!$data || !isset($data['id']) || !isset($data['timestamp']) || !isset($data['checksum'])) {
                return false;
            }
            
            // Get patient information
            $stmt = $this->conn->prepare("
                SELECT p.id, u.email
                FROM patient_profiles p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$data['id']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                return false;
            }
            
            // Verify checksum
            $expectedChecksum = hash('sha256', $patient['id'] . $patient['email'] . $data['timestamp']);
            return hash_equals($expectedChecksum, $data['checksum']);
        } catch (Exception $e) {
            error_log('QR Code Validation Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get patient information from QR code
     * @param string $qrData Encrypted QR data
     * @return array|false Patient information or false on failure
     */
    public function scanQRCode($qrData) {
        try {
            // Validate QR code first
            if (!$this->validateQRCode($qrData)) {
                return false;
            }
            
            // Decode data
            $data = json_decode(base64_decode($qrData), true);
            
            // Get patient information
            $stmt = $this->conn->prepare("
                SELECT p.*, u.email
                FROM patient_profiles p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$data['id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('QR Code Scanning Error: ' . $e->getMessage());
            return false;
        }
    }
}
?> 