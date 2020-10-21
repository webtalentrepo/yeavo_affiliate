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
                        @keyup.enter="searchData"
                        @click:append="searchData"
                    >
                        <template #append>
                            <img
                                src="/assets/icons/search.png"
                                alt=""
                                class="append-icon cursor-pointer"
                                @click="searchData"
                            />
                        </template>
                    </v-text-field>
                </v-col>
            </v-row>

            <v-row v-if="refine_keys && refine_keys.length" justify="center">
                <v-col
                    v-for="(rItem, rKey) in refine_keys"
                    :key="`rKey${rKey}`"
                    cols="12"
                    xl="2"
                    lg="2"
                    md="5"
                    sm="5"
                >
                    <label class="font-weight-black refine-key-label">{{
                        rItem.name
                    }}</label>
                    <div v-if="rItem.value && rItem.value.length">
                        <div
                            v-for="(cRItem, cRI) in rItem.value"
                            :key="`cRItem${cRI}`"
                        >
                            <span>{{ cRItem.name }}: </span>
                            <span>{{ cRItem.value }}</span>
                        </div>
                    </div>
                </v-col>
            </v-row>

            <v-row justify="center">
                <v-col
                    cols="12"
                    md="12"
                    sm="12"
                    lg="11"
                    xl="10"
                    class="content-table"
                >
                    <v-card>
                        <v-card-title>
                            <v-text-field
                                v-model="search"
                                append-icon="mdi-magnify"
                                label="Filter Keyword"
                                single-line
                                hide-details
                            ></v-text-field>
                        </v-card-title>
                        <v-data-table
                            :headers="headers"
                            :items="desserts"
                            :page.sync="page"
                            :items-per-page="itemsPerPage"
                            hide-default-footer
                            class="elevation-1"
                            :loading="searchStart"
                            :search="search"
                            loading-text="Loading... Please wait"
                        >
                            <template #[`item.name`]="{ item }">
                                <div>
                                    <div
                                        v-if="item.index === 0"
                                        class="height-50 refine-key-label"
                                    >
                                        - Keyword you provided
                                    </div>
                                    <div
                                        v-if="item.index === 1"
                                        class="height-50 refine-key-label"
                                    >
                                        - Keyword ideas
                                    </div>
                                    <div
                                        :class="{ 'height-50': item.index < 2 }"
                                    >
                                        {{ item.name }}
                                    </div>
                                </div>
                            </template>
                            <template #[`item.bid_low`]="{ item }">
                                <div v-if="item.bid_low === 'NA'">
                                    {{ item.bid_low }}
                                </div>
                                <div v-else>${{ item.bid_low }}</div>
                            </template>
                            <template #[`item.bid_high`]="{ item }">
                                <div v-if="item.bid_high === 'NA'">
                                    {{ item.bid_high }}
                                </div>
                                <div v-else>${{ item.bid_high }}</div>
                            </template>
                        </v-data-table>
                    </v-card>
                </v-col>
            </v-row>

            <v-row class="mt-10">
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

                <v-col cols="12" md="2">
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

            <v-row justify="center">
                <v-col cols="12" md="12" sm="12" lg="11" xl="10"
                    >Top 10 url ranking on this keyword
                </v-col>
            </v-row>
            <v-row justify="center">
                <v-col
                    cols="12"
                    md="12"
                    sm="12"
                    lg="11"
                    xl="10"
                    class="content-table"
                >
                    <v-data-table
                        :headers="top_headers"
                        :items="desserts1"
                        :page.sync="top_page"
                        :items-per-page="itemsPerPage1"
                        hide-default-footer
                        class="elevation-1"
                    >
                        <template #[`item.url`]="{ item }">
                            <div>{{ item.Name }}</div>
                            <div>
                                <a target="_blank" :href="item.Url">{{
                                    item.Url
                                }}</a>
                            </div>
                        </template>
                        <template #[`item.topics`]="{ item }">
                            <div>{{ item.Topics }}</div>
                        </template>
                    </v-data-table>
                </v-col>
            </v-row>
        </v-container>
    </v-app>
</template>

<script>
import PageHeader from '../layout/users/PageHeader';

export default {
    name: 'KeywordTool',
    components: { PageHeader },
    data: () => ({
        search: '',
        search_str: '',
        searchStart: false,
        page: 1,
        page1: 1,
        pageCount: 0,
        itemsPerPage: 10,
        headers: [
            { text: 'Keyword(by relevance)', value: 'name', width: '35%' },
            {
                text: 'Avg. monthly searches',
                value: 'month_search',
                align: 'right',
                width: '17%',
            },
            {
                text: 'Competition',
                value: 'competition',
                align: 'left',
                width: '12%',
            },
            {
                text: 'Top of page bid(low range)',
                value: 'bid_low',
                align: 'right',
                width: '12%',
            },
            {
                text: 'Top of page bid(high range)',
                value: 'bid_high',
                align: 'right',
                width: '12%',
            },
            {
                text: 'Competition(indexed value)',
                value: 'competition_index',
                align: 'left',
                width: '12%',
            },
        ],
        desserts: [],
        dialog: false,
        keyword_str: '',
        chart_data: {},
        top_page: 1,
        pageCount1: 0,
        itemsPerPage1: 10,
        top_headers: [
            { text: 'Title & URL', value: 'url', width: '55%' },
            { text: 'Topics', value: 'topics', align: 'right', width: '45%' },
        ],
        desserts1: [],
        refine_keys: [],
    }),
    methods: {
        clickData(query) {
            this.search_str = query;

            this.desserts = [];
            this.desserts1 = [];

            this.searchData();
        },
        searchData() {
            this.searchStart = true;
            const params = {
                search_str: this.search_str,
            };

            this.$http
                .post('/keyword-data', params)
                .then((r) => {
                    this.desserts = r.data.result;
                    this.desserts1 = r.data.rank.TopUrls;
                    this.pageCount = Math.ceil(
                        r.data.pageCount / this.itemsPerPage,
                    );
                    this.searchStart = false;
                    const re_keys = r.data.re_keys;
                    if (re_keys) {
                        re_keys.map((el, key) => {
                            this.refine_keys[key] = { name: '', value: [] };
                            Object.keys(el).map((cKey) => {
                                if (parseInt(cKey) === 1) {
                                    this.refine_keys[key].name = el[cKey];
                                } else {
                                    if (parseInt(cKey) !== 4) {
                                        el[cKey].map((eEl) => {
                                            Object.keys(eEl).map((sKey) => {
                                                if (parseInt(sKey) === 1) {
                                                    this.refine_keys[
                                                        key
                                                    ].value.push({
                                                        name: eEl[sKey],
                                                        value: eEl[2],
                                                    });
                                                }
                                            });
                                        });
                                    }
                                }

                                return cKey;
                            });

                            return el;
                        });
                    }
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
