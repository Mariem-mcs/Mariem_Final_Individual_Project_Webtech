<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
if (!is_logged_in() || $_SESSION['user_type'] !== 'non_citizen') {
    header("Location: login.php");  
    exit();                          
}

$user_id = $_SESSION['user_id'];
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'ar', 'en'])) {
    $_SESSION['language'] = $_GET['lang'];
    header("Location: noncitizen_dashboard.php");
    exit();
}
$lang = $_SESSION['language'] ?? 'fr';
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$payment_amount = 45000; // Default for other countries
if (strtolower($user['nationality']) === 'senegal' || strtolower($user['nationality']) === 'sénégalaise') {
    $payment_amount = 1500;
}
$payment_amount_formatted = number_format($payment_amount, 0, ',', ' ') . ' MRU';

// Language translations
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
                    <i class="fas fa-home"></i>
                    <span><?php echo $text['dashboard']; ?></span>
                </a>
                <a href="#profile" class="nav-item">
                    <i class="fas fa-user"></i>
                    <span><?php echo $text['profile']; ?></span>
                </a>
                <a href="#residence" class="nav-item">
                    <i class="fas fa-passport"></i>
                    <span><?php echo $text['residence_permit']; ?></span>
                </a>
                <a href="#documents" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span><?php echo $text['documents']; ?></span>
                </a>
                <a href="#apply-id" class="nav-item highlight">
                    <i class="fas fa-id-card"></i>
                    <span><?php echo $text['apply_id']; ?></span>
                </a>
                <a href="#settings" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span><?php echo $text['settings']; ?></span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span><?php echo $text['logout']; ?></span>
                </a>
            </div>
        </aside>
        <main class="main-content">
            <header class="header">
                <div class="welcome">
                    <h1><?php echo $text['welcome']; ?>, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                    <p><?php echo date('l, F j, Y'); ?></p>
                </div>
                <div class="header-actions">
                    <div class="language-switch">
                        <a href="noncitizen_dashboard.php?lang=fr" class="<?php echo $lang === 'fr' ? 'active' : ''; ?>">FR</a>
                        <a href="noncitizen_dashboard.php?lang=ar" class="<?php echo $lang === 'ar' ? 'active' : ''; ?>">AR</a>
                        <a href="noncitizen_dashboard.php?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                    </div>
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <!-- The Dashboard Grid: -->
            <div class="dashboard-grid">
                <div class="card info-card" id="profile">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> <?php echo $text['personal_info']; ?></h3>
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
                <?php if ($has_active_permit): ?>
                <div class="card residence-card" id="residence">
                    <div class="card-header">
                        <h3><i class="fas fa-passport"></i> <?php echo $text['residence_info']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="label"><?php echo $text['permit_number']; ?>:</span>
                            <span class="value"><?php echo htmlspecialchars($permit['permit_number']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label"><?php echo $text['entry_date']; ?>:</span>
                            <span class="value"><?php echo date('d/m/Y', strtotime($permit['entry_date'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label"><?php echo $text['expiry_date']; ?>:</span>
                            <?php 
                            $permit_expiry = $permit['expiry_date'];
                            $days_until_expiry = ceil((strtotime($permit_expiry) - time()) / 86400);
                            ?>
                            <span class="value"><?php echo date('d/m/Y', strtotime($permit_expiry)); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label"><?php echo $text['visa_type']; ?>:</span>
                            <span class="value"><?php echo ucfirst($permit['visa_type']); ?></span>
                        </div>
                        <?php if ($days_until_expiry <= 30 && $days_until_expiry > 0): ?>
                        <div class="info-note" style="margin-top: 1rem; padding: 0.75rem; background: rgba(254, 243, 199, 1); border-radius: 6px; font-size: 0.9rem;">
                            <i class="fas fa-exclamation-triangle" style="color: rgba(245, 158, 11, 1);"></i>
                            <span style="color: rgba(146, 64, 14, 1); margin-left: 0.5rem;">
                                Your permit expires in <?php echo $days_until_expiry; ?> days. Please renew soon.
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="card residence-card" id="residence">
                    <div class="card-header">
                        <h3><i class="fas fa-clock"></i> <?php echo $text['residence_status']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="status-section" style="margin-bottom: 1.5rem;">
                            <div class="status-label" style="color: rgba(100, 116, 139, 1); font-weight: 600; margin-bottom: 0.5rem;">
                                Current Status:
                            </div>
                            <div class="status-value" style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="status-badge status-pending"><?php echo $text['application_pending']; ?></span>
                                <span style="color: rgba(100, 116, 139, 1); font-size: 0.9rem;">
                                    (Submitted on <?php echo date('d/m/Y'); ?>)
                                </span>
                            </div>
                        </div>
                            <div class="timeline-section" style="margin-bottom: 1.5rem;">
                            <div class="timeline-label" style="color: rgba(100, 116, 139, 1); font-weight: 600; margin-bottom: 0.5rem;">
                                <?php echo $text['processing_time']; ?>:
                            </div>
                            <div class="timeline-value" style="color: rgba(30, 41, 59, 1); font-weight: 500;">
                                <?php echo $text['working_days']; ?>
                            </div>
                        </div>
                            <div class="next-steps" style="padding: 1rem; background: rgba(240, 249, 255, 1); border-radius: 8px; border-left: 4px solid #3b82f6;">
                            <div style="font-weight: 600; color: rgba(30, 64, 175, 1); margin-bottom: 0.5rem;">
                                <i class="fas fa-arrow-right"></i> <?php echo $text['after_approval']; ?>
                            </div>
                            <div style="color: #475569; font-size: 0.9rem;">
                                <?php echo $text['will_be_assigned']; ?><br>
                                <?php echo $text['one_year_validity']; ?><br>
                                <?php echo $text['download_permit_card']; ?><br>
                                <?php echo $text['renewal_available']; ?>
                            </div>
                        </div>
                        <div class="info-note" style="margin-top: 1rem; padding: 0.75rem; background: rgba(241, 245, 249, 1); border-radius: 6px; font-size: 0.85rem; color: #64748b;">
                            <i class="fas fa-info-circle"></i>
                            <span style="margin-left: 0.5rem;">
                                <?php echo $text['sample_info_note']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="card payment-card">
                    <div class="card-header">
                        <h3><i class="fas fa-money-bill-wave"></i> <?php echo $text['payment_fee']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="amount-display">
                            <div class="amount-label"><?php echo $text['nationality_note']; ?></div>
                            <div class="amount-value"><?php echo $payment_amount_formatted; ?></div>
                            <div class="amount-description">
                                <?php echo strtolower($user['nationality']) === 'senegal' || strtolower($user['nationality']) === 'sénégalaise' ? 
                                    $text['senegal_rate'] : $text['other_rate']; ?>
                            </div>
                            <div class="transaction-id" id="transactionId"></div>
                        </div>
                        <button class="action-btn payment" onclick="makePayment()">
                            <i class="fas fa-credit-card"></i>
                            <?php echo $text['pay_now']; ?>
                        </button>
                        <p class="payment-amount-note">
                            <?php echo $text['make_payment']; ?> via mobile money
                        </p>
                    </div>
                </div>
            </div>

            <!-- The Payment Modal: -->
            <div id="paymentModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><?php echo $text['payment_fee']; ?></h3>
                        <span class="close-modal" onclick="closePaymentModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form id="receiptUploadForm" action="process_payment.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="transaction_id" id="paymentTransactionId" value="">
                            <input type="hidden" name="selected_provider" id="selectedProvider" value="">
                            <input type="hidden" name="amount" value="<?php echo $payment_amount; ?>">
                            
                            <div class="payment-info">
                                <div class="amount-display">
                                    <div class="amount-label"><?php echo $text['nationality_note']; ?></div>
                                    <div class="amount-value"><?php echo $payment_amount_formatted; ?></div>
                                    <div class="amount-description" id="referenceId"></div>
                                    <div class="amount-note">
                                        <?php echo strtolower($user['nationality']) === 'senegal' || strtolower($user['nationality']) === 'sénégalaise' ? 
                                            $text['senegal_rate'] : $text['other_rate']; ?>
                                    </div>
                                </div>
                                
                                <h4><?php echo $text['select_provider']; ?></h4>
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
                                    <h4><?php echo $lang === 'en' ? 'Payment Number:' : ($lang === 'fr' ? 'Numéro de paiement:' : 'رقم الدفع:'); ?></h4>
                                    <div class="provider-number" id="providerNumber"></div>
                                    <p class="provider-instruction"><?php echo $lang === 'en' ? 'Use this number to make payment via selected provider' : ($lang === 'fr' ? 'Utilisez ce numéro pour effectuer le paiement via l\'opérateur sélectionné' : 'استخدم هذا الرقم للدفع عبر المزود المحدد'); ?></p>
                                </div>
                                
                                <div class="payment-instructions">
                                    <h4><?php echo $text['payment_instructions']; ?></h4>
                                    <ol>
                                        <li><?php echo $text['step_payment_1']; ?></li>
                                        <li><?php echo $text['step_payment_2']; ?>: <code id="instructionTransactionId"></code></li>
                                        <li><?php echo $text['step_payment_3']; ?> <strong><?php echo $payment_amount_formatted; ?></strong></li>
                                        <li><?php echo $text['step_payment_4']; ?></li>
                                        <li><?php echo $text['step_payment_5']; ?></li>
                                    </ol>
                                </div>
                                
                                <div class="receipt-upload">
                                    <h4><?php echo $text['upload_receipt']; ?></h4>
                                    <div class="file-upload-area">
                                        <input type="file" id="receiptFile" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <label for="receiptFile" class="file-label">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #64748b; margin-bottom: 1rem;"></i>
                                            <div class="upload-text"><?php echo $lang === 'en' ? 'Click to upload receipt' : ($lang === 'fr' ? 'Cliquez pour télécharger le reçu' : 'انقر لرفع الإيصال'); ?></div>
                                            <div class="file-size"><?php echo $lang === 'en' ? 'JPG, PNG, or PDF (max 5MB)' : ($lang === 'fr' ? 'JPG, PNG ou PDF (max 5MB)' : 'JPG، PNG، أو PDF (الحد الأقصى 5 ميغابايت)'); ?></div>
                                            <div id="fileName"></div>
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn-upload-receipt">
                                        <i class="fas fa-paper-plane"></i>
                                        <?php echo $text['submit_receipt']; ?>
                                    </button>
                                </div>
                                
                                <div class="payment-notes">
                                    <p><strong><?php echo $text['payment_notes']; ?></strong></p>
                                    <p><?php echo $text['note_1']; ?></p>
                                    <p><?php echo $text['note_2']; ?></p>
                                    <p><?php echo $text['note_3']; ?></p>
                                    <p><?php echo $text['note_4']; ?></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- The National ID Application Section: -->
            <div class="card apply-id-card" id="apply-id">
                <div class="card-header">
                    <h3><i class="fas fa-id-card"></i> <?php echo $text['apply_national_id']; ?></h3>
                </div>
                <div class="card-body">
                    <div class="apply-grid">
                        <div class="apply-info">
                            <h4><?php echo $text['requirements']; ?></h4>
                            <ul class="requirements-list">
                                <li><?php echo $text['req_1']; ?></li>
                                <li><?php echo $text['req_2']; ?></li>
                                <li><?php echo $text['req_3']; ?></li>
                                <li><?php echo $text['req_4']; ?></li>
                                <li><?php echo $text['req_5']; ?></li>
                            </ul>
                            
                            <h4 style="margin-top: 2rem"><?php echo $text['steps_title']; ?></h4>
                            <div class="steps-list">
                                <div class="step-item">
                                    <span class="step-number">1</span>
                                    <p><?php echo $text['step_1']; ?></p>
                                </div>
                                <div class="step-item">
                                    <span class="step-number">2</span>
                                    <p><?php echo $text['step_2']; ?></p>
                                </div>
                                <div class="step-item">
                                    <span class="step-number">3</span>
                                    <p><?php echo $text['step_3']; ?></p>
                                </div>
                                <div class="step-item">
                                    <span class="step-number">4</span>
                                    <p><?php echo $text['step_4']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="apply-form">
                            <h4><?php echo $text['document_upload']; ?></h4>
                            <form id="nationalIdForm" enctype="multipart/form-data">
                                <div class="file-input-wrapper">
                                    <label>
                                        <i class="fas fa-passport"></i>
                                        <?php echo $text['upload_passport']; ?>
                                    </label>
                                    <input type="file" name="passport" accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                                
                                <div class="file-input-wrapper">
                                    <label>
                                        <i class="fas fa-camera"></i>
                                        <?php echo $text['upload_photo']; ?>
                                    </label>
                                    <input type="file" name="photo" accept=".jpg,.jpeg,.png" required>
                                </div>
                                
                                <div class="file-input-wrapper">
                                    <label>
                                        <i class="fas fa-home"></i>
                                        <?php echo $text['upload_proof']; ?>
                                    </label>
                                    <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                                
                                <button type="submit" class="action-btn primary">
                                    <i class="fas fa-paper-plane"></i>
                                    <?php echo $text['apply_now']; ?>
                                </button>
                            </form>
                            
                            <div class="application-status-box">
                                <h4><?php echo $text['application_status']; ?></h4>
                                <div class="status-message">
                                    <i class="fas fa-info-circle"></i>
                                    <?php echo $text['no_application']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The Quick Actions Section: -->
            <div class="card actions-card">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> <?php echo $text['quick_actions']; ?></h3>
                </div>
                <div class="card-body">
                    <div class="actions-grid">
                        <button class="action-btn secondary" onclick="updateProfile()">
                            <i class="fas fa-edit"></i>
                            <?php echo $text['update_profile']; ?>
                        </button>
                        <button class="action-btn tertiary" onclick="extendStay()">
                            <i class="fas fa-calendar-plus"></i>
                            <?php echo $text['extend_stay']; ?>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="noncitizen_dashboard.js"></script>
</body>
</html>

[file content end]
