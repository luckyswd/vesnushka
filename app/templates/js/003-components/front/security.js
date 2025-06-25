import Api from "../common/api.js";

class Security {
    constructor() {
        this.api = new Api();
        this.init();
    }

    init() {
        this.register();
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
                const result = await this.api.post('/api/register', {
                    email,
                    password,
                    firstName,
                });

                window.notofication.success(result.data.message);
            } catch (errMessage) {
                window.notofication.error(errMessage || 'Что-то пошло не так при регистрации. Попробуйте ещё раз.');
            }
        });
    }
}

new Security();
