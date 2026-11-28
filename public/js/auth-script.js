/**
 * Auth Pages Interactive Controller
 * Lumbi Uwuu Store
 */

function toggleRegisterPassword() {
    const pass = document.getElementById('register_password');
    const passConfirm = document.getElementById('register_password_confirm');
    const checkbox = document.getElementById('show-register-password');
    if (!pass || !passConfirm) return;

    const showText = checkbox ? checkbox.checked : (pass.type === 'password');
    pass.type = showText ? 'text' : 'password';
    passConfirm.type = showText ? 'text' : 'password';
}

document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('show-register-password');
    if (checkbox) {
        checkbox.addEventListener('change', toggleRegisterPassword);
    }
});

/**
 * Auth Pages Interactive Controller
 * Lumbi Uwuu Store
 */

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const checkbox = document.getElementById('show-password');
    if (!passwordInput) return;

    passwordInput.type = checkbox ? (checkbox.checked ? 'text' : 'password') : (passwordInput.type === 'password' ? 'text' : 'password');
}

function toggleRegisterPassword() {
    const pass = document.getElementById('register_password');
    const passConfirm = document.getElementById('register_password_confirm');
    const checkbox = document.getElementById('show-register-password');
    if (!pass || !passConfirm) return;

    const showText = checkbox ? checkbox.checked : (pass.type === 'password');
    pass.type = showText ? 'text' : 'password';
    passConfirm.type = showText ? 'text' : 'password';
}

document.addEventListener('DOMContentLoaded', function () {
    const loginCheckbox = document.getElementById('show-password');
    if (loginCheckbox) {
        loginCheckbox.addEventListener('change', togglePassword);
    }

    const registerCheckbox = document.getElementById('show-register-password');
    if (registerCheckbox) {
        registerCheckbox.addEventListener('change', toggleRegisterPassword);
    }
});