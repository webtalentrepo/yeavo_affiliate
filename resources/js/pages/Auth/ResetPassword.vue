<template>
    <ValidationObserver ref="observer">
        <v-container>
            <v-row justify="center">
                <v-col cols="12" md="6" sm="12" lg="5" xl="4">
                    <form>
                        <v-row
                            class="mt-md-10 mt-sm-8 pb-2 blue-grey--text text-center text-sm-h6 ml-0 mr-0"
                            justify="center"
                            >Set New Password
                        </v-row>

                        <ValidationProvider
                            v-slot="{ errors }"
                            ref="password"
                            name="password"
                            rules="required|min:6"
                        >
                            <v-text-field
                                v-model="credentials.password"
                                :error-messages="errors"
                                type="password"
                                label="Password"
                                solo
                                clearable
                                required
                                autofocus
                            ></v-text-field>
                        </ValidationProvider>

                        <ValidationProvider
                            v-slot="{ errors }"
                            name="Confirm Password"
                            rules="required|min:6|password_confirmed:password"
                            data-vv-as="password"
                        >
                            <v-text-field
                                v-model="credentials.password_confirmation"
                                :error-messages="errors"
                                type="password"
                                label="Confirm Password"
                                solo
                                clearable
                                required
                            ></v-text-field>
                        </ValidationProvider>

                        <v-row
                            justify="center"
                            align="center"
                            class="ml-0 mr-0"
                        >
                            <v-btn class="mr-4" @click="submit">Reset</v-btn>

                            <router-link
                                to="/login"
                                class="ml-1 blue-grey--text text-decoration-underline"
                                >Return to Log In
                            </router-link>
                        </v-row>
                    </form>
                </v-col>
            </v-row>
        </v-container>
    </ValidationObserver>
</template>

<script>
import { ValidationObserver, ValidationProvider } from 'vee-validate';
import { mapActions, mapMutations } from 'vuex';

export default {
    name: 'ResetPassword',

    components: {
        ValidationProvider,
        ValidationObserver,
    },

    data: () => ({
        credentials: {
            password: '',
            password_confirmation: '',
            token: '',
        },
    }),

    mounted() {
        this.credentials.token = this.$route.params.token;
    },

    methods: {
        ...mapMutations({
            setAccessToken: 'setAccessToken',
            setUserInfo: 'setUserInfo',
        }),

        ...mapActions({
            resetPasswordByToken: 'resetPasswordByToken',
        }),

        submit() {
            this.$refs.observer.validate().then((r) => {
                if (r) {
                    this.resetPasswordByToken({ ...this.credentials })
                        .then((response) => {
                            if (response.data.result === 'success') {
                                // set access token and user role in store and cookie
                                const {
                                    accessToken,
                                    expiresIn,
                                    isAdmin,
                                    userInfo,
                                } = response.data;

                                this.setUserInfo(userInfo);

                                this.setAccessToken({
                                    token: accessToken,
                                    expires: expiresIn,
                                    isAdmin: isAdmin,
                                });

                                this.$nextTick(() => {
                                    this.$router.push('/');

                                    this.$forceUpdate();
                                });
                            }
                        })
                        .catch((error) => {
                            alert(error.response.data.message);
                        });
                }
            });
        },
    },
};
</script>
