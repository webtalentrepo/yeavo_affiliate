<template>
    <v-app>
        <v-container>
            <v-row justify="center" class="search-box-row">
                <v-col cols="8">
                    <done-for-you-header></done-for-you-header>
                </v-col>
            </v-row>
            <PageHeader :icons="false"></PageHeader>

            <v-row justify="center" class="trend-recent-list">
                <v-col
                    v-if="trending_list && trending_list.length"
                    cols="8"
                    lg="8"
                    md="10"
                    sm="11"
                    xs="12"
                >
                    <v-row
                        v-for="(trend, t) in trending_list"
                        :key="t"
                        justify="center"
                        class="cursor-pointer"
                    >
                        <v-col cols="4" md="4" sm="11" xs="12">
                            <a :href="trend.worker_url" target="_blank">
                                <v-img
                                    :src="`/storage${trend.image_name}`"
                                    aspect-ratio="1.7"
                                ></v-img
                            ></a>
                        </v-col>
                        <v-col
                            cols="7"
                            md="7"
                            sm="9"
                            xs="10"
                            class="list-content"
                        >
                            <v-row>
                                <v-col cols="12" class="list-title">
                                    {{ trend.worker_description }}
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col
                                    cols="6"
                                    md="6"
                                    sm="12"
                                    xs="12"
                                    class="font-italic"
                                >
                                    <div>
                                        Category:
                                        {{ filterTag(trend, false) }}
                                    </div>
                                    <div>
                                        Platform:
                                        {{ filterTag(trend, true) }}
                                    </div>
                                    <div>
                                        Added By:
                                        {{ trend.owner_user.name }}
                                    </div>
                                </v-col>
                                <v-col
                                    cols="6"
                                    md="6"
                                    sm="12"
                                    xs="12"
                                    class="d-flex trend-like-item"
                                >
                                    <div class="like-item">
                                        <div class="list-like">
                                            <div class="like">
                                                <img
                                                    v-if="
                                                        filterLike(trend, true)
                                                    "
                                                    src="/assets/menu-icons/like-fill.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            trend,
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
                                                            trend,
                                                            true,
                                                        )
                                                    "
                                                />

                                                <span>
                                                    {{
                                                        trend.like_users.length
                                                            ? trend.like_users
                                                                  .length
                                                            : 0
                                                    }}
                                                </span>
                                            </div>
                                            <div class="dis-like">
                                                <span>
                                                    {{
                                                        trend.dislike_users
                                                            .length
                                                            ? trend
                                                                  .dislike_users
                                                                  .length
                                                            : 0
                                                    }}
                                                </span>

                                                <img
                                                    v-if="
                                                        filterLike(trend, false)
                                                    "
                                                    src="/assets/menu-icons/dislike-fill.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            trend,
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
                                                            trend,
                                                            false,
                                                        )
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </v-col>
                            </v-row>
                        </v-col>
                        <v-col
                            cols="1"
                            md="1"
                            sm="2"
                            xs="2"
                            class="d-flex trend-like-item"
                        >
                            <img
                                v-if="filterFavorites(trend)"
                                src="/assets/menu-icons/big-heart-fill.png"
                                alt=""
                                width="35px"
                                height="31px"
                                @click="favoritesAction(trend)"
                            />

                            <img
                                v-else
                                src="/assets/menu-icons/big-heart.png"
                                alt=""
                                width="35px"
                                height="31px"
                                @click="favoritesAction(trend)"
                            />
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>
        </v-container>
    </v-app>
</template>

<script>
import DoneForYouHeader from '../../components/DoneForYouHeader';
import PageHeader from '../../layout/users/PageHeader';

export default {
    name: 'Favorites',
    components: { PageHeader, DoneForYouHeader },
    data: () => ({
        trending_list: null,
        like_list: [],
        dislike_list: [],
        favorites_list: [],
        user_id: null,
    }),
    mounted() {
        if (this.$store.state.userData) {
            this.user_id = this.$store.state.userData.id;

            this.getFavorites();
        } else {
            const intervalCheck = setInterval(() => {
                if (this.$store.state.userData) {
                    this.user_id = this.$store.state.userData.id;

                    this.getFavorites();

                    clearInterval(intervalCheck);
                }
            }, 200);
        }
    },
    methods: {
        getFavorites() {
            this.favorites_list = [];
            this.like_list = [];
            this.dislike_list = [];

            this.$http.post('/get-favorites-workers', {}).then((r) => {
                if (r.data.result === 'success') {
                    this.trending_list = r.data.favorites_list;

                    for (const tEl of this.trending_list) {
                        const like_list = tEl.like_users.filter((el1) => {
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

                        const dislike_list = tEl.dislike_users.filter((el2) => {
                            return this.user_id === el2.id;
                        });

                        if (dislike_list && dislike_list.length) {
                            for (const item of dislike_list) {
                                this.$set(
                                    this.dislike_list,
                                    this.dislike_list.length,
                                    item.pivot.worker_id,
                                );
                            }
                        }

                        const fav_list = tEl.favorites_users.filter((fEl) => {
                            return this.user_id === fEl.id;
                        });

                        if (fav_list && fav_list.length) {
                            for (const item1 of fav_list) {
                                this.$set(
                                    this.favorites_list,
                                    this.favorites_list.length,
                                    item1.pivot.worker_id,
                                );
                            }
                        }
                    }

                    this.$forceUpdate();
                }
            });
        },

        async setLikes(id, flag, add) {
            await this.$http
                .post('/vote-worker', {
                    worker_id: id,
                    flag: flag,
                    add: add,
                })
                .then((r) => {
                    if (r.data.result === 'success') {
                        this.getFavorites();
                    }
                });
        },

        async setFavorites(id, add) {
            await this.$http
                .post('/favorites-worker', {
                    worker_id: id,
                    add: add,
                })
                .then((r) => {
                    if (r.data.result === 'success') {
                        this.getFavorites();
                    }
                });
        },

        favoritesAction(item) {
            if (!item) {
                return;
            }

            if (this.favorites_list.length) {
                if (this.favorites_list.indexOf(item.id) > -1) {
                    this.favorites_list = this.favorites_list.filter((el) => {
                        return el !== item.id;
                    });

                    this.$forceUpdate();

                    this.setFavorites(item.id, 'no');

                    return;
                }
            }

            this.$set(this.favorites_list, this.favorites_list.length, item.id);

            this.setFavorites(item.id, 'yes');

            this.$forceUpdate();
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

        filterFavorites(item) {
            if (!item) {
                return false;
            }

            return this.favorites_list.indexOf(item.id) > -1;
        },

        filterTag(item, flag) {
            if (flag) {
                const platforms = this.$store.state.platform_tags;
                for (let p = 0; p < platforms.length; p++) {
                    const p_item = platforms[p];
                    if (item.search_tags.indexOf(p_item) > -1) {
                        return p_item;
                    }
                }
            } else {
                const services = this.$store.state.service_tags;
                for (let s = 0; s < services.length; s++) {
                    const s_item = services[s];
                    if (item.search_tags.indexOf(s_item) > -1) {
                        return s_item;
                    }
                }
            }

            return '';
        },
    },
};
</script>

<style lang="scss" src="../../../sass/pages/_common.scss"></style>
<style lang="scss" src="../../../sass/pages/_done.scss"></style>
