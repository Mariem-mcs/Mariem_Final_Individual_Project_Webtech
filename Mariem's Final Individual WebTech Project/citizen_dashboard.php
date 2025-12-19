<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();
require_once 'config.php';

// FIX 1: Check for user_type instead of role, and use header() instead of redirect()
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'citizen') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'ar', 'en'])) {
    $_SESSION['language'] = $_GET['lang'];
}

$lang = $_SESSION['language'] ?? 'fr';
$user_id = $_SESSION['user_id'];

// Get the user data to proceed:
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// FIX 2: Add additional check to ensure user is citizen
if (!$user || $user['user_type'] !== 'citizen') {
    header("Location: login.php?error=not_citizen");
    exit();
}

// Rest of your code remains the same...
$applications = [];
$result = $conn->query("
    SELECT * FROM applications 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT 5
");
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

$documents = [];
$result = $conn->query("
    SELECT d.*, a.application_type 
    FROM documents d
    LEFT JOIN applications a ON d.application_id = a.id
    WHERE a.user_id = $user_id 
    ORDER BY d.uploaded_at DESC
");
while ($row = $result->fetch_assoc()) {
    $documents[] = $row;
}

$has_pending_application = $conn->query("
    SELECT COUNT(*) as count FROM applications 
    WHERE user_id = $user_id AND status IN ('pending', 'under_review')
")->fetch_assoc()['count'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apply_new'])) {
        $application_type = $_POST['application_type'];
        $notes = $_POST['notes'] ?? '';
        
        $stmt = $conn->prepare("
            INSERT INTO applications (user_id, application_type, status, notes) 
            VALUES (?, ?, 'pending', ?)
        ");
        $stmt->bind_param("iss", $user_id, $application_type, $notes);
        
        if ($stmt->execute()) {
            $application_id = $stmt->insert_id;
            log_activity($user_id, 'application_submitted', "Submitted $application_type application");
            $success_message = "Application submitted successfully! Application ID: #$application_id";
        }
        $stmt->close();
    }
}

// The Language translations section with added no_documents key:
$translations = [
    'en' => [
        'dashboard' => 'Dashboard',
        'welcome' => 'Welcome back',
        'personal_info' => 'Personal Information',
        'full_name' => 'Full Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'dob' => 'Date of Birth',
        'nationality' => 'Nationality',
        'quick_actions' => 'Quick Actions',
        'download_id' => 'Download ID Card',
        'update_profile' => 'Update Profile',
        'request_renewal' => 'Request Renewal',
        'my_applications' => 'My Applications',
        'upload_documents' => 'Upload Documents',
        'track_status' => 'Track Status',
        'apply_new' => 'Apply for New ID',
        'apply_replacement' => 'Request Replacement',
        'apply_renewal' => 'Renew ID Card',
        'no_applications' => 'No applications yet',
        'no_documents' => 'No documents uploaded yet', // ADDED THIS
        'application_id' => 'Application ID',
        'type' => 'Type',
        'status' => 'Status',
        'date' => 'Date',
        'documents' => 'Documents',
        'document_name' => 'Document Name',
        'verification_status' => 'Verification',
        'uploaded' => 'Uploaded',
        'pending' => 'Pending',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'verified' => 'Verified',
        'not_verified' => 'Not Verified',
        'application_form' => 'Application Form',
        'select_type' => 'Select Application Type',
        'new_id' => 'New National ID',
        'replacement' => 'ID Replacement',
        'renewal' => 'ID Renewal',
        'notes' => 'Additional Notes (optional)',
        'submit' => 'Submit Application',
        'logout' => 'Logout',
        'select_office' => 'Select Pickup Office',
        'office_location' => 'Office Location',
        'pay_renewal' => 'Pay Renewal Fee',
        'payment_fee' => 'ID Card Fee',
        'pay_now' => 'Pay Now',
        'renewal_fee' => 'Renewal/Replacement Fee: ',
        'payment_amount' => '3,000 MRU',
        'payment_description' => 'For ID card renewal or replacement',
        'payment_note' => 'Pay via mobile money (Bankily, Masrivi, Sadad, etc.)',
        'form_note' => 'After submission, you will need to upload required documents.' // ADDED THIS
    ],
    'fr' => [
        'dashboard' => 'Tableau de bord',
        'welcome' => 'Bon retour',
        'personal_info' => 'Informations personnelles',
        'full_name' => 'Nom complet',
        'email' => 'Email',
        'phone' => 'Téléphone',
        'dob' => 'Date de naissance',
        'nationality' => 'Nationalité',
        'quick_actions' => 'Actions rapides',
        'download_id' => 'Télécharger la carte',
        'update_profile' => 'Mettre à jour',
        'request_renewal' => 'Demander renouvellement',
        'my_applications' => 'Mes demandes',
        'upload_documents' => 'Télécharger documents',
        'track_status' => 'Suivre statut',
        'apply_new' => 'Demander nouvelle carte',
        'apply_replacement' => 'Demander remplacement',
        'apply_renewal' => 'Renouveler carte',
        'no_applications' => 'Aucune demande',
        'no_documents' => 'Aucun document téléchargé', // ADDED THIS
        'application_id' => 'N° de demande',
        'type' => 'Type',
        'status' => 'Statut',
        'date' => 'Date',
        'documents' => 'Documents',
        'document_name' => 'Document',
        'verification_status' => 'Vérification',
        'uploaded' => 'Téléchargé',
        'pending' => 'En attente',
        'under_review' => 'En examen',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
        'verified' => 'Vérifié',
        'not_verified' => 'Non vérifié',
        'application_form' => 'Formulaire de demande',
        'select_type' => 'Type de demande',
        'new_id' => 'Nouvelle carte nationale',
        'replacement' => 'Remplacement',
        'renewal' => 'Renouvellement',
        'notes' => 'Notes additionnelles',
        'submit' => 'Soumettre',
        'logout' => 'Déconnexion',
        'select_office' => 'Choisir bureau',
        'office_location' => 'Lieu de retrait',
        'pay_renewal' => 'Payer frais de renouvellement',
        'payment_fee' => 'Frais de carte',
        'pay_now' => 'Payer maintenant',
        'renewal_fee' => 'Frais renouvellement/remplacement: ',
        'payment_amount' => '3 000 MRU',
        'payment_description' => 'Pour renouvellement ou remplacement de carte',
        'payment_note' => 'Payer via mobile money (Bankily, Masrivi, Sadad, etc.)',
        'form_note' => 'Après soumission, vous devrez télécharger les documents requis.' // ADDED THIS
    ],
    'ar' => [
        'dashboard' => 'لوحة التحكم',
        'welcome' => 'مرحبا بعودتك',
        'personal_info' => 'المعلومات الشخصية',
        'full_name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'dob' => 'تاريخ الميلاد',
        'nationality' => 'الجنسية',
        'quick_actions' => 'إجراءات سريعة',
        'download_id' => 'تحميل البطاقة',
        'update_profile' => 'تحديث الملف',
        'request_renewal' => 'طلب تجديد',
        'my_applications' => 'طلباتي',
        'upload_documents' => 'تحميل المستندات',
        'track_status' => 'تتبع الحالة',
        'apply_new' => 'طلب بطاقة جديدة',
        'apply_replacement' => 'طلب استبدال',
        'apply_renewal' => 'تجديد البطاقة',
        'no_applications' => 'لا توجد طلبات',
        'no_documents' => 'لا توجد مستندات', // ADDED THIS
        'application_id' => 'رقم الطلب',
        'type' => 'النوع',
        'status' => 'الحالة',
        'date' => 'التاريخ',
        'documents' => 'المستندات',
        'document_name' => 'اسم المستند',
        'verification_status' => 'التحقق',
        'uploaded' => 'تم التحميل',
        'pending' => 'قيد الانتظار',
        'under_review' => 'قيد المراجعة',
        'approved' => 'موافق عليه',
        'rejected' => 'مرفوض',
        'verified' => 'تم التحقق',
        'not_verified' => 'لم يتم التحقق',
        'application_form' => 'نموذج الطلب',
        'select_type' => 'اختر نوع الطلب',
        'new_id' => 'بطاقة وطنية جديدة',
        'replacement' => 'استبدال',
        'renewal' => 'تجديد',
        'notes' => 'ملاحظات إضافية',
        'submit' => 'إرسال الطلب',
        'logout' => 'تسجيل الخروج',
        'select_office' => 'اختر المكتب',
        'office_location' => 'مكان الاستلام',
        'pay_renewal' => 'دفع رسوم التجديد',
        'payment_fee' => 'رسوم البطاقة',
        'pay_now' => 'ادفع الآن',
        'renewal_fee' => 'رسوم التجديد/الاستبدال: ',
        'payment_amount' => '3,000 أوقية',
        'payment_description' => 'لتجديد أو استبدال البطاقة',
        'payment_note' => 'الدفع عبر الموبايل موني (بنكيلي، مسرافي، سداد، إلخ)',
        'form_note' => 'بعد الإرسال، ستحتاج إلى تحميل المستندات المطلوبة.' // ADDED THIS
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
    <link rel="stylesheet" href="citizen_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="authentifactionAuthorizer.png" alt="Logo">
                <span>IDTrack</span>
            </div>
            
            <nav class="nav-menu">
                <a href="#dashboard" class="nav-item active"><?php echo $text['dashboard']; ?></a>
                <a href="#applications" class="nav-item"><?php echo $text['my_applications']; ?></a>
                <a href="#documents" class="nav-item"><?php echo $text['documents']; ?></a>
                <a href="#apply" class="nav-item"><?php echo $text['apply_new']; ?></a>
                <a href="#profile" class="nav-item"><?php echo $text['personal_info']; ?></a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><?php echo $text['logout']; ?></a>
            </div>
        </aside>
        <main class="main-content">
            <header class="header">
                <div class="welcome">
                    <h1><?php echo $text['welcome']; ?>, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                    <p><?php echo date('l, F j, Y'); ?></p>
                </div>
            </header>
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <section id="dashboard">
                <div class="dashboard-grid">
                    <div class="card info-card">
                        <div class="card-header">
                            <h3><?php echo $text['personal_info']; ?></h3>
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
                    <div class="card payment-card">
                        <div class="card-header">
                            <h3>💳 <?php echo $text['payment_fee']; ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="amount-display">
                                <div class="amount-label"><?php echo $text['renewal_fee']; ?></div>
                                <div class="amount-value"><?php echo $text['payment_amount']; ?></div>
                                <div class="amount-description">
                                    <?php echo $text['payment_description']; ?>
                                </div>
                                <div class="transaction-id" id="transactionId"></div>
                            </div>
                            <button class="action-btn payment" onclick="makePayment('id_renewal')">
                                💳 <?php echo $text['pay_now']; ?>
                            </button>
                            <p class="payment-amount-note">
                                <?php echo $text['payment_note']; ?>
                            </p>
                        </div>
                    </div>
                    <div class="card actions-card">
                        <div class="card-header">
                            <h3><?php echo $text['quick_actions']; ?></h3>
                        </div>
                        <div class="card-body">
                            <button class="action-btn primary" onclick="downloadID()">
                                📥 <?php echo $text['download_id']; ?>
                            </button>
                            <button class="action-btn secondary" onclick="showApplicationForm()">
                                📄 <?php echo $text['apply_new']; ?>
                            </button>
                            <button class="action-btn tertiary" onclick="uploadDocuments()">
                                📎 <?php echo $text['upload_documents']; ?>
                            </button>
                            <button class="action-btn secondary" onclick="makePayment('id_renewal')">
                                💰 <?php echo $text['pay_renewal']; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <section id="applications" class="section">
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo $text['my_applications']; ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($applications)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th><?php echo $text['application_id']; ?></th>
                                    <th><?php echo $text['type']; ?></th>
                                    <th><?php echo $text['status']; ?></th>
                                    <th><?php echo $text['date']; ?></th>
                                    <th><?php echo $text['track_status']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>#<?php echo $app['id']; ?></td>
                                    <td><?php echo ucfirst($app['application_type']); ?></td>
                                    <td><span class="status-badge status-<?php echo $app['status']; ?>">
                                        <?php echo $text[$app['status']] ?? ucfirst($app['status']); ?>
                                    </span></td>
                                    <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                    <td>
                                        <button class="btn-track" onclick="trackApplication(<?php echo $app['id']; ?>)">
                                            <?php echo $text['track_status']; ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="no-data"><?php echo $text['no_applications']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <section id="documents" class="section">
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo $text['documents']; ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($documents)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th><?php echo $text['document_name']; ?></th>
                                    <th><?php echo $text['application_id']; ?></th>
                                    <th><?php echo $text['verification_status']; ?></th>
                                    <th><?php echo $text['uploaded']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td><?php echo ucfirst($doc['document_type']); ?></td>
                                    <td>#<?php echo $doc['application_id']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $doc['verification_status'] ?? 'not_verified'; ?>">
                                            <?php echo $text[$doc['verification_status']] ?? $text['not_verified']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="no-data"><?php echo $text['no_documents']; ?></p>
                        <?php endif; ?>
                        <div class="upload-section">
                            <button class="btn-upload" onclick="uploadDocuments()">
                                📎 <?php echo $text['upload_documents']; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <section id="apply" class="section">
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo $text['application_form']; ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if ($has_pending_application): ?>
                        <div class="warning-message">
                            <p> You have a pending application. Please wait for it to be processed before submitting a new one.</p>
                        </div>
                        <?php else: ?>
                        <form method="POST" id="applicationForm">
                            <div class="form-group">
                                <label><?php echo $text['select_type']; ?> *</label>
                                <select name="application_type" required>
                                    <option value="">-- <?php echo $text['select_type']; ?> --</option>
                                    <option value="new_id"><?php echo $text['new_id']; ?></option>
                                    <option value="replacement"><?php echo $text['replacement']; ?></option>
                                    <option value="renewal"><?php echo $text['renewal']; ?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo $text['office_location']; ?> *</label>
                                <select name="office_location" required>
                                    <option value="">-- <?php echo $text['select_office']; ?> --</option>
                                    <option value="nouakchott_central">Nouakchott Central Office</option>
                                    <option value="nouakchott_north">Nouakchott North Office</option>
                                    <option value="nouadhibou">Nouadhibou Office</option>
                                    <option value="kaedi">Kaédi Office</option>
                                    <option value="kiffa">Kiffa Office</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo $text['notes']; ?></label>
                                <textarea name="notes" rows="3" placeholder="<?php echo $text['notes']; ?>"></textarea>
                            </div>
                            
                            <div class="form-footer">
                                <p class="form-note">* <?php echo $text['form_note']; ?></p>
                                <button type="submit" name="apply_new" class="btn-submit">
                                    <?php echo $text['submit']; ?>
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <div id="paymentModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 id="paymentTitle"><?php echo $text['payment_fee']; ?></h3>
                        <span class="close-modal" onclick="closePaymentModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form id="receiptUploadForm" action="process_payment_citizen.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="transaction_id" id="paymentTransactionId" value="">
                            <input type="hidden" name="selected_provider" id="selectedProvider" value="">
                            <input type="hidden" name="payment_type" id="paymentType" value="">
                            <input type="hidden" name="amount" value="3000">
                            
                            <div class="payment-info">
                                <div class="amount-display">
                                    <div class="amount-label"><?php echo $text['renewal_fee']; ?></div>
                                    <div class="amount-value" id="amountValue"><?php echo $text['payment_amount']; ?></div>
                                    <div class="amount-description" id="amountDescription"><?php echo $text['payment_description']; ?></div>
                                    <div class="transaction-id" id="referenceId"></div>
                                </div>
                                
                                <h4>Select Mobile Money Provider</h4>
                                <div class="providers-grid">
                                    <div class="provider-option" onclick="selectProvider('bankily')">
                                        <div class="provider-name">Bankily</div>
                                    </div>
                                    <div class="provider-option" onclick="selectProvider('masrivi')">
                                        <div class="provider-name">Masrivi</div>
                                    </div>
                                    <div class="provider-option" onclick="selectProvider('sadad')">
                                        <div class="provider-name">Sadad</div>
                                    </div>
                                    <div class="provider-option" onclick="selectProvider('click')">
                                        <div class="provider-name">Click</div>
                                    </div>
                                    <div class="provider-option" onclick="selectProvider('binbank')">
                                        <div class="provider-name">Binbank</div>
                                    </div>
                                    <div class="provider-option" onclick="selectProvider('moovemauritel')">
                                        <div class="provider-name">Moove/Mauritel</div>
                                    </div>
                                </div>
                                
                                <div id="selectedProviderInfo" class="selected-provider" style="display: none;">
                                    <h4>Payment Number:</h4>
                                    <div class="provider-number" id="providerNumber"></div>
                                    <p class="provider-instruction">Use this number to make payment via selected provider</p>
                                </div>
                                
                                <div class="payment-instructions">
                                    <h4>Payment Instructions:</h4>
                                    <ol>
                                        <li>Dial the payment number on your phone</li>
                                        <li>Enter the Transaction ID as reference: <code id="instructionTransactionId"></code></li>
                                        <li>Confirm payment of <strong>3,000 MRU</strong></li>
                                        <li>Take screenshot of payment confirmation</li>
                                        <li>Upload receipt below</li>
                                    </ol>
                                </div>
                                
                                <div class="receipt-upload">
                                    <h4>Upload Payment Receipt</h4>
                                    <div class="file-upload-area">
                                        <input type="file" id="receiptFile" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <label for="receiptFile" class="file-label">
                                            📎
                                            <div class="upload-text">Click to upload receipt</div>
                                            <div class="file-size">JPG, PNG, or PDF (max 5MB)</div>
                                            <div id="fileName"></div>
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn-upload-receipt">
                                        📤 Submit Receipt
                                    </button>
                                </div>
                                
                                <div class="payment-notes">
                                    <p><strong>Important Notes:</strong></p>
                                    <p>• Payment verification takes 24-48 hours</p>
                                    <p>• Keep your Transaction ID for reference</p>
                                    <p>• Only approved providers are listed</p>
                                    <p>• Contact support if payment fails</p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript file -->
    <script src="citizen_dashboard.js"></script>
</body>
</html>
