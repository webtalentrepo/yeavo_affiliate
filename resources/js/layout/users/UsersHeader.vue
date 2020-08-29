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

            <div class="px-10">Home</div>
            <div class="px-10">Billing Settings</div>
            <div class="px-10">Customer Support</div>
            <div class="px-10">Video Tutorials</div>

            <v-list-item-avatar>
                <v-icon>{{ icons.mdiAccountCircleOutline }}</v-icon>
            </v-list-item-avatar>
        </v-app-bar>
    </div>
</template>

<script>
    import {mdiAccountCircleOutline} from '@mdi/js'

    export default {
        name: "UsersHeader",
        data: () => ({
            icons: {mdiAccountCircleOutline},
        })
    }
</script>

<style scoped>

</style>
