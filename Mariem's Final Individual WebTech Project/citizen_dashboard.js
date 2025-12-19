// Utility function to get element by ID
function $(id) {
    return document.getElementById(id);
}

// Generate transaction ID
function generateTransactionId() {
    const timestamp = Date.now();
    const random = Math.floor(Math.random() * 10000);
    return 'TRX-' + timestamp + '-' + random;
}

// Open payment modal
function openPaymentModal() {
    const modal = $('paymentModal');
    if (!modal) return;
    
    modal.style.display = 'block';
    
    // Generate new transaction ID
    const transactionId = generateTransactionId();
    
    // Update all transaction ID displays
    const displayIds = document.querySelectorAll('[id*="TransactionId"], [id*="transactionId"]');
    displayIds.forEach(el => {
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
            el.value = transactionId;
        } else {
            el.textContent = transactionId;
        }
    });
    
    // Update specific elements
    const displayId = $('displayTransactionId');
    const paymentId = $('paymentTransactionId');
    
    if (displayId) displayId.textContent = transactionId;
    if (paymentId) paymentId.value = transactionId;
    
    // Reset form
    resetPaymentForm();
}

// Close payment modal
function closePaymentModal() {
    const modal = $('paymentModal');
    if (modal) {
        modal.style.display = 'none';
        resetPaymentForm();
    }
}

// Reset payment form
function resetPaymentForm() {
    // Hide provider info
    const providerInfo = $('selectedProviderInfo');
    if (providerInfo) providerInfo.style.display = 'none';
    
    // Clear provider selection
    const providerInput = $('selectedProvider');
    if (providerInput) providerInput.value = '';
    
    // Clear file input
    const receiptFile = $('receiptFile');
    if (receiptFile) receiptFile.value = '';
    
    // Clear file name display
    const fileName = $('fileName');
    if (fileName) fileName.textContent = '';
    
    // Remove selected class from all providers
    document.querySelectorAll('.provider-option').forEach(el => {
        el.classList.remove('selected');
    });
}

// Select payment provider
function selectProvider(provider, name, element) {
    // Remove selected class from all providers
    document.querySelectorAll('.provider-option').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Add selected class to clicked provider
    if (element) element.classList.add('selected');
    
    // Set provider value
    const providerInput = $('selectedProvider');
    if (providerInput) providerInput.value = provider;
    
    // Show provider info
    const providerInfo = $('selectedProviderInfo');
    const providerName = $('selectedProviderName');
    const providerNumber = $('providerNumber');
    
    if (providerInfo && providerName && providerNumber) {
        providerName.textContent = name || provider;
        
        // Set provider number
        const providerNumbers = {
            'bankily': '+222 32423440',
            'masrivi': '+222 32423440',
            'sadad': '+222 32423440',
            'click': '+222 48305130',
            'binbank': '+222 48305130',
            'moovemauritel': '+222 48305130'
        };
        
        providerNumber.textContent = providerNumbers[provider] || '+222 XX XX XX XX';
        providerInfo.style.display = 'block';
    }
}

// Handle document upload alerts
function uploadDocuments() {
    alert('Document upload feature is active. Please use the form above to upload documents.');
}

function downloadPermit() {
    alert('Residence permit download will be available after admin approval.');
}

function updateProfile() {
    alert('Profile update feature coming soon.');
}

function extendStay() {
    alert('Residence extension request submitted.');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize transaction ID
    const transactionId = generateTransactionId();
    const displayId = $('displayTransactionId');
    const paymentId = $('paymentTransactionId');
    
    if (displayId) displayId.textContent = transactionId;
    if (paymentId) paymentId.value = transactionId;
    
    // Handle receipt file change
    const receiptFile = $('receiptFile');
    if (receiptFile) {
        receiptFile.addEventListener('change', function () {
            if (this.files.length > 0) {
                const file = this.files[0];
                const fileName = $('fileName');
                if (fileName) {
                    fileName.textContent = `${file.name} (${Math.round(file.size / 1024)} KB)`;
                }
            }
        });
    }
    
    // Handle receipt form submission
    const receiptForm = $('receiptUploadForm');
    if (receiptForm) {
        receiptForm.addEventListener('submit', function (e) {
            const providerInput = $('selectedProvider');
            const receiptFile = $('receiptFile');
            
            if (!providerInput || !providerInput.value) {
                e.preventDefault();
                alert('Veuillez sélectionner un opérateur de paiement.');
                return;
            }
            
            if (!receiptFile || !receiptFile.files.length) {
                e.preventDefault();
                alert('Veuillez télécharger votre reçu de paiement.');
                return;
            }
            
            const file = receiptFile.files[0];
            const allowed = ['image/jpeg', 'image/png', 'application/pdf'];
            
            if (!allowed.includes(file.type)) {
                e.preventDefault();
                alert('Seuls les fichiers JPG, PNG ou PDF sont autorisés.');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('La taille du fichier doit être inférieure à 5MB.');
                return;
            }
            
            if (!confirm('Soumettre le reçu pour vérification?')) {
                e.preventDefault();
            }
        });
    }
    
    // Handle navigation
    document.querySelectorAll('.nav-item').forEach(link => {
        link.addEventListener('click', e => {
            const target = link.getAttribute('href');
            if (target && target.startsWith('#')) {
                e.preventDefault();
                
                // Update active state
                document.querySelectorAll('.nav-item')
                    .forEach(i => i.classList.remove('active'));
                link.classList.add('active');
                
                // Smooth scroll to section
                const targetElement = document.querySelector(target);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', e => {
        const modal = $('paymentModal');
        if (modal && e.target === modal) {
            closePaymentModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closePaymentModal();
        }
    });
    
    // Auto-select first provider if only one exists
    const providerOptions = document.querySelectorAll('.provider-option');
    if (providerOptions.length === 1) {
        const option = providerOptions[0];
        const provider = option.getAttribute('onclick')?.match(/'([^']+)'/)?.[1];
        const name = option.querySelector('.provider-name')?.textContent;
        if (provider && name) {
            selectProvider(provider, name, option);
        }
    }
    
    // Session timeout management
    let sessionTimeout, warningTimeout;
    
    function resetSessionTimer() {
        clearTimeout(sessionTimeout);
        clearTimeout(warningTimeout);
        
        // Show warning 10 minutes before timeout (50 minutes)
        warningTimeout = setTimeout(() => {
            if (confirm('La session expire dans 10 minutes. Continuer?')) {
                resetSessionTimer();
            }
        }, 50 * 60 * 1000);
        
        // Redirect after 1 hour
        sessionTimeout = setTimeout(() => {
            window.location.href = 'login.php?timeout=1';
        }, 60 * 60 * 1000);
    }
    
    // Reset timer on user activity
    ['click', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, resetSessionTimer, { passive: true });
    });
    
    // Start session timer
    resetSessionTimer();
    
    // Security warning in console
    console.log('%c ATTENTION', 'color:red;font-size:28px;font-weight:bold;');
    console.log('%cNe collez pas de code ici si quelqu\'un vous le demande.', 'font-size:14px;color:#666;');
    console.log('%cCela pourrait compromettre la sécurité de votre compte.', 'font-size:14px;color:#666;');
});

// Make functions available globally
window.openPaymentModal = openPaymentModal;
window.closePaymentModal = closePaymentModal;
window.selectProvider = selectProvider;
window.uploadDocuments = uploadDocuments;
window.downloadPermit = downloadPermit;
window.updateProfile = updateProfile;
window.extendStay = extendStay;
