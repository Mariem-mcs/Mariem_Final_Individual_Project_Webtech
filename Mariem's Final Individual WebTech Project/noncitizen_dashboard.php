<?php
// Enable ALL errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEBUG: Show session info (comment out after fixing)
echo "<!-- DEBUG: Session ID = " . session_id() . " -->\n";
echo "<!-- DEBUG: Session data = " . json_encode($_SESSION) . " -->\n";

// Include config
require_once 'config.php';

// Simple check - no complex function calls
if (!isset($_SESSION['user_id'])) {
    echo "<!-- DEBUG: No user_id in session, redirecting to login -->\n";
    header("Location: login.php");
    exit();
}

// Check user type - accept both formats
$user_type = $_SESSION['user_type'] ?? '';
// Debug output
echo "<!-- DEBUG: user_type = '$user_type' -->\n";

// Accept both 'noncitizen' and 'non_citizen'
if ($user_type !== 'noncitizen' && $user_type !== 'non_citizen') {
    echo "<!-- DEBUG: user_type = '$user_type', not noncitizen -->\n";
    // If not noncitizen, redirect to appropriate page
    if ($user_type === 'citizen') {
        header("Location: citizen_dashboard.php");
    } elseif ($user_type === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        // Invalid user_type, go to login
        header("Location: login.php");
    }
    exit();
}

echo "<!-- DEBUG: User is noncitizen, continuing... -->\n";

$user_id = $_SESSION['user_id'];

// Language handling
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'ar', 'en'])) {
    $_SESSION['language'] = $_GET['lang'];
    header("Location: noncitizen_dashboard.php");
    exit();
}
$lang = $_SESSION['language'] ?? 'fr';

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if user exists
if (!$user) {
    echo "<!-- DEBUG: User not found in database -->\n";
    session_destroy();
    header("Location: login.php");
    exit();
}

$payment_amount = 45000; // Default for other countries
if (strtolower($user['nationality']) === 'senegal' || strtolower($user['nationality']) === 'sénégalaise') {
    $payment_amount = 1500;
}
$payment_amount_formatted = number_format($payment_amount, 0, ',', ' ') . ' MRU';

