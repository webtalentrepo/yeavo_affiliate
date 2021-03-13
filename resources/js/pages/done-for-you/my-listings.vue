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
                                    <v-col
                                        v-if="!watchedLike"
                                        cols="5"
                                        class="justify-center"
                                    >
                                        <div class="list-like">
                                            <div class="like">
                                                <img
                                                    v-if="filterLike(row, true)"
                                                    src="/assets/menu-icons/like-fill.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            row,
                                                            true,
                                                        )
                                                    "
                                                />

                                                <img
                                                    v-else
                                                    src="/assets/menu-icons/like.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            row,
                                                            true,
                                                        )
                                                    "
                                                />

                                                <span>
                                                    {{ checkLike(row, true) }}
                                                </span>
                                            </div>
                                            <div class="dis-like">
                                                <span>
                                                    {{ checkLike(row, false) }}
                                                </span>

                                                <img
                                                    v-if="
                                                        filterLike(row, false)
                                                    "
                                                    src="/assets/menu-icons/dislike-fill.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            row,
                                                            false,
                                                        )
                                                    "
                                                />

                                                <img
                                                    v-else
                                                    src="/assets/menu-icons/dislike.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            row,
                                                            false,
                                                        )
                                                    "
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
        like_list: [],
        dislike_list: [],
        user_id: null,
        watchedLike: false,
    }),
    mounted() {
        this.user_id = this.$store.state.userData.id;

        this.getListingsData();
    },
    methods: {
        ...mapActions({
            getData: 'getData',
            postData: 'post',
        }),

        checkLike(item, flag) {
            if (!item) {
                return 0;
            }

            if (flag) {
                if (this.like_list && this.like_list.indexOf(item.id) > -1) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                if (
                    this.dislike_list &&
                    this.dislike_list.indexOf(item.id) > -1
                ) {
                    return 1;
                } else {
                    return 0;
                }
            }
        },

        async setLikes(id, flag, add) {
            await this.$http.post('/vote-worker', {
                worker_id: id,
                flag: flag,
                add: add,
            });
        },

        likeDislikeAction(item, flag) {
            if (!item) {
                return;
            }

            if (flag) {
                if (this.like_list.length) {
                    if (this.like_list.indexOf(item.id) > -1) {
                        this.like_list = this.like_list.filter((el) => {
                            return el !== item.id;
                        });

                        this.$forceUpdate();

                        this.setLikes(item.id, 'like', 'no');

                        return;
                    }
                }

                if (this.dislike_list.length) {
                    if (this.dislike_list.indexOf(item.id) > -1) {
                        return;
                    }
                }

                this.$set(this.like_list, this.like_list.length, item.id);

                this.setLikes(item.id, 'like', 'yes');

                this.$forceUpdate();
            } else {
                if (this.dislike_list.length) {
                    if (this.dislike_list.indexOf(item.id) > -1) {
                        this.dislike_list = this.dislike_list.filter((el) => {
                            return el !== item.id;
                        });

                        this.$forceUpdate();

                        this.setLikes(item.id, 'dislike', 'no');

                        return;
                    }
                }

                if (this.like_list.length) {
                    if (this.like_list.indexOf(item.id) > -1) {
                        return;
                    }
                }

                this.$set(this.dislike_list, this.dislike_list.length, item.id);

                this.setLikes(item.id, 'dislike', 'yes');

                this.$forceUpdate();
            }
        },

        filterLike(item, flag) {
            if (!item) {
                return false;
            }

            if (flag) {
                if (this.like_list.indexOf(item.id) > -1) {
                    return true;
                }
            } else {
                if (this.dislike_list.indexOf(item.id) > -1) {
                    return true;
                }
            }

            return false;
        },

        getListingsData() {
            this.listings = null;
            this.getData({ url: '/workers', config: {} })
                .then((re) => {
                    if (re.data.result === 'success') {
                        this.listings = re.data.message;

                        for (const el of this.listings) {
                            const like_list = el.like_users.filter((el1) => {
                                return this.user_id === el1.id;
                            });

                            if (like_list && like_list.length) {
                                for (const item of like_list) {
                                    this.$set(
                                        this.like_list,
                                        this.like_list.length,
                                        item.pivot.worker_id,
                                    );
                                }
                            }

                            const dislike_list = el.dislike_users.filter(
                                (el2) => {
                                    return this.user_id === el2.id;
                                },
                            );

                            if (dislike_list && dislike_list.length) {
                                for (const item of dislike_list) {
                                    this.$set(
                                        this.dislike_list,
                                        this.dislike_list.length,
                                        item.pivot.worker_id,
                                    );
                                }
                            }
                        }
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
