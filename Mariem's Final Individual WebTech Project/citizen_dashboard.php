<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config
require_once 'config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check user type
$user_type = $_SESSION['user_type'] ?? '';
if ($user_type !== 'noncitizen' && $user_type !== 'non_citizen') {
    if ($user_type === 'citizen') {
        header("Location: citizen_dashboard.php");
        exit();
    } elseif ($user_type === 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

$user_id = $_SESSION['user_id'];

// Language handling
$lang = 'fr'; // Default
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'ar', 'en'])) {
    $lang = $_GET['lang'];
    $_SESSION['language'] = $lang;
} elseif (isset($_SESSION['language']) && in_array($_SESSION['language'], ['fr', 'ar', 'en'])) {
    $lang = $_SESSION['language'];
} else {
    $_SESSION['language'] = $lang;
}

// Get user data
// Replace with your database connection
$conn = new mysqli('localhost', 'username', 'password', 'database_name');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Calculate payment amount based on nationality
$nationality = strtolower(trim($user['nationality'] ?? ''));
$is_senegalese = false;

// Check if Senegalese
$senegalese_keywords = ['senegal', 'sénégal', 'sénégalaise', 'senegalese'];
foreach ($senegalese_keywords as $keyword) {
    if (strpos($nationality, $keyword) !== false) {
        $is_senegalese = true;
        break;
    }
}

$payment_amount = $is_senegalese ? 1500 : 45000;
$payment_amount_formatted = number_format($payment_amount, 0, ',', ' ') . ' MRU';