// The Language translations section:
$translations = [
    'en' => [
        'dashboard' => 'Resident Dashboard',
        'welcome' => 'Welcome',
        'account_status' => 'Residence Status',
        'profile' => 'My Profile',
        'residence_permit' => 'Residence Permit',
        'documents' => 'My Documents',
        'apply_id' => 'Apply for National ID',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'status_pending' => 'Pending Review',
        'status_active' => 'Valid Residence',
        'status_suspended' => 'Suspended',
        'personal_info' => 'Personal Information',
        'full_name' => 'Full Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'dob' => 'Date of Birth',
        'nationality' => 'Nationality',
        'quick_actions' => 'Quick Actions',
        'download_permit' => 'Download Residence Permit',
        'update_profile' => 'Update Profile',
        'extend_stay' => 'Extend Stay',
        'apply_national_id' => 'Apply for National ID',
        'residence_info' => 'Residence Information',
        'permit_number' => 'Permit Number',
        'entry_date' => 'Entry Date',
        'expiry_date' => 'Expiry Date',
        'visa_type' => 'Visa Type',
        'document_upload' => 'Document Upload',
        'upload_passport' => 'Upload Passport Copy',
        'upload_photo' => 'Upload Photo',
        'upload_proof' => 'Upload Proof of Address',
        'application_status' => 'Application Status',
        'no_application' => 'No active application',
        'apply_now' => 'Apply Now',
        'requirements' => 'Requirements for National ID',
        'req_1' => '• Valid passport',
        'req_2' => '• Valid residence permit',
        'req_3' => '• Proof of address',
        'req_4' => '• Two passport photos',
        'req_5' => '• Police clearance certificate',
        'steps_title' => 'Application Process',
        'step_1' => '1. Upload required documents',
        'step_2' => '2. Wait for verification (5-7 days)',
        'step_3' => '3. Visit nearest center for biometrics',
        'step_4' => '4. Receive ID card (10-14 days)',
        'recent_activity' => 'Recent Activity',
        'make_payment' => 'Make Payment',
        'payment_fee' => 'Residence Permit Fee',
        'pay_now' => 'Pay Now',
        'nationality_note' => 'Fee based on your nationality: ',
        'payment_modal_title' => 'Pay Residence Permit Fee',
        'select_provider' => 'Select Mobile Money Provider',
        'payment_instructions' => 'Payment Instructions',
        'step_payment_1' => '1. Dial the payment number on your phone',
        'step_payment_2' => '2. Enter the transaction ID as reference',
        'step_payment_3' => '3. Confirm payment of ',
        'step_payment_4' => '4. Take screenshot of confirmation',
        'step_payment_5' => '5. Upload receipt below',
        'upload_receipt' => 'Upload Payment Receipt',
        'submit_receipt' => 'Submit Receipt',
        'payment_notes' => 'Important Notes:',
        'note_1' => '• Payment verification takes 24-48 hours',
        'note_2' => '• Keep your transaction ID for reference',
        'note_3' => '• Only approved providers are listed',
        'note_4' => '• Contact support if payment fails',
        'senegal_rate' => 'Special rate for Senegalese nationals: 1,500 MRU/year',
        'other_rate' => 'Standard rate for other nationalities: 45,000 MRU/year',
        'residence_status' => 'Residence Permit Status',
        'application_pending' => 'Application Pending',
        'processing_time' => 'Estimated Processing Time',
        'working_days' => '15-30 working days',
        'after_approval' => 'After Approval:',
        'will_be_assigned' => '• Permit number will be assigned',
        'one_year_validity' => '• 1-year validity from approval date',
        'download_permit_card' => '• You can download your permit card',
        'renewal_available' => '• Renewal available 30 days before expiry',
        'sample_info_note' => 'Sample information shown. Your actual permit details will appear here after approval.'
    ],
    'fr' => [
        'dashboard' => 'Tableau de bord Résident',
        'welcome' => 'Bienvenue',
        'account_status' => 'Statut de résidence',
        'profile' => 'Mon profil',
        'residence_permit' => 'Permis de résidence',
        'documents' => 'Mes documents',
        'apply_id' => 'Demander carte nationale',
        'settings' => 'Paramètres',
        'logout' => 'Déconnexion',
        'status_pending' => 'En cours d\'examen',
        'status_active' => 'Résidence valide',
        'status_suspended' => 'Suspendu',
        'personal_info' => 'Informations personnelles',
        'full_name' => 'Nom complet',
        'email' => 'Email',
        'phone' => 'Téléphone',
        'dob' => 'Date de naissance',
        'nationality' => 'Nationalité',
        'quick_actions' => 'Actions rapides',
        'download_permit' => 'Télécharger le permis',
        'update_profile' => 'Mettre à jour',
        'extend_stay' => 'Prolonger le séjour',
        'apply_national_id' => 'Demander carte nationale',
        'residence_info' => 'Informations de résidence',
        'permit_number' => 'Numéro de permis',
        'entry_date' => 'Date d\'entrée',
        'expiry_date' => 'Date d\'expiration',
        'visa_type' => 'Type de visa',
        'document_upload' => 'Téléchargement de documents',
        'upload_passport' => 'Télécharger copie passeport',
        'upload_photo' => 'Télécharger photo',
        'upload_proof' => 'Télécharger justificatif',
        'application_status' => 'Statut de la demande',
        'no_application' => 'Aucune demande active',
        'apply_now' => 'Demander maintenant',
        'requirements' => 'Requis pour carte nationale',
        'req_1' => '• Passeport valide',
        'req_2' => '• Permis de résidence valide',
        'req_3' => '• Justificatif de domicile',
        'req_4' => '• Deux photos d\'identité',
        'req_5' => '• Certificat de police',
        'steps_title' => 'Processus de demande',
        'step_1' => '1. Télécharger les documents',
        'step_2' => '2. Attendre vérification (5-7 jours)',
        'step_3' => '3. Visiter le centre pour biométrie',
        'step_4' => '4. Recevoir la carte (10-14 jours)',
        'recent_activity' => 'Activité récente',
        'make_payment' => 'Effectuer le paiement',
        'payment_fee' => 'Frais de permis de résidence',
        'pay_now' => 'Payer maintenant',
        'nationality_note' => 'Frais selon votre nationalité: ',
        'payment_modal_title' => 'Payer les frais de permis',
        'select_provider' => 'Sélectionner un opérateur',
        'payment_instructions' => 'Instructions de paiement',
        'step_payment_1' => '1. Composez le numéro sur votre téléphone',
        'step_payment_2' => '2. Entrez l\'ID transaction comme référence',
        'step_payment_3' => '3. Confirmez le paiement de ',
        'step_payment_4' => '4. Prenez une capture d\'écran',
        'step_payment_5' => '5. Téléchargez le reçu ci-dessous',
        'upload_receipt' => 'Télécharger le reçu',
        'submit_receipt' => 'Soumettre le reçu',
        'payment_notes' => 'Notes importantes:',
        'note_1' => '• Vérification sous 24-48 heures',
        'note_2' => '• Gardez votre ID transaction',
        'note_3' => '• Seuls les opérateurs approuvés sont listés',
        'note_4' => '• Contactez le support en cas d\'échec',
        'senegal_rate' => 'Tarif spécial Sénégalais: 1 500 MRU/an',
        'other_rate' => 'Tarif standard autres nationalités: 45 000 MRU/an',
        'residence_status' => 'Statut du permis de résidence',
        'application_pending' => 'Demande en attente',
        'processing_time' => 'Temps de traitement estimé',
        'working_days' => '15-30 jours ouvrables',
        'after_approval' => 'Après approbation:',
        'will_be_assigned' => '• Numéro de permis sera attribué',
        'one_year_validity' => '• Validité d\'1 an à partir de la date d\'approbation',
        'download_permit_card' => '• Vous pourrez télécharger votre carte de permis',
        'renewal_available' => '• Renouvellement disponible 30 jours avant expiration',
        'sample_info_note' => 'Informations d\'exemple affichées. Vos détails réels apparaîtront ici après approbation.'
    ],
    'ar' => [
        'dashboard' => 'لوحة تحكم المقيم',
        'welcome' => 'مرحبا',
        'account_status' => 'حالة الإقامة',
        'profile' => 'ملفي',
        'residence_permit' => 'تصريح الإقامة',
        'documents' => 'مستنداتي',
        'apply_id' => 'طلب البطاقة الوطنية',
        'settings' => 'الإعدادات',
        'logout' => 'خروج',
        'status_pending' => 'قيد المراجعة',
        'status_active' => 'إقامة صالحة',
        'status_suspended' => 'معلق',
        'personal_info' => 'المعلومات الشخصية',
        'full_name' => 'الاسم الكامل',
        'email' => 'البريد',
        'phone' => 'الهاتف',
        'dob' => 'تاريخ الميلاد',
        'nationality' => 'الجنسية',
        'quick_actions' => 'إجراءات سريعة',
        'download_permit' => 'تحميل التصريح',
        'update_profile' => 'تحديث',
        'extend_stay' => 'تمديد الإقامة',
        'apply_national_id' => 'طلب البطاقة الوطنية',
        'residence_info' => 'معلومات الإقامة',
        'permit_number' => 'رقم التصريح',
        'entry_date' => 'تاريخ الدخول',
        'expiry_date' => 'تاريخ الانتهاء',
        'visa_type' => 'نوع التأشيرة',
        'document_upload' => 'رفع المستندات',
        'upload_passport' => 'رفع نسخة الجواز',
        'upload_photo' => 'رفع الصورة',
        'upload_proof' => 'رفع إثبات العنوان',
        'application_status' => 'حالة الطلب',
        'no_application' => 'لا يوجد طلب نشط',
        'apply_now' => 'قدم الآن',
        'requirements' => 'متطلبات البطاقة الوطنية',
        'req_1' => '• جواز سفر صالح',
        'req_2' => '• تصريح إقامة صالح',
        'req_3' => '• إثبات العنوان',
        'req_4' => '• صورتان شخصيتان',
        'req_5' => '• شهادة حسن سيرة وسلوك',
        'steps_title' => 'خطوات التقديم',
        'step_1' => '1. رفع المستندات المطلوبة',
        'step_2' => '2. انتظار التحقق (5-7 أيام)',
        'step_3' => '3. زيارة المركز للبيانات البيومترية',
        'step_4' => '4. استلام البطاقة (10-14 يوم)',
        'recent_activity' => 'النشاط الأخير',
        'make_payment' => 'دفع الرسوم',
        'payment_fee' => 'رسوم تصريح الإقامة',
        'pay_now' => 'ادفع الآن',
        'nationality_note' => 'الرسوم حسب جنسيتك: ',
        'payment_modal_title' => 'دفع رسوم تصريح الإقامة',
        'select_provider' => 'اختر مزود الموبايل موني',
        'payment_instructions' => 'تعليمات الدفع',
        'step_payment_1' => '1. اطلب رقم الدفع على هاتفك',
        'step_payment_2' => '2. أدخل رمز العملية كمرجع',
        'step_payment_3' => '3. تأكيد دفع مبلغ ',
        'step_payment_4' => '4. التقط صورة للتأكيد',
        'step_payment_5' => '5. ارفع الإيصال أدناه',
        'upload_receipt' => 'رفع إيصال الدفع',
        'submit_receipt' => 'إرسال الإيصال',
        'payment_notes' => 'ملاحظات هامة:',
        'note_1' => '• التحقق يستغرق 24-48 ساعة',
        'note_2' => '• احتفظ برمز العملية للرجوع إليه',
        'note_3' => '• فقط المزودون المعتمدون مدرجون',
        'note_4' => '• اتصل بالدعم إذا فشل الدفع',
        'senegal_rate' => 'سعر خاص للسنغاليين: 1,500 أوقية/سنة',
        'other_rate' => 'سعر قياسي للجنسيات الأخرى: 45,000 أوقية/سنة',
        'residence_status' => 'حالة تصريح الإقامة',
        'application_pending' => 'الطلب قيد الانتظار',
        'processing_time' => 'الوقت المقدر للمعالجة',
        'working_days' => '15-30 يوم عمل',
        'after_approval' => 'بعد الموافقة:',
        'will_be_assigned' => '• سيتم تعيين رقم التصريح',
        'one_year_validity' => '• صلاحية سنة واحدة من تاريخ الموافقة',
        'download_permit_card' => '• يمكنك تحميل بطاقة التصريح الخاصة بك',
        'renewal_available' => '• التجديد متاح قبل 30 يومًا من انتهاء الصلاحية',
        'sample_info_note' => 'يتم عرض معلومات نموذجية. ستظهر تفاصيل التصريح الفعلية هنا بعد الموافقة.'
    ]
];

