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
        const swiperLeft = new Swiper(".swiper-left", {
            direction: 'vertical',
            speed: 0,
            loop: false,
            spaceBetween: 12,
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
        });

        swiperLeft.slides.forEach((slide, index) => {
            slide.addEventListener('mouseenter', () => {
                swiperMain.slideTo(index, 0);
            });
        });
    }
}

new Item();
