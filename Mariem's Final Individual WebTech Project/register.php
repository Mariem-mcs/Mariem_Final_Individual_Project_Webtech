<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'ar', 'en'])) {
    $lang = $_GET['lang'];
} elseif (isset($_SESSION['language']) && in_array($_SESSION['language'], ['fr', 'ar', 'en'])) {
    $lang = $_SESSION['language'];
} elseif (isset($_COOKIE['preferred_language']) && in_array($_COOKIE['preferred_language'], ['fr', 'ar', 'en'])) {
    $lang = $_COOKIE['preferred_language'];
} else {
    $lang = 'fr';
}

$_SESSION['language'] = $lang;

// Translations
$t = [
    'en' => [
        'title' => 'Create Account', 'subtitle' => 'Join IDTrack Mauritania', 'user_type' => 'User Type',
        'citizen' => 'Mauritanian Citizen', 'non_citizen' => 'Resident', 'full_name' => 'Full Name',
        'gender' => 'Gender', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other',
        'email' => 'Email Address', 'phone' => 'Phone Number', 'dob' => 'Date of Birth',
        'nationality' => 'Nationality', 'national_id' => 'National ID (NMI)', 
        'national_id_placeholder' => '1234567890', 'national_id_help' => '10-digit National ID number',
        'passport' => 'Passport Number', 'passport_placeholder' => 'A12345678',
        'passport_help' => 'Format: Letter followed by 7-8 digits',
        'password' => 'Password', 'confirm_password' => 'Confirm Password',
        'required' => '*', 'password_requirements' => 'Password must contain:', 'req_length' => 'At least 8 characters',
        'req_upper' => 'Uppercase & lowercase', 'req_number' => 'At least one number', 'req_special' => 'Special character',
        'register_btn' => 'Create Account', 'have_account' => 'Already have an account?', 'login_link' => 'Login Here →',
        'back_home' => 'Back to Home', 'success_msg' => 'Registration successful! Redirecting to login...',
        'error_name' => 'Name must be at least 3 characters.', 'error_gender' => 'Please select your gender.',
        'error_email' => 'Invalid email address.', 'error_phone' => 'Phone number required.',
        'error_password_length' => 'Password must be 8+ characters.',
        'error_password_strength' => 'Password must contain uppercase, lowercase, number, special character.',
        'error_password_match' => 'Passwords do not match.', 'error_dob' => 'Date of birth required.',
        'error_age' => 'You must be 18+ years old.', 'error_nationality' => 'Nationality required.',
        'error_national_id' => 'Valid 10-digit National ID required.',
        'error_passport' => 'Valid passport number required (A12345678).',
        'error_email_exists' => 'Email already registered.', 'error_registration' => 'Registration failed.'
    ],
    'fr' => [
        'title' => 'Créer un Compte', 'subtitle' => 'Rejoignez IDTrack', 'user_type' => 'Type',
        'citizen' => 'Citoyen Mauritanien', 'non_citizen' => 'Résident', 'full_name' => 'Nom Complet',
        'gender' => 'Genre', 'male' => 'Homme', 'female' => 'Femme', 'other' => 'Autre',
        'email' => 'Email', 'phone' => 'Téléphone', 'dob' => 'Date de Naissance',
        'nationality' => 'Nationalité', 'national_id' => 'Numéro NMI',
        'national_id_placeholder' => '1234567890', 'national_id_help' => 'Numéro d\'identité nationale (10 chiffres)',
        'passport' => 'Numéro de Passeport', 'passport_placeholder' => 'A12345678',
        'passport_help' => 'Format: Lettre suivie de 7-8 chiffres',
        'password' => 'Mot de passe', 'confirm_password' => 'Confirmer',
        'required' => '*', 'password_requirements' => 'Le mot de passe doit contenir:', 'req_length' => '8+ caractères',
        'req_upper' => 'Majuscules et minuscules', 'req_number' => 'Un chiffre', 'req_special' => 'Caractère spécial',
        'register_btn' => 'Créer le Compte', 'have_account' => 'Vous avez un compte?', 'login_link' => 'Connectez-vous →',
        'back_home' => 'Retour', 'success_msg' => 'Inscription réussie! Redirection vers la connexion...',
        'error_name' => 'Nom: 3+ caractères.', 'error_gender' => 'Veuillez sélectionner votre genre.',
        'error_email' => 'Email invalide.', 'error_phone' => 'Téléphone requis.',
        'error_password_length' => 'Mot de passe: 8+ caractères.',
        'error_password_strength' => 'Mot de passe: majuscules, minuscules, chiffre, caractère spécial.',
        'error_password_match' => 'Mots de passe différents.', 'error_dob' => 'Date requise.',
        'error_age' => '18+ ans requis.', 'error_nationality' => 'Nationalité requise.',
        'error_national_id' => 'NMI valide requis (10 chiffres).',
        'error_passport' => 'Passeport valide requis (A12345678).',
        'error_email_exists' => 'Email déjà utilisé.', 'error_registration' => 'Inscription échouée.'
    ],
    'ar' => [
        'title' => 'إنشاء حساب', 'subtitle' => 'انضم إلى IDTrack', 'user_type' => 'النوع',
        'citizen' => 'مواطن موريتاني', 'non_citizen' => 'مقيم', 'full_name' => 'الاسم الكامل',
        'gender' => 'الجنس', 'male' => 'ذكر', 'female' => 'أنثى', 'other' => 'آخر',
        'email' => 'البريد الإلكتروني', 'phone' => 'الهاتف', 'dob' => 'تاريخ الميلاد',
        'nationality' => 'الجنسية', 'national_id' => 'رقم البطاقة الوطنية',
        'national_id_placeholder' => '1234567890', 'national_id_help' => 'رقم البطاقة الوطنية (10 أرقام)',
        'passport' => 'رقم جواز السفر', 'passport_placeholder' => 'A12345678',
        'passport_help' => 'التنسيق: حرف متبوع بـ 7-8 أرقام',
        'password' => 'كلمة المرور', 'confirm_password' => 'تأكيد',
        'required' => '*', 'password_requirements' => 'يجب أن تحتوي:', 'req_length' => '8+ أحرف',
        'req_upper' => 'أحرف كبيرة وصغيرة', 'req_number' => 'رقم', 'req_special' => 'حرف خاص',
        'register_btn' => 'إنشاء', 'have_account' => 'لديك حساب؟', 'login_link' => 'تسجيل الدخول ←',
        'back_home' => 'العودة', 'success_msg' => 'تم التسجيل! جاري التحويل...',
        'error_name' => 'الاسم: 3+ أحرف.', 'error_gender' => 'يرجى اختيار الجنس.',
        'error_email' => 'بريد غير صالح.', 'error_phone' => 'الهاتف مطلوب.',
        'error_password_length' => 'كلمة المرور: 8+ أحرف.',
        'error_password_strength' => 'كلمة المرور: أحرف كبيرة وصغيرة ورقم وحرف خاص.',
        'error_password_match' => 'كلمات المرور مختلفة.', 'error_dob' => 'التاريخ مطلوب.',
        'error_age' => 'العمر: 18+ سنة.', 'error_nationality' => 'الجنسية مطلوبة.',
        'error_national_id' => 'رقم بطاقة وطنية صحيح مطلوب (10 أرقام).',
        'error_passport' => 'رقم جواز سفر صحيح مطلوب (A12345678).',
        'error_email_exists' => 'البريد مستخدم.', 'error_registration' => 'فشل التسجيل.'
    ]
];

