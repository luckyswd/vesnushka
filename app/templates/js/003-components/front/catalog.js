import Api from "../common/api.js";
import Select from "../common/select.js";
import Checkbox from "../common/checkbox.js";
import Input from "../common/input.js";

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
        this.catalogWrap = document.querySelector(".catalog__wrap");

        this.init();
    }

    init() {
        this.setInitialSort();
        this.attachScrollListener();
        this.attachSortChangeListener();
        this.attachShowMoreToggle();
        this.attachFilterCheckboxListener();
        this.attachChipRemoveListener();
        this.attachClearAllChipsListener();
        this.attachPriceInputListener();
        this.openMobileFilter();
        this.search();
        this.attachSearchInputListener();
        this.handleHasMore();
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

    attachPriceInputListener() {
        const minInput = document.getElementById("min_price");
        const maxInput = document.getElementById("max_price");

        const validateAndFetch = (e) => {
            const min = parseFloat(minInput?.value ?? '');
            const max = parseFloat(maxInput?.value ?? '');

            const isMinValid = !isNaN(min);
            const isMaxValid = !isNaN(max);

            if (isMinValid && isMaxValid) {
                if (e.target === minInput && min > max) {
                    minInput.value = "";
                }

                if (e.target === maxInput && max < min) {
                    maxInput.value = "";
                }
            }

            this.resetAndFetch();
        };

        if (minInput) {
            minInput.addEventListener("change", validateAndFetch);
        }

        if (maxInput) {
            maxInput.addEventListener("change", validateAndFetch);
        }
    }

    isAtBottom() {
        if (!this.catalogWrap) {
            return false;
        }

        const rect = this.catalogWrap.getBoundingClientRect();
        const offsetBottom = 1000;

        return (rect.bottom - offsetBottom) <= window.innerHeight;
    }

    async fetchItems(page = 1, reset = false) {
        this.loading = true;

        if (this.catalogWrap) {
            this.catalogWrap.classList.add("loader");
        }

        const sort = Select.getValueById("sort-select");
        const path = window.location.pathname.replace(/\/$/, "");
        const filters = this.collectFilters();
        const params = { page };

        if (sort) {
            params.sort = sort;
        }

        Object.assign(params, filters);

        if (reset) {
            this.updateUrl(params);
        }

        try {
            const data = await this.api.get(path, params);

            if (!data || !data.items) {
                this.hasMore = false;

                return;
            }

            if (data.items.trim() === "") {
                this.hasMore = false;
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

            this.handleHasMore();

            this.appendItems(data.items);
            new Checkbox();
            new Input();
        } catch (err) {
            console.error("Error fetching items:", err);
        } finally {
            this.loading = false;
            if (this.catalogWrap) {
                this.catalogWrap.classList.remove("loader");
            }
        }
    }

    handleHasMore() {
        const countText = document.querySelector('.catalog__right-items-count')?.textContent ?? '';
        const itemsCount = parseInt(countText.replace(/\D/g, ''), 10);

        const limit = parseInt(this.catalogWrap?.dataset.limit ?? '0', 10);
        const totalPages = Math.ceil(itemsCount / limit);

        if (this.page >= totalPages) {
            this.hasMore = false;
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

        if (newItems.length > 0 ) {
            newItems.forEach((item) => {
                this.container.appendChild(item);
            });
        } else {
            this.container.innerHTML = '<p class="item-not-found">По выбранным фильтрам товаров не найдено.</p>';
        }
    }

    updateFilters(filtersHtml) {
        if (this.filtersContainer && filtersHtml) {
            this.filtersContainer.innerHTML = filtersHtml;

            this.attachFilterCheckboxListener();
            this.attachShowMoreToggle();
            this.attachPriceInputListener(); // --- ЦЕНА ---
        }
    }

    updateItemsCount(html) {
        if (this.containerItemsCount) {
            this.containerItemsCount.textContent = html;
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

        const minInput = document.getElementById("min_price");
        const maxInput = document.getElementById("max_price");

        const currentMin = parseFloat(minInput?.value ?? '');
        const currentMax = parseFloat(maxInput?.value ?? '');

        if (currentMin) {
            filters.min_price = currentMin;
        }

        if (currentMax) {
            filters.max_price = currentMax;
        }

        const searchInput = document.querySelector(".input-search");
        const searchValue = searchInput?.value?.trim();

        if (searchValue) {
            filters.search = searchValue;
        }

        return filters;
    }

    attachClearAllChipsListener() {
        window.addEventListener("click", (e) => {
            const clearBtn = e.target.closest(".clear-all-chips");
            if (!clearBtn) return;

            document.querySelectorAll(".input-checkbox__input:checked")
              .forEach(cb => cb.checked = false);

            const minInput = document.getElementById("min_price");
            const maxInput = document.getElementById("max_price");

            if (minInput) minInput.value = "";
            if (maxInput) maxInput.value = "";

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

            if (type === "price") {
                const minInput = document.getElementById("min_price");
                const maxInput = document.getElementById("max_price");

                if (minInput) minInput.value = "";
                if (maxInput) maxInput.value = "";

                this.resetAndFetch();

                return;
            }

            const selector = `.input-checkbox__input[data-type="${type}"][data-value="${value}"]`;
            const checkbox = document.querySelector(selector);

            if (checkbox) {
                checkbox.checked = false;
            }

            this.resetAndFetch();
        });
    }

    openMobileFilter() {
        const mobileFilterBtn = document.querySelector('.mobile-filters');
        const body = document.body;

        if (!mobileFilterBtn || !this.filtersContainer) return;

        mobileFilterBtn.addEventListener('click', () => {
            const isActive = this.filtersContainer.classList.toggle('active-mobile');
            this.filtersContainer.style.maxHeight = (window.innerHeight - 120) + 'px';

            mobileFilterBtn.textContent = isActive ? 'Скрыть фильтры' : 'Фильтры';

            body.style.overflow = isActive ? 'hidden' : '';
        });
    }

    search() {
        const params = new URLSearchParams(window.location.search);
        const searchValue = params.get("search");

        if (searchValue !== null) {
            const input = document.querySelector(".input-search");
            if (input) {
                input.value = searchValue;
            }
        }
    }

    attachSearchInputListener() {
        const input = document.querySelector(".input-search");
        if (!input) return;

        const handler = () => {
            const value = input.value.trim();
            if (!value) return;

            // Если не на /search — делаем редирект
            if (window.location.pathname !== "/search") {
                const params = new URLSearchParams();
                params.set("search", value);
                const query = params.toString();
                window.location.href = `/search?${query}`;
                return;
            }

            // Ниже — только если уже на /search:

            // Сброс чекбоксов
            document.querySelectorAll(".input-checkbox__input:checked")
              .forEach(cb => cb.checked = false);

            // Сброс цен
            const minInput = document.getElementById("min_price");
            const maxInput = document.getElementById("max_price");
            if (minInput) minInput.value = "";
            if (maxInput) maxInput.value = "";

            // Сброс сортировки
            const select = document.getElementById("sort-select");
            if (select) {
                select.value = "popular"; // или "" если значение по умолчанию
                const triggerText = select
                  .closest(".custom-select")
                  ?.querySelector(".custom-select-trigger-text");
                if (triggerText) {
                    const option = select.querySelector('option[value="popular"]');
                    if (option) triggerText.textContent = option.textContent;
                }
            }

            // Обновить URL только с search
            const params = new URLSearchParams();
            params.set("search", value);
            const query = params.toString();
            const newUrl = `${window.location.pathname}?${query}`;
            window.history.replaceState(null, "", newUrl);

            // Обновить флаг и запросить заново
            this.hasMore = true;
            this.fetchItems(1, true);
        };

        const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

        if (isMobile) {
            input.addEventListener("change", handler);
        } else {
            input.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    handler();
                }
            });
        }
    }
}

new Catalog();
