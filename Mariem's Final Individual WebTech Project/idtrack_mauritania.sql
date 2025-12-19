-- Temporarily disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Drop all tables in correct order
DROP TABLE IF EXISTS renewal_requests;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS payment_settings;
DROP TABLE IF EXISTS users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Create clean users table WITHOUT status
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female', 'other') DEFAULT 'male',
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    user_type ENUM('citizen', 'non_citizen', 'admin') DEFAULT 'citizen',
    document_type ENUM('national_id', 'passport') NOT NULL DEFAULT 'national_id',
    document_number VARCHAR(50) NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_document (document_type, document_number),
    UNIQUE KEY unique_document (document_type, document_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create other tables
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payment_settings (
  id INT NOT NULL AUTO_INCREMENT,
  payment_method VARCHAR(50) NOT NULL,
  momo_number VARCHAR(20) NOT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY payment_method (payment_method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE renewal_requests (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  request_type ENUM('id_card_renewal', 'permit_renewal') NOT NULL,
  renewal_reason VARCHAR(100) DEFAULT NULL,
  nationality VARCHAR(100) DEFAULT NULL,
  permit_duration INT DEFAULT NULL,
  payment_method VARCHAR(50) NOT NULL,
  sender_phone VARCHAR(20) NOT NULL,
  transaction_ref VARCHAR(100) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
  admin_notes TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  verified_at TIMESTAMP NULL DEFAULT NULL,
  verified_by INT DEFAULT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS residence_permits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permit_number VARCHAR(50) UNIQUE NOT NULL,
    visa_type ENUM('work', 'student', 'family', 'business', 'tourist', 'other') DEFAULT 'work',
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    entry_date DATE NOT NULL,
    sponsor_name VARCHAR(100),
    sponsor_contact VARCHAR(20),
    occupation VARCHAR(100),
    status ENUM('active', 'expired', 'cancelled', 'under_review') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create national_id_applications table
CREATE TABLE IF NOT EXISTS national_id_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    application_number VARCHAR(50) UNIQUE NOT NULL,
    passport_document VARCHAR(255),
    photo_document VARCHAR(255),
    proof_address VARCHAR(255),
    police_clearance VARCHAR(255),
    biometric_completed BOOLEAN DEFAULT FALSE,
    biometric_date DATE NULL,
    application_status ENUM('submitted', 'documents_verified', 'biometric_pending', 'biometric_completed', 'approved', 'rejected', 'card_issued') DEFAULT 'submitted',
    rejection_reason TEXT NULL,
    card_number VARCHAR(50) NULL,
    card_issue_date DATE NULL,
    card_expiry_date DATE NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create uploaded_documents table
CREATE TABLE IF NOT EXISTS uploaded_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_type ENUM('passport', 'photo', 'proof', 'police_clearance', 'bank_statement', 'employment_letter', 'other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP NULL,
    verified_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- First, ensure admin users exist with proper nationalities
INSERT INTO users (full_name, email, phone, password, date_of_birth, nationality, user_type, document_type, document_number)
VALUES 
('Mariem Sall', 'mariem.sall@gmail.com', '+22200000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2005-01-01', 'Mauritanian', 'admin', 'national_id', 'ADMIN001'),
('Kgosafo Maafo', 'kgosafomaafo@ashesi.edu.gh', '+23300000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1980-01-01', 'Ghanaian', 'admin', 'passport', 'ADMIN002'),
('Marie Doh', 'marie.doh@ashesi.edu.gh', '+23300000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1990-01-01', 'Ghanaian', 'admin', 'passport', 'ADMIN003')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), user_type = 'admin';

-- Update phone numbers for Mauritanian user (Mauritania: +222)
UPDATE users SET phone = '+22200000000' WHERE email = 'mariem.sall@gmail.com';

-- Update phone numbers for Ghanaian users (Ghana: +233)
UPDATE users SET phone = '+23300000001' WHERE email = 'kgosafomaafo@ashesi.edu.gh';
UPDATE users SET phone = '+23300000002' WHERE email = 'marie.doh@ashesi.edu.gh';

-- Simple security logs table (optional)
CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    event_type VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin actions log (optional)
CREATE TABLE IF NOT EXISTS admin_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-

INSERT INTO users (full_name, gender, email, phone, password, date_of_birth, nationality, user_type, document_type, document_number)