<template>
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
            <v-col cols="12" md="3" sm="12">
                <div>Sale $Amount</div>
                <div class="d-flex align-center">
                    <v-text-field
                        v-model="sale_min"
                        label="Min"
                        type="number"
                        clearable
                        :disabled="disableMin[sel_network]"
                    ></v-text-field>
                    <div class="pl-2 pr-2">-</div>
                    <v-text-field
                        v-model="sale_max"
                        label="Max"
                        type="number"
                        :disabled="disableMin[sel_network]"
                        clearable
                    ></v-text-field>
                </div>
            </v-col>

            <v-col cols="12" md="3" sm="12">
                <div>Popularity</div>
                <div class="d-flex align-center">
                    <v-text-field
                        v-model="pop_min"
                        label="Min"
                        type="number"
                        :disabled="disableMin[sel_network]"
                        clearable
                    ></v-text-field>
                    <div class="pl-2 pr-2">-</div>
                    <v-text-field
                        v-model="pop_max"
                        label="Max"
                        type="number"
                        :disabled="disableMin[sel_network]"
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

        <!--        <v-row v-if="searchStart">-->
        <!--            <v-col cols="12">-->
        <!--                <v-progress-linear-->
        <!--                    color="teal accent-4"-->
        <!--                    indeterminate-->
        <!--                    height="3"-->
        <!--                ></v-progress-linear>-->
        <!--            </v-col>-->
        <!--        </v-row>-->

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
                    <template v-slot:item.sign_up="{ item }">
                        <a :href="item.sign_up" target="_blank">Sign Up</a>
                    </template>
                </v-data-table>
                <v-row>
                    <v-col cols="12">
                        <v-pagination
                            v-model="page"
                            :length="pageCount"
                            :total-visible="10"
                            @input="getSalesData"
                        ></v-pagination>
                    </v-col>

                    <!--                        <v-col cols="12" md="2">-->
                    <!--                            <v-text-field-->
                    <!--                                :value="itemsPerPage"-->
                    <!--                                label="Items per page"-->
                    <!--                                type="number"-->
                    <!--                                min="-1"-->
                    <!--                                max="15"-->
                    <!--                                @input="itemsPerPage = parseInt($event, 10)"-->
                    <!--                            ></v-text-field>-->
                    <!--                        </v-col>-->
                </v-row>
            </v-col>
        </v-row>
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
                sel_network: 'cj.com',
                searchStart: false,
                disableMin: {
                    'clickbank.com': true,
                    'cj.com': true,
                    'Rakuten Linkshare': false,
                    'maxbounty.com': false,
                    'jvzoo.com': false,
                    'shareasale.com': false,
                },
                page: 1,
                pageCount: 0,
                itemsPerPage: 10,
                headers: [
                    {text: 'Offer Name', value: 'name'},
                    {text: '$ Sale', value: 'sale'},
                    {text: 'Popularity(Is it selling well)', value: 'popularity'},
                    {text: 'Network', value: 'network', sortable: false},
                    {text: 'Sign Up', value: 'sign_up', sortable: false},
                ],
                desserts: []
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
