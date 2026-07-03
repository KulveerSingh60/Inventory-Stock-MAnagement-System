document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        
        if (username === '' || password === '') {
            alert('Please fill in both username and password.');
            return;
        }
        
        // Demo credentials
        if (username === 'admin' && password === 'admin123') {
            alert('Login successful! Redirecting to dashboard...');
            // In real app: window.location.href = 'dashboard.html';
            console.log('Logged in as:', username);
        } else {
            alert('Invalid credentials. Try: admin / admin123');
        }
    });
    
    // Add floating label effect
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.parentElement.classList.remove('focused');
            }
        });
    });
});
