<template>
    <ValidationObserver ref="observer">
        <v-app>
            <v-container>
                <v-row justify="center" class="search-box-row">
                    <v-col cols="8">
                        <done-for-you-header></done-for-you-header>
                    </v-col>
                </v-row>
                <PageHeader :icons="false"></PageHeader>

                <v-row justify="center">
                    <v-col cols="6" md="6" sm="8" xs="10">
                        <form enctype="multipart/form-data">
                            <ValidationProvider
                                v-slot="{ errors }"
                                name="Title"
                                rules="required"
                            >
                                <label class="form-label">Title</label>
                                <v-text-field
                                    v-model="worker_title"
                                    :error-messages="errors"
                                    solo
                                    clearable
                                    autofocus
                                    required
                                ></v-text-field>
                            </ValidationProvider>

                            <ValidationProvider
                                v-slot="{ errors }"
                                name="URL"
                                rules="required|url"
                            >
                                <label class="form-label">URL</label>
                                <v-text-field
                                    v-model="worker_url"
                                    :error-messages="errors"
                                    solo
                                    clearable
                                    required
                                ></v-text-field>
                            </ValidationProvider>

                            <ValidationProvider
                                v-slot="{ errors }"
                                name="Image"
                            >
                                <label class="form-label">Image</label>
                                <v-file-input
                                    v-model="images"
                                    :rules="rules"
                                    accept="image/png, image/jpeg, image/bmp"
                                    :error-messages="errors"
                                    solo
                                    placeholder="Optional"
                                    prepend-icon="mdi-camera"
                                ></v-file-input>
                            </ValidationProvider>

                            <ValidationProvider name="SearchTags">
                                <label class="form-label">Search Tags</label>
                                <v-combobox
                                    v-model="search_tags"
                                    :items="items"
                                    chips
                                    clearable
                                    multiple
                                    solo
                                >
                                    <template
                                        #selection="{
                                            attrs,
                                            item,
                                            select,
                                            selected,
                                        }"
                                    >
                                        <v-chip
                                            v-bind="attrs"
                                            :input-value="selected"
                                            close
                                            @click="select"
                                            @click:close="remove(item)"
                                        >
                                            <strong>{{ item }}</strong>
                                        </v-chip>
                                    </template>
                                </v-combobox>
                            </ValidationProvider>

                            <ValidationProvider
                                v-slot="{ errors }"
                                name="Description"
                                rules="required"
                            >
                                <label class="form-label">
                                    What this worker does best?
                                </label>
                                <v-textarea
                                    v-model="description"
                                    :error-messages="errors"
                                    solo
                                    clearable
                                    required
                                ></v-textarea>
                            </ValidationProvider>

                            <v-row justify="center">
                                <v-col
                                    cols="6"
                                    md="6"
                                    sm="8"
                                    xs="10"
                                    class="text-align-center"
                                >
                                    <v-btn
                                        class="text-capitalize worker-submit"
                                        @click="submit"
                                    >
                                        Submit
                                    </v-btn>
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
import { ValidationObserver, ValidationProvider } from 'vee-validate';
import DoneForYouHeader from '../../components/DoneForYouHeader';
import PageHeader from '../../layout/users/PageHeader';

export default {
    name: 'AddAWorker',
    components: {
        PageHeader,
        DoneForYouHeader,
        ValidationProvider,
        ValidationObserver,
    },
    data: () => ({
        worker_title: '',
        worker_url: '',
        search_tags: [],
        items: [
            'Upwork',
            'Konker',
            'Freelancer',
            '99 Designs',
            'Guru',
            'Fiverr',
            'Graphic Design',
            'Traffic',
            'SEO',
            'Programming',
            'Video Editing',
            'Others',
        ],
        description: '',
        rules: [
            (value) =>
                !value ||
                value.size < 2000000 ||
                'Image size should be less than 2 MB!',
        ],
        images: null,
        edit: false,
        worker_id: null,
    }),
    created() {
        if (this.$route.params && this.$route.params.id) {
            this.edit = true;
            this.worker_id = this.$route.params.id;
        }
    },
    mounted() {
        if (this.edit && this.worker_id) {
            this.getWorkerData();
        }
    },
    methods: {
        ...mapActions({
            postData: 'post',
            getData: 'getData',
        }),
        getWorkerData() {
            this.getData({
                url: `/workers/${this.worker_id}/edit`,
                config: {},
            })
                .then((r) => {
                    if (r.data.result === 'success') {
                        this.worker_title = r.data.message.worker_title;
                        this.worker_url = r.data.message.worker_url;
                        this.description = r.data.message.description;
                        this.search_tags =
                            r.data.message.search_tags ??
                            r.data.message.search_tags;
                    }
                })
                .catch((e) => {
                    console.log('get worker edit data error:', e);
                    this.$router.push('/logout');
                });
        },
        submit() {
            this.$refs.observer.validate().then((r) => {
                if (r) {
                    let formData = new FormData();
                    if (this.images) {
                        formData.append(
                            'worker_image',
                            this.images,
                            this.images.name,
                        );
                    }

                    formData.append('worker_title', this.worker_title);
                    formData.append('worker_url', this.worker_url);
                    formData.append('worker_description', this.description);
                    if (this.search_tags && this.search_tags.length) {
                        this.search_tags.map((el) => {
                            formData.append('search_tags[]', el);
                        });
                    }

                    if (this.edit && this.worker_id) {
                        formData.append('_method', 'PUT');
                    }

                    const post_data = {
                        url:
                            this.edit && this.worker_id
                                ? `/workers/${this.worker_id}`
                                : '/workers',
                        data: formData,
                    };

                    this.postData({ ...post_data })
                        .then((re) => {
                            if (re.data.result === 'success') {
                                this.$router.push('/done-for-you/my-listings');
                            } else {
                                // this.$router.push('/logout');
                            }
                        })
                        .catch((e) => {
                            console.log(e);
                        });
                }
            });
        },
        remove(item) {
            this.search_tags.splice(this.search_tags.indexOf(item), 1);
            this.search_tags = [...this.search_tags];
        },
    },
};
</script>

<style scoped lang="scss">
.form-label {
    font-weight: bold;
    font-size: 18px;
}

.worker-submit {
    color: #ffffff !important;
    box-shadow: none;
    background: #121212 !important;
    border: 0;
    border-radius: 5px !important;
}
</style>
