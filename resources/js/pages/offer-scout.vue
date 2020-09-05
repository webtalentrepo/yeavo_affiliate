<template>
    <v-app>
        <v-container>
            <PageHeader></PageHeader>

            <v-row justify="center" class="search-box-row">
                <v-col cols="8">
                    <v-text-field
                        v-model="search_str"
                        solo
                        label="Search"
                        append-icon="mdi-search-web"
                        @keyup.enter="searchData"
                        @click:append="searchData"
                    ></v-text-field>
                </v-col>
            </v-row>

            <v-row justify="center" align="center">
                <v-col
                    v-if="!disableMin[sel_network][0]"
                    cols="8"
                    md="8"
                    sm="10"
                    xs="12"
                >
                    <v-row justify="center" align="center" class="search-row">
                        <v-col
                            v-if="!disableMin[sel_network][0]"
                            cols="3"
                            md="3"
                            sm="12"
                        >
                            <div class="text-center mb-4">Sale $Amount</div>
                            <div class="d-flex align-center">
                                <v-text-field
                                    v-model="sale_min"
                                    label="Min"
                                    type="number"
                                    solo
                                    dense
                                    :disabled="disableMin[sel_network][0]"
                                    @keyup.enter="searchData"
                                ></v-text-field>
                                <div class="pl-4 pr-4">-</div>
                                <v-text-field
                                    v-model="sale_max"
                                    label="Max"
                                    type="number"
                                    :disabled="disableMin[sel_network][0]"
                                    solo
                                    dense
                                    @keyup.enter="searchData"
                                ></v-text-field>
                            </div>
                        </v-col>

                        <v-col
                            v-if="!disableMin[sel_network][1]"
                            cols="3"
                            md="3"
                            sm="12"
                        >
                            <div
                                v-if="sel_network !== 'shareasale.com'"
                                class="text-center mb-4"
                            >
                                Popularity
                            </div>
                            <div
                                v-if="sel_network === 'shareasale.com'"
                                class="text-center mb-4"
                            >
                                Powerrank Top 100
                            </div>
                            <div
                                v-if="sel_network !== 'shareasale.com'"
                                class="d-flex align-center"
                            >
                                <v-text-field
                                    v-model="pop_min"
                                    label="Min"
                                    type="number"
                                    :disabled="disableMin[sel_network][1]"
                                    solo
                                    dense
                                    @keyup.enter="searchData"
                                ></v-text-field>
                                <div class="pl-4 pr-4">-</div>
                                <v-text-field
                                    v-model="pop_max"
                                    label="Max"
                                    type="number"
                                    :disabled="disableMin[sel_network][1]"
                                    solo
                                    dense
                                    @keyup.enter="searchData"
                                ></v-text-field>
                            </div>
                            <div
                                v-if="sel_network === 'shareasale.com'"
                                class="d-flex align-center"
                            >
                                <v-select
                                    v-model="pop_max"
                                    solo
                                    dense
                                    :items="['', 'Yes', 'No']"
                                    @change="getSalesData()"
                                ></v-select>
                            </div>
                        </v-col>

                        <v-col cols="4" md="4" sm="12">
                            <div class="text-center mb-4">Network</div>
                            <v-select
                                v-model="sel_network"
                                :items="scout_network"
                                solo
                                dense
                                @change="getSalesData()"
                            ></v-select>
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>

            <v-row justify="center">
                <v-col cols="10">
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
                        <template #[`item.name`]="{ item }">
                            <div
                                class="py-2 cursor-pointer"
                                @click="showDialog(item.network, item.id)"
                            >
                                <div class="text--primary text-sm-body-1">
                                    {{ item.name }}
                                </div>
                                <div class="text--secondary text-sm-caption">
                                    {{ item.full_category }}
                                </div>
                                <div
                                    class="mt-2 text--secondary text-sm-caption"
                                >
                                    {{ item.p_description }}
                                </div>
                            </div>
                        </template>
                        <template #[`item.sale`]="{ item }">
                            <div
                                class="py-2 cursor-pointer"
                                @click="showDialog(item.network, item.id)"
                            >
                                <SaleCJComponent
                                    v-if="item.network === 'cj.com'"
                                    :item="item"
                                />

                                <SaleCBComponent
                                    v-if="item.network === 'clickbank.com'"
                                    :item="item"
                                />

                                <SaleSSComponent
                                    v-if="item.network === 'shareasale.com'"
                                    :item="item"
                                />
                            </div>
                        </template>
                        <template #[`item.popularity`]="{ item }">
                            <div
                                class="py-2 cursor-pointer"
                                @click="showDialog(item.network, item.id)"
                            >
                                <span v-if="item.network !== 'shareasale.com'">
                                    {{ item.popularity }}
                                </span>
                                <span v-if="item.network === 'shareasale.com'">
                                    Powerrank TOP 100:
                                    {{ item.popularity ? 'Yes' : 'No' }}<br />
                                    Average Commission: ${{ item.popular_rank
                                    }}<br />
                                    Reversal Rate: {{ item.child_category }}%
                                </span>
                            </div>
                        </template>
                        <template #[`item.network`]="{ item }">
                            <div
                                class="py-2 cursor-pointer"
                                @click="showDialog(item.network, item.id)"
                            >
                                {{ item.network }}
                            </div>
                        </template>
                        <template #[`item.sign_up`]="{ item }">
                            <a
                                v-if="!disableMin[sel_network][2]"
                                :href="item.sign_up"
                                target="_blank"
                                >Sign Up</a
                            >
                        </template>
                    </v-data-table>
                    <v-row>
                        <v-col cols="10" md="8">
                            <v-pagination
                                v-model="page"
                                :length="pageCount"
                                :total-visible="10"
                                circle
                                dark
                                color="white"
                            ></v-pagination>
                        </v-col>

                        <v-col cols="10" md="2">
                            <v-text-field
                                v-model="page1"
                                type="number"
                                label="Go to Page"
                                width="60px"
                                min="1"
                                outlined
                                dense
                                @input="page = parseInt(page1)"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>
        </v-container>
        <v-container class="v-overlay--absolute">
            <v-dialog
                v-model="cj_dialog"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
            >
                <v-card>
                    <v-toolbar dark color="primary">
                        <v-btn icon dark @click="cj_dialog = false">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                        <v-toolbar-title>Products</v-toolbar-title>
                    </v-toolbar>
                    <v-row class="overflow-auto" justify="center">
                        <v-col cols="10" class="overflow-auto">
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
                                <template #[`item.name`]="{ item }">
                                    <div class="py-1">
                                        <div
                                            class="text--primary text-sm-body-1"
                                        >
                                            {{ item.title }}
                                        </div>
                                        <div
                                            class="text--secondary text-sm-caption"
                                        >
                                            {{ item.brand }}
                                        </div>
                                        <div
                                            class="mt-2 text--secondary text-sm-caption"
                                        >
                                            {{ item.description }}
                                        </div>
                                    </div>
                                </template>
                                <template #[`item.sale`]="{ item }">
                                    <div class="py-1">
                                        <div>
                                            Price: {{ item.p_amount }}
                                            {{ item.p_currency }}
                                        </div>
                                        <div class="mt-2">
                                            Sale: {{ item.s_amount }}
                                            {{ item.s_currency }}
                                        </div>
                                    </div>
                                </template>
                                <template #[`item.sign_up`]="{ item }">
                                    <a :href="item.sign_up" target="_blank"
                                        >Sign Up</a
                                    >
                                </template>
                            </v-data-table>
                            <v-row>
                                <v-col cols="12">
                                    <v-pagination
                                        v-model="c_page"
                                        :length="c_pageCount"
                                        :total-visible="10"
                                        circle
                                        color="grey darken-3"
                                    ></v-pagination>
                                    <!--                            @input="getSalesData"-->
                                </v-col>
                            </v-row>
                        </v-col>
                    </v-row>
                </v-card>
            </v-dialog>
        </v-container>
    </v-app>
