function toggleDocumentFields() {
    const citizenFields = document.getElementById('citizen_fields');
    const nonCitizenFields = document.getElementById('non_citizen_fields');
    const nationalityField = document.getElementById('nationality_field');
    const citizenRadio = document.getElementById('citizen_radio');
    
    if (!citizenFields || !nonCitizenFields || !nationalityField || !citizenRadio) {
        console.error('Form elements not found');
        return;
    }
    
    const isCitizen = citizenRadio.checked;
    
    const nationalIdInput = document.getElementById('national_id_input');
    const passportInput = document.getElementById('passport_input');
    const nationalityInput = document.getElementById('nationality_input');
    
    if (isCitizen) {
        citizenFields.style.display = 'block';
        nonCitizenFields.style.display = 'none';
        nationalityField.style.display = 'none';
        if (nationalIdInput) nationalIdInput.required = true;
        if (passportInput) passportInput.required = false;
        if (nationalityInput) nationalityInput.required = false;
        
        console.log('Switched to CITIZEN mode');
    } else {
        citizenFields.style.display = 'none';
        nonCitizenFields.style.display = 'block';
        nationalityField.style.display = 'block';
        if (nationalIdInput) nationalIdInput.required = false;
        if (passportInput) passportInput.required = true;
        if (nationalityInput) nationalityInput.required = true;
        
        console.log('Switched to NON-CITIZEN mode');
    }
}

function formatMauritanianPhone(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value && !value.startsWith('222')) {
        value = '222' + value;
    }
    
    if (value.length > 3) {
        input.value = '+' + value.substring(0, 3) + ' ' + value.substring(3, 11).replace(/(.{2})/g, '$1 ').trim();
    } else if (value) {
        input.value = '+' + value;
    }
}

function validateMauritanianPhone(phone) {
    return /^\+222[0-9]{8}$/.test(phone.replace(/\s+/g, ''));
}

function validateNationalID(id) {
    return /^[0-9]{10}$/.test(id);
}

function validatePassport(passport) {
    return /^[A-Z][0-9]{7,8}$/.test(passport);
}

function updateRadioLabelStyle(radioInput) {
    const label = radioInput.closest('.radio-label');
    const radioGroup = radioInput.closest('.radio-group');
    
    if (radioGroup && label) {
        radioGroup.querySelectorAll('.radio-label').forEach(lbl => {
            lbl.style.borderColor = '#e0e0e0';
            lbl.style.background = 'white';
            lbl.style.fontWeight = 'normal';
            lbl.style.color = '#2c3e50';
        });
        if (radioInput.checked) {
            label.style.borderColor = 'rgb(0, 98, 51)';
            label.style.background = 'rgba(0, 98, 51, 0.1)';
            label.style.fontWeight = '600';
            label.style.color = 'rgb(0, 98, 51)';
        }
    }
}

// Validate entire form section:
function validateForm() {
    const phone = document.querySelector('input[name="phone"]').value;
    const isCitizen = document.getElementById('citizen_radio').checked;
    const nationalIdInput = document.getElementById('national_id_input');
    const passportInput = document.getElementById('passport_input');
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const dob = document.querySelector('input[name="date_of_birth"]').value;

    if (!validateMauritanianPhone(phone)) {
        alert('Entrez un numéro de téléphone mauritanien valide (+222 XX XX XX XX)');
        return false;
    }
    if (isCitizen) {
        const nationalId = nationalIdInput ? nationalIdInput.value : '';
        if (!validateNationalID(nationalId)) {
            alert('Entrez un numéro NMI valide (10 chiffres)');
            return false;
        }
    } else {
        const passport = passportInput ? passportInput.value : '';
        if (!validatePassport(passport)) {
            alert('Entrez un numéro de passeport valide (A12345678)');
            return false;
        }
    }
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[^A-Za-z0-9]/.test(password);
    
    if (!(hasUpper && hasLower && hasNumber && hasSpecial && password.length >= 8)) {
        alert('Le mot de passe doit contenir majuscules, minuscules, chiffre, caractère spécial et avoir 8+ caractères');
        return false;
    }
    
    if (password !== confirmPassword) {
        alert('Les mots de passe ne correspondent pas');
        return false;
    }
    if (dob) {
        const age = new Date().getFullYear() - new Date(dob).getFullYear();
        if (age < 18) {
            alert('Vous devez avoir au moins 18 ans');
            return false;
        }
    }
    
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded - Initializing form...');
    
    const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
    console.log('Found user type radio buttons:', userTypeRadios.length);
    
    userTypeRadios.forEach(radio => {
        updateRadioLabelStyle(radio);
        
        radio.addEventListener('change', function() {
            console.log('User type changed to:', this.value);
            updateRadioLabelStyle(this);
            toggleDocumentFields();
        });
    });
    const genderRadios = document.querySelectorAll('input[name="gender"]');
    console.log('Found gender radio buttons:', genderRadios.length);
    
    genderRadios.forEach(radio => {
        updateRadioLabelStyle(radio);
        
        radio.addEventListener('change', function() {
            console.log('Gender changed to:', this.value);
            updateRadioLabelStyle(this);
        });
    });
    toggleDocumentFields();
    
    // Phone formatting:
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            formatMauritanianPhone(this);
        });
    }
    
    // Form submission:
    const form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Setting the max date for DOB (must be 18+):
    const dobInput = document.querySelector('input[name="date_of_birth"]');
    if (dobInput) {
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() - 18);
        dobInput.max = maxDate.toISOString().split('T')[0];
    }
    
    console.log('Registration form initialized successfully!');
});