<template>
    <v-card class="app-sidebar">
        <v-navigation-drawer
            v-model="drawer"
            :mini-variant.sync="mini"
            permanent
        >
            <v-list-item class="px-2">
                <v-list-item-avatar>
                    <v-icon>{{ icons.mdiAccountCircleOutline }}</v-icon>
                </v-list-item-avatar>

                <v-list-item-title>John Leider</v-list-item-title>

                <v-btn
                    icon
                    @click.stop="mini = !mini"
                >
                    <v-icon>mdi-chevron-left</v-icon>
                </v-btn>
            </v-list-item>

            <v-divider></v-divider>

            <v-list dense class="pt-10">
                <v-list-item
                    v-for="item in items"
                    :key="item.title"
                    link
                >
                    <v-list-item-icon>
                        <v-icon>{{ item.icon }}</v-icon>
                    </v-list-item-icon>

                    <v-list-item-content>
                        <v-list-item-title>{{ item.title }}</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>
    </v-card>
</template>

<script>
    import {mdiAccountCircleOutline} from '@mdi/js'

    export default {
        name: "UsersSidebar",
        data: () => ({
            icons: {mdiAccountCircleOutline},
            mini: false,
            drawer: true,
            items: [
                {title: 'Home', icon: 'mdi-view-dashboard'},
                {title: 'Domain Ideas', icon: 'mdi-image'},
                {title: 'Domain Sniper', icon: 'mdi-image'},
                {title: 'Offer Scout', icon: 'mdi-image'},
                {title: '? Keyword', icon: 'mdi-image'},
                {title: 'Viral Content', icon: 'mdi-image'},
                {title: 'Competitor Spy', icon: 'mdi-image'},
            ],
            color: 'primary',
            colors: [
                'primary',
                'blue',
                'success',
                'red',
                'teal',
            ],
            permanent: true,
        })
    }
</script>

<style scoped>

</style>
