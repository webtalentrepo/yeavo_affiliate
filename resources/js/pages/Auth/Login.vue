<template>
    <ValidationObserver ref="observer">
        <v-container class="login-container">
            <v-row justify="center">
                <v-col cols="12" md="5" sm="10" xs="12" lg="4" xl="3">
                    <form>
                        <v-row
                            class="mt-md-10 mt-sm-8 pb-4 blue-grey--text ml-0 mr-0"
                            justify="center"
                        >
                            Please Log In, or Sign Up to Get Started
                        </v-row>

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
                                autofocus
                                required
                            ></v-text-field>
                        </ValidationProvider>

                        <ValidationProvider
                            v-slot="{ errors }"
                            name="Password"
                            rules="required"
                        >
                            <v-text-field
                                v-model="credentials.password"
                                :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
                                :type="show1 ? 'text' : 'password'"
                                :error-messages="errors"
                                label="Password"
                                solo
                                required
                                @click:append="show1 = !show1"
                            ></v-text-field>
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
                                >Log In
                            </v-btn>
                        </v-row>

                        <v-row
                            class="mt-md-10 mt-sm-8 blue-grey--text ml-0 mr-0"
                            justify="center"
                        >
                            <span>Don't have an account,</span>
                            <router-link
                                class="ml-1 blue-grey--text text-decoration-underline"
                                to="/register"
                                >Sign Up Here
                            </router-link>
                        </v-row>

                        <v-row
                            justify="center"
                            align="center"
                            class="mt-md-10 mt-sm-8 ml-0 mr-0"
                        >
                            <router-link
                                class="ml-1 blue-grey--text text-decoration-underline"
                                to="/forgot-password"
                                >Forgot Password?
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
    name: 'Login',
    components: {
        ValidationProvider,
        ValidationObserver,
    },
    data: () => ({
        credentials: {
            email: '',
            password: '',
            remember_me: false,
        },
        show1: false,
    }),
    mounted() {
        if (window.BCookie.get('DB-Auth-Email')) {
            this.credentials.email = window.BCookie.get('DB-Auth-Email');
            window.BCookie.remove('DBAccessToken');
        }

        if (
            window.Ls.get('DB-Auth-Remember') &&
            window.Ls.get('DB-Auth-Remember') === 'true'
        ) {
            this.credentials.remember_me = true;
        }
    },
    methods: {
        ...mapMutations({
            destroyAccessToken: 'destroyAccessToken',
        }),

        ...mapActions({
            retrieveToken: 'retrieveToken',
            getUserData: 'getUserData',
        }),

        submit() {
            this.$refs.observer
                .validate()
                .then((r) => {
                    if (r) {
                        this.retrieveToken({ ...this.credentials }).then(() => {
                            // console.log(response);
                            this.$nextTick(() => {
                                this.getUserData()
                                    .then(() => {
                                        window.Ls.remove('DB-Auth-Remember');
                                        this.$router.push({
                                            name: 'Home',
                                        });
                                        this.$forceUpdate();
                                    })
                                    .catch((e) => {
                                        // this.destroyAccessToken();
                                        console.log(e);
                                    });
                            });
                        });
                    }
                })
                .catch((e) => {
                    console.log('e', e);
                });
        },
        clear() {
            this.$refs.observer.reset();
        },

        /**
         * Remember me
         */
        rememberMe() {
            if (this.credentials.remember_me) {
                window.Ls.set('DB-Auth-Remember', 'true');
            } else {
                window.Ls.set('DB-Auth-Remember', 'false');
            }
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
