<template>
    <div>
        <div class="row d-lg-flex">
            <div class="col-12">
                <div class="bread">
                    <ul>
                        <li v-for="b in breadcrumbs" v-if="breadcrumbs">
                            <n-link :to="'/'+b.url">
                                {{ b.name }}
                            </n-link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 col-md-3">
                <LeftMenu/>
            </div>
            <div class="col-sm-8 col-md-12 col-lg-9">
                <h2 class="catalog-title">
                    {{ category.name }}
                </h2>

                <div class="range" v-if="subCategories.length < 1">
                    <form action="">
<!--                        <h3 class="text-left">-->
<!--                            Выбор по параметрам-->
<!--                        </h3>-->
                        <div class="range__select">

                            <div class="range__top">
                                <div class="range__title">
                                    Страна:
                                </div>
                                <select v-model="selectCountry">
                                    <option value="">Выбрать</option>
                                    <option
                                        v-for="item in countries"
                                        :key="item.text"
                                        :label="item.text"
                                        :value="item.text">
                                    </option>
                                </select>
                            </div>

                            <div class="range__top">
                                <div class="range__title">
                                    Материал:
                                </div>
                                <select v-model="selectMaterial">
                                    <option value="">Выбрать</option>
                                    <option
                                        v-for="item in materials"
                                        :key="item.text"
                                        :label="item.text"
                                        :value="item.text">
                                    </option>
                                </select>
                            </div>

                            <!--                            <div class="range__top">-->
                            <!--                                <div class="range__title">-->
                            <!--                                    Изготовитель:-->
                            <!--                                </div>-->
                            <!--                                <select>-->
                            <!--                                    <option value="">Выбрать</option>-->
                            <!--                                    <option value="">Майолика</option>-->
                            <!--                                    <option value="">Металл / Эмаль</option>-->
                            <!--                                    <option value="">Фарвор</option>-->
                            <!--                                    <option value="">Фарфар</option>-->
                            <!--                                    <option value="">Фарфор</option>-->
                            <!--                                    <option value="">фарфор / металл</option>-->
                            <!--                                    <option value="">Фаянс</option>-->
                            <!--                                </select>-->
                            <!--                            </div>-->

                            <div class="range-price">
                                <div class="range__top">
                                    <div class="range__title">
                                        Цена:
                                    </div>
                                    <div class="text-center">
                                        <b-form-slider ref="range"
                                                       v-model="rangeValue"
                                                       range
                                                       :min="min_price"
                                                       :max="max_price"></b-form-slider>
                                    </div>
                                    <div class="text-center">
                                        <span>от</span>
                                        <input type="text" v-model="rangeValue[0]"/>
                                        <span>до</span>
                                        <input type="text" v-model="rangeValue[1]"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="range__btn">
                            <a href="#" type="button" @click="setQuery">
                                Применить
                            </a>
                            <a href="#" type="button" @click="clearQuery">
                                Сбросить
                            </a>
                        </div>
                    </form>
                    <div class="text-left" style="margin-left: 10px">
                        Найдено {{ totalProducts }} товаров
                    </div>
                </div>

                <div class="catalog">
                    <div class="product" v-for="subCategory in subCategories">
                        <div class="product__content">
                            <n-link :to="subCategory.url">
                                <img :src="apiWebUrl+'/image/'+subCategory.image_url"
                                     @error="imageUrlAlt"
                                     alt=""
                                >
                            </n-link>
                        </div>
                        <div class="product__link">
                            <n-link :to="subCategory.url">
                                {{ subCategory.name }}
                            </n-link>
                        </div>
                    </div>

                    <div class="product" v-for="product in products" v-if="subCategories.length < 1">
                        <div class="product__content">
                            <i :class="product.zone === 'yellow' ? 'pos-2 reserve' : 'pos-' + product.jan" v-if="product.jan && product.zone === 'yellow' || product.jan && product.upc "></i>
                            <i class="prod prod-preview" v-if="product.zone === 'black'" style="display: block;left: 135px;top: 130px;"></i>
                            <n-link :to="product.url">
                                <img :src="apiWebUrl+'/image/'+product.image_url"
                                     :data-image="apiWebUrl+'/image/'+product.image_url"
                                     :data-zoom-image="apiWebUrl+'/image/'+product.image_url"
                                     @error="imageUrlAlt"
                                     class="zoom_01"
                                     alt=""
                                >
                            </n-link>
                        </div>
                        <div class="product__price">
							<span>
								{{ product.price }} р.
							</span>
                            <span>
								Арт: {{ product.article }}
							</span>
                        </div>
                        <div class="product__link">
                            <n-link :to="product.url" v-html="product.name">

                            </n-link>
                        </div>
                    </div>



                    <div class="tags-container">
                        <div v-html="category.seo_text" :class="{'truncate-text': !visible_full_seo_text}"></div>
                        <a href="#" class="readmore-link" @click="visible_full_seo_text = true" v-if="!visible_full_seo_text">Читать далее</a>
                    </div>

                    <div v-observe-visibility="currentPage !== lastPage ? visibilityChanged : false"></div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import 'bootstrap-slider/dist/css/bootstrap-slider.css'

