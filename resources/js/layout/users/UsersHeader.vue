<template>
    <div class="users-header">
        <v-app-bar
            color="grey darken-3"
            dense
            dark
            flat
            absolute
        >
            <v-toolbar-title class="app-logo"
                >{{ $store.state.siteName }}
            </v-toolbar-title>

            <v-spacer></v-spacer>

            <div class="px-10">
                <router-link class="header-link" to="/">Home</router-link>
            </div>
            <div class="px-10">Billing Settings</div>
            <div class="px-10">Customer Support</div>
            <div class="px-10">Video Tutorials</div>

            <v-list-item-avatar>
                <v-icon v-if="!user" large color="#f0f0f0"
                    >mdi-account-circle-outline
                </v-icon>

                <v-img v-if="user" :src="user.profile.image_ext" />
            </v-list-item-avatar>
        </v-app-bar>
    </div>
</template>

<script>
export default {
    name: 'UsersHeader',
    computed: {
        user: {
            get() {
                return this.$store.state.userData;
            },
        },
    },
};
</script>

<style scoped lang="scss">
.header-link {
    color: #f0f0f0;
    text-decoration: none;
}

.users-header {
    font-size: 85%;

    .v-toolbar__title {
        font-size: 1rem;
    }
}
</style>
