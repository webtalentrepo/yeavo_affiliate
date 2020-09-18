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
        itemsPerPage: 25,
        headers: [
            { text: 'Keyword', value: 'keyword', width: '35%' },
            { text: 'Volume', value: 'volume', align: 'center', width: '13%' },
            {
                text: 'Competition',
                value: 'competition',
                align: 'center',
                width: '13%',
            },
            {
                text: 'IAAT',
                value: 'anchor',
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
        searchData() {},
    },
};
</script>

<style lang="scss" src="../../sass/pages/_common.scss"></style>