// Language translations
$translations = [
    'fr' => [
        'dashboard' => 'Tableau de bord Résident',
        'welcome' => 'Bienvenue',
        'personal_info' => 'Informations personnelles',
        'full_name' => 'Nom complet',
        'email' => 'Email',
        'phone' => 'Téléphone',
        'dob' => 'Date de naissance',
        'nationality' => 'Nationalité',
        'logout' => 'Déconnexion',
        'status_pending' => 'En cours d\'examen',
        'status_active' => 'Résidence valide',
        'payment_fee' => 'Frais de permis de résidence',
        'pay_now' => 'Payer maintenant',
        'nationality_note' => 'Frais selon votre nationalité:',
        'senegal_rate' => 'Tarif spécial Sénégalais: 1 500 MRU/an',
        'other_rate' => 'Tarif standard autres nationalités: 45 000 MRU/an',
        'residence_status' => 'Statut du permis de résidence',
        'welcome_message' => 'Bienvenue sur votre tableau de bord résident!',
        'user_type_label' => 'Type d\'utilisateur',
        'documents' => 'Mes documents',
        'upload_document' => 'Télécharger un document',
        'passport_copy' => 'Copie passeport',
        'photo' => 'Photo',
        'upload' => 'Télécharger',
        'transaction_id' => 'ID de transaction',
        'payment_provider' => 'Opérateur de paiement',
        'bankily' => 'Bankily',
        'masrivi' => 'Masrivi',
        'sadad' => 'Sadad',
        'click' => 'Click',
        'binbank' => 'BinBank',
        'moovemauritel' => 'Moove/Mauritel',
        'submit_receipt' => 'Soumettre le reçu',
        'payment_instructions' => 'Instructions de paiement',
        'step_payment_1' => '1. Composez le numéro sur votre téléphone',
        'step_payment_2' => '2. Entrez l\'ID transaction comme référence',
        'step_payment_3' => '3. Confirmez le paiement de',
        'upload_receipt' => 'Télécharger le reçu',
        'payment_notes' => 'Notes importantes:',
        'note_1' => '• Vérification sous 24-48 heures',
        'note_2' => '• Gardez votre ID transaction'
    ],
    'en' => [
        'dashboard' => 'Resident Dashboard',
        'welcome' => 'Welcome',
        'personal_info' => 'Personal Information',
        'full_name' => 'Full Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'dob' => 'Date of Birth',
        'nationality' => 'Nationality',
        'logout' => 'Logout',
        'status_pending' => 'Pending Review',
        'status_active' => 'Valid Residence',
        'payment_fee' => 'Residence Permit Fee',
        'pay_now' => 'Pay Now',
        'nationality_note' => 'Fee based on your nationality:',
        'senegal_rate' => 'Special rate for Senegalese: 1,500 MRU/year',
        'other_rate' => 'Standard rate for other nationalities: 45,000 MRU/year',
        'residence_status' => 'Residence Permit Status',
        'welcome_message' => 'Welcome to your resident dashboard!',
        'user_type_label' => 'User Type',
        'documents' => 'My Documents',
        'upload_document' => 'Upload Document',
        'passport_copy' => 'Passport Copy',
        'photo' => 'Photo',
        'upload' => 'Upload',
        'transaction_id' => 'Transaction ID',
        'payment_provider' => 'Payment Provider',
        'bankily' => 'Bankily',
        'masrivi' => 'Masrivi',
        'sadad' => 'Sadad',
        'click' => 'Click',
        'binbank' => 'BinBank',
        'moovemauritel' => 'Moove/Mauritel',
        'submit_receipt' => 'Submit Receipt',
        'payment_instructions' => 'Payment Instructions',
        'step_payment_1' => '1. Dial the number on your phone',
        'step_payment_2' => '2. Enter the transaction ID as reference',
        'step_payment_3' => '3. Confirm payment of',
        'upload_receipt' => 'Upload Receipt',
        'payment_notes' => 'Important Notes:',
        'note_1' => '• Verification within 24-48 hours',
        'note_2' => '• Keep your transaction ID'
    ],
    'ar' => [
        'dashboard' => 'لوحة تحكم المقيم',
        'welcome' => 'مرحبا',
        'personal_info' => 'المعلومات الشخصية',
        'full_name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'dob' => 'تاريخ الميلاد',
        'nationality' => 'الجنسية',
        'logout' => 'تسجيل الخروج',
        'status_pending' => 'قيد المراجعة',
        'status_active' => 'إقامة صالحة',
        'payment_fee' => 'رسوم تصريح الإقامة',
        'pay_now' => 'ادفع الآن',
        'nationality_note' => 'الرسوم حسب جنسيتك:',
        'senegal_rate' => 'سعر خاص للسنغاليين: 1,500 أوقية/سنة',
        'other_rate' => 'سعر قياسي للجنسيات الأخرى: 45,000 أوقية/سنة',
        'residence_status' => 'حالة تصريح الإقامة',
        'welcome_message' => 'مرحبا بكم في لوحة تحكم المقيم!',
        'user_type_label' => 'نوع المستخدم',
        'documents' => 'مستنداتي',
        'upload_document' => 'رفع مستند',
        'passport_copy' => 'نسخة جواز السفر',
        'photo' => 'صورة',
        'upload' => 'رفع',
        'transaction_id' => 'معرف المعاملة',
        'payment_provider' => 'مزود الدفع',
        'bankily' => 'بنكيلي',
        'masrivi' => 'مصرفي',
        'sadad' => 'سداد',
        'click' => 'كليك',
        'binbank' => 'بن بانك',
        'moovemauritel' => 'موف/موريتل',
        'submit_receipt' => 'إرسال الإيصال',
        'payment_instructions' => 'تعليمات الدفع',
        'step_payment_1' => '1. اطلب الرقم على هاتفك',
        'step_payment_2' => '2. أدخل معرف المعاملة كمرجع',
        'step_payment_3' => '3. تأكيد دفع مبلغ',
        'upload_receipt' => 'رفع الإيصال',
        'payment_notes' => 'ملاحظات هامة:',
        'note_1' => '• التحقق خلال 24-48 ساعة',
        'note_2' => '• احتفظ بمعرف المعاملة'
    ]
];

