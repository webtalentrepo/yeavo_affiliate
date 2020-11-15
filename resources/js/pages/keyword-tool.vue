<template>
    <v-app>
        <v-container>
            <PageHeader></PageHeader>

            <v-row justify="center" class="search-box-row">
                <v-col cols="8">
                    <v-combobox
                        v-model="search_str"
                        :items="questionItems"
                        value="search_str"
                        label="Search"
                        solo
                        hide-no-data
                        hide-selected
                        @keyup.enter="clickData"
                        @click:append="clickData"
                    >
                        <template #append>
                            <img
                                src="/assets/icons/search.png"
                                alt=""
                                class="append-icon cursor-pointer"
                                @click="clickData"
                            />
                        </template>
                    </v-combobox>
                </v-col>
            </v-row>
            <v-row justify="center" align="center">
                <v-col cols="12" md="11" sm="12" lg="11" xl="11">
                    <v-radio-group v-model="checked_type" row>
                        <v-radio
                            label="Exact Match"
                            value="exact"
                            @click="clickData"
                        ></v-radio>
                        <v-radio
                            label="Non-Questions"
                            value="non"
                            @click="clickData"
                        ></v-radio>
                        <v-radio
                            label="Broad Match"
                            value="broad"
                            @click="clickData"
                        ></v-radio>
                    </v-radio-group>
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
                            <template #[`item.trend`]="{ item }">
                                <div>
                                    <keyword-trends
                                        :chart-data="getChartData(item.trends)"
                                    ></keyword-trends>
                                </div>
                            </template>
                            <template #[`item.month_search`]="{ item }">
                                <div>
                                    {{ item.month }}
                                </div>
                            </template>
                            <!--                            <template #[`item.bid_low`]="{ item }">-->
                            <!--                                <div v-if="item.bid_low === 'NA'">-->
                            <!--                                    {{ item.bid_low }}-->
                            <!--                                </div>-->
                            <!--                                <div v-else>${{ item.bid_low }}</div>-->
                            <!--                            </template>-->
                            <!--                            <template #[`item.bid_high`]="{ item }">-->
                            <!--                                <div v-if="item.bid_high === 'NA'">-->
                            <!--                                    {{ item.bid_high }}-->
                            <!--                                </div>-->
                            <!--                                <div v-else>${{ item.bid_high }}</div>-->
                            <!--                            </template>-->
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
import KeywordTrends from '../components/KeywordTrends';

export default {
    name: 'KeywordTool',
    components: { KeywordTrends, PageHeader },
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
                text: 'Volume(AVG. monthly)',
                value: 'month_search',
                align: 'right',
                width: '15%',
            },
            {
                text: 'Trend',
                value: 'trend',
                align: 'left',
                width: '15%',
            },
            {
                text: 'State',
                value: 'competition',
                align: 'left',
                width: '9%',
            },
            {
                text: 'BID(low)',
                value: 'bid_low',
                align: 'right',
                width: '9%',
            },
            {
                text: 'BID(high)',
                value: 'bid_high',
                align: 'right',
                width: '9%',
            },
            {
                text: 'Com.',
                value: 'competition_index',
                align: 'left',
                width: '8%',
            },
        ],
        desserts: [],
        dialog: false,
        keyword_str: '',
        chart_data: {},
        top_page: 1,
        itemsPerPage1: 15,
        top_headers: [
            { text: 'Title & URL', value: 'url', width: '55%' },
            { text: 'Topics', value: 'topics', align: 'right', width: '45%' },
        ],
        desserts1: [],
        checked_type: 'exact',
        questionItems: [],
        rCal: 0,
        isQuestion: false,
        keyword_str1: '',
        keyword_str2: '',
    }),
    created() {
        this.questionItems = this.$store.state.questions;
    },
    methods: {
        clickData() {
            this.desserts = [];

            this.startSearch();
            this.getTopUrls();
        },
        getTopUrls() {
            this.desserts1 = [];

            const params = {
                search_str: this.search_str,
            };

            this.$http
                .post('/keyword-top-data', params)
                .then((r) => {
                    this.desserts1 = r.data.rank.TopUrls;
                })
                // eslint-disable-next-line no-unused-vars
                .catch((e) => {
                    console.log(e);
                });
        },
        startSearch() {
            this.isQuestion = false;
            this.rCal = 0;
            this.keyword_str1 = '';
            for (let i = 0; i < this.questionItems.length; i++) {
                const sAry = this.search_str
                    .toLowerCase()
                    .split(this.questionItems[i].toLowerCase());
                if (sAry[0] === '' && sAry[1] && sAry[1] !== '') {
                    this.isQuestion = true;
                    this.keyword_str1 = sAry[1];

                    break;
                }
            }

            if (this.isQuestion) {
                this.keyword_str2 = this.search_str;
            } else {
                this.keyword_str2 = `${this.questionItems[0]}${this.search_str}`;
                this.keyword_str1 = this.search_str;
            }

            this.desserts = [];
            this.pageCount = 0;
            this.page = 1;
            this.page1 = 1;

            this.searchData();
        },
        searchData() {
            if (
                !this.keyword_str2 ||
                this.keyword_str2 === '' ||
                this.keyword_str1 === ''
            ) {
                this.searchStart = false;

                return;
            }

            this.searchStart = true;
            const params = {
                search_str: this.keyword_str2,
                keyword_str: this.keyword_str1,
                checked_type: this.checked_type,
                is_question: this.isQuestion,
            };

            this.$http
                .post('/keyword-data', params)
                .then((r) => {
                    if (r.data.result && r.data.result.length) {
                        this.desserts = this.desserts.concat(r.data.result);
                        this.pageCount += Math.ceil(
                            r.data.pageCount / this.itemsPerPage,
                        );
                    }

                    if (this.isQuestion) {
                        this.searchStart = false;
                    } else {
                        if (this.rCal < this.questionItems.length - 1) {
                            this.rCal++;
                            this.keyword_str2 = `${
                                this.questionItems[this.rCal]
                            }${this.search_str}`;

                            this.searchData();
                        } else {
                            this.searchStart = false;
                        }
                    }
                })
                // eslint-disable-next-line no-unused-vars
                .catch((e) => {
                    this.searchStart = false;
                });
        },
        getChartData(item) {
            return {
                labels: item.name,
                datasets: [
                    {
                        label: '',
                        backgroundColor: 'rgba(220, 236, 255, 0.8)',
                        data: item.value,
                        lineTension: 0.2,
                        pointRadius: 2,
                        pointHoverRadius: 2,
                        borderColor: '#3392FF',
                        borderWidth: 1,
                    },
                ],
            };
        },
    },
};
</script>

<style lang="scss" src="../../sass/pages/_common.scss"></style>
