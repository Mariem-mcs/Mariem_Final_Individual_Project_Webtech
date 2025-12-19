<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
 

$error = '';
$success = '';

$lang = $_SESSION['language'] ?? 'fr';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

$translations = [
    'en' => [
        'title' => 'Forgot Password',
        'subtitle' => 'Enter your email address to receive a password reset link.',
        'email' => 'Email Address',
        'send_btn' => 'Send Reset Link',
        'back_login' => 'Back to Login',
        'email_sent' => 'If an account with that email exists, a password reset link has been sent.',
        'email_placeholder' => 'Enter your email',
        'invalid_email' => 'Please enter a valid email address.',
    ],
];

$t = $translations[$lang] ?? $translations['en'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = $t['invalid_email'];
    } else {
        // 1. Checking if the account exists:
        $stmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $success = $t['email_sent'];

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", time() + 3600); // Token expires in 1 hour
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE token=?, expires_at=?");
            $stmt->bind_param("issss", $user['id'], $token, $expires, $token, $expires);
            $stmt->execute();

            // 4. Send Email (PLACEHOLDER - Implementation required)
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
            
            // --- YOU MUST IMPLEMENT YOUR EMAIL SENDING LOGIC HERE ---
            // Example using PHP's built-in mail() function (NOT RECOMMENDED for production):
            // $subject = 'Password Reset Request';
            // $message = 'Click this link to reset your password: ' . $reset_link;
            // mail($email, $subject, $message, "From: no-reply@idtrack.com");
            
            // Log the request
            log_activity($user['id'], 'password_reset_request', 'Password reset requested via email.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> - IDTrack</title>
    <link rel="stylesheet" href="login.css"> 
</head>
<body>
    <div class="login-box">
        <div class="logo-section">
            <h1><?php echo $t['title']; ?></h1>
            <p><?php echo $t['subtitle']; ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST">
            <div class="form-group">
                <label><?php echo $t['email']; ?></label>
                <input type="email" name="email" placeholder="<?php echo $t['email_placeholder']; ?>" required>
            </div>
            <button type="submit" class="btn-login"><?php echo $t['send_btn']; ?></button>
        </form>
        <?php endif; ?>

        <div class="back-home" style="margin-top: 2rem;">
            <a href="login.php?lang=<?php echo $lang; ?>">← <?php echo $t['back_login']; ?></a>
        </div>
    </div>
</body>
</html>