$text = $translations[$lang] ?? $translations['fr'];
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

// Check for residence permit
$permit_query = $conn->prepare("SELECT * FROM residence_permits WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
$permit_query->bind_param("i", $user_id);
$permit_query->execute();
$permit_result = $permit_query->get_result();
$permit = $permit_result->fetch_assoc();

$has_active_permit = ($permit && $permit['status'] === 'active');

// REMOVE THE DEBUG COMMENTS FROM THE HTML OUTPUT
// Continue with your HTML as before...
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $text['dashboard']; ?> - IDTrack</title>
    <!-- Use citizen dashboard CSS or create your own -->
    <link rel="stylesheet" href="citizen_dashboard.css">
    <style>
        /* Add any noncitizen-specific styles here */
        .highlight {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%) !important;
        }
        .status-pending {
            background: rgba(241, 196, 15, 0.15);
            color: #92400e;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Continue with your HTML from line ~340 onward -->
        <!-- Your existing HTML code for the dashboard -->
        
        <!-- I'll show you where to continue - from your original file around line 340: -->
        <aside class="sidebar">
            <div class="logo">
                <img src="authentifactionAuthorizer.png" alt="Logo">
                <span>IDTrack</span>
            </div>
            
            <nav class="nav-menu">
                <a href="#overview" class="nav-item active">
                    📊 <span><?php echo $text['dashboard']; ?></span>
                </a>
                <a href="#profile" class="nav-item">
                    👤 <span><?php echo $text['profile']; ?></span>
                </a>
                <a href="#residence" class="nav-item">
                    📄 <span><?php echo $text['residence_permit']; ?></span>
                </a>
                <a href="#documents" class="nav-item">
                    📎 <span><?php echo $text['documents']; ?></span>
                </a>
                <a href="#apply-id" class="nav-item highlight">
                    🆔 <span><?php echo $text['apply_id']; ?></span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    🚪 <span><?php echo $text['logout']; ?></span>
                </a>
            </div>
        </aside>
        
        <main class="main-content">
            <!-- Continue with the rest of your HTML from the original file -->
            <!-- Copy everything from line ~360 to the end of your original file -->
            
            <!-- IMPORTANT: Copy the rest of your HTML from the original noncitizen_dashboard.php -->
            <!-- Starting from around line 360 to the end -->
            
            <!-- For now, I'll show a simplified version to test -->
            <header class="header">
                <div class="welcome">
                    <h1><?php echo $text['welcome']; ?>, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                    <p><?php echo date('l, F j, Y'); ?></p>
                </div>
            </header>
            
            <div class="dashboard-grid">
                <div class="card info-card">
                    <div class="card-header">
                        <h3>👤 <?php echo $text['personal_info']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="label"><?php echo $text['full_name']; ?>:</span>
                            <span class="value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label"><?php echo $text['email']; ?>:</span>
                            <span class="value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label"><?php echo $text['phone']; ?>:</span>
                            <span class="value"><?php echo htmlspecialchars($user['phone']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label"><?php echo $text['dob']; ?>:</span>
                            <span class="value"><?php echo date('d/m/Y', strtotime($user['date_of_birth'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label"><?php echo $text['nationality']; ?>:</span>
                            <span class="value"><?php echo htmlspecialchars($user['nationality']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>🏠 <?php echo $text['residence_status']; ?></h3>
                    </div>
                    <div class="card-body">
                        <p>Welcome to your noncitizen dashboard!</p>
                        <p>Your user type: <strong><?php echo $user_type; ?></strong></p>
                        <p>Your nationality: <strong><?php echo htmlspecialchars($user['nationality']); ?></strong></p>
                    </div>
                </div>
            </div>
            
            <div style="padding: 20px; text-align: center;">
                <p>✅ Noncitizen dashboard loaded successfully!</p>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </main>
    </div>
</body>
</html>
