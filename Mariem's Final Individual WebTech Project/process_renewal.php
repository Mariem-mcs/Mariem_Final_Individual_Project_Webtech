<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in:
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$user_id = $_SESSION['user_id'];
$request_type = $data['request_type'] ?? '';
$payment_method = $data['payment_method'] ?? '';
$sender_phone = $data['sender_phone'] ?? '';
$transaction_ref = $data['transaction_ref'] ?? '';
$amount = $data['amount'] ?? 0;

if (empty($payment_method) || empty($sender_phone) || empty($transaction_ref) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}
if (!in_array($request_type, ['id_card_renewal', 'permit_renewal'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request type']);
    exit();
}

try {
    if ($request_type === 'id_card_renewal') {
        $renewal_reason = $data['renewal_reason'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO renewal_requests (user_id, request_type, renewal_reason, payment_method, sender_phone, transaction_ref, amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isssssd", $user_id, $request_type, $renewal_reason, $payment_method, $sender_phone, $transaction_ref, $amount);
        
    } elseif ($request_type === 'permit_renewal') {
        $nationality = $data['nationality'] ?? '';
        $permit_duration = $data['permit_duration'] ?? 1;
        
        $stmt = $conn->prepare("INSERT INTO renewal_requests (user_id, request_type, nationality, permit_duration, payment_method, sender_phone, transaction_ref, amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isssissd", $user_id, $request_type, $nationality, $permit_duration, $payment_method, $sender_phone, $transaction_ref, $amount);
    }
    
    if ($stmt->execute()) {
        $request_id = $stmt->insert_id;
        if (function_exists('log_activity')) {
            try {
                log_activity($user_id, 'renewal_request', "Submitted $request_type request with transaction $transaction_ref");
            } catch (Exception $e) {
                error_log("Failed to log activity: " . $e->getMessage());
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Request submitted successfully',
            'request_id' => $request_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => "Failed to submit request: {$stmt->error}"]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>