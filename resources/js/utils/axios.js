/**
 * Created by erwinflaming on 14/08/17.
 */
import Vue from 'vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import store from '../store/store';

axios.interceptors.request.use(
    (requestConfig) => {
        //  Generate cancel token source
        let source = axios.CancelToken.source();

        // Set cancel token on this request
        requestConfig.cancelToken = source.token;

        // Add to vuex to make cancellation available from anywhere
        store.commit('ADD_CANCEL_TOKEN', source);

        if (store.getters.isAuthenticated) {
            requestConfig.headers.Authorization = `Bearer ${store.state.accessToken}`;
        }

        return requestConfig;
    },
    (requestError) => Promise.reject(requestError),
);

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status) {
            if (
                error.response.status === 401 &&
                error.config.url.indexOf('/api/login') < 0
            ) {
                store.commit('destroyAccessToken');

                window.location.replace(`${window.location.origin}/login`);
            }
        }

        return Promise.reject(error);
    },
);

Vue.use(VueAxios, axios);

Vue.axios.defaults.baseURL = `${window.location.origin}/api`;
Vue.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export default axios;
