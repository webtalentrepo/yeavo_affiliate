<template>
    <div>
        <v-app-bar
            color="#f5f5f5 accent-4"
            dense
            dark
            flat
            absolute
            background-color="#363636"
        >
            <v-toolbar-title>{{ $store.state.siteName }}</v-toolbar-title>

            <v-spacer></v-spacer>

            <div class="px-10">
                <router-link class="header-link" to="/">Home</router-link>
            </div>
            <div class="px-10">Billing Settings</div>
            <div class="px-10">Customer Support</div>
            <div class="px-10">Video Tutorials</div>

            <v-list-item-avatar>
                <v-icon large color="#f0f0f0"
                    >mdi-account-circle-outline
                </v-icon>
            </v-list-item-avatar>
        </v-app-bar>
    </div>
</template>

<script>
export default {
    name: 'UsersHeader',
};
</script>

<style scoped lang="scss">
.header-link {
    color: #f0f0f0;
    text-decoration: none;
}
</style>
