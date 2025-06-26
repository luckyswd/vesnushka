import Api from "../common/api.js";

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
    }

    register() {
        const btnRegister = document.querySelector('.btn-register');

        btnRegister?.addEventListener('click', async (e) => {
            e.preventDefault();

            const email = document.getElementById('register-email').value.trim();
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-password-confirm').value;
            const firstName = document.getElementById('register-first-name').value.trim();

            if (!email || !password || !confirmPassword || !firstName) {
                window.notofication.error('Пожалуйста, заполните все поля — без них регистрация невозможна.');

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

    successMessage() {
        const message = sessionStorage.getItem('success_message');

        if (message) {
            window.notofication.success(message);
            sessionStorage.removeItem('success_message');
        }
    }
}

new Security();
