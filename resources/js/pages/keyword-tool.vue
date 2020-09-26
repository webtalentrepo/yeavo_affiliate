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
                        :headers="headers"
                        :items="desserts"
                        :page.sync="page"
                        :items-per-page="itemsPerPage"
                        hide-default-footer
                        class="elevation-1"
                        :loading="searchStart"
                        loading-text="Loading... Please wait"
                    >
                        <template #[`item.keyword`]="{ item }">
                            <div @click="clickData(item.keyword)">
                                {{ item.keyword }}
                            </div>
                        </template>
                        <template #[`item.trends`]="{ item }">
                            <v-btn
                                light
                                color="white"
                                small
                                @click="showTrendsData(item.keyword)"
                                >Show
                            </v-btn>
                        </template>
                        <template #[`item.broad_impressions`]="{ item }">
                            <div @click="clickData(item.keyword)">
                                {{ item.broad_impressions }}
                            </div>
                        </template>
                        <template #[`item.impressions`]="{ item }">
                            <div @click="clickData(item.keyword)">
                                {{ item.impressions }}
                            </div>
                        </template>
                    </v-data-table>
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
        <v-dialog v-model="dialog" persistent max-width="400" width="400">
            <v-card>
                <v-card-title class="headline">{{ keyword_str }}</v-card-title>
                <v-card-text>
                    <v-row justify="center" align="center">
                        <v-col cols="10">
                            <keyword-trends
                                :chart-data="chart_data"
                            ></keyword-trends>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" text @click="dialog = false"
                        >Close
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-app>
</template>

<script>
import PageHeader from '../layout/users/PageHeader';
import KeywordTrends from '../components/KeywordTrends';

export default {
    name: 'KeywordTool',
    components: { KeywordTrends, PageHeader },
    data: () => ({
        search_str: '',
        searchStart: false,
        page: 1,
        page1: 1,
        pageCount: 0,
        itemsPerPage: 10,
        headers: [
            { text: 'Keywords', value: 'keyword', width: '35%' },
            { text: 'Trends', value: 'trends', align: 'center', width: '25%' },
            {
                text: 'Broad Impressions',
                value: 'broad_impressions',
                align: 'center',
                width: '20%',
            },
            {
                text: 'Impressions',
                value: 'impressions',
                align: 'center',
                width: '20%',
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
                })
                // eslint-disable-next-line no-unused-vars
                .catch((e) => {
                    this.searchStart = false;
                });
        },
        getChartData(item) {
            return {
                labels: item.date,
                datasets: [
                    {
                        label: 'Impressions',
                        backgroundColor: 'rgba(220, 236, 255, 0.8)',
                        data: item.impressions,
                        lineTension: 0.2,
                        pointRadius: 2,
                        pointHoverRadius: 2,
                        borderColor: '#3392FF',
                        borderWidth: 1,
                    },
                ],
            };
        },
        showTrendsData(keyword) {
            this.chart_data = {
                labels: [],
                datasets: [],
            };
            this.dialog = true;

            const params = {
                keyword: keyword,
            };

            this.$http
                .post('/keyword-data-trends', params)
                .then((r) => {
                    this.keyword_str = keyword;
                    this.chart_data = this.getChartData(r.data.result);
                })
                // eslint-disable-next-line no-unused-vars
                .catch((e) => {
                    this.chart_data = {
                        labels: [],
                        datasets: [],
                    };

                    this.dialog = false;
                });
        },
    },
};
</script>

<style lang="scss" src="../../sass/pages/_common.scss"></style>
