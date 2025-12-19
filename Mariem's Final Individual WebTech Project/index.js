const LANGUAGE_STORAGE_KEY = 'selectedLanguage';
const LANGUAGE_COOKIE_NAME = 'preferred_language';

function selectLanguage(lang) {
    console.log('Language selected:', lang);
    
    if (!['fr', 'ar', 'en'].includes(lang)) return;
    localStorage.setItem(LANGUAGE_STORAGE_KEY, lang);
    
    // Save to cookie with 1 year expiration
    const expiration = new Date();
    expiration.setFullYear(expiration.getFullYear() + 1);
    document.cookie = `preferred_language=${lang}; expires=${expiration.toUTCString()}; path=/; SameSite=Lax`;
    localStorage.setItem('hasSelectedLanguage', 'true');
    const languageScreen = document.getElementById('languageScreen');
    const mainApp = document.getElementById('mainApp');
    
    if (languageScreen) languageScreen.classList.add('hidden');
    if (mainApp) mainApp.classList.remove('hidden');
    
    window.location.href = `index.html?lang=${lang}&t=${Date.now()}`;
}

function changeLanguage(lang) {
    console.log('Changing language to:', lang);
    
    if (!['fr', 'ar', 'en'].includes(lang)) return;
    
    localStorage.setItem(LANGUAGE_STORAGE_KEY, lang);
    
    const expiration = new Date();
    expiration.setFullYear(expiration.getFullYear() + 1);
    document.cookie = `preferred_language=${lang}; expires=${expiration.toUTCString()}; path=/; SameSite=Lax`;
    
    applyLanguageToPage(lang);
    
    const dropdown = document.getElementById('langDropdown');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
    
    updateLinksWithLanguage(lang);
}

function applyLanguageToPage(lang) {
    console.log('Applying language to page:', lang);
    
    document.querySelectorAll('[data-fr], [data-ar], [data-en]').forEach(element => {
        const translation = element.getAttribute(`data-${lang}`);
        if (translation) {
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.placeholder = translation;
            } else {
                element.textContent = translation;
            }
        }
    });
    
    document.documentElement.lang = lang;
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    
    const langIndicator = document.getElementById('currentLang');
    if (langIndicator) {
        const langCodes = { fr: 'FR', ar: 'AR', en: 'EN' };
        langIndicator.textContent = langCodes[lang] || lang.toUpperCase();
    }
}

function updateLinksWithLanguage(lang) {
    const timestamp = Date.now();
    const loginBtn = document.querySelector('.btn-login');
    const registerBtn = document.querySelector('.btn-register');
    
    if (loginBtn) {
        loginBtn.href = `login.php?lang=${lang}&t=${timestamp}`;
    }
    if (registerBtn) {
        registerBtn.href = `register.php?lang=${lang}&t=${timestamp}`;
    }
}

function toggleLanguageMenu() {
    const dropdown = document.getElementById('langDropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Check if user has selected language before:
    const hasSelectedBefore = localStorage.getItem('hasSelectedLanguage');
    
    const languageScreen = document.getElementById('languageScreen');
    const mainApp = document.getElementById('mainApp');
    
    if (!hasSelectedBefore) {
        if (languageScreen) languageScreen.classList.remove('hidden');
        if (mainApp) mainApp.classList.add('hidden');
    } else {
        if (languageScreen) languageScreen.classList.add('hidden');
        if (mainApp) mainApp.classList.remove('hidden');
        const urlParams = new URLSearchParams(window.location.search);
        let lang = urlParams.get('lang');
        
        if (!lang || !['fr', 'ar', 'en'].includes(lang)) {
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'preferred_language' && ['fr', 'ar', 'en'].includes(value)) {
                    lang = value;
                    break;
                }
            }
            
            if (!lang) lang = '';
        }
        applyLanguageToPage(lang);
    }
});

window.selectLanguage = selectLanguage;
window.changeLanguage = changeLanguage;
window.toggleLanguageMenu = toggleLanguageMenu;
