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
                                src="assets/icons/search.png"
                                alt=""
                                class="append-icon"
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
                    ></v-data-table>
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
        </v-container>
    </v-app>
</template>

<script>
import PageHeader from '../layout/users/PageHeader';

export default {
    name: 'KeywordTool',
    components: { PageHeader },
    data: () => ({
        search_str: '',
        searchStart: false,
        page: 1,
        page1: 1,
        pageCount: 0,
        itemsPerPage: 10,
        headers: [
            { text: 'Keywords', value: 'keyword', width: '35%' },
            { text: 'Trends', value: 'trends', align: 'center', width: '13%' },
            {
                text: 'Broad Impressions',
                value: 'broad_impressions',
                align: 'center',
                width: '13%',
            },
            {
                text: 'Impressions',
                value: 'impressions',
                align: 'center',
                width: '13%',
            },
            {
                text: 'Social media on front page?',
                value: 'social',
                align: 'center',
                width: '13%',
            },
            {
                text: 'Exact match?',
                value: 'exact',
                align: 'center',
                width: '13%',
            },
        ],
        desserts: [],
    }),
    methods: {
        searchData() {
            this.searchStart = true;
            const params = {
                search_str: this.search_str,
            };

            this.$http
                .post('/keyword-data', params)
                .then((r) => {
                    this.desserts = r.data.result;
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
    },
};
</script>

<style lang="scss" src="../../sass/pages/_common.scss"></style>
