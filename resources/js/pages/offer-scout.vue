<template>
    <v-container>
        <v-container>
            <v-row>
                <v-col cols="12">
                    <v-text-field
                        v-model="search_str"
                        filled
                        label="Enter keyword, or leave blank if you don't have a keyword yet."
                        append-icon="mdi-search-web"
                        @keyup.enter="searchData"
                        @click:append="searchData"
                    ></v-text-field>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12" md="3" sm="12" v-if="!disableMin[sel_network]">
                    <div>Sale $Amount</div>
                    <div class="d-flex align-center">
                        <v-text-field
                            v-model="sale_min"
                            label="Min"
                            type="number"
                            clearable
                            :disabled="disableMin[sel_network]"
                            @keyup.enter="searchData"
                        ></v-text-field>
                        <div class="pl-2 pr-2">-</div>
                        <v-text-field
                            v-model="sale_max"
                            label="Max"
                            type="number"
                            :disabled="disableMin[sel_network]"
                            @keyup.enter="searchData"
                            clearable
                        ></v-text-field>
                    </div>
                </v-col>

                <v-col cols="12" md="3" sm="12" v-if="!disableMin[sel_network]">
                    <div>Popularity</div>
                    <div class="d-flex align-center">
                        <v-text-field
                            v-model="pop_min"
                            label="Min"
                            type="number"
                            :disabled="disableMin[sel_network]"
                            @keyup.enter="searchData"
                            clearable
                        ></v-text-field>
                        <div class="pl-2 pr-2">-</div>
                        <v-text-field
                            v-model="pop_max"
                            label="Max"
                            type="number"
                            :disabled="disableMin[sel_network]"
                            @keyup.enter="searchData"
                            clearable
                        ></v-text-field>
                    </div>
                </v-col>

                <v-col cols="12" md="4" sm="12">
                    <div>Network</div>
                    <v-select
                        v-model="sel_network"
                        :items="scout_network"
                        @change="getSalesData()"
                    ></v-select>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12">
                    <v-data-table
                        :headers="headers"
                        :items="desserts"
                        :page.sync="page"
                        :items-per-page="itemsPerPage"
                        hide-default-footer
                        class="elevation-1"
                        :loading="searchStart"
                        loading-text="Loading... Please wait"
                    >
                        <template v-slot:item.name="{ item }">
                            <div class="py-2 cursor-pointer" @click="showDialog(item.network, item.id)">
                                <div class="text--primary text-sm-body-1">
                                    {{ item.name }}
                                </div>
                                <div class="text--secondary text-sm-caption">
                                    {{ item.full_category }}
                                </div>
                                <div class="mt-2 text--secondary text-sm-caption">
                                    {{ item.p_description }}
                                </div>
                            </div>
                        </template>
                        <template v-slot:item.sale="{ item }">
                            <div class="py-2 cursor-pointer" @click="showDialog(item.network, item.id)">
                                <div v-if="item.network === 'cj.com'">
                                    3 month EPC: {{ item.three_month_epc }}
                                </div>
                                <div v-if="item.network === 'cj.com'">
                                    7 day EPC: {{ item.seven_day_epc }}
                                </div>
                                <div v-if="item.network === 'clickbank.com'">
                                    Initial $/Sale: ${{ item.three_month_epc }}
                                </div>
                                <div v-if="item.network === 'clickbank.com'">
                                    Avg $/Sale: ${{ item.seven_day_epc }}
                                </div>
                                <div class="mt-2">
                                    <span v-if="item.network === 'clickbank.com'">Avg %/</span>Sale: {{ (item.seven_day_epc === 0 ? 0 : item.sale) }}
                                </div>
                            </div>
                        </template>
                        <template v-slot:item.popularity="{ item }">
                            <div class="py-2 cursor-pointer" @click="showDialog(item.network, item.id)">
                                {{ item.popularity }}
                            </div>
                        </template>
                        <template v-slot:item.network="{ item }">
                            <div class="py-2 cursor-pointer" @click="showDialog(item.network, item.id)">
                                {{ item.network }}
                            </div>
                        </template>
                        <template v-slot:item.sign_up="{ item }">
                            <a v-if="!disableMin[sel_network]" :href="item.sign_up" target="_blank">Sign Up</a>
                        </template>
                    </v-data-table>
                    <v-row>
                        <v-col cols="12">
                            <v-pagination
                                v-model="page"
                                :length="pageCount"
                                :total-visible="10"
                            ></v-pagination>
                            <!--                            @input="getSalesData"-->
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>
        </v-container>
        <v-container class="v-overlay--absolute">
            <v-dialog v-model="cj_dialog" fullscreen hide-overlay transition="dialog-bottom-transition">
                <v-card>
                    <v-toolbar dark color="primary">
                        <v-btn icon dark @click="cj_dialog = false">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                        <v-toolbar-title>Products</v-toolbar-title>
                    </v-toolbar>
                    <v-row class="overflow-auto">
                        <v-col cols="12" class="overflow-auto">
                            <v-data-table
                                :headers="c_headers"
                                :items="c_deserts"
                                :page.sync="c_page"
                                :items-per-page="cItemsPerPage"
                                hide-default-footer
                                class="elevation-1"
                                :loading="cSearchStart"
                                loading-text="Loading... Please wait"
                            >
                                <template v-slot:item.name="{ item }">
                                    <div class="py-1">
                                        <div class="text--primary text-sm-body-1">
                                            {{ item.title }}
                                        </div>
                                        <div class="text--secondary text-sm-caption">
                                            {{ item.brand }}
                                        </div>
                                        <div class="mt-2 text--secondary text-sm-caption">
                                            {{ item.description }}
                                        </div>
                                    </div>
                                </template>
                                <template v-slot:item.sale="{ item }">
                                    <div class="py-1">
                                        <div>
                                            Price: {{ item.p_amount }} {{ item.p_currency }}
                                        </div>
                                        <div class="mt-2">
                                            Sale: {{ item.s_amount }} {{ item.s_currency }}
                                        </div>
                                    </div>
                                </template>
                                <template v-slot:item.sign_up="{ item }">
                                    <a :href="item.sign_up" target="_blank">Sign Up</a>
                                </template>
                            </v-data-table>
                            <v-row>
                                <v-col cols="12">
                                    <v-pagination
                                        v-model="c_page"
                                        :length="c_pageCount"
                                        :total-visible="10"
                                    ></v-pagination>
                                    <!--                            @input="getSalesData"-->
                                </v-col>
                            </v-row>
                        </v-col>
                    </v-row>
                </v-card>
            </v-dialog>
        </v-container>
    </v-container>
