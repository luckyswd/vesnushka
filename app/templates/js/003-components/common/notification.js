class Notification {
  constructor() {
    this.notification = document.querySelector('.notification');
    this.delayTime = 2000;
  }

  showMessage(message, type) {
    this.notification.textContent = message;
    this.notification.classList.add('active', type);

    setTimeout(() => {
      this.notification.classList.remove('active', type);
    }, this.delayTime);
  }

  success(message) {
    this.showMessage(message, 'success');
  }

  error(message) {
    this.showMessage(message, 'error');
  }
}

window.notofication = new Notification();