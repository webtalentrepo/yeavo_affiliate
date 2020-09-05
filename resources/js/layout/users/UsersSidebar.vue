<template>
    <v-card class="app-sidebar">
        <v-navigation-drawer
            v-model="drawer"
            :mini-variant.sync="mini"
            permanent
            width="245px"
        >
            <v-list-item class="px-2">
                <v-spacer v-if="!user"></v-spacer>

                <v-list-item-avatar v-if="user">
                    <v-img :src="user.profile.image_ext" />
                </v-list-item-avatar>

                <v-list-item-title v-if="user"
                    >{{ user.name }}
                </v-list-item-title>

                <v-btn icon @click.stop="mini = !mini">
                    <v-icon>mdi-chevron-left</v-icon>
                </v-btn>
            </v-list-item>

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

                <v-list-item v-if="user" link to="/logout">
                    <v-list-item-icon>
                        <v-icon>mdi-logout</v-icon>
                    </v-list-item-icon>

                    <v-list-item-content>
                        <v-list-item-title>Log Out</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>
    </v-card>
</template>

<script>
import menuItems from '../../router/menuItem';

export default {
    name: 'UsersSidebar',
    data: () => ({
        mini: false,
        drawer: true,
        color: 'primary',
        colors: ['primary', 'blue', 'success', 'red', 'teal'],
        permanent: true,
    }),
    computed: {
        user: {
            get() {
                return this.$store.state.userData;
            },
        },

        items: {
            get() {
                return menuItems;
            },
        },
    },
};
</script>
