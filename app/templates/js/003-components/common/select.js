class Select {
  constructor() {
    this.activeSelect = null;
    this.init();
    this.attachGlobalClickListener();
  }

  init() {
    const selects = document.querySelectorAll('.custom-select-wrap');

    selects.forEach((selectWrap) => {
      const customSelect = selectWrap.querySelector('.custom-select');
      const trigger = selectWrap.querySelector('.custom-select-trigger');
      const nativeSelect = selectWrap.querySelector('select');
      const optionsList = selectWrap.querySelectorAll('.custom-select-option');
      const triggerText = selectWrap.querySelector('.custom-select-trigger-text');

      if (!customSelect || !nativeSelect) return;

      // Открыть/закрыть список
      trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        // Закрыть предыдущий, если другой
        if (this.activeSelect && this.activeSelect !== selectWrap) {
          this.activeSelect.classList.remove('active');
        }

        selectWrap.classList.toggle('active');
        this.activeSelect = selectWrap.classList.contains('active') ? selectWrap : null;
      });

      // Клик по опциям
      optionsList.forEach((option) => {
        option.addEventListener('click', (e) => {
          const value = option.dataset.value;
          const text = option.textContent;

          // Установить значение
          nativeSelect.value = value;

          // Обновить триггерный текст
          if (triggerText) {
            triggerText.textContent = text;
          }

          // Выключить активный класс
          selectWrap.classList.remove('active');
          this.activeSelect = null;

          // Запустить событие "change"
          nativeSelect.dispatchEvent(new Event('change'));
        });
      });
    });
  }

  attachGlobalClickListener() {
    document.addEventListener('click', (e) => {
      if (this.activeSelect && !this.activeSelect.contains(e.target)) {
        this.activeSelect.classList.remove('active');
        this.activeSelect = null;
      }
    });
  }

  // Метод получения выбранного значения по ID
  static getValueById(id) {
    const select = document.getElementById(id);

    return select?.value || null;
  }
}

new Select();

export default Select;
