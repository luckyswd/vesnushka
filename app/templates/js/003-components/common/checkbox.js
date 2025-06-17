class Checkbox {
  constructor() {
    this.init();
  }

  init() {
    document.querySelectorAll('.input-checkbox').forEach((wrapper) => {
      const input = wrapper.querySelector('.input-checkbox__input');

      if (!input) {
        return
      }

      wrapper.classList.toggle('active', input.checked);

      wrapper.addEventListener('click', (e) => {
        const tag = e.target.tagName.toLowerCase();

        if (e.target === input || tag === 'label') {
          return
        }

        input.click();
      });

      input.addEventListener('change', () => {
        wrapper.classList.toggle('active', input.checked);
      });
    });
  }
}

new Checkbox();

export default Checkbox;