</template>

<script>
    import {mapGetters} from 'vuex'

    export default {
        name: "offer-scout",
        data() {
            return {
                search_str: '',
                sale_min: '',
                sale_max: '',
                pop_min: '',
                pop_max: '',
                sel_network: 'All Networks',
                searchStart: false,
                disableMin: {
                    'clickbank.com': false,
                    'cj.com': false,
                    'Rakuten Linkshare': false,
                    'maxbounty.com': false,
                    'jvzoo.com': false,
                    'shareasale.com': false,
                },
                page: 1,
                pageCount: 0,
                itemsPerPage: 25,
                headers: [
                    {text: 'Offer Name', value: 'name', width: '35%'},
                    {text: '$ Sale', value: 'sale', width: '25%'},
                    {text: 'Popularity(Is it selling well)', value: 'popularity', align: 'center', width: '15%'},
                    {text: 'Network', value: 'network', sortable: false, width: '12%'},
                    {text: 'Sign Up', value: 'sign_up', sortable: false, width: '12%'},
                ],
                desserts: [],
                cj_dialog: false,
                c_headers: [
                    {text: 'Product', value: 'name', width: '35%'},
                    {text: '$ Sale', value: 'sale', width: '25%'},
                    {text: 'Network', value: 'network', sortable: false, width: '20%'},
                    {text: 'Sign Up', value: 'sign_up', sortable: false, width: '20%'},
                ],
                c_page: 1,
                c_pageCount: 0,
                cItemsPerPage: 25,
                cSearchStart: false,
                c_deserts: []
            }
        },
        // ===Computed properties for the component
        computed: {
            ...mapGetters({
                scout_network: 'getNetworkList',
            })
        },
        mounted() {
            this.getSalesData();
        },
        methods: {
            showDialog(network, id) {
                if (network === 'cj.com') {
                    this.cj_dialog = true;

                    this.getChildData(id);
                }
            },
            getChildData(id) {
                this.page = 1;
                this.cSearchStart = true;

                const params = {
                    search_str: this.search_str,
                    sale_min: this.sale_min,
                    sale_max: this.sale_max,
                    limit: this.cItemsPerPage,
                    parent_id: id,
                }

                this.$http.post('/api/child-data', params).then((r) => {
                    if (r.data.result === 'success') {
                        // console.log(r)
                        this.c_deserts = r.data.rows
                        this.c_pageCount = r.data.pageCount
                    }

                    this.cSearchStart = false;
                }).catch((e) => {
                    this.cSearchStart = false;
                })
            },
            searchData() {
                this.page = 1;
                this.getSalesData();
            },
            getSalesData() {
                this.searchStart = true;
                const params = {
                    search_str: this.search_str,
                    sel_network: this.sel_network,
                    sale_min: this.sale_min,
                    sale_max: this.sale_max,
                    popular_min: this.pop_min,
                    popular_max: this.pop_max,
                    page: this.page,
                    limit: this.itemsPerPage,
                }

                this.$http.post('/api/scout-data', params).then((r) => {
                    if (r.data.result === 'success') {
                        // console.log(r)
                        this.desserts = r.data.rows
                        this.pageCount = r.data.pageCount
                    }
                    this.searchStart = false;
                }).catch((e) => {
                    this.searchStart = false;
                })
            }
        }
    }
</script>
<style lang="scss">
    .v-dialog.v-dialog--fullscreen {
        position: fixed;
        width: 100%;
        top: 0;
        left: 0;
        height: 100%;
    }

    .v-dialog.v-dialog--fullscreen .v-card {
        width: 100%;
        height: 100vh;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .v-toolbar.primary .v-toolbar__content {
        display: flex;
        align-items: center;
    }

    .v-dialog.v-dialog--fullscreen .v-data-table__wrapper {
        height: 78vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>
