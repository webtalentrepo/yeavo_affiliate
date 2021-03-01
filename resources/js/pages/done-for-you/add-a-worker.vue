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
                                    v-model="title"
                                    :error-messages="errors"
                                    solo
                                    clearable
                                    autofocus
                                    required
                                ></v-text-field>
                            </ValidationProvider>

                            <ValidationProvider v-slot="{ errors }" name="URL">
                                <label class="form-label">URL</label>
                                <v-text-field
                                    v-model="url"
                                    :error-messages="errors"
                                    solo
                                    clearable
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
import DoneForYouHeader from '../../components/DoneForYouHeader';
import PageHeader from '../../layout/users/PageHeader';
import { ValidationObserver, ValidationProvider } from 'vee-validate';

export default {
    name: 'AddAWorker',
    components: {
        PageHeader,
        DoneForYouHeader,
        ValidationProvider,
        ValidationObserver,
    },
    data: () => ({
        title: '',
        url: '',
        search_tags: [],
        items: [
            'Design',
            'Work',
            'Develop',
            'Online',
            'Freelancer',
            'Upwork',
            'Service',
            'Developer',
            'Program',
            'Home',
            'Remote',
            'Website',
            'Manager',
            'Expert',
            'Write',
        ],
        description: '',
        rules: [
            (value) =>
                !value ||
                value.size < 2000000 ||
                'Image size should be less than 2 MB!',
        ],
        images: null,
    }),
    methods: {
        submit() {
            this.$refs.observer.validate().then((r) => {
                console.log(r);
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
    color: #ffffff;
    box-shadow: none;
    background: #121212 !important;
    border: 0;
    border-radius: 5px !important;
}
</style>
