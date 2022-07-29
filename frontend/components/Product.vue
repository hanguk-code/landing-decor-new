<template>
    <div>
        <div class="row">
            <div class="col-12">
                <div class="bread">
                    <ul>
                        <li v-for="bc in product.breadcrumbs">
                            <n-link :to="bc.url" v-html="bc.name"></n-link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="product-select">
                    <i :class="product.zone === 'yellow' ? 'sticker-pos-2 reserve' : 'sticker-pos-' + product.jan"></i>
                    <i class="prod prod-preview prod-preview-card" v-if="product.zone === 'black'"></i>
                    <div class="slider">
                        <div class="slider__zoom">
                            <a ref="mainPhotoHref" data-fancybox="gallery"
                               :href="apiWebUrl + '/image/' + product.image_url">
                                <img
                                    ref="mainPhoto"
                                    :src="apiWebUrl + '/image/' + product.image_url"
                                    class="zoom_04"
                                    :alt="product.name"
                                />
                            </a>
                        </div>
                        <div class="swiper-container swiper1 gallery-thumbs">
                            <div id="gallery_01" class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <a
                                        :href="apiWebUrl + '/image/' + product.image_url"
                                        class="elevatezoom-gallery"
                                        data-update=""
                                        :data-image="apiWebUrl + '/image/' + product.image_url"
                                        :data-zoom-image="apiWebUrl + '/image/' + product.image_url"
                                        @click="setMainPhoto"
                                        data-fancybox-index="gallery"
                                    >
                                        <img
                                            :src="apiWebUrl + '/image/' + product.image_url"
                                            :alt="product.name"
                                        />
                                    </a>
                                </div>
                                <div class="swiper-slide" v-for="image in product.gallery">
                                    <a
                                        :href="apiWebUrl + '/image/'+image.image_url"
                                        class="elevatezoom-gallery"
                                        data-update=""
                                        :data-image="apiWebUrl + '/image/'+image.image_url"
                                        :data-zoom-image="apiWebUrl + '/image/'+image.image_url"
                                        @click="setMainPhoto"
                                        data-fancybox="gallery"
                                    >
                                        <img
                                            :src="apiWebUrl + '/image/'+image.image_url"
                                            :alt="image.name"
                                        />
                                    </a>
                                </div>
                            </div>
                            <div class="swiper-button-prev swiper-button-prev1"></div>
                            <div class="swiper-button-next swiper-button-next1"></div>
                        </div>
                    </div>
                    <div class="product-select__content">
                        <div class="article">
                            <p>
                                Артикул: {{ product.article }}
                            </p>
                        </div>
                        <h2 v-html="product.name"></h2>
                        <span class="product-select__price">
							{{ product.price }} руб.
						</span>
                        <a href="#" class="product-select__link" @click="addToCart" v-if="product.zone !== 'yellow' && product.zone !== 'black'">
                            Добавить в корзину
                        </a>

                        <a href="#" class="product-select__link" v-if="product.zone === 'yellow'" style="background: url('http://web.archive.org/web/20180714224802im_/http://decor-retro.ru/catalog/view/theme/consta/i/button_buy1.png') no-repeat scroll 0 0 rgba(0, 0, 0, 0);width: 350px;
    padding-left: 0 !important;
    font-size: 18px;
    text-align: center;">
                            Товар забронирован
                        </a>
                        <table class="attribute">
                            <tbody>
                            <tr v-for="attribute in product.attributes">
                                <td>{{ attribute.description }}</td>
                                <td>{{ attribute.name }}</td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="product-description" v-html="product.description"></div>
                    </div>
                </div>
            </div>

            <div class="col-12" v-if="relatedProducts.length">
                <h2>ПОХОЖИЕ ТОВАРЫ</h2>
                <div class="slider slider-related">
                    <div class="swiper-container swiper2">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide" v-for="item in relatedProducts">
                                <n-link :to="item.url">
                                    <img
                                        :src="apiWebUrl + '/image/'+item.image_url"
                                        :title="item.name"
                                        :alt="item.name"
                                        @error="imageUrlAlt"
                                        class="zoom_01"
                                    >
                                    <div class="swiper-product-name" v-html="item.name"></div>
                                </n-link>
                            </div>
                        </div>
                        <div class="swiper-button-prev swiper-button-prev2"></div>
                        <div class="swiper-button-next swiper-button-next2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {isMobileOnly} from 'mobile-device-detect';

