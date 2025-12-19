function $(id) {
    return document.getElementById(id);
}
function uploadDocuments() {
    const applySection = $('apply-id');
    if (applySection) {
        applySection.scrollIntoView({ behavior: 'smooth' });
        applySection.style.outline = '3px solid #22c55e';
        setTimeout(() => applySection.style.outline = 'none', 2000);
    }
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
function makePayment() {
    const transactionId = 'TRX-' + Date.now();

    $('transactionId').textContent = 'Transaction ID: ' + transactionId;
    $('referenceId').textContent = transactionId;
    $('instructionTransactionId').textContent = transactionId;
    $('paymentTransactionId').value = transactionId;

    resetPaymentForm();
    $('paymentModal').style.display = 'block';
}

function closePaymentModal() {
    $('paymentModal').style.display = 'none';
    resetPaymentForm();
}

function resetPaymentForm() {
    $('selectedProviderInfo').style.display = 'none';
    $('selectedProvider').value = '';
    $('receiptFile').value = '';
    $('fileName').textContent = '';

    document.querySelectorAll('.provider-option')
        .forEach(p => p.classList.remove('selected'));
}

function selectProvider(provider, event) {
    document.querySelectorAll('.provider-option')
        .forEach(p => p.classList.remove('selected'));

    event.currentTarget.classList.add('selected');

    const providerNumbers = {
        bankily: '+222 32423440',
        masrivi: '+222 32423440',
        sadad: '+222 32423440',
        click: '+222 48305130',
        binbank: '+222 48305130',
        moovemauritel: '+222 48305130'
    };

    $('providerNumber').textContent =
        providerNumbers[provider] || '+222 XX XX XX XX';

    $('selectedProvider').value = provider;
    $('selectedProviderInfo').style.display = 'block';
}
document.addEventListener('DOMContentLoaded', () => {

    const receiptFile = $('receiptFile');
    if (receiptFile) {
        receiptFile.addEventListener('change', function () {
            if (this.files.length > 0) {
                const file = this.files[0];
                $('fileName').textContent =
                    `${file.name} (${Math.round(file.size / 1024)} KB)`;
            }
        });
    }
    const receiptForm = $('receiptUploadForm');
    if (receiptForm) {
        receiptForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!$('selectedProvider').value) {
                alert('Please select a payment provider.');
                return;
            }

            if (!receiptFile.files.length) {
                alert('Please upload your payment receipt.');
                return;
            }

            const file = receiptFile.files[0];
            const allowed = ['image/jpeg', 'image/png', 'application/pdf'];

            if (!allowed.includes(file.type)) {
                alert('Only JPG, PNG, or PDF files are allowed.');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be under 5MB.');
                return;
            }

            if (confirm('Submit receipt for verification?')) {
                this.submit();
            }
        });
    }
    document.querySelectorAll('.nav-item').forEach(link => {
        link.addEventListener('click', e => {
            const target = link.getAttribute('href');
            if (target && target.startsWith('#')) {
                e.preventDefault();

                document.querySelectorAll('.nav-item')
                    .forEach(i => i.classList.remove('active'));
                link.classList.add('active');

                document.querySelector(target)
                    ?.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    window.addEventListener('click', e => {
        if (e.target === $('paymentModal')) closePaymentModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closePaymentModal();
    });
});
let sessionTimeout, warningTimeout;

function resetSessionTimer() {
    clearTimeout(sessionTimeout);
    clearTimeout(warningTimeout);

    warningTimeout = setTimeout(() => {
        if (confirm('Session expires in 10 minutes. Continue?')) {
            resetSessionTimer();
        }
    }, 50 * 60 * 1000);

    sessionTimeout = setTimeout(() => {
        window.location.href = 'login.php?timeout=1';
    }, 60 * 60 * 1000);
}

['click', 'mousemove', 'keypress', 'scroll']
    .forEach(evt => document.addEventListener(evt, resetSessionTimer));

resetSessionTimer();
console.log('%cWARNING', 'color:red;font-size:28px;font-weight:bold');
console.log('%cDo not paste code here if someone asked you to.', 'font-size:14px');
