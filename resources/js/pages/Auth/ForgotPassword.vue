<template>
    <ValidationObserver ref="observer">
        <v-container>
            <v-row justify="center">
                <v-col cols="12" md="5" sm="10" xs="12" lg="4" xl="3">
                    <form>
                        <v-row
                            v-if="!sent_email"
                            class="mt-md-10 mt-sm-8 pb-4 blue-grey--text text-center"
                            justify="center"
                        >
                            Fear not. We’ll email you instructions to reset your
                            password.
                        </v-row>

                        <v-row
                            v-if="sent_email"
                            class="mt-md-10 mt-sm-8 pb-4 green--text text-center"
                            justify="center"
                        >
                            Success!<br />
                            We’ve sent an email with password reset
                            instructions.
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

                        <v-row justify="center" align="center">
                            <v-btn class="mr-4" @click="submit">Send</v-btn>
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

export default {
    name: 'ForgotPassword',

    components: {
        ValidationProvider,
        ValidationObserver,
    },

    data: () => ({
        credentials: {
            email: '',
        },
        sent_email: false,
    }),

    methods: {
        submit() {
            this.$refs.observer.validate().then((r) => {
                if (r) {
                    this.$http
                        .post('/send-reset-password-link', {
                            email: this.credentials.email,
                        })
                        .then((response) => {
                            this.sent_email =
                                response.data.result === 'success';
                            if (!this.sent_email) {
                                console.log(response.data);
                            }
                        })
                        .catch((error) => {
                            console.log(error.response.data.message);
                        });
                }
            });
        },
    },
};
</script>

<style scoped></style>