export default {
    components: {},
    data() {
        return {
            apiWebUrl: process.env.apiWebUrl,
            isMobile: isMobileOnly,
            archive: this.product.archive
        }
    },
    props: ['product', 'relatedProducts'],

    async fetch() {
        await this.setStatistic();
    },

    mounted() {
        this.swiper()
        this.swiper2()
        console.log('this.product', this.product)
    },

    methods: {
        async setStatistic() {
            let days = ['ВС', 'ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'];
            this.$axios.post(`https://decor-retro.ru/api/adm/statistic/set`, {
                type: "product",
                product_id: this.product.id,
                price: this.product.price,
                date_viewed: days[(new Date()).getDay()],
                watch_time: (new Date()).getHours()
            });
        },

        imageUrlAlt(event) {
            event.target.src = this.apiWebUrl + "/image/no_image.jpg"
        },

        swiper2() {
            // this.zoom_01()

            let swiper2 = new Swiper('.swiper2', {
                // slidesPerView: 4,
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 20
                    },
                    375: {
                        slidesPerView: 2,
                        spaceBetween: 30
                    },
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 40
                    },
                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 40
                    },
                },
                spaceBetween: 15,
                cssMode: true,
                zoom: true,
                loop: true,
                // lazy: true,
                freeMode: true,
                grabCursor: true,
                // watchSlidesVisibility: true,
                // watchSlidesProgress: true,
                allowTouchMove: true,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },
                navigation: {
                    prevEl: '.swiper-button-prev2',
                    nextEl: '.swiper-button-next2',
                },
            });


        },

        swiper() {
            $('[data-fancybox="gallery"]').fancybox({
                backFocus: false,
                buttons: [
                    "zoom",
                    //"share",
                    // "slideShow",
                    //"fullScreen",
                    //"download",
                    // "thumbs",
                    "close"
                ],
                beforeClose: function () {
                    console.log(111)
                }
            });

            new Swiper('.swiper1', {
                slidesPerView: 4,
                spaceBetween: 5,
                zoom: true,
                loop: false,
                freeMode: true,
                grabCursor: true,
                watchSlidesVisibility: true,
                watchSlidesProgress: true,
                navigation: {
                    prevEl: '.swiper-button-prev1',
                    nextEl: '.swiper-button-next1',
                },
            });

            this.zoom_04()
        },

        zoom_01() {
            if (!this.isMobile) {
                $('.zoomContainer').remove()
                $(".zoom_01").elevateZoom({
                    zoomWindowWidth: 300,
                    zoomWindowHeight: 300,
                    zoomWindowPosition: 1,
                    zoomWindowOffetx: 15,
                    cursor: 'pointer',
                });
            }
        },

        zoom_04() {
            if (!this.isMobile) {
                $('.zoomContainer').remove()
                $(".zoom_04").elevateZoom({
                    zoomWindowWidth: 300,
                    zoomWindowHeight: 300,
                    zoomWindowPosition: 1,
                    zoomWindowOffetx: 15,
                    cursor: 'pointer',
                });
            }
        },

        setMainPhoto(event) {
            event.preventDefault()
            this.$refs.mainPhoto.src = event.target.src
            this.$refs.mainPhotoHref.href = event.target.src
            this.zoom_04()
        },

        addToCart() {
            this.$store.dispatch('item/saveCartItem', {
                cartData: this.product
            })
            this.$notify({
                type: 'success',
                group: 'basket',
                // title: 'Сообщение',
                text: 'Товар добавлен в корзину'
            });
        }
    }
}


</script>

<style scoped>
    i.prod.prod-preview.prod-preview-card {
        top: 350px;
        left: 370px;
    }
</style>
