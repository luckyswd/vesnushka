class Input {
  constructor() {
    this.init();
  }

  init() {
    const inputs = document.querySelectorAll(".input-price");

    inputs.forEach((input) => {
      input.addEventListener("input", () => {
        let val = parseFloat(input.value);

        if (isNaN(val)) {
          input.value = "";

          return;
        }

        if (val < 1) {
          input.value = 1;
        } else if (val > 1000000) {
          input.value = 1000000;
        }
      });

      input.addEventListener("keypress", (e) => {
        const char = String.fromCharCode(e.which);

        if (!/[0-9.]/.test(char)) {
          e.preventDefault();
        }
      });
    });
  }
}

new Input();

export default Input;
