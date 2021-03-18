<template>
    <ValidationObserver ref="observer">
        <v-app>
            <v-container>
                <v-row justify="center" class="search-box-row">
                    <v-col cols="8">
                        <done-for-you-header></done-for-you-header>
                    </v-col>
                </v-row>

                <v-row v-if="worker" justify="center">
                    <v-col cols="7" lg="7" md="8" sm="10" xs="11">
                        <v-row justify="center">
                            <v-col
                                cols="12"
                                class="pt-3 pb-3 font-weight-bold text-h4 text-center"
                            >
                                {{ filterTag(worker, false) }}
                            </v-col>
                        </v-row>
                        <form enctype="multipart/form-data">
                            <v-row justify="center">
                                <v-col cols="12" class="worker-image">
                                    <v-img
                                        :src="`/storage${worker.image_name}`"
                                        aspect-ratio="1.7"
                                    ></v-img>
                                </v-col>
                                <v-col cols="12" class="text-center mt-3">
                                    {{ worker.worker_title }}
                                </v-col>
                                <v-col cols="12" class="text-center">
                                    <div>
                                        Category: {{ filterTag(worker, false) }}
                                    </div>
                                    <div>
                                        Platform: {{ filterTag(worker, true) }}
                                    </div>
                                    <div>
                                        Added By: {{ worker.owner_user.name }}
                                    </div>
                                </v-col>
                                <v-col
                                    cols="12"
                                    class="worker-image text-center description-block"
                                >
                                    {{ worker.worker_description }}
                                </v-col>
                                <v-col cols="12">
                                    <span class="font-weight-bold">Tags: </span>
                                    {{ worker.search_tags.join(', ') }}
                                </v-col>
                                <v-col cols="12" class="comment-title">
                                    <img
                                        src="/assets/menu-icons/comment.png"
                                        alt=""
                                    />
                                    <div>Comments</div>
                                </v-col>
                                <v-col cols="12" class="add-comment">
                                    <ValidationProvider
                                        v-slot="{ errors }"
                                        name="Comment"
                                        rules="required"
                                    >
                                        <v-textarea
                                            v-model="comment_body"
                                            :error-messages="errors"
                                            solo
                                            clearable
                                            label="Add a comment..."
                                            rows="1"
                                            required
                                            @keyup.enter="saveComment(true, 0)"
                                        ></v-textarea>
                                    </ValidationProvider>
                                </v-col>
                                <v-col cols="12">
                                    <div
                                        v-if="
                                            worker.comments &&
                                            worker.comments.length
                                        "
                                    >
                                        <v-row
                                            v-for="(w_comment,
                                            w) in worker.comments"
                                            :key="w"
                                            class="worker-comments"
                                            justify="end"
                                        >
                                            <v-col
                                                cols="12"
                                                class="comment-content"
                                            >
                                                <div class="comment-user">
                                                    {{ w_comment.user.name }}
                                                </div>
                                                <div class="comment-body">
                                                    {{ w_comment.body }}
                                                </div>
                                                <div class="comment-reply">
                                                    <v-btn
                                                        @click="showReply(w)"
                                                    >
                                                        <img
                                                            src="/assets/menu-icons/reply.png"
                                                            alt=""
                                                        />
                                                        Reply
                                                    </v-btn>
                                                </div>
                                            </v-col>
                                            <v-col
                                                v-if="
                                                    w_comment.replies &&
                                                    w_comment.replies.length
                                                "
                                                cols="11"
                                                class="worker-comment-reply"
                                            >
                                                <v-row
                                                    v-for="(w_reply,
                                                    r) in w_comment.replies"
                                                    :key="r"
                                                    class="worker-comments"
                                                >
                                                    <v-col
                                                        cols="12"
                                                        class="comment-content"
                                                    >
                                                        <div
                                                            class="comment-user"
                                                        >
                                                            {{
                                                                w_reply.user
                                                                    .name
                                                            }}
                                                        </div>
                                                        <div
                                                            class="comment-body"
                                                        >
                                                            {{ w_reply.body }}
                                                        </div>
                                                        <div
                                                            class="comment-reply"
                                                        >
                                                            <v-btn
                                                                @click="
                                                                    showReply(w)
                                                                "
                                                            >
                                                                <img
                                                                    src="/assets/menu-icons/reply.png"
                                                                    alt=""
                                                                />
                                                                Reply
                                                            </v-btn>
                                                        </div>
                                                    </v-col>
                                                </v-row>
                                            </v-col>
                                            <v-col
                                                v-show="filterReplyBox(w)"
                                                class="add-comment"
                                            >
                                                <v-textarea
                                                    v-model="reply_body"
                                                    solo
                                                    clearable
                                                    label="Add a reply..."
                                                    rows="1"
                                                    @keyup.enter="
                                                        saveComment(
                                                            false,
                                                            w_comment.id,
                                                        )
                                                    "
                                                ></v-textarea>
                                            </v-col>
                                        </v-row>
                                    </div>
                                </v-col>
                            </v-row>
                        </form>
                    </v-col>
                </v-row>
            </v-container>
        </v-app>
    </ValidationObserver>
