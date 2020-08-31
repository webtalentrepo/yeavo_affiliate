<template>
    <ValidationObserver ref="observer">
        <v-container>
            <v-row justify="center">
                <v-col cols="12" md="6" sm="12" lg="5" xl="4">
                    <form>
                        <v-row
                            class="mt-md-10 mt-sm-8 pb-2 blue-grey--text"
                            justify="center"
                        ></v-row>
                        <ValidationProvider
                            v-slot="{ errors }"
                            name="Name"
                            rules="required"
                        >
                            <v-text-field
                                v-model="credentials.name"
                                :error-messages="errors"
                                label="Name"
                                solo
                                clearable
                                autofocus
                                required
                            ></v-text-field>
                        </ValidationProvider>

                        <ValidationProvider
                            v-slot="{ errors }"
                            name="Email"
                            rules="required|email"
                        >
                            <v-text-field
                                v-model="credentials.email"
                                :error-messages="errors"
                                label="Email"
                                solo
                                clearable
                                required
                            ></v-text-field>
                        </ValidationProvider>

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

                        <ValidationProvider
                            v-slot="{ errors }"
                            rules="agree_tos"
                            name="Agree"
                        >
                            <v-checkbox
                                v-model="credentials.agree_tos"
                                :error-messages="errors"
                                value="1"
                                type="checkbox"
                                required
                            >
                                <template #label>
                                    <div>
                                        I agree to the
                                        <a class="blue-grey--text"
                                            >Terms of Service</a
                                        >
                                        and
                                        <a class="blue-grey--text"
                                            >Privacy Policy</a
                                        >
                                    </div>
                                </template>
                            </v-checkbox>
                        </ValidationProvider>

                        <v-row justify="center" align="center">
                            <v-btn class="mr-4" @click="submit">Sign Up</v-btn>
                        </v-row>
                    </form>
                </v-col>
            </v-row>
        </v-container>
    </ValidationObserver>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';
import { ValidationObserver, ValidationProvider } from 'vee-validate';

export default {
    name: 'Register',
    components: {
        ValidationProvider,
        ValidationObserver,
    },
    data: () => ({
        credentials: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            agree_tos: null,
        },
    }),
    methods: {
        ...mapMutations({
            setAccessToken: 'setAccessToken',
            setUserInfo: 'setUserInfo',
            destroyAccessToken: 'destroyAccessToken',
        }),

        ...mapActions({
            registerUser: 'registerUser',
        }),

        submit() {
            this.$refs.observer.validate().then((r) => {
                if (r) {
                    this.registerUser({ ...this.credentials })
                        .then((response) => {
                            if (response.data.result === 'success') {
                                this.setUserInfo(response.data.userInfo);
                                window.BCookie.set(
                                    'DB-Auth-Email',
                                    response.data.email,
                                    259200,
                                );

                                const {
                                    access_token: accessToken,
                                    expires_in: tokenExpired,
                                } = response.data;

                                this.setAccessToken({
                                    token: accessToken,
                                    expires: tokenExpired,
                                });

                                this.$router.push('/');
                                this.$forceUpdate();
                            } else {
                                console.log('email exist');
                                // this.destroyAccessToken();
                            }
                        })
                        .catch((error) => {
                            if (error.response.status === 422) {
                                if (error.response.data.errors) {
                                    if (error.response.data.errors.email) {
                                        console.log('email exist');
                                    }
                                }
                            }

                            this.destroyAccessToken();
                        });
                }
            });
        },
    },
};
</script>