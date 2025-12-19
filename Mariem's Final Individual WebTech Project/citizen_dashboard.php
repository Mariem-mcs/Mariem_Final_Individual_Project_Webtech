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


// Start session using your config function
start_secure_session();

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// Check user type - normalize different formats
$user_type = $_SESSION['user_type'] ?? '';
$normalized_user_type = strtolower(str_replace([' ', '_'], '', $user_type));

// If not noncitizen, redirect to appropriate page
if ($normalized_user_type !== 'noncitizen') {
    if ($normalized_user_type === 'citizen') {
        header("Location: citizen_dashboard.php");
    } elseif ($normalized_user_type === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle document upload
$upload_message = '';
$upload_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {
    $document_type = $_POST['document_type'] ?? '';
    $allowed_types = ['passport', 'photo', 'proof_of_address', 'police_certificate'];
    
    if (in_array($document_type, $allowed_types) && isset($_FILES['document_file'])) {
        $file = $_FILES['document_file'];
        
        // Check for errors
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
            $file_name = $file['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_extensions)) {
                $max_size = 5 * 1024 * 1024; // 5MB
                
                if ($file['size'] <= $max_size) {
                    // Create uploads directory if it doesn't exist
                    $upload_dir = 'uploads/documents/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Generate unique filename
                    $new_filename = 'doc_' . $user_id . '_' . time() . '_' . $document_type . '.' . $file_ext;
                    $destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // Save to database
                        $stmt = $conn->prepare("INSERT INTO user_documents (user_id, document_type, file_name, file_path, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->bind_param("isss", $user_id, $document_type, $file_name, $destination);
                        
                        if ($stmt->execute()) {
                            $upload_message = "Document uploaded successfully!";
                            $upload_success = true;
                        } else {
                            $upload_message = "Error saving to database.";
                        }
                        $stmt->close();
                    } else {
                        $upload_message = "Error moving uploaded file.";
                    }
                } else {
                    $upload_message = "File size must be less than 5MB.";
                }
            } else {
                $upload_message = "Only JPG, PNG, and PDF files are allowed.";
            }
        } else {
            $upload_message = "Error uploading file. Error code: " . $file['error'];
        }
    } else {
        $upload_message = "Invalid document type or no file selected.";
    }
}

// Handle receipt upload
$receipt_message = '';
$receipt_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_receipt'])) {
    $transaction_id = $_POST['transaction_id'] ?? '';
    $payment_provider = $_POST['payment_provider'] ?? '';
    
    if (!empty($transaction_id) && !empty($payment_provider) && isset($_FILES['receipt_file'])) {
        $file = $_FILES['receipt_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
            $file_name = $file['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_extensions)) {
                $max_size = 5 * 1024 * 1024; // 5MB
                
                if ($file['size'] <= $max_size) {
                    // Create receipts directory if it doesn't exist
                    $upload_dir = 'uploads/receipts/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Generate unique filename
                    $new_filename = 'receipt_' . $user_id . '_' . time() . '.' . $file_ext;
                    $destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // Save to database
                        $stmt = $conn->prepare("INSERT INTO payments (user_id, transaction_id, provider, receipt_path, amount, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
                        $stmt->bind_param("isssi", $user_id, $transaction_id, $payment_provider, $destination, $payment_amount);
                        
                        if ($stmt->execute()) {
                            $receipt_message = "Receipt uploaded successfully! Payment will be verified within 24-48 hours.";
                            $receipt_success = true;
                        } else {
                            $receipt_message = "Error saving payment to database.";
                        }
                        $stmt->close();
                    } else {
                        $receipt_message = "Error moving uploaded file.";
                    }
                } else {
                    $receipt_message = "File size must be less than 5MB.";
                }
            } else {
                $receipt_message = "Only JPG, PNG, and PDF files are allowed.";
            }
        } else {
            $receipt_message = "Error uploading file.";
        }
    } else {
        $receipt_message = "Please fill all required fields and select a file.";
    }
}

// Language handling
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'ar', 'en'])) {
    $_SESSION['language'] = $_GET['lang'];
    $lang = $_GET['lang'];
} else {
    $lang = $_SESSION['language'] ?? 'fr';
}

// Get user data
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

