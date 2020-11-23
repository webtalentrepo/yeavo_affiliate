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
                        @keyup.enter="clickData"
                        @click:append="clickData"
                    >
                        <template #append>
                            <img
                                src="/assets/menu-icons/search.png"
                                alt=""
                                class="append-icon cursor-pointer"
                                @click="clickData"
                            />
                        </template>
                    </v-text-field>
                    <!--                    <v-combobox-->
                    <!--                        v-model="search_str"-->
                    <!--                        :items="questionItems"-->
                    <!--                        value="search_str"-->
                    <!--                        label="Search"-->
                    <!--                        solo-->
                    <!--                        hide-no-data-->
                    <!--                        hide-selected-->
                    <!--                        @keyup.enter="clickData"-->
                    <!--                        @click:append="clickData"-->
                    <!--                    >-->
                    <!--                        <template #append>-->
                    <!--                            <img-->
                    <!--                                src="/assets/menu-icons/search.png"-->
                    <!--                                alt=""-->
                    <!--                                class="append-icon cursor-pointer"-->
                    <!--                                @click="clickData"-->
                    <!--                            />-->
                    <!--                        </template>-->
                    <!--                    </v-combobox>-->
                </v-col>
            </v-row>
            <v-row justify="center" align="center">
                <v-col
                    cols="12"
                    md="11"
                    sm="12"
                    lg="11"
                    xl="11"
                    class="custom-radio-group"
                >
                    <v-radio-group v-model="checked_type" row>
                        <v-radio
                            label="Exact Match"
                            value="exact"
                            :disabled="searchStart"
                            @click="clickData"
                        ></v-radio>
                        <v-radio
                            label="Non-Questions"
                            value="non"
                            :disabled="searchStart"
                            @click="clickData"
                        ></v-radio>
                        <v-radio
                            label="Broad Match"
                            value="broad"
                            :disabled="searchStart"
                            @click="clickData"
                        ></v-radio>
                    </v-radio-group>
                </v-col>
            </v-row>

            <v-row
                v-if="(!desserts || !desserts.length) && search_str === ''"
                justify="center"
            >
                <v-col
                    cols="12"
                    md="12"
                    sm="12"
                    lg="11"
                    xl="10"
                    class="content-table"
                >
                    <div class="introduce-text">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Vivamus sed magna quam. Vivamus consectetur lacinia mi,
                        et tincidunt tortor. Aenean aliquet sollicitudin tempus.
                        Proin tempus eros vitae sem pellentesque malesuada.
                        Maecenas sit amet metus nec purus sodales lobortis id id
                        dui. Etiam eu luctus ligula. Curabitur blandit lectus
                        quis odio rutrum gravida. Etiam posuere lorem vel nisi
                        consequat, non sagittis sem pellentesque. Donec non odio
                        tristique, condimentum nibh in, commodo ipsum. In
                        venenatis, nisi in elementum dapibus, lectus turpis
                        luctus eros, non ultrices diam tellus eget nisi. Aenean
                        efficitur ex vel sodales lobortis. Aenean turpis orci,
                        pellentesque vitae congue vel, varius sed odio. Nunc
                        dapibus quis diam nec condimentum. Maecenas tristique
                        eros sed lacinia blandit.
                        <br /><br />
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Vivamus sed magna quam. Vivamus consectetur lacinia mi,
                        et tincidunt tortor. Aenean aliquet sollicitudin tempus.
                        Proin tempus eros vitae sem pellentesque malesuada.
                        Maecenas sit amet metus nec purus sodales lobortis id id
                        dui. Etiam eu luctus ligula. Curabitur blandit lectus
                        quis odio rutrum gravida. Etiam posuere lorem vel nisi
                        consequat, non sagittis sem pellentesque. Donec non odio
                        tristique, condimentum nibh in, commodo ipsum.
                        <br /><br />
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Vivamus sed magna quam. Vivamus consectetur lacinia mi,
                        et tincidunt tortor. Aenean aliquet sollicitudin tempus.
                        Proin tempus eros vitae sem pellentesque malesuada.
                        Maecenas sit amet metus nec purus sodales lobortis id id
                        dui. Etiam eu luctus ligula. Curabitur blandit lectus
                        quis odio rutrum gravida. Etiam posuere lorem vel nisi
                        consequat, non sagittis sem pellentesque. Donec non odio
                        tristique, condimentum nibh in, commodo ipsum.
                    </div>
                </v-col>
                <v-col
                    cols="12"
                    md="12"
                    sm="12"
                    lg="11"
                    xl="10"
                    class="content-table"
                >
                    <div class="introduce-video">
                        <div class="introduce-video-back">
                            <v-icon x-large>mdi-youtube</v-icon>
                        </div>
                    </div>
                </v-col>
            </v-row>

            <v-row
                v-show="desserts && desserts.length && search_str !== ''"
                justify="center"
            >
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
                        :search="search"
                        :loader-height="3"
                        loading-text="Loading... Please wait"
                    >
                        <template #[`item.name`]="{ item }">
                            <div class="w-100" @click="clickRow(item)">
                                {{ item.name }}
                            </div>
                        </template>
                        <template #[`item.trend`]="{ item }">
                            <div class="keyword-trend-area">
                                <v-btn
                                    depressed
                                    dark
                                    small
                                    @click="showTrendDialog(item)"
                                    >View
                                </v-btn>
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
                        <template #[`item.competition_index`]="{ item }">
                            <div
                                :class="{
                                    'light-green--text':
                                        item.competition_index <= 40,
                                    'amber--text':
                                        item.competition_index > 40 &&
                                        item.competition_index <= 68,
                                    'red--text': item.competition_index > 68,
                                }"
                            >
                                {{ item.competition_index }}
                            </div>
                        </template>
                    </v-data-table>
                </v-col>
                <v-col
                    cols="12"
                    md="12"
                    sm="12"
                    lg="11"
                    xl="10"
                    class="content-table py-0 px-0"
                >
                    <v-dialog
                        v-model="showTrend"
                        persistent
                        max-width="600"
                        class="custom-trend-dialog"
                    >
                        <v-card
                            v-if="selectedItem"
                            class="custom-trend-dialog-card"
                        >
                            <div class="close-btn-area">
                                <div
                                    class="close-dialog"
                                    @click="closeTrendDialog"
                                >
                                    (&times;)Close
                                </div>
                            </div>
                            <div class="chart-axis-label">
                                <div class="y-axis-label">
                                    <div>Monthly</div>
                                    <div>Volume</div>
                                </div>
                                <div class="x-axis-label">Date</div>
                            </div>
                            <div
                                style="
                                    height: 470px;
                                    margin-top: 30px;
                                    padding-left: 19px;
                                    z-index: 3;
                                    position: relative;
                                "
                            >
                                <dialog-trends
                                    :chart-data="
                                        getDialogChatData(selectedItem.trends)
                                    "
                                ></dialog-trends>
                            </div>
                            <v-card-text v-if="selectedItem">
                                <div class="border-card-text">
                                    {{ selectedItem.name }} |
                                    <span class="font-italic">{{
                                        selectedItem.month
                                    }}</span>
                                    <span
                                        :class="{
                                            'light-green--text':
                                                selectedItem.competition_index <=
                                                40,
                                            'amber--text':
                                                selectedItem.competition_index >
                                                    40 &&
                                                selectedItem.competition_index <=
                                                    68,
                                            'red--text':
                                                selectedItem.competition_index >
                                                68,
                                        }"
                                    >
                                        | {{ selectedItem.competition_index }}
                                    </span>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-dialog>
                </v-col>
            </v-row>

            <v-row
                v-show="desserts && desserts.length && search_str !== ''"
                justify="center"
            >
                <v-col
                    cols="12"
                    md="12"
                    sm="12"
                    lg="11"
                    xl="10"
                    class="content-table"
                >
                    <v-row>
                        <v-col cols="12" md="6">
                            <div
                                id="custom-pagination-header"
                                class="font-weight-bold custom-pagination-header mb-2"
                            >
                                Page Number
                            </div>
                            <paginate
                                v-model="page"
                                :page-range="5"
                                :margin-pages="2"
                                :page-count="pageCount"
                                :prev-text="'Previous'"
                                :next-text="'Next'"
                                :container-class="'custom-pagination'"
                                :page-class="'custom-page-item'"
                                :click-handler="clickPaginate"
                            >
                            </paginate>
                        </v-col>

                        <v-col cols="12" md="4"></v-col>

                        <v-col
                            cols="12"
                            md="2"
                            class="custom-page-filter align-center justify-end d-flex"
                        >
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
            <v-row
                v-show="desserts && desserts.length && search_str !== ''"
                justify="center"
            >
                <v-col
                    cols="12"
                    md="12"
                    sm="12"
                    lg="11"
                    xl="10"
                    class="text-align-center font-weight-bold"
                    >Top 10 URL's Ranking on this Keyword
                </v-col>
            </v-row>
            <v-row
                v-show="desserts && desserts.length && search_str !== ''"
                justify="center"
            >
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
import Paginate from 'vuejs-paginate';
import PageHeader from '../layout/users/PageHeader';
import KeywordTrends from '../components/KeywordTrends';
import DialogTrends from '../components/DialogTrends';

