<template>
    <v-container>
        <v-row justify="center" align="center">
            <v-col cols="6">
                <v-row
                    justify="center"
                    align="center"
                    class="text-align-center pt-10"
                >
                    Welcome {{ userInfo ? userInfo.name : '' }}!<br />
                    Please select a tool from the left sidebar to get started.
                </v-row>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
export default {
    name: 'Index',
    data: () => ({
        userInfo: null,
    }),
    computed: {
        user: {
            get() {
                return this.$store.state.userData;
            },
        },
    },
    watch: {
        user(newUser, oldUser) {
            if (newUser) {
                this.setUserData(newUser);
                this.$nextTick(() => {
                    if (!oldUser || newUser !== oldUser) {
                        this.$forceUpdate();
                    }
                });
            }
        },
    },
    mounted() {
        console.log(this.userInfo);
    },
    methods: {
        setUserData(newUser) {
            if (newUser && newUser.userInfo) {
                this.userInfo = newUser.userInfo;
            }
        },
    },
};
</script>
