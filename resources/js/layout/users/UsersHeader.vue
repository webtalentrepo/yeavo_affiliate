<template>
    <div class="users-header">
        <v-app-bar color="#363636" dense dark flat absolute>
            <!--            <v-toolbar-title class="app-logo"-->
            <!--                >{{ $store.state.siteName }}-->
            <!--                <span class="sub-title">{{ $store.state.siteSubName }}</span>-->
            <!--            </v-toolbar-title>-->

            <v-spacer></v-spacer>

            <div class="px-10">
                <router-link class="header-link" to="/">Home</router-link>
            </div>
            <div class="px-10">Billing Settings</div>
            <div class="px-10">Customer Support</div>
            <div class="px-10">Video Tutorials</div>

            <v-list-item-avatar>
                <v-img v-if="!user" src="/assets/menu-icons/user.png" alt="" />

                <!--                <v-img v-else :src="user.profile.image_ext" />-->
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
