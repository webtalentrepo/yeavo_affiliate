<template>
    <v-container>
        <v-row justify="center" align="center">
            <v-col cols="6">
                <v-row
                    justify="center"
                    align="center"
                    class="text-align-center pt-10"
                >
                    Welcome
                    {{ user ? user.name : '' }}!<br />
                    Please select a tool from the left sidebar to get started.
                </v-row>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
export default {
    name: 'Index',
    computed: {
        user: {
            get() {
                return this.$store.state.userData;
            },
        },
    },
};
</script>
