/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.Vue = require('vue');

import vuetify from './vuetify'
import axios from 'axios'
import VueAxios from 'vue-axios'
import router from './router';
import store from './store/store.js';
import App from './App.vue'
import VEvent from "./utils/VEvent";
import Ls from "./utils/Ls";
import BCookie from "./utils/BCookie";

Vue.use(VueAxios, axios);
/**
 * Vue Event Bus.
 * @type {VEvent}
 */
window.vEvent = new VEvent();

/**
 * Window Localstorage
 * @type {{set, get, remove}}
 */
window.Ls = Ls;

/**
 * Cookie
 * @type {{set, get, check, remove}}
 */
window.BCookie = BCookie;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = new Vue({
    el: '#app',
    router,
    store,
    axios,
    vuetify,
    render: h => h(App),
});
