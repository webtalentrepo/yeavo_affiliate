<template>
    <v-app>
        <v-container>
            <v-app-bar></v-app-bar>
            <PageHeader></PageHeader>

            <v-row justify="center" class="search-box-row">
                <v-col cols="8">
                    <v-text-field
                        v-model="search_str"
                        solo
                        label="Search, e.g. upwork affiliate copywriting"
                        @keyup.enter="searchData"
                        @click:append="searchData"
                    >
                        <template #append>
                            <img
                                src="/assets/menu-icons/search.png"
                                alt=""
                                class="append-icon cursor-pointer"
                                @click="searchData"
                            />
                        </template>
                    </v-text-field>
                </v-col>
            </v-row>

            <v-row justify="center" align="center" class="p-0">
                <v-col cols="8" md="8" sm="10" xs="12">
                    <v-row justify="center" align="center" class="search-row">
                        <v-col cols="5" md="5" sm="12">
                            <div
                                class="d-flex align-center justify-center min-max-select-box"
                            >
                                <v-select
                                    v-model="platform"
                                    :items="platform_list"
                                    solo
                                    dense
                                    label="Platform"
                                ></v-select>
                            </div>
                        </v-col>
                        <v-col cols="5" md="5" sm="12">
                            <div
                                class="d-flex align-center justify-center min-max-select-box"
                            >
                                <v-select
                                    v-model="service_category"
                                    :items="service_category_list"
                                    solo
                                    dense
                                    label="Service Category"
                                ></v-select>
                            </div>
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>

            <v-row justify="center" align="center" class="p-0">
                <v-col
                    cols="8"
                    md="8"
                    sm="10"
                    xs="12"
                    class="text-align-center"
                >
                    <h2>Top Workers</h2>
                </v-col>
            </v-row>
            <v-row justify="center" align="center" class="p-0">
                <v-col
                    cols="10"
                    md="10"
                    sm="12"
                    xs="12"
                    class="text-align-center"
                >
                    <!--                    top workers here when back end completed-->
                </v-col>
            </v-row>

            <v-row justify="center" align="center" class="p-0">
                <v-col cols="7" md="7" sm="8" xs="12">
                    <v-row>
                        <v-col cols="12">
                            <h2>Trending</h2>
                        </v-col>
                    </v-row>
                </v-col>
                <v-col cols="3" md="3" sm="4" xs="12">
                    <v-row>
                        <v-col cols="12">
                            <h2>Recently Added</h2>
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>
        </v-container>
    </v-app>
</template>

<script>
import PageHeader from '../layout/users/PageHeader';

export default {
    name: 'DoneForYou',
    components: { PageHeader },
    data: () => ({
        search_str: '',
        searchData: '',
        platform: '',
        service_category: '',
        show_workers: [0, 1, 2, 3, 4],
        platform_list: [
            'All',
            'Upwork',
            'Konker',
            'Freelancer',
            '99 Designs',
            'Guru',
            'Fiverr',
        ],
        service_category_list: [
            'All',
            'Graphic Design',
            'Traffic',
            'SEO',
            'Programming',
            'Video Editing',
            'Others',
        ],
        top_workers: [
            'primary',
            'secondary',
            'yellow darken-2',
            'red',
            'orange',
        ],
    }),
};
</script>
<style lang="scss" src="../../sass/pages/_common.scss"></style>
<style lang="scss" src="../../sass/pages/_done.scss"></style>
