<template>
    <v-card class="app-sidebar">
        <v-navigation-drawer
            v-model="drawer"
            :mini-variant.sync="mini"
            permanent
        >
            <v-list-item class="px-2">
                <v-spacer></v-spacer>
                <!--                <v-list-item-avatar>-->
                <!--                    <v-icon>mdi-account-circle-outline</v-icon>-->
                <!--                </v-list-item-avatar>-->

                <!--                <v-list-item-title>John Leider</v-list-item-title>-->

                <v-btn icon @click.stop="mini = !mini">
                    <v-icon>mdi-chevron-left</v-icon>
                </v-btn>
            </v-list-item>

            <!--            <v-divider></v-divider>-->

            <v-list dense class="pt-10">
                <v-list-item
                    v-for="item in items"
                    :key="item.title"
                    link
                    :to="item.link"
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
export default {
    name: 'UsersSidebar',
    data: () => ({
        mini: false,
        drawer: true,
        items: [
            { title: 'Home', icon: 'mdi-home-outline', link: '/' },
            {
                title: 'Domain Ideas',
                icon: 'mdi-lightbulb-on-outline',
                link: '/',
            },
            { title: 'Domain Sniper', icon: 'mdi-target', link: '/' },
            { title: 'Offer Scout', icon: 'mdi-tag-outline', link: '/' },
            { title: '? Keyword', icon: 'mdi-search-web', link: '/' },
            { title: 'Viral Content', icon: 'mdi-bomb', link: '/' },
            { title: 'Competitor Spy', icon: 'mdi-incognito', link: '/' },
        ],
        color: 'primary',
        colors: ['primary', 'blue', 'success', 'red', 'teal'],
        permanent: true,
    }),
};
</script>
