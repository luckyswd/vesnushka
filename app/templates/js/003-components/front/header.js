import Api from "../common/api.js";

class Header {
  constructor() {
    this.api = new Api();
    this.init();
  }

  init() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.add-cart');

      if (btn) {
        const sku = btn.dataset.sku;
        if (sku && typeof sku === 'string') {
          this.addToCart(sku);
        }
      }
    });

    this.updateCartCounter();
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

  async addToCart(sku) {
    let cart = this.getCart();
    let item = cart.find(i => i.sku === sku);

    if (item) {
      item.qty++;
    } else {
      cart.push({ sku: sku, qty: 1 });
    }

    try {
      await this.api.post('/api/cart/add', { sku: sku, qty: 1 });

      this.setCart(cart);
      this.updateCartCounter();

      window.notofication.success(`Товар ${sku} добавлен в корзину`, 2000);
    } catch (e) {
      window.notification.error(`Возникла ошибка при добавлении товара ${sku} в корзину`, 2000);
    }
  }

  updateCartCounter() {
    const cart = this.getCart();
    const count = cart.reduce((sum, item) => sum + item.qty, 0);
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
}

new Header();
