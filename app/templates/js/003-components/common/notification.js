class Notification {
  constructor() {
    this.notification = document.querySelector('.notification');
    this.delayTime = 5000;
    this.timeoutId = null;
    this.currentMessage = '';
    this.currentType = '';
  }

  moveToTopLayer() {
    const topLayer = document.querySelector('.fancybox__dialog');

    if (topLayer && this.notification && !topLayer.contains(this.notification)) {
      topLayer.appendChild(this.notification);
    }
  }

  showMessage(message, type, time = this.delayTime) {
    this.moveToTopLayer();

    const sameMessage = this.currentMessage === message;
    const sameType = this.currentType === type;

    if (this.timeoutId) {
      clearTimeout(this.timeoutId);
      this.timeoutId = null;
    }

    if (!sameMessage || !sameType) {
      this.notification.textContent = message;
      this.notification.classList.remove('success', 'error');
      this.notification.classList.add('active', type);
      this.currentMessage = message;
      this.currentType = type;
    }

    this.timeoutId = setTimeout(() => {
      this.notification.classList.remove('active', type);
      this.timeoutId = null;
      this.currentMessage = '';
      this.currentType = '';
    }, time);
  }

  success(message, time) {
    this.showMessage(message, 'success', time);
  }

  error(message, time) {
    this.showMessage(message, 'error', time);
  }
}

window.notofication = new Notification();