$text = $t[$lang];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $gender = $_POST['gender'] ?? 'male';
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $date_of_birth = sanitize_input($_POST['date_of_birth'] ?? '');
    $user_type = sanitize_input($_POST['user_type'] ?? 'citizen');
    
    $errors = [];
    if (strlen($full_name) < 3) $errors[] = $text['error_name'];
    if (!in_array($gender, ['male', 'female', 'other'])) $errors[] = $text['error_gender'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = $text['error_email'];
    if (empty($phone)) $errors[] = $text['error_phone'];
    $document_type = '';
    $document_number = '';
    $nationality = '';
    
    if ($user_type === 'citizen') {
        $national_id = sanitize_input($_POST['national_id'] ?? '');
        if (!preg_match('/^[0-9]{10}$/', $national_id)) {
            $errors[] = $text['error_national_id'];
        }
        $document_type = 'national_id';
        $document_number = $national_id;
        $nationality = 'Mauritanian';
    } else {
        $passport_number = sanitize_input($_POST['passport_number'] ?? '');
        $nationality = sanitize_input($_POST['nationality'] ?? '');
        if (!preg_match('/^[A-Z][0-9]{7,8}$/', $passport_number)) {
            $errors[] = $text['error_passport'];
        }
        if (empty($nationality)) {
            $errors[] = $text['error_nationality'];
        }
        $document_type = 'passport';
        $document_number = $passport_number;
    }
    
    if (strlen($password) < 8) $errors[] = $text['error_password_length'];
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = $text['error_password_strength'];
    }
    if ($password !== $confirm_password) $errors[] = $text['error_password_match'];
    
    if (empty($date_of_birth)) {
        $errors[] = $text['error_dob'];
    } else {
        $age = date_diff(date_create($date_of_birth), date_create('today'))->y;
        if ($age < 18) $errors[] = $text['error_age'];
    }
    
    if (empty($errors)) {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = $text['error_email_exists'];
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_stmt = $conn->prepare("INSERT INTO users (full_name, gender, email, phone, password, date_of_birth, nationality, user_type, document_type, document_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssssssss", 
                $full_name, $gender, $email, $phone, $hashed_password, 
                $date_of_birth, $nationality, $user_type, $document_type, $document_number
            );
            
            if ($insert_stmt->execute()) {
                $user_id = $insert_stmt->insert_id;
                
                try {
                    log_activity($user_id, 'register', 'New user registered');
                } catch (Exception $e) {
                    error_log("Failed to log activity: " . $e->getMessage());
                }
                
                header("Location: login.php?lang=" . $lang . "&registered=1");
                exit();
            } else {
                $error = $text['error_registration'] . ': ' . $conn->error;
            }
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

$dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $text['title']; ?> - IDTrack</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="register-box">
        <div class="logo-section">
            <img src="authentifactionAuthorizer.png" alt="Logo">
            <h1><?php echo $text['title']; ?></h1>
            <p><?php echo $text['subtitle']; ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="form-group">
                <label><?php echo $text['user_type']; ?> <span style="color:red">*</span></label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" id="citizen_radio" name="user_type" value="citizen" checked>
                        <span><?php echo $text['citizen']; ?></span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" id="non_citizen_radio" name="user_type" value="non_citizen">
                        <span><?php echo $text['non_citizen']; ?></span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label><?php echo $text['full_name']; ?> <span style="color:red">*</span></label>
                <input type="text" name="full_name" required minlength="3" 
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo $text['gender']; ?> <span style="color:red">*</span></label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="gender" value="male" required>
                        <span><?php echo $text['male']; ?></span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="gender" value="female" required>
                        <span><?php echo $text['female']; ?></span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="gender" value="other" required>
                        <span><?php echo $text['other']; ?></span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label><?php echo $text['email']; ?> <span style="color:red">*</span></label>
                <input type="email" name="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo $text['phone']; ?> <span style="color:red">*</span></label>
                <input type="tel" name="phone" placeholder="+222 XX XX XX XX" required 
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo $text['dob']; ?> <span style="color:red">*</span></label>
                <input type="date" name="date_of_birth" required 
                       value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
            </div>
            <div class="form-group" id="citizen_fields" style="display:block;">
                <label><?php echo $text['national_id']; ?> <span style="color:red">*</span></label>
                <input type="text" name="national_id" id="national_id_input" 
                       placeholder="<?php echo $text['national_id_placeholder']; ?>" 
                       pattern="[0-9]{10}" maxlength="10" 
                       value="<?php echo htmlspecialchars($_POST['national_id'] ?? ''); ?>">
                <small class="form-text"><?php echo $text['national_id_help']; ?></small>
            </div>
            <div class="form-group" id="non_citizen_fields" style="display:none;">
                <label><?php echo $text['passport']; ?> <span style="color:red">*</span></label>
                <input type="text" name="passport_number" id="passport_input" 
                       placeholder="<?php echo $text['passport_placeholder']; ?>" 
                       pattern="[A-Z][0-9]{7,8}" maxlength="9" 
                       value="<?php echo htmlspecialchars($_POST['passport_number'] ?? ''); ?>">
                <small class="form-text"><?php echo $text['passport_help']; ?></small>
            </div>

            <div class="form-group" id="nationality_field" style="display:none;">
                <label><?php echo $text['nationality']; ?> <span style="color:red">*</span></label>
                <input type="text" name="nationality" id="nationality_input" 
                       value="<?php echo htmlspecialchars($_POST['nationality'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo $text['password']; ?> <span style="color:red">*</span></label>
                <input type="password" name="password" id="password" required minlength="8">
            </div>
            <div class="form-group">
                <label><?php echo $text['confirm_password']; ?> <span style="color:red">*</span></label>
                <input type="password" name="confirm_password" id="confirm_password" required minlength="8">
            </div>
            <div class="password-requirements">
                <strong><?php echo $text['password_requirements']; ?></strong>
                <ul>
                    <li><?php echo $text['req_length']; ?></li>
                    <li><?php echo $text['req_upper']; ?></li>
                    <li><?php echo $text['req_number']; ?></li>
                    <li><?php echo $text['req_special']; ?></li>
                </ul>
            </div>

            <button type="submit" class="btn-register"><?php echo $text['register_btn']; ?></button>
        </form>

        <div class="divider"><span><?php echo $text['have_account']; ?></span></div>
        
        <div class="login-link">
            <a href="login.php?lang=<?php echo $lang; ?>"><?php echo $text['login_link']; ?></a>
        </div>

        <div class="back-home">
            <a href="index.html">← <?php echo $text['back_home']; ?></a>
        </div>
    </div>
    <script src="register.js"></script>
</body>
</html>