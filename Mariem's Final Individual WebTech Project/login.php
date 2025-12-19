<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config FIRST (before any session start)
require_once 'config.php';

// Now start the secure session manually
start_secure_session();

// FORCE LOGOUT - Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy the session
session_destroy();

// Start fresh session
session_start();
$_SESSION = array();

// Language handling
$lang = 'fr'; // Default
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'ar', 'en'])) {
    $lang = $_GET['lang'];
} elseif (isset($_SESSION['language']) && in_array($_SESSION['language'], ['fr', 'ar', 'en'])) {
    $lang = $_SESSION['language'];
}
$_SESSION['language'] = $lang;

$translations = [
    'en' => [
        'title' => 'Welcome Back',
        'subtitle' => 'Login to your account',
        'email' => 'Email Address',
        'password' => 'Password',
        'login_btn' => 'Login',
        'no_account' => "Don't have an account?",
        'register' => 'Register Now →',
        'back_home' => 'Back to Home',
        'email_placeholder' => 'Enter your email',
        'password_placeholder' => 'Enter your password',
        'empty_fields' => 'Please enter both email and password.',
        'invalid_credentials' => 'Invalid email or password.',
        'forget_password' => 'Forgot Password?',
        'admin_access_denied' => 'Admin access denied. Please use a valid admin email.',
        'account_locked' => 'Account locked. Please try again in 30 minutes.'
    ],
    'fr' => [
        'title' => 'Bon Retour',
        'subtitle' => 'Connectez-vous à votre compte',
        'email' => 'Adresse Email',
        'password' => 'Mot de passe',
        'login_btn' => 'Connexion',
        'no_account' => "Vous n'avez pas de compte?",
        'register' => 'Inscrivez-vous →',
        'back_home' => "Retour à l'accueil",
        'email_placeholder' => 'Entrez votre email',
        'password_placeholder' => 'Entrez votre mot de passe',
        'empty_fields' => "Veuillez entrer l'email et le mot de passe.",
        'invalid_credentials' => 'Email ou mot de passe invalide.',
        'forget_password' => 'Mot de passe oublié?',
        'admin_access_denied' => 'Accès administrateur refusé. Veuillez utiliser un email admin valide.',
        'account_locked' => 'Compte bloqué. Veuillez réessayer dans 30 minutes.'
    ],
    'ar' => [
        'title' => 'مرحبا بعودتك',
        'subtitle' => 'تسجيل الدخول إلى حسابك',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'login_btn' => 'تسجيل الدخول',
        'no_account' => 'ليس لديك حساب؟',
        'register' => 'سجل الآن ←',
        'back_home' => 'العودة للرئيسية',
        'email_placeholder' => 'أدخل بريدك الإلكتروني',
        'password_placeholder' => 'أدخل كلمة المرور',
        'empty_fields' => 'يرجى إدخال البريد الإلكتروني وكلمة المرور.',
        'invalid_credentials' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
        'forget_password' => 'هل نسيت كلمة المرور؟',
        'admin_access_denied' => 'تم رفض وصول المسؤول. الرجاء استخدام بريد إلكتروني صالح للمسؤول.',
        'account_locked' => 'الحساب مغلق. يرجى المحاولة مرة أخرى بعد 30 دقيقة.'
    ]
];

$t = $translations[$lang] ?? $translations['fr'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = $t['empty_fields'];
    } else {
        $email = sanitize_input($email);
        
        $stmt = $conn->prepare("SELECT id, full_name, email, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                if ($user['user_type'] === 'admin') {
                    if (!is_valid_admin_email($user['email'])) {
                        $error = $t['admin_access_denied'];
                        log_failed_login($email);
                        log_security_event('admin_access_denied', "Non-admin email tried to login as admin: $email");
                    } else {
                        // Admin login successful
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['language'] = $lang;
                        $_SESSION['LAST_ACTIVITY'] = time();
                        
                        log_activity($user['id'], 'login', 'Admin logged in');
                        header("Location: admin_dashboard.php");
                        exit();
                    }
                } else {
                    // Regular user login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['language'] = $lang;
                    $_SESSION['LAST_ACTIVITY'] = time();
                    
                    log_activity($user['id'], 'login', 'User logged in');
                    
                    if ($user['user_type'] === 'citizen') {
                        header("Location: citizen_dashboard.php");
                    } else {
                        header("Location: noncitizen_dashboard.php");
                    }
                    exit();
                }
            } else {
                $error = $t['invalid_credentials'];
                log_failed_login($email);
            }
        } else {
            $error = $t['invalid_credentials'];
            log_failed_login($email);
        }
        $stmt->close();
    }
}

$dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> - IDTrack</title>
    <link rel="stylesheet" href="login.css">
    <style>
        /* Minimal inline CSS in case external CSS fails */
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .login-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .logo-section h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 8px;
        }
        .logo-section p {
            color: #666;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
        }
        .form-footer {
            text-align: right;
            margin-bottom: 20px;
        }
        .forget-password-link {
            color: #667eea;
            text-decoration: none;
            font-size: 13px;
        }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 25px;
        }
        .divider {
            position: relative;
            margin: 25px 0;
            text-align: center;
        }
        .divider span {
            background: white;
            padding: 0 15px;
            color: #666;
            font-size: 13px;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
        }
        .register-link, .back-home {
            margin: 10px 0;
        }
        .register-link a, .back-home a {
            color: #667eea;
            text-decoration: none;
        }
        .language-switch {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }
        .language-switch a {
            color: #666;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .language-switch a.active {
            background: #667eea;
            color: white;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo-section">
            <img src="authentifactionAuthorizer.png" alt="Logo" style="width: 80px; height: 80px; margin-bottom: 15px;">
            <h1><?php echo $t['title']; ?></h1>
            <p><?php echo $t['subtitle']; ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off"> 
            <div class="form-group">
                <label><?php echo $t['email']; ?></label>
                <input type="email" name="email" placeholder="<?php echo $t['email_placeholder']; ?>" required 
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        autocomplete="off">
            </div>

            <div class="form-group">
                <label><?php echo $t['password']; ?></label>
                <input type="password" name="password" placeholder="<?php echo $t['password_placeholder']; ?>" required
                       autocomplete="off">
            </div>
            
            <div class="form-footer">
                <a href="forgot_password.php?lang=<?php echo $lang; ?>" class="forget-password-link">
                    <?php echo $t['forget_password']; ?>
                </a>
            </div>
            <button type="submit" class="btn-login"><?php echo $t['login_btn']; ?></button>
        </form>

        <div class="divider"><span><?php echo $t['no_account']; ?></span></div> 

        <div class="register-link">
            <a href="register.php?lang=<?php echo $lang; ?>"><?php echo $t['register']; ?></a>
        </div>

        <div class="back-home">
            <a href="index.html"><?php echo $t['back_home']; ?></a>
        </div>

        <div class="language-switch">
            <a href="?lang=fr" class="<?php echo $lang === 'fr' ? 'active' : ''; ?>">FR</a>
            <a href="?lang=ar" class="<?php echo $lang === 'ar' ? 'active' : ''; ?>">AR</a>
            <a href="?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
        </div>
    </div>
</body>
</html>
