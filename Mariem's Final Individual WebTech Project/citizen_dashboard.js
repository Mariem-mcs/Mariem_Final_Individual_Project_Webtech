// citizen_dashboard.js
function showApplicationForm() {
    document.getElementById("apply").scrollIntoView({ behavior: "smooth" });
}

function uploadDocuments() {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.jpg,.jpeg,.png,.pdf';
    fileInput.click();
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            alert("Selected file: " + this.files[0].name);
            // You can add file upload logic here
        }
    });
}

function downloadID() {
    alert("Your ID card will be available for download once approved.");
}

function trackApplication(appId) {
    alert("Tracking application ID: " + appId);
}

function makePayment(paymentType) {
    const transactionId = 'TX' + Date.now() + Math.floor(Math.random() * 1000);
    document.getElementById('transactionId').textContent = 'Transaction ID: ' + transactionId;
    document.getElementById('referenceId').textContent = transactionId;
    document.getElementById('paymentTransactionId').value = transactionId;
    document.getElementById('paymentType').value = paymentType;
    document.getElementById('instructionTransactionId').textContent = transactionId;
    
    let amount = '3,000 MRU';
    if (paymentType === 'id_renewal') {
        amount = '3,000 MRU';
        document.getElementById('paymentTitle').textContent = 'ID Card Renewal Fee';
        document.getElementById('amountValue').textContent = amount;
        document.getElementById('amountDescription').textContent = 'For ID card renewal/replacement';
    }
    
    document.getElementById('selectedProviderInfo').style.display = 'none';
    document.getElementById('receiptFile').value = '';
    document.getElementById('fileName').textContent = '';
    document.querySelectorAll('.provider-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    document.getElementById('selectedProvider').value = '';
    document.getElementById('paymentModal').style.display = 'block';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    resetPaymentForm();
}

function resetPaymentForm() {
    document.getElementById('selectedProviderInfo').style.display = 'none';
    document.getElementById('receiptFile').value = '';
    document.getElementById('fileName').textContent = '';
    document.querySelectorAll('.provider-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    document.getElementById('selectedProvider').value = '';
}

function selectProvider(provider) {
    document.querySelectorAll('.provider-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    event.target.closest('.provider-option').classList.add('selected');
    
    const providerNumbers = {
        'bankily': '+222 32423440',
        'masrivi': '+222 32423440',
        'sadad': '+222 32423440',
        'click': '+222 48305130',
        'binbank': '+222 48305130',
        'moovemauritel': '+222 48305130'
    };
    
    document.getElementById('providerNumber').textContent = providerNumbers[provider] || '+222 XX XX XX XX';
    document.getElementById('selectedProviderInfo').style.display = 'block';
    document.getElementById('selectedProvider').value = provider;
}

// Document ready function
document.addEventListener("DOMContentLoaded", function () {
    // File upload preview for receipt
    const receiptFile = document.getElementById('receiptFile');
    if (receiptFile) {
        receiptFile.addEventListener('change', function() {
            if (this.files.length > 0) {
                document.getElementById('fileName').textContent = 
                    'Selected: ' + this.files[0].name + ' (' + 
                    Math.round(this.files[0].size / 1024) + ' KB)';
            }
        });
    }
    
    // Form validation for receipt upload
    const receiptForm = document.getElementById('receiptUploadForm');
    if (receiptForm) {
        receiptForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selectedProvider = document.getElementById('selectedProvider').value;
            if (!selectedProvider) {
                alert('Please select a mobile money provider');
                return false;
            }
            
            const fileInput = document.getElementById('receiptFile');
            if (!fileInput.files.length) {
                alert('Please select a receipt file');
                return false;
            }
            
            const file = fileInput.files[0];
            const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            const maxSize = 5 * 1024 * 1024; 
            
            if (!validTypes.includes(file.type)) {
                alert('Please upload only JPG, PNG, or PDF files');
                return false;
            }
            
            if (file.size > maxSize) {
                alert('File size must be less than 5MB');
                return false;
            }
            
            if (confirm('Submit receipt? Payment will be verified within 24-48 hours.')) {
                this.submit();
            }
            
            return false;
        });
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('paymentModal');
        if (event.target === modal) {
            closePaymentModal();
        }
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePaymentModal();
        }
    });
    
    // Smooth scroll for navigation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                // Update active nav item
                document.querySelectorAll('.nav-item').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
                
                // Scroll to target
                targetElement.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Generate initial transaction ID
    const initialId = 'IDT' + Date.now();
    document.getElementById('transactionId').textContent = 'Reference: ' + initialId;
});
