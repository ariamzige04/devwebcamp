import Swiper, { Navigation } from 'swiper';
import 'swiper/css';
import 'swiper/css/navigation';


document.addEventListener('DOMContentLoaded', function() {
    if(document.querySelector('.slider')) {
        const opciones = {
            slidesPerView: 1,//va a aparecer un slider en pantalla
            spaceBetween: 15,//es la separacion en pixeles
            freeMode: true,//esto es por si no funciona bien
            navigation: {//son los botones de la navegacion 
                nextEl: '.swiper-button-next',//elemento siguiente
                prevEl: '.swiper-button-prev'//elemento previo
            },
            breakpoints: {
                768: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 3
                },
                1200: {
                    slidesPerView: 4//va a aparecer 4 sliders en pantalla
                }
            }
        }

        Swiper.use([Navigation])//le pasas el modulo para que aparesca la avegacion de botones
        new Swiper('.slider', opciones)
    }
});