// Get user documents
$documents_stmt = $conn->prepare("SELECT * FROM user_documents WHERE user_id = ? ORDER BY uploaded_at DESC");
$documents_stmt->bind_param("i", $user_id);
$documents_stmt->execute();
$documents = $documents_stmt->get_result();
$has_documents = $documents->num_rows > 0;

// Get payment status
$payment_stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$payment_stmt->bind_param("i", $user_id);
$payment_stmt->execute();
$latest_payment = $payment_stmt->get_result()->fetch_assoc();

// Check for residence permit
$permit_query = $conn->prepare("SELECT * FROM residence_permits WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
$permit_query->bind_param("i", $user_id);
$permit_query->execute();
$permit_result = $permit_query->get_result();
$permit = $permit_result->fetch_assoc();

$has_active_permit = ($permit && $permit['status'] === 'active');

// Language translations (same as before, but I'll include a shorter version)
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
        'payment_modal_title' => 'Payer les frais de permis',
        'select_provider' => 'Sélectionner un opérateur',
        'payment_instructions' => 'Instructions de paiement',
        'step_payment_1' => '1. Composez le numéro sur votre téléphone',
        'step_payment_2' => '2. Entrez l\'ID transaction comme référence',
        'step_payment_3' => '3. Confirmez le paiement de',
        'upload_receipt' => 'Télécharger le reçu',
        'submit_receipt' => 'Soumettre le reçu',
        'senegal_rate' => 'Tarif spécial Sénégalais: 1 500 MRU/an',
        'other_rate' => 'Tarif standard autres nationalités: 45 000 MRU/an',
        'residence_status' => 'Statut du permis de résidence',
        'welcome_message' => 'Bienvenue sur votre tableau de bord résident!',
        'user_type_label' => 'Type d\'utilisateur',
        'nationality_label' => 'Nationalité',
        'documents' => 'Mes documents',
        'upload_document' => 'Télécharger un document',
        'document_type' => 'Type de document',
        'choose_file' => 'Choisir un fichier',
        'upload' => 'Télécharger',
        'passport_copy' => 'Copie passeport',
        'photo' => 'Photo',
        'proof_address' => 'Justificatif de domicile',
        'police_certificate' => 'Certificat de police',
        'your_documents' => 'Vos documents',
        'file_name' => 'Nom du fichier',
        'upload_date' => 'Date de téléchargement',
        'status' => 'Statut',
        'verified' => 'Vérifié',
        'pending' => 'En attente',
        'transaction_id' => 'ID de transaction',
        'generate_id' => 'Générer un ID',
        'payment_provider' => 'Opérateur de paiement',
        'bankily' => 'Bankily',
        'masrivi' => 'Masrivi',
        'sadad' => 'Sadad',
        'click' => 'Click',
        'binbank' => 'BinBank',
        'moovemauritel' => 'Moove/Mauritel',
        'payment_notes' => 'Notes importantes:',
        'note_1' => '• Vérification sous 24-48 heures',
        'note_2' => '• Gardez votre ID transaction',
        'note_3' => '• Seuls les opérateurs approuvés sont listés',
        'note_4' => '• Contactez le support en cas d\'échec'
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
            
            <!-- Overview Section -->
            <section id="overview">
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
                
                <div class="card">
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
                                <div style="margin-top: 10px; color: #666; font-size: 0.9rem;">
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
            
            <!-- Documents Section -->
            <section id="documents" style="margin-top: 30px;">
                <div class="card">
                    <div class="card-header">
                        <h3>📎 <?php echo $text['documents']; ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if ($upload_message): ?>
                            <div class="alert <?php echo $upload_success ? 'alert-success' : 'alert-error'; ?>">
                                <?php echo htmlspecialchars($upload_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h4><?php echo $text['upload_document']; ?></h4>
                        <form method="POST" enctype="multipart/form-data" class="upload-form">
                            <div class="form-group">
                                <label><?php echo $text['document_type']; ?>:</label>
                                <select name="document_type" required>
                                    <option value="">-- Sélectionner --</option>
                                    <option value="passport"><?php echo $text['passport_copy']; ?></option>
                                    <option value="photo"><?php echo $text['photo']; ?></option>
                                    <option value="proof_of_address"><?php echo $text['proof_address']; ?></option>
                                    <option value="police_certificate"><?php echo $text['police_certificate']; ?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo $text['choose_file']; ?>:</label>
                                <input type="file" name="document_file" accept=".jpg,.jpeg,.png,.pdf" required>
                                <small>Formats acceptés: JPG, PNG, PDF (max 5MB)</small>
                            </div>
                            
                            <button type="submit" name="upload_document" class="action-btn primary">
                                📤 <?php echo $text['upload']; ?>
                            </button>
                        </form>
                        
                        <hr style="margin: 30px 0;">
                        
                        <h4><?php echo $text['your_documents']; ?></h4>
                        <?php if ($has_documents): ?>
                            <div class="documents-list">
                                <table>
                                    <thead>
                                        <tr>
                                            <th><?php echo $text['document_type']; ?></th>
                                            <th><?php echo $text['file_name']; ?></th>
                                            <th><?php echo $text['upload_date']; ?></th>
                                            <th><?php echo $text['status']; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($doc = $documents->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $type_names = [
                                                    'passport' => $text['passport_copy'],
                                                    'photo' => $text['photo'],
                                                    'proof_of_address' => $text['proof_address'],
                                                    'police_certificate' => $text['police_certificate']
                                                ];
                                                echo $type_names[$doc['document_type']] ?? $doc['document_type'];
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($doc['file_name']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo ($doc['verified'] ?? false) ? 'status-active' : 'status-pending'; ?>">
                                                    <?php echo ($doc['verified'] ?? false) ? $text['verified'] : $text['pending']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: #666; text-align: center; padding: 20px;">
                                Aucun document téléchargé pour le moment.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            
            <!-- Payment Status Section -->
            <?php if ($latest_payment): ?>
            <section id="payment-status" style="margin-top: 30px;">
                <div class="card">
                    <div class="card-header">
                        <h3>💰 Statut de paiement</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="label">ID Transaction:</span>
                            <span class="value"><?php echo htmlspecialchars($latest_payment['transaction_id']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Opérateur:</span>
                            <span class="value"><?php echo htmlspecialchars($latest_payment['provider']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Montant:</span>
                            <span class="value"><?php echo number_format($latest_payment['amount'], 0, ',', ' '); ?> MRU</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Statut:</span>
                            <span class="value">
                                <span class="status-badge <?php echo $latest_payment['status'] === 'verified' ? 'status-active' : 'status-pending'; ?>">
                                    <?php echo $latest_payment['status'] === 'verified' ? 'Vérifié' : 'En attente de vérification'; ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="label">Date:</span>
                            <span class="value"><?php echo date('d/m/Y H:i', strtotime($latest_payment['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>💰 <?php echo $text['payment_modal_title']; ?></h3>
                <span class="close-modal" onclick="closePaymentModal()">×</span>
            </div>
            <div class="modal-body">
                <?php if ($receipt_message): ?>
                    <div class="alert <?php echo $receipt_success ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo htmlspecialchars($receipt_message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="payment-instructions">
                    <h4><?php echo $text['payment_instructions']; ?></h4>
                    <ol>
                        <li><?php echo $text['step_payment_1']; ?></li>
                        <li><?php echo $text['step_payment_2']; ?>: <strong id="displayTransactionId">TRX-<?php echo time(); ?></strong></li>
                        <li><?php echo $text['step_payment_3']; ?> <strong><?php echo $payment_amount_formatted; ?></strong></li>
                        <li>Prenez une capture d'écran de la confirmation</li>
                        <li><?php echo $text['upload_receipt']; ?></li>
                    </ol>
                </div>
                
                <div class="selected-provider" id="selectedProviderInfo" style="display: none;">
                    <p><strong>Opérateur sélectionné:</strong> <span id="selectedProviderName"></span></p>
                    <p><strong>Numéro à composer:</strong> <span id="providerNumber" class="provider-number">+222 XX XX XX XX</span></p>
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="receiptUploadForm">
                    <input type="hidden" name="transaction_id" id="paymentTransactionId" value="TRX-<?php echo time(); ?>">
                    
                    <div class="form-group">
                        <label><?php echo $text['payment_provider']; ?>:</label>
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
                        <input type="hidden" name="payment_provider" id="selectedProvider" value="">
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo $text['upload_receipt']; ?>:</label>
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
                        <p><?php echo $text['note_3']; ?></p>
                        <p><?php echo $text['note_4']; ?></p>
                    </div>
                    
                    <button type="submit" name="upload_receipt" class="btn-upload-receipt">
                        📤 <?php echo $text['submit_receipt']; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="noncitizen_dashboard.js"></script>
</body>
</html>

