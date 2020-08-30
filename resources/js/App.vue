<template>
    <v-app v-cloak>
        <users-header v-if="!isAdmin"></users-header>

        <div
            class="app-body d-flex justify-center align-content-center align-center"
        >
            <users-sidebar v-if="!isAdmin"></users-sidebar>

            <v-container class="app-container">
                <router-view></router-view>
            </v-container>
        </div>

        <users-footer v-if="!isAdmin"></users-footer>
    </v-app>
</template>

<script>
import UsersHeader from './layout/users/UsersHeader';
import UsersFooter from './layout/users/UsersFooter';
import UsersSidebar from './layout/users/UsersSidebar';
import { mapGetters, mapMutations } from 'vuex';

export default {
    name: 'App',
    components: { UsersSidebar, UsersFooter, UsersHeader },
    computed: {
        ...mapGetters({
            isAdmin: 'adminCheck',
            isLoggedIn: 'isAuthenticated',
        }),
        user: {
            get() {
                return this.$store.state.userData;
            },
        },
    },
    data() {
        return {
            userId: 0,
            name: '',
            email: '',
            userInfo: null,
        };
    },
    beforeDestroy() {
        window.vEvent.stop('update-user-data', this.updateUserInfo);
    },
    mounted() {
        if (this.isLoggedIn) {
            this.getUserDetail();
        }

        this.eventListen();
    },
    methods: {
        ...mapMutations({
            setUserInfo: 'setUserInfo',
        }),

        /**
         * Components Events Listen.
         */
        eventListen() {
            window.vEvent.listen('update-user-data', this.updateUserInfo);
        },

        async getUserDetail() {
            await this.$store
                .dispatch('getUserData')
                .then((r) => {
                    this.$nextTick(() => {
                        this.setUserData(this.user);
                        this.setUserActive();
                    });
                })
                .catch((e) => {
                    this.$store.commit('destroyAccessToken');
                    this.$router.push('/login');
                });
        },

        setUserData(data) {
            if (data) {
                this.userInfo = data;
                this.userId = this.userInfo.id;
                this.name = this.userInfo.name;
                this.email = this.userInfo.email;
            }
        },
        updateUserInfo(user) {
            this.setUserInfo(user);
        },
    },
    watch: {
        user(newUser, oldUser) {
            this.userInfo = false;
            if (this.isLoggedIn) {
                if (newUser) {
                    this.setUserData(newUser);
                    this.$nextTick(() => {
                        if (!oldUser || newUser !== oldUser) {
                            this.$forceUpdate();
                        }
                    });
                }
            }
        },
    },
};
</script>

<style lang="scss">
html {
    overflow-y: auto !important;
}

[v-cloak] * {
    display: none;
}

.app-sidebar,
.app-container {
    height: calc(100vh - 96px);
    margin-top: 48px;
}

.app-container {
    overflow: auto;
}
</style>
