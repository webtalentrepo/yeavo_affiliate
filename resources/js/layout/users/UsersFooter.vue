<template>
    <v-bottom-navigation
        horizontal
        fixed
        height="48px"
        background-color="#363636"
        color="#f5f5f5"
        dark
        class="app-footer"
    >
        <div class="px-10">About Us</div>
        <div class="px-10">Contact Us</div>
        <div class="px-10">Privacy Policy</div>
        <div class="px-10">Disclaimer</div>

        <v-spacer></v-spacer>

        <div class="px-5">
            All Rights Reserved
            <v-icon small color="#f5f5f5">mdi-copyright</v-icon>
            {{ new Date().getFullYear() }} - {{ $store.state.siteName }}
        </div>
    </v-bottom-navigation>
</template>

<script>
export default {
    name: 'UsersFooter',
};
</script>

<style scoped lang="scss">
.app-footer {
    align-items: center;
    font-size: 85%;
}
</style>
