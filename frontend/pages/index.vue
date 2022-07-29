<template>
    <section class="content" id="content">
        <div class="container" v-if="!$fetchState.pending">
            <Vector/>
            <MenuMobile/>
            <div class="row d-none d-lg-flex">
                <Menu/>
            </div>
            <div class="row">
                <div class="col-sm-4 col-md-12 col-lg-3">
                    <LeftMenu/>
                    <div class="left-news">
                        <h2>Новинки</h2>
                        <div class="product" v-for="product in newProducts">
                            <n-link :to="product.url" v-show="showImage">
                                <div class="product__content">
                                    <i :class="'pos-' + product.jan"></i>
                                    <img
                                        :src="apiWebUrl + '/image/' + product.image_url" alt=""
                                        @error="imageUrlAlt"
                                        :data-image="apiWebUrl + '/image/' + product.image_url"
                                        :data-zoom-image="apiWebUrl + '/image/' + product.image_url"
                                        :alt="product.name"
                                        class="zoom_01"
                                    >
                                </div>
                                <div class="product__price">
                                    <span>{{ product.price }} р.</span>
                                    <span>Арт: {{ product.article }}</span>
                                </div>
                                <div class="product__link">
                                    {{ product.name }}
                                </div>
                            </n-link>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8 col-md-12 col-lg-9">
                    <n-link to="/products/all" class="all-catalog">
                        Показать весь ассортимент товара
                    </n-link>
                    <div class="catalog">
                        <n-link :to="cat.url" class="catalog__card" v-for="cat in categories">
                            <div class="catalog__image">
                                <img :src="apiWebUrl + '/image/' + cat.image_url" :alt="cat.name">
                            </div>
                            <div class="catalog__text">
                                {{ cat.name }}
                            </div>
                        </n-link>
                    </div>
                    <div class="content-info">
                        <h1>Интернет-магазин "Декор-Ретро"</h1>

                        <p>
                            Приветствуем ценителей прекрасного! Вы находитесь на страницах интернет-магазина
                            «Декор-Ретро» -
                            мир изысканного антиквариата.
                        </p>
                        <p>
                            Сперва хотелось бы рассказать, что же можно отнести к антиквариату? Чтобы ответить на этот
                            вопрос, необходимо дать обозначение этому слову. Термин «антиквариат» используют, чтобы дать
                            описание различным категориям старинных и ценных вещей. Если обобщить, то антиквариатом
                            могут
                            быть старые картины и всевозможные иные ценные и старые предметы, которые можно
                            коллекционировать или совершать с ними различные операции торговли. Антикварными вещами
                            считаются также аксессуары, предметы интерьера, монеты, хрусталь и т. д.. Заниматься
                            антикварными предметами могут частные коллекционеры и государство. Для последнего подобные
                            старинные вещи с важным историческим прошлым имеют историко-правовой ценный интерес. Стоит
                            отметить, что по всем странам мира, захватывая и Россию, разветвляется сеть магазинов и
                            аукционов, которые существуют как в физическом формате, так и в электронном.
                        </p>
                        <p>
                            На нашем сайте представлены частные коллекции винтажных и антикварных вещей.
                            Интернет-магазин
                            «Декор-Ретро» содержит предметы интерьера, фарфор, стекло и хрусталь, кухонную и домашнюю
                            утварь, технику и аксессуары.
                        </p>
                        <p>
                            Среди предметов интерьера у нас можно встретить:светильники, лампы, бра, торшеры,
                            декоративное
                            панно. В разделе техники можно увидеть часы, фотоаппараты, бытовые и измерительные приборы,
                            радиотехнику. Вазы, бокалы, фужеры, графины, кувшины, стаканы и бокалы можно встретить в
                            разделе
                            стекла и хрусталя. Необыкновенные шкатулки, статуэтки, вазы и чайники встречаются в разделе
                            фарфора, фаянса, керамики. Ещё больше вещей можно увидеть ознакомившись более подробно с
                            категориями нашего интернет-магазина.
                        </p>
                        <p>
                            На сайте представлены неповторимые и изысканные вещи различных не только годов, но и даже
                            эпох.
                            Имея винтажные часы или старинный чайный сервиз становится возможным прикоснуться к истории
                            и
                            прочувствовать дух тех времён.
                        </p>
                        <a class="readmore-link" href="#" v-if="!readMoreActivated" @click="activateReadMore">Читать
                            далее</a>
                        <span v-if="readMoreActivated">
                            <p>
                                Старинные предметы интерьера могут с лёгкость создать особый стиль и шик Вашего дома,
                                который
                                сможет заворожить любого гостя. Запоминающимся и дорогим подарком всегда может послужить
                                антиквариат.
                            </p>
                            <p>
                                Старинные аксессуары могут с лёгкостью создать в человеке образ из прошлого, ведь мелочи
                                задают
                                тон. Раздел вещей из СССР позволит каждому человеку окунуться в, казалось бы, столь
                                родное,
                                но
                                уже в столько далёкое время – время СССР, которое имеет свою особую атмосферу.
                            </p>
                            <p>
                                Винтажные и антикварные вещи подчёркивают утончённость вкуса. Ведь познать красоту и
                                ценность
                                вещей может далеко не каждый человек.
                            </p>
                            <p>
                                Интернет-магазин «Декор-Ретро» поразит каждого человека обилием красивых старинных вещей
                                -
                                вещей
                                с историей.
                            </p>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import {isMobileOnly} from 'mobile-device-detect';
import MenuMobile from '~/components/Menu/MenuMobile'
import Menu from '~/components/Menu/Menu'
import LeftMenu from "~/components/Menu/LeftMenu";
import Vector from "~/components/Partials/Vector";
import {mapGetters} from "vuex";

export default {
    components: {
        LeftMenu,
        MenuMobile,
        Menu,
        Vector,
    },
    head: {
        bodyAttrs: {
            class: 'main-page'
        },
        title: 'Антикварный интернет-магазин "Декор Ретро"',
        meta: [
            {hid: 'description', name: 'description', content: 'Большой выбор изделий антиквариата - лучшие цены, удобный способ заказа и доставки!'}
        ],
    },
    computed: {
        ...mapGetters({
            categories: 'item/categories',
        }),
    },
    data() {
        return {
            isMobile: isMobileOnly,
            // categories: this.$root.categories,
            newProducts: [],
            showImage: true,
            readMoreActivated: false,

            apiWebUrl: process.env.apiWebUrl
        };
    },

    async fetch() {
        await this.getNewProducts()
        if (this.newProducts.length) {
            this.zoom_01()
        }
    },

    mounted() {
        if (this.newProducts.length) {
            this.zoom_01()
        }
    },

    methods: {
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

        activateReadMore(event) {
            event.preventDefault()
            this.readMoreActivated = true
        },

        imageUrlAlt(event) {
            // this.showImage = false
            event.target.src = this.apiWebUrl + "/image/no_image.jpg"
        },

        async getNewProducts() {
            const request = await this.$axios.$get(`last/products`)
            if (request) {
                this.newProducts = request.data
            }
        },
    }
}
</script>

<style scoped>

</style>