export default {
    name: 'KeywordTool',
    components: { Paginate, KeywordTrends, DialogTrends, PageHeader },
    data: () => ({
        search: '',
        search_str: '',
        searchStart: false,
        page: 1,
        page1: 1,
        pageCount: 0,
        itemsPerPage: 10,
        headers: [
            { text: 'Keyword', value: 'name', width: '38%' },
            {
                text: 'Average Month',
                value: 'month_search',
                align: 'center',
                width: '27%',
            },
            {
                text: 'Trend',
                value: 'trend',
                align: 'center',
                width: '16%',
            },
            {
                text: 'Competition',
                value: 'competition_index',
                align: 'center',
                width: '19%',
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
        rowCount: 0,
        selectedItem: null,
        showTrend: false,
    }),
    created() {
        this.questionItems = this.$store.state.questions;
    },
    beforeDestroy() {
        this.desserts = [];
        this.isQuestion = true;
        this.keyword_str = '';
        this.keyword_str1 = '';
        this.keyword_str2 = '';
        this.rowCount = 0;
        this.rCal = 0;
        this.pageCount = 0;
        this.search_str = '';
        this.page = 1;
        this.page1 = 1;
        this.searchStart = false;
    },
    methods: {
        showTrendDialog(item) {
            this.selectedItem = item;
            this.showTrend = true;
        },
        closeTrendDialog() {
            this.showTrend = false;
            this.selectedItem = null;
        },
        clickPaginate() {
            this.page1 = this.page;
            this.updatePaginate();
        },
        clickRow(item) {
            this.search_str = item.name;

            this.clickData();
        },
        clickData() {
            this.desserts = [];

            this.startSearch();
            this.getTopUrls();
        },
        getTopUrls() {
            if (this.search_str === '') {
                return;
            }

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
            this.$store.dispatch('CANCEL_PENDING_REQUESTS');

            if (this.search_str === '') {
                return;
            }

            setTimeout(() => {
                this.$nextTick(() => {
                    this.isQuestion = false;
                    this.rCal = 0;
                    this.keyword_str1 = '';
                    this.searchStart = false;

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
                    this.rowCount = 0;
                    this.page = 1;
                    this.page1 = 1;

                    this.searchData();
                });
            }, 500);
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
                        this.rowCount += r.data.pageCount;
                        this.pageCount = Math.ceil(
                            this.rowCount / this.itemsPerPage,
                        );
                    }

                    this.updatePaginate();

                    if (this.isQuestion) {
                        this.searchStart = false;
                    } else {
                        if (
                            this.searchStart &&
                            this.rCal < this.questionItems.length - 1
                        ) {
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
                        backgroundColor: 'transparent',
                        data: item.value,
                        lineTension: 0,
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        borderColor: '#363636',
                        borderWidth: 2,
                    },
                ],
            };
        },
        getDialogChatData(item) {
            return {
                labels: item.name,
                datasets: [
                    {
                        label: '',
                        backgroundColor: 'transparent',
                        data: item.value,
                        lineTension: 0,
                        pointRadius: 4,
                        pointHoverRadius: 4,
                        borderColor: '#c3c3c3',
                        borderWidth: 4,
                    },
                ],
            };
        },
        updatePaginate() {
            this.$nextTick(() => {
                setTimeout(() => {
                    const childLength = document.querySelector(
                        '.custom-pagination',
                    ).childNodes.length
                        ? Math.ceil(
                              document.querySelector('.custom-pagination')
                                  .childNodes.length /
                                  2 +
                                  2,
                          )
                        : 0;
                    const countVal =
                        this.pageCount > 6
                            ? 140.16 + 18.91 * childLength
                            : 253.62;
                    document.querySelector(
                        '#custom-pagination-header',
                    ).style.width = `${
                        countVal > 469.13 ? 469.13 : countVal
                    }px`;
                });
            });
        },
    },
};
</script>

<style lang="scss" src="../../sass/pages/_common.scss"></style>
