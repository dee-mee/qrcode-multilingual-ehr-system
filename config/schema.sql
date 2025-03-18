-- Create database
CREATE DATABASE IF NOT EXISTS ehr_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ehr_system;

-- Users table (for both patients and doctors)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('patient', 'doctor') NOT NULL,
    language_preference VARCHAR(10) DEFAULT 'en',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Patient profiles
CREATE TABLE patient_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('M', 'F', 'Other') NOT NULL,
    phone_number VARCHAR(20),
    address TEXT,
    qr_code VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Doctor profiles
CREATE TABLE doctor_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Medical records
CREATE TABLE medical_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    diagnosis TEXT NOT NULL,
    prescription TEXT,
    notes TEXT,
    visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctor_profiles(id) ON DELETE CASCADE
);

-- Language translations
CREATE TABLE translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    language_code VARCHAR(10) NOT NULL,
    translation_key VARCHAR(100) NOT NULL,
    translation_value TEXT NOT NULL,
    UNIQUE KEY unique_translation (language_code, translation_key)
);

-- Insert default translations for English
INSERT INTO translations (language_code, translation_key, translation_value) VALUES
('en', 'welcome', 'Welcome to EHR System'),
('en', 'login', 'Login'),
('en', 'register', 'Register'),
('en', 'email', 'Email'),
('en', 'password', 'Password'),
('en', 'submit', 'Submit'),
('en', 'logout', 'Logout'),
('en', 'dashboard', 'Dashboard'),
('en', 'profile', 'Profile'),
('en', 'medical_history', 'Medical History'),
('en', 'qr_code', 'QR Code'),
('en', 'scan_qr', 'Scan QR Code'),
('en', 'change_language', 'Change Language');

-- Insert default translations for Swahili
INSERT INTO translations (language_code, translation_key, translation_value) VALUES
('sw', 'welcome', 'Karibu kwenye Mfumo wa EHR'),
('sw', 'login', 'Ingia'),
('sw', 'register', 'Jisajili'),
('sw', 'email', 'Barua pepe'),
('sw', 'password', 'Neno la siri'),
('sw', 'submit', 'Wasilisha'),
('sw', 'logout', 'Ondoka'),
('sw', 'dashboard', 'Dashibodi'),
('sw', 'profile', 'Wasifu'),
('sw', 'medical_history', 'Historia ya Matibabu'),
('sw', 'qr_code', 'Msimbo QR'),
('sw', 'scan_qr', 'Skani Msimbo QR'),
('sw', 'change_language', 'Badilisha Lugha'); 