$text = $translations[$lang] ?? $translations['fr'];
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $text['dashboard']; ?> - IDTrack</title>
    <link rel="stylesheet" href="noncitizen_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="authentifactionAuthorizer.png" alt="Logo">
                <span>IDTrack</span>
            </div>
            
            <nav class="nav-menu">
                <a href="#overview" class="nav-item active">
                    📊 <span><?php echo $text['dashboard']; ?></span>
                </a>
                <a href="#documents" class="nav-item">
                    📎 <span><?php echo $text['documents']; ?></span>
                </a>
                <a href="#payment" class="nav-item">
                    💰 <span>Paiement</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    🚪 <span><?php echo $text['logout']; ?></span>
                </a>
            </div>
        </aside>
        
        <main class="main-content">
            <div class="language-switch">
                <a href="?lang=fr" class="<?php echo $lang === 'fr' ? 'active' : ''; ?>">FR</a>
                <a href="?lang=ar" class="<?php echo $lang === 'ar' ? 'active' : ''; ?>">AR</a>
                <a href="?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
            </div>
            
            <header class="header">
                <div class="welcome">
                    <h1><?php echo $text['welcome']; ?>, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                    <p><?php echo date('d/m/Y'); ?></p>
                </div>
            </header>
            
            <section id="overview" class="section">
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
                                <span class="value"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label"><?php echo $text['dob']; ?>:</span>
                                <span class="value"><?php echo !empty($user['date_of_birth']) ? date('d/m/Y', strtotime($user['date_of_birth'])) : 'N/A'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label"><?php echo $text['nationality']; ?>:</span>
                                <span class="value"><?php echo htmlspecialchars($user['nationality'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>🏠 <?php echo $text['residence_status']; ?></h3>
                        </div>
                        <div class="card-body">
                            <p><?php echo $text['welcome_message']; ?></p>
                            <div class="info-item">
                                <span class="label"><?php echo $text['user_type_label']; ?>:</span>
                                <span class="value">Résident</span>
                            </div>
                            <div class="info-item">
                                <span class="label"><?php echo $text['nationality']; ?>:</span>
                                <span class="value"><?php echo htmlspecialchars($user['nationality'] ?? 'N/A'); ?></span>
                            </div>
                            
                            <div class="status-badge status-pending">
                                ⏳ <?php echo $text['status_pending']; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card payment-card">
                    <div class="card-header">
                        <h3>💰 <?php echo $text['payment_fee']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="payment-info">
                            <div class="amount-display">
                                <div class="amount-label"><?php echo $text['nationality_note']; ?></div>
                                <div class="amount-value"><?php echo $payment_amount_formatted; ?></div>
                                <div class="amount-description">
                                    <?php echo $is_senegalese ? $text['senegal_rate'] : $text['other_rate']; ?>
                                </div>
                                <div class="payment-amount-note">
                                    <?php echo $is_senegalese ? '🇸🇳 Vous êtes Sénégalais(e) - tarif préférentiel' : '🌍 Autre nationalité - tarif standard'; ?>
                                </div>
                            </div>
                            <button onclick="openPaymentModal()" class="action-btn payment">
                                💳 <?php echo $text['pay_now']; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            
            <section id="documents" class="section">
                <div class="card">
                    <div class="card-header">
                        <h3>📎 <?php echo $text['documents']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="upload-section">
                            <p style="margin-bottom: 15px; color: #666;">
                                Téléchargez les documents requis pour votre permis de résidence.
                            </p>
                            <button onclick="uploadDocuments()" class="btn-upload">
                                📤 <?php echo $text['upload_document']; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            
            <div class="success-message">
                ✅ Tableau de bord résident fonctionnel!
                <div style="font-size: 0.9rem; margin-top: 5px; opacity: 0.9;">
                    <?php echo $text['nationality']; ?>: <strong><?php echo htmlspecialchars($user['nationality'] ?? 'N/A'); ?></strong> • 
                    Tarif: <strong><?php echo $is_senegalese ? '1,500 MRU' : '45,000 MRU'; ?></strong>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>💰 Payer les frais de permis</h3>
                <span class="close-modal" onclick="closePaymentModal()">×</span>
            </div>
            <div class="modal-body">
                <div class="payment-info">
                    <div class="amount-display">
                        <div class="amount-label"><?php echo $text['nationality_note']; ?></div>
                        <div class="amount-value"><?php echo $payment_amount_formatted; ?></div>
                        <div class="amount-description">
                            <?php echo $is_senegalese ? $text['senegal_rate'] : $text['other_rate']; ?>
                        </div>
                        <div class="transaction-id" id="displayTransactionId">TRX-<?php echo time(); ?></div>
                    </div>
                    
                    <h4><?php echo $text['payment_provider']; ?>:</h4>
                    <div class="providers-grid">
                        <div class="provider-option" onclick="selectProvider('bankily', 'Bankily', this)">
                            <div class="provider-name"><?php echo $text['bankily']; ?></div>
                        </div>
                        <div class="provider-option" onclick="selectProvider('masrivi', 'Masrivi', this)">
                            <div class="provider-name"><?php echo $text['masrivi']; ?></div>
                        </div>
                        <div class="provider-option" onclick="selectProvider('sadad', 'Sadad', this)">
                            <div class="provider-name"><?php echo $text['sadad']; ?></div>
                        </div>
                        <div class="provider-option" onclick="selectProvider('click', 'Click', this)">
                            <div class="provider-name"><?php echo $text['click']; ?></div>
                        </div>
                        <div class="provider-option" onclick="selectProvider('binbank', 'BinBank', this)">
                            <div class="provider-name"><?php echo $text['binbank']; ?></div>
                        </div>
                        <div class="provider-option" onclick="selectProvider('moovemauritel', 'Moove/Mauritel', this)">
                            <div class="provider-name"><?php echo $text['moovemauritel']; ?></div>
                        </div>
                    </div>
                    
                    <div class="selected-provider" id="selectedProviderInfo" style="display: none;">
                        <p><strong>Opérateur sélectionné:</strong> <span id="selectedProviderName"></span></p>
                        <p><strong>Numéro à composer:</strong> <span id="providerNumber" class="provider-number">+222 XX XX XX XX</span></p>
                    </div>
                    
                    <form id="receiptUploadForm">
                        <input type="hidden" name="transaction_id" id="paymentTransactionId" value="TRX-<?php echo time(); ?>">
                        <input type="hidden" name="payment_provider" id="selectedProvider" value="">
                        
                        <div class="payment-instructions">
                            <h4><?php echo $text['payment_instructions']; ?></h4>
                            <ol>
                                <li><?php echo $text['step_payment_1']; ?></li>
                                <li><?php echo $text['step_payment_2']; ?>: <strong id="instructionTransactionId">TRX-<?php echo time(); ?></strong></li>
                                <li><?php echo $text['step_payment_3']; ?> <strong><?php echo $payment_amount_formatted; ?></strong></li>
                                <li>Prenez une capture d'écran de la confirmation</li>
                                <li><?php echo $text['upload_receipt']; ?></li>
                            </ol>
                        </div>
                        
                        <button type="button" onclick="completePayment()" class="btn-upload-receipt">
                            📤 <?php echo $text['submit_receipt']; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="noncitizen_dashboard.js"></script>
    <script>
    function openPaymentModal() {
        document.getElementById('paymentModal').style.display = 'block';
        const transactionId = 'TRX-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
        document.getElementById('displayTransactionId').textContent = transactionId;
        document.getElementById('instructionTransactionId').textContent = transactionId;
        document.getElementById('paymentTransactionId').value = transactionId;
    }
    
    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
        document.getElementById('selectedProviderInfo').style.display = 'none';
        document.getElementById('selectedProvider').value = '';
        document.querySelectorAll('.provider-option').forEach(el => {
            el.classList.remove('selected');
        });
    }
    
    function selectProvider(provider, name, element) {
        document.querySelectorAll('.provider-option').forEach(el => {
            el.classList.remove('selected');
        });
        element.classList.add('selected');
        document.getElementById('selectedProvider').value = provider;
        document.getElementById('selectedProviderName').textContent = name;
        document.getElementById('providerNumber').textContent = '+222 48305130';
        document.getElementById('selectedProviderInfo').style.display = 'block';
    }
    
    function completePayment() {
        const provider = document.getElementById('selectedProvider').value;
        if (!provider) {
            alert('Veuillez sélectionner un opérateur de paiement.');
            return;
        }
        alert('Paiement simulé avec succès via ' + provider + '!');
        closePaymentModal();
    }
    
    function uploadDocuments() {
        alert('Fonctionnalité de téléchargement de documents!');
    }
    
    window.onclick = function(event) {
        if (event.target == document.getElementById('paymentModal')) {
            closePaymentModal();
        }
    }
    </script>
</body>
</html>
