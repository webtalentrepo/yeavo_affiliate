import '@mdi/font/css/materialdesignicons.css'
import Vue from 'vue'
import Vuetify, {
    VLayout, VContainer, VRow, VCol, VIcon, VTextField, VSelect, VProgressLinear, VDataTable, VPagination
} from 'vuetify/lib'

Vue.use(Vuetify, {
    components: {
        VLayout, VContainer, VRow, VCol, VIcon, VTextField, VSelect, VProgressLinear, VDataTable, VPagination
    },
    iconfont: 'mdi'
})

export default new Vuetify({})