</template>

<script>
import { mapGetters } from 'vuex';
import SaleCJComponent from '../components/SaleCJComponent';
import SaleCBComponent from '../components/SaleCBComponent';
import SaleSSComponent from '../components/SaleSSComponent';
import PageHeader from '../layout/users/PageHeader';

export default {
    name: 'OfferScout',
    components: {
        PageHeader,
        SaleSSComponent,
        SaleCBComponent,
        SaleCJComponent,
    },
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
                'All Networks': [false, false, false],
                'clickbank.com': [false, false, false],
                'cj.com': [false, false, false],
                'Rakuten Linkshare': [false, false, false],
                'maxbounty.com': [false, false, false],
                'jvzoo.com': [false, false, false],
                'shareasale.com': [false, false, false],
            },
            page: 1,
            page1: 1,
            pageCount: 0,
            itemsPerPage: 25,
            headers: [
                { text: 'Offer Name', value: 'name', width: '35%' },
                { text: '$ Sale', value: 'sale', width: '23%' },
                {
                    text: 'Popularity(Is it selling well)',
                    value: 'popularity',
                    align: 'center',
                    width: '18%',
                },
                {
                    text: 'Network',
                    value: 'network',
                    sortable: false,
                    width: '12%',
                },
                {
                    text: 'Sign Up',
                    value: 'sign_up',
                    sortable: false,
                    width: '12%',
                },
            ],
            desserts: [],
            cj_dialog: false,
            c_headers: [
                { text: 'Product', value: 'name', width: '35%' },
                { text: '$ Sale', value: 'sale', width: '25%' },
                {
                    text: 'Network',
                    value: 'network',
                    sortable: false,
                    width: '20%',
                },
                {
                    text: 'Sign Up',
                    value: 'sign_up',
                    sortable: false,
                    width: '20%',
                },
            ],
            c_page: 1,
            c_pageCount: 0,
            cItemsPerPage: 25,
            cSearchStart: false,
            c_deserts: [],
        };
    },
    // ===Computed properties for the component
    computed: {
        ...mapGetters({
            scout_network: 'getNetworkList',
        }),
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
            };

            this.$http
                .post('/child-data', params)
                .then((r) => {
                    if (r.data.result === 'success') {
                        // console.log(r)
                        this.c_deserts = r.data.rows;
                        this.c_pageCount = r.data.pageCount;
                    }

                    this.cSearchStart = false;
                })
                // eslint-disable-next-line no-unused-vars
                .catch(() => {
                    this.cSearchStart = false;
                });
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
            };

            this.$http
                .post('/scout-data', params)
                .then((r) => {
                    if (r.data.result === 'success') {
                        // console.log(r)
                        this.desserts = r.data.rows;
                        this.pageCount = r.data.pageCount;
                    }
                    this.searchStart = false;
                })
                // eslint-disable-next-line no-unused-vars
                .catch((e) => {
                    this.searchStart = false;
                });
        },
    },
};
</script>
<style lang="scss" src="../../sass/pages/_common.scss"></style>