export default {
    components: {},
    props: [
        'categories',
        'subCategories',
        'products',
        'category',
        'breadcrumbs',
        'currentPage',
        'lastPage',
        'totalProducts',
        'countries',
        'materials',
        'min_price',
        'max_price',
    ],
    head() {
        return {
            title: this.category.seo_title,
            meta: [
                {
                    hid: 'description',
                    name: 'description',
                    content: this.category.meta_description
                },
                {
                    hid: 'keywords',
                    name: 'keywords',
                    content: this.category.meta_keyword
                }
            ],
        }
    },
    data() {
        return {
            rangeValue: [this.min_price, this.max_price],
            // rangeValue: [30, 882],
            apiWebUrl: process.env.apiWebUrl,
            isMobile: this.$parent.isMobile,
            selectCountry: '',
            selectMaterial: '',
            query: [],
            mainCat: '',
            showChildren: 'display: block;',

            short_seo_text: '',
            full_seo_text: '',
            visible_full_seo_text: false
        };
    },

    async fetch() {
        let splitUrl = this.$route.params.pathMatch.split('/')

        if (splitUrl[0]) {
            this.mainCat = splitUrl[0]
        } else {
            this.mainCat = splitUrl[1]
        }
        await this.setStatistic();


        this.full_seo_text = this.category.seo_text;
        this.short_seo_text = this.category.seo_text;
        console.log(this.short_seo_text)

    },

    mounted() {
        this.zoom1()
    },

    methods: {
        async setStatistic() {
            let days = ['ВС', 'ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'];
            this.$axios.post(`https://decor-retro.ru/api/adm/statistic/set`, {
                type: "category",
                category: this.breadcrumbs[(this.breadcrumbs).length - 1].name,
                date_viewed: days[(new Date()).getDay()],
                watch_time: (new Date()).getHours()
            });
        },

        imageUrlAlt(event) {
            event.target.src = this.apiWebUrl + "/image/no_image.jpg"
        },

        zoom1() {
            if (!this.isMobile) {
                $('.zoomContainer').remove()
                $(".zoom_01").elevateZoom({
                    zoomWindowWidth: 300,
                    zoomWindowHeight: 300,
                    zoomWindowPosition: 1,
                    zoomWindowOffetx: -515,
                    lensSize: 500,
                    cursor: 'pointer',
                });
            }
        },

        setQuery(event) {
            event.preventDefault()
            this.query = {
                price_min: this.rangeValue[0],
                price_max: this.rangeValue[1],
                country: this.selectCountry,
                material: this.selectMaterial,
            }
            this.$emit('queryProducts', this.query)
        },

        clearQuery(event) {
            event.preventDefault()
            this.query = []
            this.selectCountry = ''
            this.selectMaterial = ''
            this.rangeValue = [this.min_price, this.max_price],
            this.$emit('queryProducts', this.query)
        },

        visibilityChanged(currentPage) {
            $('.product:last').after('<div class="ajaxblock"><img src="/img/loader.gif" /></div>');
            this.currentPage = currentPage + 1
            let nextPage = currentPage + 1
            this.$emit('visibilityChanged', nextPage)
            $('.ajaxblock').remove()
            this.zoom1()
        }
    }
}
</script>

<style scoped>
    i.prod.prod-preview {
        display: block;
        left: 135px;
        top: 130px;
    }

    .truncate-text {
        height: 170px;
        background: transparent;
        padding: 1rem;
        overflow: hidden;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }

    .readmore-link {
        margin-top: 10px;
        margin-left: 10px;
    }
</style>
