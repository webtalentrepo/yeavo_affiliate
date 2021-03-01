<template>
    <v-app-bar class="no-border-header flex justify-center text-align-center">
        <router-link class="header-link" to="/done-for-you">
            <img src="/assets/menu-icons/home.png" alt="" />
            Main
        </router-link>
        <router-link class="header-link" to="/done-for-you/add-a-worker">
            <img src="/assets/menu-icons/plus.png" alt="" />
            Add A Worker
        </router-link>
        <router-link class="header-link" to="/done-for-you/my-listings">
            <img src="/assets/menu-icons/listings.png" alt="" />
            My Listings
        </router-link>
        <router-link class="header-link" to="/done-for-you/favorites">
            <img src="/assets/menu-icons/heart.png" alt="" />
            Favorites
        </router-link>
    </v-app-bar>
</template>

<script>
export default {
    name: 'DoneForYouHeader',
};
</script>

<style lang="scss">
.no-border-header.v-app-bar.v-toolbar:not(.v-sheet--outlined) {
    box-shadow: none;
    background: transparent;
    border: 2px solid #f3f3f3;
    border-radius: 4px;
    height: 50px !important;

    .v-toolbar__content {
        height: 50px !important;
        display: flex;
        justify-content: space-between;
        text-align: center;
        padding: 0 20px;

        .header-link {
            color: #000 !important;
            text-decoration: none;
            font-size: 18px;
        }
    }
}
</style>
