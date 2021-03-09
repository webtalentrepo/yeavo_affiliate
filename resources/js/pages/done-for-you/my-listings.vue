<template>
    <v-app>
        <v-container>
            <v-row justify="center" class="search-box-row">
                <v-col cols="8">
                    <done-for-you-header></done-for-you-header>
                </v-col>
            </v-row>
            <PageHeader :icons="false"></PageHeader>
            <v-row justify="center">
                <v-col cols="10" md="10" sm="11" xs="12">
                    <v-container v-if="listings" class="body-content">
                        <v-row
                            v-for="(row, key) in listings"
                            :key="key"
                            justify="center"
                            class="cursor-pointer"
                        >
                            <v-col cols="3" md="3" sm="11" xs="12">
                                <v-img
                                    :src="`/storage${row.image_name}`"
                                    aspect-ratio="1.7"
                                ></v-img>
                            </v-col>
                            <v-col
                                cols="7"
                                md="7"
                                sm="11"
                                xs="12"
                                class="list-content"
                            >
                                <v-row>
                                    <v-col cols="12" class="list-title">
                                        {{ row.worker_title }}
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="7">
                                        {{ row.worker_description }}
                                    </v-col>
                                    <v-col cols="5" class="justify-center">
                                        <div class="list-like">
                                            <div class="like">
                                                <img
                                                    src="/assets/menu-icons/like.png"
                                                    alt=""
                                                />

                                                <span>
                                                    {{ row.like_users.length }}
                                                </span>
                                            </div>
                                            <div class="dis-like">
                                                <span>
                                                    {{
                                                        row.dislike_users.length
                                                    }}
                                                </span>

                                                <img
                                                    src="/assets/menu-icons/dislike.png"
                                                    alt=""
                                                />
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-col>
                            <v-col
                                cols="2"
                                md="2"
                                sm="11"
                                xs="12"
                                class="list-actions"
                            >
                                <div>
                                    <v-btn color="dark">
                                        <router-link
                                            :to="`/done-for-you/edit/${row.id}`"
                                        >
                                            Edit
                                        </router-link>
                                    </v-btn>
                                </div>
                                <div>
                                    <v-btn
                                        color="error"
                                        @click="openDelConfirm(row.id)"
                                    >
                                        Delete
                                    </v-btn>
                                </div>
                            </v-col>
                        </v-row>
                    </v-container>
                    <v-container v-else class="text-align-center">
                        Not exist listing's data.
                    </v-container>
                </v-col>
            </v-row>
        </v-container>
        <v-dialog v-model="dialog" persistent max-width="290">
            <v-card>
                <v-card-title class="headline"> Are you sure?</v-card-title>
                <v-card-text>
                    You will lost all settings of this data. Do you still delete
                    this?
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" @click="deleteListingsData()">
                        Delete
                    </v-btn>
                    <v-btn color="grey darken-1" @click="dialog = false">
                        Cancel
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-app>
</template>

<script>
import DoneForYouHeader from '../../components/DoneForYouHeader';
import PageHeader from '../../layout/users/PageHeader';
import { mapActions } from 'vuex';

export default {
    name: 'MyListings',
    components: { PageHeader, DoneForYouHeader },
    data: () => ({
        listings: null,
        del_list: null,
        dialog: false,
    }),
    mounted() {
        this.getListingsData();
    },
    methods: {
        ...mapActions({
            getData: 'getData',
            postData: 'post',
        }),

        getListingsData() {
            this.listings = null;
            this.getData({ url: '/workers', config: {} })
                .then((re) => {
                    if (re.data.result === 'success') {
                        this.listings = re.data.message;
                        console.log(this.listings);
                    }
                })
                .catch((e) => {
                    console.log(e);
                });
        },

        openDelConfirm(id) {
            this.del_list = id;
            this.dialog = true;
        },

        deleteListingsData() {
            if (this.del_list) {
                this.postData({
                    url: `/workers/${this.del_list}`,
                    data: {
                        _method: 'DELETE',
                    },
                })
                    .then((r) => {
                        this.dialog = false;

                        if (r.data.result === 'success') {
                            this.listings = this.listings.filter((e) => {
                                return e.id !== this.del_list;
                            });

                            this.del_list = null;
                        }
                    })
                    .catch((e) => {
                        console.log(e);
                    });
            }
        },
    },
};
</script>

<style lang="scss" src="../../../sass/pages/_common.scss"></style>
<style lang="scss" src="../../../sass/pages/_done.scss"></style>
