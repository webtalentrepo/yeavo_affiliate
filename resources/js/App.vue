<template>
    <v-app v-cloak>
        <users-header></users-header>

        <router-view></router-view>
    </v-app>
</template>

<script>
    import UsersHeader from "./layout/users/UsersHeader";

    export default {
        name: "App",
        components: {UsersHeader}
    }
</script>

<style lang="scss">
    html {
        overflow-y: auto !important;
    }

    [v-cloak] * {
        display: none;
    }
</style>
