<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php'; 

$error = '';
$success = '';
$user_id = null;
$token_is_valid = false;

// Get language from session, default to 'fr'
$lang = $_SESSION['language'] ?? 'fr';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

// Language translations for this page
$translations = [
    'en' => [
        'title' => 'Reset Password',
        'subtitle' => 'Set a new password for your account.',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'reset_btn' => 'Reset Password',
        'back_login' => 'Back to Login',
        'token_invalid' => 'The password reset link is invalid or has expired. Please request a new one.',
        'password_min' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.',
        'password_match' => 'Passwords do not match.',
        'password_success' => 'Your password has been successfully reset! You can now log in.',
        'password_placeholder' => 'Enter new password',
        'confirm_placeholder' => 'Confirm new password',
    ],
    'fr' => [
        'title' => 'Réinitialiser le Mot de Passe',
        'subtitle' => 'Définissez un nouveau mot de passe pour votre compte.',
        'new_password' => 'Nouveau Mot de Passe',
        'confirm_password' => 'Confirmer le Mot de Passe',
        'reset_btn' => 'Réinitialiser le Mot de Passe',
        'back_login' => 'Retour à la Connexion',
        'token_invalid' => 'Le lien de réinitialisation est invalide ou a expiré. Veuillez en demander un nouveau.',
        'password_min' => 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères.',
        'password_match' => 'Les mots de passe ne correspondent pas.',
        'password_success' => 'Votre mot de passe a été réinitialisé avec succès ! Vous pouvez vous connecter.',
        'password_placeholder' => 'Entrez un nouveau mot de passe',
        'confirm_placeholder' => 'Confirmez le nouveau mot de passe',
    ],
    'ar' => [
        'title' => 'إعادة تعيين كلمة المرور',
        'subtitle' => 'قم بتعيين كلمة مرور جديدة لحسابك.',
        'new_password' => 'كلمة المرور الجديدة',
        'confirm_password' => 'تأكيد كلمة المرور',
        'reset_btn' => 'إعادة تعيين كلمة المرور',
        'back_login' => 'العودة لتسجيل الدخول',
        'token_invalid' => 'رابط إعادة تعيين كلمة المرور غير صالح أو انتهت صلاحيته. يرجى طلب رابط جديد.',
        'password_min' => 'يجب أن تتكون كلمة المرور من ' . PASSWORD_MIN_LENGTH . ' أحرف على الأقل.',
        'password_match' => 'كلمتا المرور غير متطابقتين.',
        'password_success' => 'تم إعادة تعيين كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول.',
        'password_placeholder' => 'أدخل كلمة المرور الجديدة',
        'confirm_placeholder' => 'تأكيد كلمة المرور الجديدة',
    ]
];

$t = $translations[$lang] ?? $translations['fr'];
if (isset($_GET['token'])) {
    $token = sanitize_input($_GET['token']);
    $current_time = date("Y-m-d H:i:s");

    // Check if token exists and is not expired
    $stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > ?");
    $stmt->bind_param("ss", $token, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();
        $user_id = $data['user_id'];
        $token_is_valid = true;
    } else {
        $error = $t['token_invalid'];
    }
} else {
    $error = $t['token_invalid'];
}

if ($token_is_valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error = $t['empty_fields'];
    } elseif ($new_password !== $confirm_password) {
        $error = $t['password_match'];
    } elseif (strlen($new_password) < PASSWORD_MIN_LENGTH) {
        $error = $t['password_min'];
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $conn->begin_transaction();
        
        try {
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $hashed_password, $user_id);
            $stmt_update->execute();
            $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt_delete->bind_param("s", $token);
            $stmt_delete->execute();
            $conn->commit();
            log_activity($user_id, 'password_reset', 'Password successfully reset via token.');
            
            $_SESSION['success_message'] = $t['password_success'];
            header("Location: login.php?lang=$lang");
            exit();

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $error = "Database error: " . $exception->getMessage();
            log_security_event('password_reset_fail', 'Password reset failed during transaction for user ID ' . $user_id);
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
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Verdana;
            background: linear-gradient(135deg, rgb(0, 98, 51) 0%, rgba(206, 17, 39, 0.916) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            background: white;
            max-width: 450px;
            width: 100%;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-section img {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
        }

        .logo-section h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .logo-section p {
            color: #666;
            font-size: 0.95rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
        }
        input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: rgb(0, 98, 51);
        }

        input::placeholder {
            color: #999;
        }
        a {
            color: rgb(0, 98, 51);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: white;
            color: rgb(0, 98, 51);
            border: 3px solid rgb(0, 98, 51);
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background: rgb(0, 98, 51);
            color: white;
        }

        .back-home {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-home a {
            color: #666;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: normal;
        }

        .back-home a:hover {
            color: rgb(0, 98, 51);
        }
        .language-switch {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
        }
        .language-switch a {
            padding: 0.5rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            color: #666;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        .language-switch a.active,
        .language-switch a:hover {
            background: rgb(0, 98, 51);
            color: white;
            border-color: rgb(0, 98, 51);
        }
        @media (max-width: 480px) {
            .login-box {
                padding: 2rem;
            }

            .logo-section h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo-section">
            <img src="authentifactionAuthorizer.png" alt="Logo">
            <h1><?php echo $t['title']; ?></h1>
            <p><?php echo $t['subtitle']; ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php 
        // Only show the password form if the token is valid:
        if ($token_is_valid && !$success): 
        ?>
        <form method="POST" autocomplete="off">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            
            <div class="form-group">
                <label for="password"><?php echo $t['new_password']; ?></label>
                <input type="password" name="password" id="password" 
                       placeholder="<?php echo $t['password_placeholder']; ?>" 
                       required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="confirm_password"><?php echo $t['confirm_password']; ?></label>
                <input type="password" name="confirm_password" id="confirm_password" 
                       placeholder="<?php echo $t['confirm_placeholder']; ?>" 
                       required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" autocomplete="off">
            </div>
            
            <button type="submit" class="btn-login"><?php echo $t['reset_btn']; ?></button>
        </form>
        <?php endif; ?>

        <div class="back-home" style="margin-top: 2rem;">
            <a href="login.php?lang=<?php echo $lang; ?>">← <?php echo $t['back_login']; ?></a>
        </div>
        
        <div class="language-switch">
            <a href="?lang=fr&token=<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>" class="<?php echo $lang === 'fr' ? 'active' : ''; ?>">FR</a>
            <a href="?lang=ar&token=<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>" class="<?php echo $lang === 'ar' ? 'active' : ''; ?>">AR</a>
            <a href="?lang=en&token=<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
        </div>
    </div>
</body>
</html>