<template>

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
                breadcrumbs: [{
                    url: '/articles',
                    title: 'Статьи'
                }],

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

</style>