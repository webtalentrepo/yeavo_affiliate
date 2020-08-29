<template>
    <v-app v-cloak>
        <users-header v-if="!isAdmin"></users-header>

        <div class="app-body d-flex justify-center align-content-center align-center">
            <users-sidebar v-if="!isAdmin"></users-sidebar>

            <v-container class="app-container">
                <router-view></router-view>
            </v-container>
        </div>

        <users-footer v-if="!isAdmin"></users-footer>
    </v-app>
</template>

<script>
    import UsersHeader from "./layout/users/UsersHeader";
    import UsersFooter from "./layout/users/UsersFooter";
    import UsersSidebar from "./layout/users/UsersSidebar";
    import {mapGetters} from 'vuex';

    export default {
        name: "App",
        components: {UsersSidebar, UsersFooter, UsersHeader},
        computed: {
            ...mapGetters({
                isAdmin: 'adminCheck'
            }),
            user: {
                get() {
                    return this.$store.state.userData;
                }
            }
        },
    }
</script>

<style lang="scss">
    html {
        overflow-y: auto !important;
    }

    [v-cloak] * {
        display: none;
    }

    .app-sidebar, .app-container {
        height: calc(100vh - 96px);
        margin-top: 48px;
    }

    .app-container {
        overflow: auto;
    }
</style>
