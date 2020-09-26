<template>
    <ValidationObserver ref="observer">
        <v-container class="login-container">
            <v-row justify="center">
                <v-col cols="12" md="6" sm="12" lg="5" xl="4">
                    <form>
                        <v-row
                            class="mt-md-10 mt-sm-8 pb-2 red--text ml-0 mr-0"
                            justify="center"
                        >
                            <v-col
                                v-if="invalid_account"
                                cols="12"
                                class="text-align-center font-weight-black"
                            >
                                Invalid Activation Token!
                            </v-col>
                        </v-row>
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

                        <v-text-field
                            v-model="credentials.email"
                            label="Email"
                            solo
                            readonly
                        ></v-text-field>

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
                                        <router-link
                                            to="/privacy"
                                            class="blue-grey--text"
                                            target="_blank"
                                            >Privacy Policy
                                        </router-link>
                                    </div>
                                </template>
                            </v-checkbox>
                        </ValidationProvider>

                        <v-row
                            justify="center"
                            align="center"
                            class="ml-0 mr-0"
                        >
                            <v-btn
                                class="mr-4"
                                light
                                color="white"
                                @click="submit"
                                >Activate
                            </v-btn>
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
    name: 'Activate',
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
            token: '',
        },
        invalid_account: false,
    }),

    created() {
        this.credentials.token = this.$route.params.token;

        this.getEmailAndName();
    },

    methods: {
        ...mapMutations({
            setAccessToken: 'setAccessToken',
            setUserInfo: 'setUserInfo',
            destroyAccessToken: 'destroyAccessToken',
        }),

        ...mapActions({
            registerUser: 'registerUser',
            getEmailByCode: 'getEmailByCode',
        }),

        getEmailAndName() {
            this.getEmailByCode(this.credentials.token).then((r) => {
                this.credentials.name = r.data.name;
                this.credentials.email = r.data.email;
            });
        },

        submit() {
            this.$refs.observer.validate().then((r) => {
                if (r) {
                    this.registerUser({ ...this.credentials })
                        .then((response) => {
                            if (response.data.result === 'success') {
                                const {
                                    email,
                                    accessToken,
                                    expiresIn,
                                    isAdmin,
                                    userInfo,
                                } = response.data;

                                window.BCookie.set(
                                    'DB-Auth-Email',
                                    email,
                                    259200,
                                );

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
<style lang="scss" scoped>
.login-container {
    *:not(i) {
        font-weight: 100;
    }
}
</style>
