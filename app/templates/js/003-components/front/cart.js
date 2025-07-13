import Api from "../common/api.js";

class Cart {
    constructor() {
        this.api = new Api();
        this.debounceTimers = {};
        this.cartFormWrap = document.querySelector('.cart-form');
        this.emptyCartWrap  = document.querySelector('.empty-cart ');
        this.init();
    }

    init() {
        this.handleAddToCart();
        this.updateCartCounter();

        this.setupInputLimits();
        this.setupMinusHandlers();
        this.setupPlusHandlers();

        this.removeItem();
    }

    setupInputLimits() {
        document.querySelectorAll('.item-qty-input').forEach(input => {
            input.addEventListener('input', () => {
                let val = parseInt(input.value, 10);

                if (isNaN(val) || val < 1) {
                    val = 1;
                } else if (val > 255) {
                    val = 255;
                }
                input.value = val;

                this.updateDisabledState(input);
            });
        });

        document.querySelectorAll('.item-qty-input').forEach(input => {
            input.addEventListener('blur', () => {
                const sku = input.dataset.sku;

                this.updateCart(sku, input.value)
                this.updateDisabledState(input);
            });
        });
    }

    setupMinusHandlers() {
        document.querySelectorAll('.item-qty-minus').forEach(minus => {
            minus.addEventListener('click', () => {
                const input = minus.parentElement.querySelector('.item-qty-input');
                const sku = input.dataset.sku;

                let val = parseInt(input.value, 10) || 1;

                if (val > 1) {
                    input.value = val - 1;
                    this.updateDisabledState(input);

                    this.debounceUpdateCart(sku, input.value);
                }
            });
        });
    }

    setupPlusHandlers() {
        document.querySelectorAll('.item-qty-plus').forEach(plus => {
            plus.addEventListener('click', () => {
                const input = plus.parentElement.querySelector('.item-qty-input');
                const sku = input.dataset.sku;

                let val = parseInt(input.value, 10) || 1;

                if (val < 255) {
                    input.value = val + 1;
                    this.updateDisabledState(input);

                    this.debounceUpdateCart(sku, input.value);
                }
            });
        });
    }

    updateDisabledState(input) {
        const minus = input.parentElement.parentElement.querySelector('.item-qty-minus');
        const plus = input.parentElement.parentElement.querySelector('.item-qty-plus');
        const val = parseInt(input.value, 10) || 1;

        if (minus) {
            if (val <= 1) {
                minus.classList.add('disabled');
            } else {
                minus.classList.remove('disabled');
            }
        }

        if (plus) {
            if (val >= 255) {
                plus.classList.add('disabled');
            } else {
                plus.classList.remove('disabled');
            }
        }
    }


    handleAddToCart() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.add-cart');

            if (btn) {
                const sku = btn.dataset.sku;

                if (sku && typeof sku === 'string') {
                    this.updateCart(sku, 1, true);
                }
            }
        });
    }

    getCart() {
        const cartCookie = this.getCookie('cart');

        if (!cartCookie) {
            return []
        }

        try {
            return JSON.parse(cartCookie);
        } catch (e) {
            console.error('Не удалось распарсить куки cart', e);

            return [];
        }
    }

    setCart(cart) {
        const value = JSON.stringify(cart);

        document.cookie = 'cart=' + encodeURIComponent(value) + '; path=/; max-age=' + (60*60*24*30);
    }

    getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));

        return match ? decodeURIComponent(match[1]) : null;
    }

    async updateCart(sku, qty = 1, add = false, remove = false) {
        try {
            const result = await this.api.post(`/api/cart/items/${sku}`, { qty: qty, add: add, remove: remove });

            this.setCart(result.data);
            this.updateCartCounter();
            this.updateCartInfo(result.data);

            if (window.location.pathname !== '/cart') {
                window.notofication.success(`Товар ${sku} добавлен в корзину`, 2000);
            }
        } catch (e) {
            window.notification.error(`Возникла ошибка при добавлении товара ${sku} в корзину`, 2000);
        }
    }

    updateCartCounter() {
        const cart = this.getCart();
        const count = cart.countCartItems || 0;
        const counters = document.querySelectorAll('.cart-counter');
        const wraps = document.querySelectorAll('.cart-counter-wrap');

        counters.forEach(counter => {
            counter.textContent = count;
        });

        wraps.forEach(wrap => {
            if (count === 0) {
                wrap.classList.add('hidden');
            } else {
                wrap.classList.remove('hidden');
            }
        });
    }

    updateCartInfo(cartData) {
        if (!cartData || !Array.isArray(cartData.cartItems)) return;
        console.log(cartData)

        // Берём список товаров с сервера
        const backendItems = cartData.cartItems;

        // Ищем все элементы на странице
        document.querySelectorAll('.cart-item')?.forEach(cartItemElem => {
            const input = cartItemElem.querySelector('.item-qty-input');

            if (!input) return;

            const sku = input.dataset.sku;

            // Ищем соответствующий элемент в ответе бэка
            const backendItem = backendItems.find(item => item.item.sku === sku);

            if (backendItem) {
                // Обновляем цену
                const priceElem = cartItemElem.querySelector('.item-price');
                if (priceElem) {
                    priceElem.textContent = backendItem.totalPrice;
                }

                // Обновим кол-во на всякий случай (если сервер мог откорректировать)
                // input.value = backendItem.qty;

                // И обновим состояние кнопок +/-
                this.updateDisabledState(input);
            }
        });

        // Ещё можно обновить блок с итоговой суммой, если есть
        const totalElem = document.querySelector('.right.wrap');

        if (totalElem && cartData.totalAmount) {
            totalElem.innerHTML = `Итого ${cartData.totalAmount}`;
        }

        // Обновление в корзине количество товаров
        const cartCountWrap = document.querySelector('.cart-count .count');
        const cartCount = cartData.countCartItems;

        if (cartCountWrap) {
            cartCountWrap.innerHTML = cartCount;
        }

        if (cartCount <= 0) {
            this.emptyCartWrap.classList.remove('hidden');
            this.cartFormWrap.classList.add('hidden');
        }
    }

    debounceUpdateCart(sku, qty) {
        if (this.debounceTimers[sku]) {
            clearTimeout(this.debounceTimers[sku]);
        }

        this.debounceTimers[sku] = setTimeout(() => {
            this.updateCart(sku, qty);
            delete this.debounceTimers[sku];
        }, 200);
    }

    removeItem() {
        document.querySelectorAll('.cart-item')?.forEach(cartItem => {
            const btn = cartItem.querySelector('.action-remove');

            btn?.addEventListener('click', async () => {
                btn.disabled = true;

                const sku = btn.dataset.sku;
                await this.updateCart(sku, 1, false, true);

                btn.disabled = false;
                cartItem.remove();
                window.notofication.success('Товар удалён из корзины', 2000);
            });
        });
    }
}

new Cart();
