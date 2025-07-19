import Api from "../common/api.js";
import {Fancybox} from "@fancyapps/ui";

class Security {
    constructor() {
        this.api = new Api();

        this.registerForm = document.querySelector('.register-form')
        this.verifyCodeForm = document.querySelector('.verify-code-wrap')

        this.init();
    }

    init() {
        this.register();
        this.verifyCode();
        this.successMessage();
        this.login();
        this.resetPassword();
        this.applyPhoneMask();
    }

    register() {
        const btnRegister = document.querySelector('.btn-register');

        btnRegister?.addEventListener('click', async (e) => {
            e.preventDefault();

            const email = document.getElementById('register-email').value.trim();
            const phone = document.getElementById('register-phone').value.trim();
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-password-confirm').value;
            const firstName = document.getElementById('register-first-name').value.trim();

            if (!email || !password || !confirmPassword || !firstName || !phone) {
                window.notofication.error('Пожалуйста, заполните все поля — без них регистрация невозможна.');

                return;
            }

            const phonePattern = /^\+375 \d{2} \d{3}-\d{2}-\d{2}$/;

            if (!phonePattern.test(phone)) {
                window.notofication.error('Введите номер в формате +375 99 999-99-99.');

                return;
            }

            if (password.length < 6) {
                window.notofication.error('Придумайте пароль длиной хотя бы в 6 символов — это важно для безопасности.');

                return;
            }

            if (password !== confirmPassword) {
                window.notofication.error('Введённые пароли не совпадают. Убедитесь, что оба поля совпадают символ в символ.');

                return;
            }

            try {
                btnRegister.classList.add('loader');

                const result = await this.api.post('/api/register', {
                    email,
                    phone,
                    password,
                    firstName,
                });

                const userGuid = document.getElementById('user-guid');
                userGuid.value = result.data.userGuid;

                window.notofication.success(result.data.message);

                this.registerForm.classList.add('hidden');
                this.verifyCodeForm.classList.remove('hidden');
            } catch (errMessage) {
                window.notofication.error(errMessage);
            }

            btnRegister.classList.remove('loader');
        });
    }

    verifyCode() {
        const btnVerifyCode = document.querySelector('.btn-verify-code');

        btnVerifyCode?.addEventListener('click', async (e) => {
            e.preventDefault();

            try {
                btnVerifyCode.classList.add('loader');

                const code = document.getElementById('verify-code').value.trim();
                const userGuid = document.getElementById('user-guid').value.trim();

                const result = await this.api.post('/api/verify-code', {
                    code,
                    userGuid,
                });

                sessionStorage.setItem('success_message', result.data.message);
                location.reload();
            } catch (errMessage) {
                window.notofication.error(errMessage);
            }

            btnVerifyCode.classList.remove('loader');
        });
    }

    login() {
        const btnLogin = document.querySelector('.btn-login');

        btnLogin?.addEventListener('click', async (e) => {
            e.preventDefault();

            try {
                btnLogin.classList.add('loader');

                const email = document.getElementById('login-email').value.trim();
                const password = document.getElementById('login-password').value.trim();

                const result = await this.api.post('/api/login', {
                    email,
                    password,
                });

                sessionStorage.setItem('success_message', result.data.message);
                location.reload();
            } catch (errMessage) {
                window.notofication.error(errMessage);
            }

            btnLogin.classList.remove('loader');
        });
    }

    resetPassword() {
        const userLoginBtn = document.querySelector('.user-login');
        const loginForm = document.querySelector('.login-form');
        const resetPasswordForm = document.querySelector('.reset-password-form');

        userLoginBtn?.addEventListener('click', async (e) => {
            loginForm.classList.remove('hidden');
            resetPasswordForm.classList.add('hidden');
        });

        const btnForgotPassword = document.querySelector('.btn-forgot-password');

        btnForgotPassword?.addEventListener('click', async (e) => {
            e.preventDefault();

            loginForm.classList.add('hidden');
            resetPasswordForm.classList.remove('hidden');
        });

        const btnResetPassword = document.querySelector('.btn-reset-password');

        btnResetPassword?.addEventListener('click', async (e) => {
            e.preventDefault();

            try {
                btnResetPassword.classList.add('loader');

                const email = document.getElementById('reset-password-email').value.trim();

                const result = await this.api.post('/api/request-reset-password', {
                    email,
                });

                Fancybox.close(true);
                window.notofication.success(result.data.message);
            } catch (errMessage) {
                window.notofication.error(errMessage);
            }

            btnResetPassword.classList.remove('loader');
        });
    }

    successMessage() {
        const params = new URLSearchParams(window.location.search);
        const urlMessage = params.get('msg');
        const urlType = params.get('type') || 'success';

        if (urlMessage) {
            sessionStorage.setItem('success_message', urlMessage);
            sessionStorage.setItem('success_type', urlType);
            history.replaceState({}, '', window.location.pathname);
        }

        const message = sessionStorage.getItem('success_message');
        const type = sessionStorage.getItem('success_type') || 'success';

        if (message) {
            if (type === 'error') {
                window.notofication.error(message);
            } else {
                window.notofication.success(message);
            }

            sessionStorage.removeItem('success_message');
            sessionStorage.removeItem('success_type');
        }
    }

    applyPhoneMask() {
        const phoneInputs = document.querySelectorAll('.phone-input');

        phoneInputs.forEach(input => {
            input.addEventListener('input', () => {
                let value = input.value.replace(/\D/g, '');

                // Удалим префиксы 375, 80 и т.п.
                if (value.startsWith('375')) {
                    value = value.slice(3);
                } else if (value.startsWith('80')) {
                    value = value.slice(2);
                }

                value = value.slice(0, 9); // Оставляем только 9 цифр: 99 9999999

                let formatted = '+375 ';

                if (value.length > 0) {
                    formatted += value.slice(0, 2); // код оператора
                }
                if (value.length >= 3) {
                    formatted += ' ' + value.slice(2, 5);
                }
                if (value.length >= 6) {
                    formatted += '-' + value.slice(5, 7);
                }
                if (value.length >= 8) {
                    formatted += '-' + value.slice(7, 9);
                }

                input.value = formatted;
            });

            // Установим стартовое значение
            if (!input.value) {
                input.value = '+375 ';
            }
        });
    }
}

new Security();
