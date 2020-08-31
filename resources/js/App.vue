<template>
    <v-app v-cloak>
        <users-header v-if="!isAdmin" :user="userInfo"></users-header>
        <!--        <users-header v-if="!userInfo" :user="userInfo"></users-header>-->

        <div
            class="app-body d-flex justify-center align-content-center align-center"
        >
            <users-sidebar v-if="!isAdmin" :user="userInfo"></users-sidebar>

            <v-container class="app-container">
                <router-view :user="userInfo"></router-view>
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
    data() {
        return {
            userId: 0,
            name: '',
            email: '',
            userInfo: null,
        };
    },
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
                .then(() => {
                    // console.log(r);
                    this.$nextTick(() => {
                        this.setUserData(this.user);
                    });
                })
                .catch((e) => {
                    console.log(e);
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

                this.updateUserInfo(data);
                this.$forceUpdate();
            }
        },
        updateUserInfo(user) {
            this.setUserInfo(user);
        },
    },
};
</script>

<style lang="scss">
html {
    overflow: hidden !important;

    *:not(i) {
        font-family: 'Poppins', sans-serif;
    }
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

@font-face {
    font-family: 'LEADvilleASTROnaut System';
    src: url('../sass/fonts/LEADvilleASTROnautSystem.eot');
    src: url('../sass/fonts/LEADvilleASTROnautSystem.eot?#iefix')
            format('embedded-opentype'),
        url('../sass/fonts/LEADvilleASTROnautSystem.woff2') format('woff2'),
        url('../sass/fonts/LEADvilleASTROnautSystem.woff') format('woff'),
        url('../sass/fonts/LEADvilleASTROnautSystem.ttf') format('truetype'),
        url('../sass/fonts/LEADvilleASTROnautSystem.svg#LEADvilleASTROnautSystem')
            format('svg');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

.app-logo {
    font-family: 'LEADvilleASTROnaut System', sans-serif;
    font-weight: normal;
    font-style: normal;
}

.text-align-center {
    text-align: center;
}
</style>
