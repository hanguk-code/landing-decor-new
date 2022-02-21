<template>
    <section class="content" id="content">
        <div class="container" v-if="!$fetchState.pending">
            <Vector/>
            <MenuMobile/>
            <div class="row d-none d-lg-flex">
                <Menu/>
            </div>

            <div class="row p-5">

                <div class="contact-info">
                    <div class="content">
                        <h2>{{ article.seo_title }}</h2>
                        <div v-html="article.text"></div>
                    </div>
                </div>
            </div>


            <div class="catalog" v-if="article.products">
                <div class="product" v-for="product in article.products">
                    <div class="product__content">
                        <i :class="product.zone === 'yellow' ? 'pos-2 reserve' : 'pos-' + product.jan" v-if="product.jan && product.zone === 'yellow' || product.jan && product.upc "></i>
                        <i class="prod prod-preview" v-if="product.zone === 'black'" style="display: block;left: 135px;top: 130px;"></i>
                        <n-link :to="'/' + product.url">
                            <img :src="apiWebUrl+'/image/'+product.image"
                                 :data-image="apiWebUrl+'/image/'+product.image"
                                 :data-zoom-image="apiWebUrl+'/image/'+product.image"
                                 @error="imageUrlAlt"
                                 class="zoom_01"
                                 alt=""
                            >
                        </n-link>
                    </div>
                    <div class="product__price">
                        <span>{{ product.price }} р.</span>
                        <span>Арт: {{ product.sku }}</span>
                    </div>
                    <div class="product__link">
                        <n-link :to="'/' + product.url">
                            {{ product.name }}
                        </n-link>
                    </div>
                </div>

            </div>

        </div>
    </section>
</template>

<script>
    import Breadcrumbs from "~/components/Layouts/Breadcrumbs";

    export default {
        components: {
            Breadcrumbs
        },
        data() {
            return {
                apiWebUrl: process.env.apiWebUrl,
                breadcrumbs: [
                    {
                        title: 'Статьи',
                        name: 'articles'
                    },
                    {
                        title: 'Редактирование',
                        name: 'articles-id',
                        params: 'id: ' + this.$route.params.id,
                    }
                ],

                article: []
            };
        },
        async fetch() {
            await this.getArticle()
            await this.zoom1()
        },

        methods: {
            async getArticle() {
                console.log(this.$route.params)
                const response = await this.$axios.get(`http://139.162.135.193/api/adm/articles/get/${this.$route.params.id}`);
                if (response.data.article) {
                    this.article = response.data.article;
                } else {
                    this.$router.push({path: '/'})
                }
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
        }
    }
</script>

<style scoped>
    .container {
        position: relative;
        background: url(http://139.162.135.193/img/foncontent.png) repeat-y scroll 0 0 rgba(0, 0, 0, 0);
        border: 2px solid #FFEA6F;
        border-radius: 9px;
        box-shadow: 0 0 22px 16px #4f0f0f inset;
        background-size: contain;
        padding: 20px;
        text-align: justify
    }

    .product {
        max-width: 200px;
        width: 100%;
        margin: 0 auto 20px;
        position: relative;
        font-family: georgia;
        font-style: italic;
        font-size: 16px;
        line-height: 1.15;
    }
</style>
