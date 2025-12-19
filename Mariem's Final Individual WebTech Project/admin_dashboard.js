document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.nav-item').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    document.querySelectorAll('.nav-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.classList.add('active');
                }
            }
        });
    });
    let sessionTimeout;
    
    function resetSessionTimer() {
        clearTimeout(sessionTimeout);
        sessionTimeout = setTimeout(() => {
            if (confirm('Your session will expire in 5 minutes. Continue?')) {
                resetSessionTimer();
                fetch('keep_alive.php').catch(err => console.error('Keep alive failed', err));
            }
        }, 25 * 60 * 1000);
    }
    ['click', 'keypress', 'mousemove'].forEach(event => {
        document.addEventListener(event, resetSessionTimer);
    });
    
    resetSessionTimer();
        console.log('=== ADMIN DASHBOARD ===');
    console.log('All actions are logged.');
});