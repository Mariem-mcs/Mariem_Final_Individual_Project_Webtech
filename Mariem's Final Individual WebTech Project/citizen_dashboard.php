<?php
// Enable ALL errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config FIRST (before any session)
require_once 'config.php';

// Start session using your config function
start_secure_session();

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// Check user type
$user_type = $_SESSION['user_type'] ?? '';
$normalized_user_type = strtolower(str_replace([' ', '_'], '', $user_type));

// If not noncitizen, redirect to appropriate page
if ($normalized_user_type !== 'noncitizen') {
    if ($normalized_user_type === 'citizen') {
        header("Location: citizen_dashboard.php");
        exit();
    } elseif ($normalized_user_type === 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

$user_id = $_SESSION['user_id'];

// Language handling
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'ar', 'en'])) {
    $_SESSION['language'] = $_GET['lang'];
    $lang = $_GET['lang'];
} else {
    $lang = $_SESSION['language'] ?? 'fr';
}

// Get user data
$conn = $GLOBALS['conn'] ?? null;
if (!$conn) {
    die("Database connection failed");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if user exists
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

// SIMPLIFIED TRANSLATIONS
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
            
            <!-- Overview Section -->
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
                                <span class="label"><?php echo $text['nationality_label']; ?>:</span>
                                <span class="value"><?php echo htmlspecialchars($user['nationality'] ?? 'N/A'); ?></span>
                            </div>
                            
                            <?php if ($has_active_permit): ?>
                                <div class="status-badge status-active">
                                    ✅ <?php echo $text['status_active']; ?>
                                </div>
                            <?php else: ?>
                                <div class="status-badge status-pending">
                                    ⏳ <?php echo $text['status_pending']; ?>
                                </div>
                                <p style="margin-top: 15px; color: #666;">
                                    Pour obtenir votre permis de résidence, veuillez:
                                </p>
                                <ol style="margin-left: 20px; color: #666;">
                                    <li>Télécharger les documents requis</li>
                                    <li>Payer les frais de permis</li>
                                    <li>Attendre la vérification (2-3 jours)</li>
                                </ol>
                            <?php endif; ?>
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
                                <div class="transaction-id" id="transactionIdDisplay">TRX-<?php echo time(); ?></div>
                            </div>
                            <button onclick="openPaymentModal()" class="action-btn payment">
                                💳 <?php echo $text['pay_now']; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Documents Section -->
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
            
            <!-- Success Message -->
            <div class="success-message">
                ✅ Tableau de bord résident fonctionnel prêt pour la soumission!
                <div style="font-size: 0.9rem; margin-top: 5px; opacity: 0.9;">
                    Nationalité détectée: <strong><?php echo htmlspecialchars($user['nationality'] ?? 'N/A'); ?></strong> • 
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
                        <?php 
                        $providers = [
                            'bankily' => $text['bankily'],
                            'masrivi' => $text['masrivi'],
                            'sadad' => $text['sadad'],
                            'click' => $text['click'],
                            'binbank' => $text['binbank'],
                            'moovemauritel' => $text['moovemauritel']
                        ];
                        foreach ($providers as $key => $name): ?>
                        <div class="provider-option" onclick="selectProvider('<?php echo $key; ?>', '<?php echo $name; ?>', this)">
                            <div class="provider-name"><?php echo $name; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="selected-provider" id="selectedProviderInfo" style="display: none;">
                        <p><strong>Opérateur sélectionné:</strong> <span id="selectedProviderName"></span></p>
                        <p><strong>Numéro à composer:</strong> <span id="providerNumber" class="provider-number">+222 XX XX XX XX</span></p>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" id="receiptUploadForm">
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
                        
                        <div class="receipt-upload">
                            <div class="file-upload-area">
                                <input type="file" name="receipt_file" id="receiptFile" accept=".jpg,.jpeg,.png,.pdf" required>
                                <label for="receiptFile" class="file-label">
                                    <div class="upload-text">Cliquez pour télécharger le reçu</div>
                                    <div class="file-size">JPG, PNG, PDF (max 5MB)</div>
                                </label>
                                <div id="fileName" class="file-name-display"></div>
                            </div>
                        </div>
                        
                        <div class="payment-notes">
                            <p><strong><?php echo $text['payment_notes']; ?></strong></p>
                            <p><?php echo $text['note_1']; ?></p>
                            <p><?php echo $text['note_2']; ?></p>
                        </div>
                        
                        <button type="submit" class="btn-upload-receipt">
                            📤 <?php echo $text['submit_receipt']; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="noncitizen_dashboard.js"></script>
</body>
</html>
