<?php
// Enable error reporting at the VERY TOP
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Check if config.php exists
if (!file_exists('config.php')) {
    die("Error: config.php file not found!");
}

// Include config with error handling
try {
    require_once 'config.php';
} catch (Exception $e) {
    die("Error loading config.php: " . $e->getMessage());
}

// Debug: Check database connection
if (!$conn || $conn->connect_error) {
    die("Database connection error: " . ($conn->connect_error ?? 'Unknown'));
}

// Check for logout
if (isset($_GET['logout'])) {
    force_logout();
    header("Location: login.php");
    exit();
}

// Check if already logged in
if (is_logged_in()) {
    $user_type = $_SESSION['user_type'] ?? null;
    if ($user_type === 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($user_type === 'citizen') {
        header("Location: citizen_dashboard.php");
    } else {
        header("Location: noncitizen_dashboard.php");
    }
    exit();
}

// Language handling
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'ar', 'en'])) {
    $lang = $_GET['lang'];
} elseif (isset($_SESSION['language']) && in_array($_SESSION['language'], ['fr', 'ar', 'en'])) {
    $lang = $_SESSION['language'];
} elseif (isset($_COOKIE['preferred_language']) && in_array($_COOKIE['preferred_language'], ['fr', 'ar', 'en'])) {
    $lang = $_COOKIE['preferred_language'];
} else {
    $lang = 'fr'; // Default to French
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simple validation
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = $t['empty_fields'];
    } else {
        try {
            // Check if account is locked (simplified for now)
            // is_account_locked function requires the failed_logins table
            
            // Check user in database
            $stmt = $conn->prepare("SELECT id, full_name, email, password, user_type FROM users WHERE email = ?");
            
            if (!$stmt) {
                $error = "Database error: " . $conn->error;
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Check for admin
                        if ($user['user_type'] === 'admin') {
                            if (!is_valid_admin_email($user['email'])) {
                                $error = $t['admin_access_denied'];
                            } else {
                                // Admin login successful
                                $_SESSION['user_id'] = $user['id'];
                                $_SESSION['user_email'] = $user['email'];
                                $_SESSION['full_name'] = $user['full_name'];
                                $_SESSION['user_type'] = 'admin';
                                $_SESSION['language'] = $lang;
                                $_SESSION['LAST_ACTIVITY'] = time();
                                
                                // Try to log activity (ignore if table doesn't exist)
                                @log_activity($user['id'], 'login', 'Admin logged in');
                                
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
                            
                            // Try to log activity
                            @log_activity($user['id'], 'login', 'User logged in');
                            
                            if ($user['user_type'] === 'citizen') {
                                header("Location: citizen_dashboard.php");
                            } else {
                                header("Location: noncitizen_dashboard.php");
                            }
                            exit();
                        }
                    } else {
                        $error = $t['invalid_credentials'];
                        @log_failed_login($email);
                    }
                } else {
                    $error = $t['invalid_credentials'];
                    @log_failed_login($email);
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = "System error: " . $e->getMessage();
        }
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
    <script src="login.js"></script>
</body>
</html>
