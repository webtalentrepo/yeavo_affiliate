import Vue from 'vue';
import VueRouter from 'vue-router';
import routes from './routes.js';
import store from '../store/store';

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    routes,
    linkActiveClass: 'active',
});

/**
 * Authentication check and return to login page before route change.
 */
router.beforeEach((to, from, next) => {
    if (!store.getters.isAuthenticated) {
        if (to.meta.auth) {
            next({
                name: 'Login',
            });
        } else {
            next();
        }
    } else {
        next();
    }
});

//====change page title after route changed.
// eslint-disable-next-line no-unused-vars
router.afterEach((to, from) => {
    if (to.meta.title) {
        document.title = to.meta.title + ' - ' + store.state.siteName;

        store.commit('changePageTitle', to.meta.title);
    }
});

export default router;
