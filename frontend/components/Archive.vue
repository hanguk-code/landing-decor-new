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
                                <i class="prod prod-preview"></i>
                                <img :src="apiWebUrl+'/image/'+product.image_url" alt="" class="zoom_03"
                                     :data-image="apiWebUrl+'/image/'+product.image_url"
                                     :data-zoom-image="apiWebUrl+'/image/'+product.image_url">
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
                            <n-link :to="product.url" v-html="product.name"></n-link>
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
                url: '/archive',
                title: 'Архив'
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
        await this.zoom3()
    },

    methods: {
        async getProducts() {
            const request = await this.$axios.$get(`/archive`, {params: this.query})
            this.products = this.products.concat(request.data.products)
            await this.zoom3()
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

        zoom3() {
            if (!this.isMobile) {
                $('.zoomContainer').remove()
                $(".zoom_03").elevateZoom({
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
    i.prod.prod-preview {
        display: block;
        left: 135px;
        top: 130px;
    }
</style>
