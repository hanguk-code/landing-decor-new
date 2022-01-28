export default {
    ssr: false,
    srcDir: __dirname,
    target: 'server',

    env: {
        appUrl: process.env.APP_URL,
        apiUrl: process.env.API_URL,
        apiWebUrl: process.env.API_WEB_URL,
        appName: process.env.APP_NAME,
        appLocale: process.env.APP_LOCALE
    },
    server: {
        port: process.env.NUXT_PORT || 3000, // default: 3000
        // host: '0.0.0.0', // default: localhost
        host: '127.0.0.1' // default: localhost
    },
    /*
    ** Headers of the page
    */
    head: {
        //title: process.env.npm_package_name || '',
        meta: [
            {charset: 'utf-8'},
            {name: 'viewport', content: 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'},
            //{ hid: 'description', name: 'description', content: process.env.npm_package_description || '' }
        ],
        link: [
            {rel: 'icon', type: 'image/x-icon', href: '/favicon.ico'},
            {
                rel: 'stylesheet',
                href: 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&amp;display=swap'
            },
            {
                rel: 'stylesheet',
                href: '/css/jquery.fancybox.min.css'
            },
            {
                rel: 'stylesheet',
                href: '/css/bootstrap.min.css'
            },
            {
                rel: 'stylesheet',
                href: '/css/swiper-bundle.min.css'
            },
            {
                rel: 'stylesheet',
                href: '/css/main.css'
            },
            {
                rel: 'stylesheet',
                href: '/css/media.css'
            }
        ],
        script: [
            {
                src: '/js/jquery-3.5.1.min.js'
            },
            {
                src: '/js/bootstrap.min.js'
            },
            {
                src: '/js/jquery.elevatezoom.js'
            },
            {
                src: '/js/jquery.fancybox.min.js'
            },
            {
                src: '/js/swiper-bundle.min.js'
            },
            {
                src: '/js/index.js'
            }
        ]
    },
    /*
    ** Customize the progress-bar color
    */
    loading: false, //{ color: '#fff' },
    /*
    ** Global CSS
    */
    css: [
        //'element-ui/lib/theme-chalk/index.css'
        // 'bootstrap-slider/dist/css/bootstrap-slider.css'
    ],
    router: {
        //trailingSlash: true,
        // middleware: ['check-auth']
    },
    /*
    ** Plugins to load before mounting the App
    */
    plugins: [
        '~/plugins/element-ui',
        //'~plugins/i18n',
        //'~plugins/axios',
        '~/plugins/vue-filter-string',
        '~/plugins/vue-observe-visibility.client.js',
        //'~plugins/nuxt-client-init', // Comment this for SSR
        //{ src: '~plugins/vue-star-rating', ssr: false },
        //{ src: '~plugins/vue-image-crop-upload', ssr: false },
        {src: '~plugins/persistedstate.js'}, //vuex save after page reload in ssr mode
        {src: '~/plugins/vue-notifications.js', ssr: false},
    ],
    components: true,
    /*
    ** Nuxt.js dev-modules
    */
    buildModules: [
        '@nuxtjs/moment',
    ],
    /*
    ** Nuxt.js modules
    */
    modules: [
        // Doc: https://axios.nuxtjs.org/usage
        '@nuxtjs/axios',
        'nuxt-bootstrap-slider',
        //'cookie-universal-nuxt',
    ],
    /*
    ** Axios module configuration
    ** See https://axios.nuxtjs.org/options
    */
    axios: {
	baseURL: process.env.API_URL
        //baseURL: 'http://139.162.135.193:81/api',
        // retry: { retries: 2 },
    },
    /*
    ** Build configuration
    */
    build: {
        generate: {
            done(generator) {
                // Copy dist files to public/_nuxt
                if (generator.nuxt.options.dev === false && generator.nuxt.options.mode === 'spa') {
                    const publicDir = join(generator.nuxt.options.rootDir, 'public', '_nuxt')
                    removeSync(publicDir) //Clear content from previous builds
                    copySync(join(generator.nuxt.options.generate.dir, '_nuxt'), publicDir)
                    copySync(join(generator.nuxt.options.generate.dir, '200.html'), join(publicDir, 'index.html'))
                    removeSync(generator.nuxt.options.generate.dir) //Delete 'Dist' folder from Laravel root
                }
            }
        },
        /*
        ** You can extend webpack config here
        */
        extend(config, ctx) {
        }
    }
}
