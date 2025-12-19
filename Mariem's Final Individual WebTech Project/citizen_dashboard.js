function showApplicationForm() {
    document.getElementById("apply").scrollIntoView({ behavior: "smooth" });
}
function uploadDocuments() {
    document.getElementById("documentInput").click();
}
document.addEventListener("DOMContentLoaded", function () {
    const docInput = document.getElementById("documentInput");
    if (docInput) {
        docInput.addEventListener("change", function () {
            if (this.files.length > 0) {
                alert("Selected file: " + this.files[0].name);
                document.getElementById("documentUploadForm").submit();
            }
        });
    }
});
function downloadID() {
    alert("Your ID card will be available for download once approved.");
}
function trackApplication(appId) {
    alert("Tracking application ID: " + appId);
}
function makePayment() {
    document.getElementById("paymentModal").style.display = "block";
    generateTransactionId();
}

function closePaymentModal() {
    document.getElementById("paymentModal").style.display = "none";
}

function generateTransactionId() {
    const tx = "TX" + Math.floor(Math.random() * 1000000);
    document.getElementById("transactionId").innerText = tx;
    document.getElementById("referenceId").innerText = tx;
    document.getElementById("paymentTransactionId").value = tx;
}

function selectProvider(provider) {
    document.getElementById("selectedProvider").value = provider;
    document.getElementById("selectedProviderInfo").style.display = "block";
    document.getElementById("providerNumber").innerText = "+222 44 55 66 77";
}
function makePayment(paymentType) {
    const transactionId = 'TX' + Date.now() + Math.floor(Math.random() * 1000);
    document.getElementById('transactionId').textContent = 'Transaction ID: ' + transactionId;
    document.getElementById('referenceId').textContent = transactionId;
    document.getElementById('paymentTransactionId').value = transactionId;
    document.getElementById('paymentType').value = paymentType;
    
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
document.addEventListener("DOMContentLoaded", function () {
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
    
    window.onclick = function(event) {
        const modal = document.getElementById('paymentModal');
        if (event.target === modal) {
            closePaymentModal();
        }
    }
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePaymentModal();
        }
    });
});
