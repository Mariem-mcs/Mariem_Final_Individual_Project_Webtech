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
define('SESSION_TIMEOUT', 1800);
define('SESSION_NAME', 'idtrack_session');

define('ADMIN_EMAILS', [
    'mariem.sall@gmail.com',
    'kgosafomaafo@ashesi.edu.gh',
    'marie.doh@ashesi.edu.gh'
]);

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// SIMPLIFIED SESSION FUNCTIONS FOR NOW
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

function force_logout($redirect_to_login = false) {
    session_destroy();
    if ($redirect_to_login) {
        header("Location: login.php");
        exit();
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Keep other functions but simplify
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function is_valid_admin_email($email) {
    return in_array($email, ADMIN_EMAILS);
}

// Comment out or simplify complex logging functions for now
function log_activity($user_id, $action, $description) {
    // Temporarily do nothing
    return true;
}

function log_security_event($event_type, $description) {
    // Temporarily do nothing
    return true;
}

function log_failed_login($email) {
    // Temporarily do nothing
    return true;
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
