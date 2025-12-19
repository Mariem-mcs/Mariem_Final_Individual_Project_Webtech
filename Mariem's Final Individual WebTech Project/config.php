<?php
$host = 'localhost';
$user = 'mariem.sall';
$pass = 'NewStrongPass123!';  
$db = 'webtech_2025A_mariem_sall';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_NAME', 'idtrack_session');


define('ADMIN_EMAILS', [
    'mariem.sall@gmail.com',
    'kgosafomaafo@ashesi.edu.gh',
    'marie.doh@ashesi.edu.gh'
]);


header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        
        
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
            force_logout(false);
            return;
        }
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } elseif (time() - $_SESSION['CREATED'] > 300) {
            session_regenerate_id(true);
            $_SESSION['CREATED'] = time();
        }
        
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

function force_logout($redirect_to_login = false) {
    $lang = $_SESSION['language'] ?? 'fr';
    
    $_SESSION = array();
    
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time() - 3600, '/');
            setcookie($name, '', time() - 3600, '/', $_SERVER['HTTP_HOST'] ?? '');
        }
    }
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    if ($redirect_to_login) {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Location: login.php?lang=" . $lang);
        exit();
    }
}


function is_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        return false;
    }
    
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        return false;
    }
    
    if (!isset($_SESSION['user_type']) || empty($_SESSION['user_type'])) {
        return false;
    }
    
    if (!isset($_SESSION['LAST_ACTIVITY'])) {
        return false;
    }
    
    if ((time() - $_SESSION['LAST_ACTIVITY']) > SESSION_TIMEOUT) {
        force_logout(false);
        return false;
    }
    
    $_SESSION['LAST_ACTIVITY'] = time();
    
    return true;
}
function require_login($user_type = null) {
    if (!is_logged_in()) {
        force_logout(true);
        exit();
    }
    
    if ($user_type !== null && $_SESSION['user_type'] !== $user_type) {
        log_security_event('unauthorized_access', "User tried to access {$user_type} area");
        force_logout(true);
        exit();
    }
}

function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}
function is_valid_admin_email($email) {
    return in_array($email, ADMIN_EMAILS);
}


function require_admin() {
    if (!is_logged_in() || !is_admin()) {
        log_security_event('unauthorized_admin_access', 'Non-admin tried to access admin area');
        header("Location: login.php?error=access_denied");
        exit();
    }
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}


function log_activity($user_id, $action, $description) {
    global $conn;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_type, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $action, $description, $ip, $user_agent);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    return false;
}

/**
 * Log security events
 */
function log_security_event($event_type, $description) {
    global $conn;
    
    $user_id = $_SESSION['user_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $conn->prepare("
        INSERT INTO security_logs 
        (user_id, event_type, description, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $event_type, $description, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Log failed login attempts
 */
function log_failed_login($email) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $stmt = $conn->prepare("
        INSERT INTO failed_logins (email, ip_address) 
        VALUES (?, ?)
    ");
    
    if ($stmt) {
        $stmt->bind_param("ss", $email, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Check if account is locked
 */
function is_account_locked($email) {
    global $conn;
    
    $time_threshold = time() - (30 * 60); // 30 minutes
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts 
        FROM failed_logins 
        WHERE email = ? 
        AND created_at > FROM_UNIXTIME(?)
    ");
    
    if ($stmt) {
        $stmt->bind_param("si", $email, $time_threshold);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['attempts'] >= 5;
    }
    
    return false;
}

/**
 * Validate IP address
 */
function validate_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $blocked_ips = [];
    
    if (in_array($ip, $blocked_ips)) {
        log_security_event('blocked_ip_access', "Blocked IP attempted access: $ip");
        die('Access denied');
    }
}

/**
 * Rate limiting check
 */
function check_rate_limit($action, $max_attempts = 5, $time_window = 300) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $time_threshold = time() - $time_window;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts 
        FROM activity_logs 
        WHERE activity_type = ? 
        AND ip_address = ? 
        AND created_at > FROM_UNIXTIME(?)
    ");
    
    if ($stmt) {
        $stmt->bind_param("ssi", $action, $ip_address, $time_threshold);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['attempts'] >= $max_attempts) {
            log_security_event('rate_limit_exceeded', "Rate limit exceeded for action: $action");
            return false;
        }
    }
    
    return true;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}





?>


