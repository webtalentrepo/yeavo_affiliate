// eslint-disable-next-line no-undef
const mix = require('laravel-mix');
// eslint-disable-next-line no-undef
require('vuetifyjs-mix-extension');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

const options = {
    progressiveImages: true,
};

mix.js('resources/js/app.js', 'public/js')
    .vuetify('vuetify-loader', options)
    .sass('resources/sass/app.scss', 'public/css')
    .extract([
        'vue',
        'axios',
        'vue-axios',
        'jquery',
        'vuex',
        'vue-router',
        'bootstrap',
    ]);
