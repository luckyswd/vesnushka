import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/thumbs';
import 'swiper/css/free-mode';
import { FreeMode, Navigation, Thumbs } from 'swiper/modules';
import Swiper from 'swiper';

Swiper.use([Navigation, Thumbs, FreeMode]);

class Item {
    constructor() {
        this.init();
    }

    init() {
        this.handleSliders();
        this.handleTabs();
    }

    handleSliders() {
        const swiperLeft = new Swiper(".swiper-left", {
            direction: 'vertical',
            speed: 0,
            loop: false,
            spaceBetween: 8,
            slidesPerView: 6,
            freeMode: true,
            watchSlidesProgress: true,
            navigation: {
                nextEl: ".swiper-left .swiper-button-next",
                prevEl: ".swiper-left .swiper-button-prev",
            },
        });

        const swiperMain = new Swiper(".swiper-main", {
            loop: false,
            speed: 0,
            allowTouchMove: false,
            navigation: {
                nextEl: ".swiper-main .swiper-button-next",
                prevEl: ".swiper-main .swiper-button-prev",
            },
            thumbs: {
                swiper: swiperLeft,
            },
            breakpoints: {
                0: {
                    allowTouchMove: true,
                    speed: 300,
                },
                1025: {
                    allowTouchMove: false,
                }
            }
        });

        const counterEl = document.querySelector('.swiper-counter');
        function updateCounter(swiper) {
            if (counterEl) {
                counterEl.textContent = `${swiper.realIndex + 1}/${swiper.slides.length}`;
            }
        }

        swiperMain.on('init', () => updateCounter(swiperMain));
        swiperMain.on('slideChange', () => updateCounter(swiperMain));

        updateCounter(swiperMain);

        swiperLeft.slides.forEach((slide, index) => {
            slide.addEventListener('mouseenter', () => {
                swiperMain.slideTo(index, 0);
                updateCounter(swiperMain);
            });
        });
    }

    handleTabs() {
        const tabButtons = document.querySelectorAll('.tabs__btn');
        const tabPanels = document.querySelectorAll('.tabs__panel');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const target = button.dataset.tab;

                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanels.forEach(panel => panel.classList.remove('active'));

                button.classList.add('active');
                document.querySelector(`.tabs__panel[data-tab="${target}"]`).classList.add('active');
            });
        });
    }
}

new Item();

// document.querySelector('.user-login').addEventListener('click', () => {
//     fetch('/api/login', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify({ email: 'admin@example.com', password: 'admin' }),
//         credentials: 'include'
//     });
// })
//
// document.querySelector('.user-logout').addEventListener('click', () => {
//     fetch('/api/logout', {
//         method: 'POST',
//         credentials: 'include'
//     }).then(() => {
//         console.log('Logged out');
//     });
// });
