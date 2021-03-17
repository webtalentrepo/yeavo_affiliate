<template>
    <v-app>
        <v-container>
            <v-row justify="center" class="search-box-row">
                <v-col cols="8">
                    <done-for-you-header></done-for-you-header>
                </v-col>
            </v-row>
            <PageHeader></PageHeader>

            <v-row justify="center" class="search-box-row">
                <v-col cols="8">
                    <v-text-field
                        v-model="search_str"
                        solo
                        label="Search, e.g. upwork affiliate copywriting"
                        @keyup.enter="getTopWorkers"
                        @click:append="getTopWorkers"
                    >
                        <template #append>
                            <img
                                src="/assets/menu-icons/search.png"
                                alt=""
                                class="append-icon cursor-pointer"
                                @click="getTopWorkers"
                            />
                        </template>
                    </v-text-field>
                </v-col>
            </v-row>

            <v-row
                justify="center"
                align="center"
                class="p-0 done-for-you-search"
            >
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
                                    @change="getTopWorkers"
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
                                    @change="getTopWorkers"
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
                    <div v-if="top_workers && top_workers.length">
                        <VueSlickCarousel v-bind="settings">
                            <div
                                v-for="(worker, i) in top_workers"
                                :key="i"
                                class="top-worker-cards"
                            >
                                <div class="worker-item">
                                    <div class="worker-service">
                                        <div class="service-title">
                                            {{ filterTag(worker, true) }}
                                        </div>
                                    </div>
                                    <v-card class="mx-auto" max-width="200">
                                        <a
                                            :href="worker.worker_url"
                                            target="_blank"
                                        >
                                            <v-img
                                                :src="`/storage${worker.image_name}`"
                                                height="120px"
                                            ></v-img>
                                        </a>

                                        <v-card-text>
                                            {{ worker.worker_description }}
                                        </v-card-text>

                                        <v-card-title>
                                            <div class="worker-category">
                                                <img
                                                    v-if="
                                                        filterFavorites(
                                                            worker,
                                                            0,
                                                        )
                                                    "
                                                    src="/assets/menu-icons/small-heart-fill.png"
                                                    alt=""
                                                    @click="
                                                        favoritesAction(
                                                            worker,
                                                            0,
                                                        )
                                                    "
                                                />

                                                <img
                                                    v-else
                                                    src="/assets/menu-icons/small-heart.png"
                                                    alt=""
                                                    @click="
                                                        favoritesAction(
                                                            worker,
                                                            0,
                                                        )
                                                    "
                                                />

                                                <div>
                                                    {{
                                                        filterTag(worker, false)
                                                    }}
                                                </div>
                                            </div>
                                        </v-card-title>
                                    </v-card>
                                    <div class="like-item">
                                        <div class="list-like">
                                            <div class="like">
                                                <img
                                                    v-if="
                                                        filterLike(
                                                            worker,
                                                            true,
                                                            0,
                                                        )
                                                    "
                                                    src="/assets/menu-icons/like-fill.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            worker,
                                                            true,
                                                            0,
                                                        )
                                                    "
                                                />

                                                <img
                                                    v-else
                                                    src="/assets/menu-icons/like.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            worker,
                                                            true,
                                                            0,
                                                        )
                                                    "
                                                />

                                                <span>
                                                    {{
                                                        worker.like_users.length
                                                            ? worker.like_users
                                                                  .length
                                                            : 0
                                                    }}
                                                </span>
                                            </div>
                                            <div class="dis-like">
                                                <span>
                                                    {{
                                                        worker.dislike_users
                                                            .length
                                                            ? worker
                                                                  .dislike_users
                                                                  .length
                                                            : 0
                                                    }}
                                                </span>

                                                <img
                                                    v-if="
                                                        filterLike(
                                                            worker,
                                                            false,
                                                            0,
                                                        )
                                                    "
                                                    src="/assets/menu-icons/dislike-fill.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            worker,
                                                            false,
                                                            0,
                                                        )
                                                    "
                                                />

                                                <img
                                                    v-else
                                                    src="/assets/menu-icons/dislike.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            worker,
                                                            false,
                                                            0,
                                                        )
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </VueSlickCarousel>
                    </div>
                    <div v-else>Not Exists</div>
                </v-col>
            </v-row>

            <v-row justify="center" class="trend-recent-list">
                <v-col cols="7" md="7" sm="8" xs="12">
                    <v-row>
                        <v-col cols="12">
                            <h2>Trending</h2>
                        </v-col>
                    </v-row>
                    <div v-if="trending_list && trending_list.length">
                        <v-row
                            v-for="(trend, t) in trending_list"
                            :key="t"
                            justify="center"
                            class="cursor-pointer"
                        >
                            <v-col cols="4" md="4" sm="11" xs="12">
                                <v-img
                                    :src="`/storage${trend.image_name}`"
                                    aspect-ratio="1.7"
                                ></v-img>
                            </v-col>
                            <v-col
                                cols="8"
                                md="8"
                                sm="11"
                                xs="12"
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
                                        <img
                                            v-if="filterFavorites(trend, 1)"
                                            src="/assets/menu-icons/big-heart-fill.png"
                                            alt=""
                                            width="28px"
                                            height="25px"
                                            @click="favoritesAction(trend, 1)"
                                        />

                                        <img
                                            v-else
                                            src="/assets/menu-icons/big-heart.png"
                                            alt=""
                                            width="28px"
                                            height="25px"
                                            @click="favoritesAction(trend, 1)"
                                        />

                                        <div class="like-item">
                                            <div class="list-like">
                                                <div class="like">
                                                    <img
                                                        v-if="
                                                            filterLike(
                                                                trend,
                                                                true,
                                                                1,
                                                            )
                                                        "
                                                        src="/assets/menu-icons/like-fill.png"
                                                        alt=""
                                                        @click="
                                                            likeDislikeAction(
                                                                trend,
                                                                true,
                                                                1,
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
                                                                1,
                                                            )
                                                        "
                                                    />

                                                    <span>
                                                        {{
                                                            trend.like_users
                                                                .length
                                                                ? trend
                                                                      .like_users
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
                                                            filterLike(
                                                                trend,
                                                                false,
                                                                1,
                                                            )
                                                        "
                                                        src="/assets/menu-icons/dislike-fill.png"
                                                        alt=""
                                                        @click="
                                                            likeDislikeAction(
                                                                trend,
                                                                false,
                                                                1,
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
                                                                1,
                                                            )
                                                        "
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-col>
                        </v-row>
                    </div>
                </v-col>
                <v-col cols="3" md="3" sm="4" xs="12" class="border-left">
                    <v-row>
                        <v-col cols="12">
                            <h2>Recently Added</h2>
                        </v-col>
                    </v-row>
                    <div v-if="recent_list">
                        <v-row v-for="(s, k) in service_category_list" :key="k">
                            <v-col v-if="s !== 'All'" cols="6">{{ s }}</v-col>
                            <v-col
                                v-if="s !== 'All'"
                                cols="6"
                                class="font-weight-bold"
                            >
                                {{
                                    recent_list[s]
                                        ? `${recent_list[s]} New Added`
                                        : 'None'
                                }}
                            </v-col>
                        </v-row>
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </v-app>
</template>

<script>
import VueSlickCarousel from 'vue-slick-carousel';
import PageHeader from '../layout/users/PageHeader';
import DoneForYouHeader from '../components/DoneForYouHeader';

export default {
    name: 'DoneForYou',
    components: { DoneForYouHeader, PageHeader, VueSlickCarousel },
    data: () => ({
        search_str: '',
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
            'Writing',
            'Graphic Design',
            'Traffic',
            'SEO',
            'Programming',
            'Video Editing',
            'Others',
        ],
        top_workers: null,
        recent_list: null,
        trending_list: null,
        like_list: [[], []],
        dislike_list: [[], []],
        favorites_list: [[], []],
        user_id: null,
        settings: {
            dots: false,
            arrows: true,
            infinite: false,
            speed: 500,
            slidesToShow: 4,
            slidesToScroll: 4,
            initialSlide: 0,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                        infinite: true,
                        dots: true,
                    },
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                        initialSlide: 2,
                    },
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    },
                },
            ],
        },
    }),
    mounted() {
        if (this.$store.state.userData) {
            this.user_id = this.$store.state.userData.id;

            this.getTopWorkers();
        } else {
            const intervalCheck = setInterval(() => {
                if (this.$store.state.userData) {
                    this.user_id = this.$store.state.userData.id;

                    this.getTopWorkers();

                    clearInterval(intervalCheck);
                }
            }, 200);
        }
    },
    methods: {
        getTopWorkers() {
            this.favorites_list = [[], []];
            this.like_list = [[], []];
            this.dislike_list = [[], []];
            this.$http
                .post('/get-top-workers', {
                    search_str: this.search_str,
                    platform: this.platform,
                    service_category: this.service_category,
                })
                .then((r) => {
                    if (r.data.result === 'success') {
                        this.top_workers = r.data.top_workers;
                        this.trending_list = r.data.trending_list;
                        this.recent_list = r.data.recent_added;

                        for (const el of this.top_workers) {
                            const like_list = el.like_users.filter((el1) => {
                                return this.user_id === el1.id;
                            });

                            if (like_list && like_list.length) {
                                for (const item of like_list) {
                                    this.$set(
                                        this.like_list[0],
                                        this.like_list[0].length,
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
                                        this.dislike_list[0],
                                        this.dislike_list[0].length,
                                        item.pivot.worker_id,
                                    );
                                }
                            }

                            const fav_list = el.favorites_users.filter(
                                (fEl) => {
                                    return this.user_id === fEl.id;
                                },
                            );

                            if (fav_list && fav_list.length) {
                                for (const item1 of fav_list) {
                                    this.$set(
                                        this.favorites_list[0],
                                        this.favorites_list[0].length,
                                        item1.pivot.worker_id,
                                    );
                                }
                            }
                        }

                        for (const tEl of this.trending_list) {
                            const like_list = tEl.like_users.filter((el1) => {
                                return this.user_id === el1.id;
                            });

                            if (like_list && like_list.length) {
                                for (const item of like_list) {
                                    this.$set(
                                        this.like_list[1],
                                        this.like_list[1].length,
                                        item.pivot.worker_id,
                                    );
                                }
                            }

                            const dislike_list = tEl.dislike_users.filter(
                                (el2) => {
                                    return this.user_id === el2.id;
                                },
                            );

                            if (dislike_list && dislike_list.length) {
                                for (const item of dislike_list) {
                                    this.$set(
                                        this.dislike_list[1],
                                        this.dislike_list[1].length,
                                        item.pivot.worker_id,
                                    );
                                }
                            }

                            const fav_list = tEl.favorites_users.filter(
                                (fEl) => {
                                    return this.user_id === fEl.id;
                                },
                            );

                            if (fav_list && fav_list.length) {
                                for (const item1 of fav_list) {
                                    this.$set(
                                        this.favorites_list[1],
                                        this.favorites_list[1].length,
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
                        this.getTopWorkers();
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
                        this.getTopWorkers();
                    }
                });
        },

        favoritesAction(item, ind) {
            if (!item) {
                return;
            }

            if (this.favorites_list[ind].length) {
                if (this.favorites_list[ind].indexOf(item.id) > -1) {
                    this.favorites_list[ind] = this.favorites_list[ind].filter(
                        (el) => {
                            return el !== item.id;
                        },
                    );

                    this.$forceUpdate();

                    this.setFavorites(item.id, 'no');

                    return;
                }
            }

            this.$set(
                this.favorites_list[ind],
                this.favorites_list[ind].length,
                item.id,
            );

            this.setFavorites(item.id, 'yes');

            this.$forceUpdate();
        },

        likeDislikeAction(item, flag, ind) {
            if (!item) {
                return;
            }

            if (flag) {
                if (this.like_list[ind].length) {
                    if (this.like_list[ind].indexOf(item.id) > -1) {
                        this.like_list[ind] = this.like_list[ind].filter(
                            (el) => {
                                return el !== item.id;
                            },
                        );

                        this.$forceUpdate();

                        this.setLikes(item.id, 'like', 'no');

                        return;
                    }
                }

                if (this.dislike_list[ind].length) {
                    if (this.dislike_list[ind].indexOf(item.id) > -1) {
                        return;
                    }
                }

                this.$set(
                    this.like_list[ind],
                    this.like_list[ind].length,
                    item.id,
                );

                this.setLikes(item.id, 'like', 'yes');

                this.$forceUpdate();
            } else {
                if (this.dislike_list[ind].length) {
                    if (this.dislike_list[ind].indexOf(item.id) > -1) {
                        this.dislike_list[ind] = this.dislike_list[ind].filter(
                            (el) => {
                                return el !== item.id;
                            },
                        );

                        this.$forceUpdate();

                        this.setLikes(item.id, 'dislike', 'no');

                        return;
                    }
                }

                if (this.like_list[ind].length) {
                    if (this.like_list[ind].indexOf(item.id) > -1) {
                        return;
                    }
                }

                this.$set(
                    this.dislike_list[ind],
                    this.dislike_list[ind].length,
                    item.id,
                );

                this.setLikes(item.id, 'dislike', 'yes');

                this.$forceUpdate();
            }
        },

        filterLike(item, flag, ind) {
            if (!item) {
                return false;
            }

            if (flag) {
                if (this.like_list[ind].indexOf(item.id) > -1) {
                    return true;
                }
            } else {
                if (this.dislike_list[ind].indexOf(item.id) > -1) {
                    return true;
                }
            }

            return false;
        },

        filterFavorites(item, ind) {
            if (!item) {
                return false;
            }

            return this.favorites_list[ind].indexOf(item.id) > -1;
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
<style lang="scss" src="../../sass/pages/_common.scss"></style>
<style lang="scss" src="../../sass/pages/_done.scss"></style>
