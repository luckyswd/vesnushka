import Api from "../common/api.js";
import Select from "../common/select.js";
import Checkbox from "../common/checkbox.js";

class Catalog {
    constructor() {
        this.api = new Api();
        this.loading = false;
        this.hasMore = true;
        this.page = 1;

        this.container = document.querySelector(".catalog__items");
        this.containerItemsCount = document.querySelector(".catalog__right-items-count");
        this.filtersContainer = document.querySelector(".catalog__left");
        this.chipsContainer = document.querySelector(".catalog__right-chips");

        if (this.container) {
            this.init();
        }
    }

    init() {
        this.setInitialSort();
        this.attachScrollListener();
        this.attachSortChangeListener();
        this.attachShowMoreToggle();
        this.attachFilterCheckboxListener();
        this.attachChipRemoveListener();
        this.attachClearAllChipsListener();
    }

    attachScrollListener() {
        window.addEventListener("scroll", () => {
            if (this.isAtBottom() && !this.loading && this.hasMore) {
                this.fetchItems(this.page + 1, false);
            }
        });
    }

    attachSortChangeListener() {
        const select = document.getElementById("sort-select");
        if (select) {
            select.addEventListener("change", () => {
                this.resetAndFetch();
            });
        }
    }

    attachFilterCheckboxListener() {
        const checkboxes = document.querySelectorAll(".input-checkbox__input");

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener("change", () => {
                this.resetAndFetch();
            });
        });
    }

    attachShowMoreToggle() {
        const toggleButtons = document.querySelectorAll(".show-more-button");

        toggleButtons.forEach((button) => {
            const targetId = button.dataset.target;
            const targetContainer = document.getElementById(targetId);

            if (!targetContainer) return;

            button.addEventListener("click", () => {
                const hiddenItems = targetContainer.querySelectorAll(".extra-filter");
                const isExpanded = button.classList.contains("expanded");

                if (isExpanded) {
                    hiddenItems.forEach((el) => el.classList.add("hidden"));
                    button.textContent = "Показать все";
                } else {
                    hiddenItems.forEach((el) => el.classList.remove("hidden"));
                    button.textContent = "Свернуть";
                }

                button.classList.toggle("expanded");
            });
        });
    }

    isAtBottom() {
        const scrollTop = window.scrollY || window.pageYOffset;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;

        return scrollTop + windowHeight >= documentHeight - 500;
    }

    async fetchItems(page = 1, reset = false) {
        this.loading = true;

        const sort = Select.getValueById("sort-select");
        const path = window.location.pathname.replace(/\/$/, "");

        const filters = this.collectFilters();
        const params = { page };

        if (sort) {
            params.sort = sort
        }

        Object.assign(params, filters);

        if (reset) {
            this.updateUrl(params);
        }

        try {
            const data = await this.api.get(path, params);

            if (!data || !data.items || data.items.trim() === "") {
                this.hasMore = false;

                return;
            }

            if (reset) {
                this.clearItems();
                this.updateFilters(data.filters);
                this.updateItemsCount(data.itemsCount);
                this.updateChips(data.chips);
                this.page = 1;
            } else {
                this.page = page;
            }

            this.appendItems(data.items);
            new Checkbox();
        } catch (err) {
            console.error("Error fetching items:", err);
        } finally {
            this.loading = false;
        }
    }

    updateUrl(params) {
        const queryParams = new URLSearchParams();

        Object.entries(params).forEach(([key, value]) => {
            if (key === "page") return;
            if (key === "sort" && value === "popular") return;

            if (Array.isArray(value)) {
                queryParams.set(key, value.join(","));
            } else {
                queryParams.set(key, value);
            }
        });

        const query = queryParams.toString();
        const newUrl = query
          ? `${window.location.pathname}?${query}`
          : window.location.pathname;

        window.history.replaceState(null, "", newUrl);
    }

    appendItems(html) {
        const tempContainer = document.createElement("div");
        tempContainer.innerHTML = html;

        const newItems = tempContainer.querySelectorAll(".item-card-wrap");
        newItems.forEach((item) => {
            this.container.appendChild(item);
        });
    }

    updateFilters(filtersHtml) {
        if (this.filtersContainer && filtersHtml) {
            this.filtersContainer.innerHTML = filtersHtml;

            this.attachFilterCheckboxListener();
            this.attachShowMoreToggle();
        }
    }

    updateItemsCount(count) {
        if (this.containerItemsCount) {
            this.containerItemsCount.textContent = count;
        }
    }

    updateChips(chipsHtml) {
        if (this.chipsContainer && chipsHtml) {
            this.chipsContainer.innerHTML = chipsHtml;
        } else {
            this.chipsContainer.innerHTML = "";
        }
    }

    clearItems() {
        this.container.innerHTML = "";
    }

    async resetAndFetch() {
        this.hasMore = true;
        await this.fetchItems(1, true);
    }

    setInitialSort() {
        const params = new URLSearchParams(window.location.search);
        const sortFromUrl = params.get("sort");
        const select = document.getElementById("sort-select");

        if (select && sortFromUrl) {
            const option = select.querySelector(`option[value="${sortFromUrl}"]`);
            if (option) {
                select.value = sortFromUrl;

                const triggerText = select
                  .closest(".custom-select")
                  ?.querySelector(".custom-select-trigger-text");

                if (triggerText) {
                    triggerText.textContent = option.textContent;
                }
            }
        }
    }

    collectFilters() {
        const filters = {};
        const checkedBoxes = document.querySelectorAll(".input-checkbox__input:checked");

        checkedBoxes.forEach((checkbox) => {
            const type = checkbox.dataset.type;
            const value = checkbox.dataset.value;

            if (!type || !value) return;

            if (!filters[type]) {
                filters[type] = [];
            }

            filters[type].push(value);
        });

        return filters;
    }

    attachClearAllChipsListener() {
        window.addEventListener("click", (e) => {
            const clearBtn = e.target.closest(".clear-all-chips");
            if (!clearBtn) return;

            document.querySelectorAll(".input-checkbox__input:checked")
              .forEach(cb => cb.checked = false);

            this.resetAndFetch();
        });
    }

    attachChipRemoveListener() {
        window.addEventListener("click", (e) => {
            const icon = e.target.closest(".chip-remove-icon");
            if (!icon) return;

            const chip = icon.closest(".chip");
            if (!chip) return;

            const type = chip.dataset.type;
            const value = chip.dataset.value;

            if (!type || !value) return;

            const selector = `.input-checkbox__input[data-type="${type}"][data-value="${value}"]`;
            const checkbox = document.querySelector(selector);

            if (checkbox) {
                checkbox.checked = false;
            }

            this.resetAndFetch();
        });
    }
}

new Catalog();