</template>

<script>
import { mapActions } from 'vuex';
import DoneForYouHeader from '../../components/DoneForYouHeader';
import { ValidationObserver, ValidationProvider } from 'vee-validate';

export default {
    name: 'DoneForYouDetail',
    components: { DoneForYouHeader, ValidationProvider, ValidationObserver },
    data: () => ({
        comment_body: '',
        reply_body: '',
        worker_id: null,
        worker: null,
        showReplyBox: null,
    }),
    created() {
        if (this.$route.params && this.$route.params.id) {
            this.worker_id = this.$route.params.id;
        }
    },
    mounted() {
        this.getWorkerDetail();
    },
    methods: {
        ...mapActions({
            postData: 'post',
            getData: 'getData',
        }),

        getWorkerDetail() {
            this.worker = null;

            this.getData({
                url: `/workers/${this.worker_id}`,
                config: {},
            }).then((r) => {
                if (r.data.result === 'success') {
                    this.worker = r.data.message;

                    if (this.worker.comments && this.worker.comments.length) {
                        this.showReplyBox = [];
                        this.worker.comments.map((el, key) => {
                            this.showReplyBox[key] = false;

                            return el;
                        });
                    }
                }
            });
        },

        saveComment(flag, id) {
            if (!flag) {
                if (
                    this.reply_body.replaceAll('\n', '') === '' ||
                    this.reply_body.replaceAll('&nbsp;', '') === '' ||
                    this.reply_body.replaceAll(' ', '') === ''
                ) {
                    return;
                }

                const post_data = {
                    url: '/worker_comments',
                    data: {
                        is_reply: 'yes',
                        comment_body: this.reply_body,
                        comment_id: id,
                        worker_id: this.worker_id,
                    },
                };

                this.postData({ ...post_data })
                    .then((re) => {
                        this.comment_body = '';
                        this.reply_body = '';
                        if (re.data.result === 'success') {
                            this.getWorkerDetail();
                        }
                    })
                    .catch((e) => {
                        this.comment_body = '';
                        console.log(e);
                    });
            } else {
                this.$refs.observer.validate().then((r) => {
                    if (r) {
                        const post_data = {
                            url: '/worker_comments',
                            data: {
                                is_reply: 'no',
                                comment_body: this.comment_body,
                                comment_id: id,
                                worker_id: this.worker_id,
                            },
                        };

                        this.postData({ ...post_data })
                            .then((re) => {
                                this.comment_body = '';
                                this.reply_body = '';
                                if (re.data.result === 'success') {
                                    this.getWorkerDetail();
                                }
                            })
                            .catch((e) => {
                                this.comment_body = '';
                                console.log(e);
                            });
                    }
                });
            }
        },

        showReply(i) {
            this.showReplyBox[i] = true;

            this.$forceUpdate();
        },

        filterReplyBox(i) {
            return this.showReplyBox && this.showReplyBox[i];
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
