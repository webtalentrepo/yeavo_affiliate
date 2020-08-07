import 'es6-promise/auto'
import Vue from 'vue'
import Vuex from 'vuex'
import state from './state';
import mutations from './mutations'
import getters from './getters'

Vue.use(Vuex);

//=======vuex store start===========
const store = new Vuex.Store({
    state: state,
    getters,
    mutations
});

//=======vuex store end===========
export default store
