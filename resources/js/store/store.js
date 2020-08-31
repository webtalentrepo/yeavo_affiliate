import 'es6-promise/auto';
import Vue from 'vue';
import Vuex from 'vuex';
import state from './state';
import mutations from './mutations';
import getters from './getters';
import actions from './actions';

Vue.use(Vuex);

//=======vuex store start===========
const store = new Vuex.Store({
    state: state,
    getters,
    mutations,
    actions,
});

//=======vuex store end===========
export default store;
