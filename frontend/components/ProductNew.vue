<template>
    <div>
        <Breadcrumbs :breadcrumbs="breadcrumbs"/>
        <div class="row">
            <div class="col-sm-4 col-md-3">
                <LeftMenu/>
            </div>
            <div class="col-sm-8 col-md-9">
                <h2 class="catalog-title">
                    {{ breadcrumbs.slice(-1)[0].title }}
                </h2>
                <div class="catalog">
                    <div class="product" v-for="product in products">
                        <div class="product__content">
                            <n-link :to="product.url">
                                <i :class="'pos-' + product.jan + ' ' + product.upc"></i>
                                <img :src="apiWebUrl + '/image/'+product.image_url"
                                     :data-image="apiWebUrl + '/image/'+product.image_url"
                                     :data-zoom-image="apiWebUrl + '/image/'+product.image_url"
                                     @error="imageUrlAlt"
                                     class="zoom_01"
                                     alt=""
                                >
                            </n-link>
                        </div>
                        <div class="product__price">
                            <span>{{ product.price }} р.</span>
                            <span>Арт: {{ product.article }}</span>
                        </div>
                        <div class="product__link">
                            <n-link :to="product.url">
                                {{ product.name }}
                            </n-link>
                        </div>
                    </div>

                    <div v-observe-visibility="visibilityChanged"></div>
                </div>

            </div>
        </div>
    </div>
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
            isMobile: this.$parent.isMobile,

            breadcrumbs: [{
                url: '/products/new',
                title: 'Новые предметы нашей коллекции'
            }],
            products: [],

            query: {
                page: 1,
                price_min: '',
                price_max: '',
            },

            pagination: {
                lastPage: '',
                currentPage: '',
                total: '',
                from: '',
                to: ''
            },
        };
    },
    async fetch() {
        await this.getProducts()
        await this.zoom1()
    },

    methods: {
        imageUrlAlt(event) {
            event.target.src = this.apiWebUrl + "/image/no_image.jpg"
        },

        async getProducts() {
            const request = await this.$axios.$get(`new/products`, {params: this.query})
            this.products = this.products.concat(request.data.products)
            await this.zoom1()
            this.configPagination(request.data.pagination)
            $('.ajaxblock').remove()
        },

        visibilityChanged(e) {
            let vm = this
            if (vm.query.page !== 1 && vm.pagination.currentPage < vm.pagination.lastPage) {
                $('.product:last').after('<div class="ajaxblock"><img src="/img/loader.gif" /></div>');
                vm.getProducts()
            }
            vm.query.page = vm.query.page + 1
        },

        configPagination(data) {
            this.pagination.lastPage = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.total = data.total;
            this.pagination.from = data.from;
            this.pagination.to = data.to;
        },

        zoom1() {
            if (!this.isMobile) {
                $('.zoomContainer').remove()
                $(".zoom_01").elevateZoom({
                    zoomWindowWidth: 300,
                    zoomWindowHeight: 300,
                    zoomWindowPosition: 11,
                    zoomWindowOffetx: -15,
                    lensSize: 500,
                    cursor: 'pointer',
                });
            }
        },
    }
}
</script>

<style scoped>

</style>
