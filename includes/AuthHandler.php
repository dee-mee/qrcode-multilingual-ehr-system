<?php
class AuthHandler {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function register($username, $email, $password, $role, $fullName, $additionalData = []) {
        try {
            // Validate input
            $this->validateInput($username, $email, $password);
            
            // Check if username or email already exists
            if ($this->userExists($username, $email)) {
                throw new Exception("Username or email already exists");
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
            
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Insert user
            $stmt = $this->conn->prepare("
                INSERT INTO users (username, email, password, role, language_preference)
                VALUES (?, ?, ?, ?, 'en')
            ");
            $stmt->execute([$username, $email, $hashedPassword, $role]);
            $userId = $this->conn->lastInsertId();
            
            // Insert profile based on role
            if ($role === 'patient') {
                $stmt = $this->conn->prepare("
                    INSERT INTO patient_profiles (user_id, full_name, date_of_birth, gender, phone_number, address)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $fullName,
                    $additionalData['date_of_birth'],
                    $additionalData['gender'],
                    $additionalData['phone_number'] ?? null,
                    $additionalData['address'] ?? null
                ]);
            } else {
                $stmt = $this->conn->prepare("
                    INSERT INTO doctor_profiles (user_id, full_name, specialization, license_number, phone_number)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $fullName,
                    $additionalData['specialization'],
                    $additionalData['license_number'],
                    $additionalData['phone_number'] ?? null
                ]);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
    
    public function login($username, $password) {
        try {
            // Get user data
            $stmt = $this->conn->prepare("
                SELECT u.*, 
                    CASE 
                        WHEN u.role = 'patient' THEN p.id
                        WHEN u.role = 'doctor' THEN d.id
                    END as profile_id
                FROM users u
                LEFT JOIN patient_profiles p ON u.id = p.user_id
                LEFT JOIN doctor_profiles d ON u.id = d.user_id
                WHERE u.username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['password'])) {
                return false;
            }
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_id'] = $user['profile_id'];
            $_SESSION['language'] = $user['language_preference'];
            
            return true;
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->conn->prepare("
                SELECT u.*, 
                    CASE 
                        WHEN u.role = 'patient' THEN p.*
                        WHEN u.role = 'doctor' THEN d.*
                    END as profile_data
                FROM users u
                LEFT JOIN patient_profiles p ON u.id = p.user_id
                LEFT JOIN doctor_profiles d ON u.id = d.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
    
    private function validateInput($username, $email, $password) {
        if (strlen($username) < 3 || strlen($username) > 50) {
            throw new Exception("Username must be between 3 and 50 characters");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
    }
    
    private function userExists($username, $email) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetchColumn() > 0;
    }
}
?> 