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
                    <div v-if="top_workers">
                        <VueSlickCarousel v-bind="settings">
                            <div
                                v-for="(worker, i) in top_workers"
                                :key="i"
                                class="top-worker-cards"
                            >
                                <div class="worker-item">
                                    <div class="worker-service">
                                        <div class="service-title">Upwork</div>
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
                                                    src="/assets/menu-icons/small-heart.png"
                                                    alt=""
                                                />
                                                <div>Writing</div>
                                            </div>
                                        </v-card-title>
                                    </v-card>
                                    <div class="like-item">
                                        <div class="list-like">
                                            <div class="like">
                                                <img
                                                    v-if="
                                                        filterLike(worker, true)
                                                    "
                                                    src="/assets/menu-icons/like-fill.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            worker,
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
                                                            worker,
                                                            true,
                                                        )
                                                    "
                                                />

                                                <span>
                                                    {{
                                                        checkLike(worker, true)
                                                    }}
                                                </span>
                                            </div>
                                            <div class="dis-like">
                                                <span>
                                                    {{
                                                        checkLike(worker, false)
                                                    }}
                                                </span>

                                                <img
                                                    v-if="
                                                        filterLike(
                                                            worker,
                                                            false,
                                                        )
                                                    "
                                                    src="/assets/menu-icons/dislike-fill.png"
                                                    alt=""
                                                    @click="
                                                        likeDislikeAction(
                                                            worker,
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
                                                            worker,
                                                            false,
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
            'Writing',
            'Graphic Design',
            'Traffic',
            'SEO',
            'Programming',
            'Video Editing',
            'Others',
        ],
        top_workers: null,
        like_list: [],
        dislike_list: [],
        recent_list: null,
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
        } else {
            this.$router.push('/logout');
        }

        this.getTopWorkers();
    },
    methods: {
        getTopWorkers() {
            this.top_workers = null;

            this.$http.post('/get-top-workers', {}).then((r) => {
                if (r.data.result === 'success') {
                    this.top_workers = r.data.top_workers;
                    this.recent_list = r.data.recent_added;

                    for (const el of this.top_workers) {
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

                        const dislike_list = el.dislike_users.filter((el2) => {
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
                    }
                }
            });
        },

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
    },
};
</script>
<style lang="scss" src="../../sass/pages/_common.scss"></style>
<style lang="scss" src="../../sass/pages/_done.scss"></style>
