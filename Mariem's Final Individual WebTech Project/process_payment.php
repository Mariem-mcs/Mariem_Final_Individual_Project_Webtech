<?php
session_start();
require_once 'config.php';

if (!is_logged_in() || $_SESSION['user_type'] !== 'non_citizen') {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $transaction_id = $_POST['transaction_id'];
    $provider = $_POST['selected_provider'];
    $amount = $_POST['amount'];
    
    if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['receipt_file']['tmp_name'];
        $file_name = basename($_FILES['receipt_file']['name']);
        $file_size = $_FILES['receipt_file']['size'];
        $file_type = $_FILES['receipt_file']['type'];
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = 'Invalid file type. Only JPG, PNG, and PDF allowed.';
            redirect('noncitizen_dashboard.php');
        }
        if ($file_size > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'File too large. Maximum size is 5MB.';
            redirect('noncitizen_dashboard.php');
        }
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_filename = 'receipt_' . $transaction_id . '.' . $file_ext;
        $upload_path = 'uploads/receipts/' . $new_filename;
                if (!file_exists('uploads/receipts')) {
            mkdir('uploads/receipts', 0777, true);
        }
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $stmt = $conn->prepare("INSERT INTO payments (user_id, transaction_id, provider, amount, receipt_path, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issds", $user_id, $transaction_id, $provider, $amount, $upload_path);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Payment receipt submitted successfully. Verification will take 24-48 hours.';
            } else {
                $_SESSION['error'] = 'Error saving payment record.';
            }
        } else {
            $_SESSION['error'] = 'Error uploading file.';
        }
    } else {
        $_SESSION['error'] = 'Please select a receipt file.';
    }
    
    redirect('noncitizen_dashboard.php');
}
?>