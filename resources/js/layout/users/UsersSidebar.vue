<template>
    <v-card class="app-sidebar">
        <v-navigation-drawer
            v-model="drawer"
            :mini-variant.sync="mini"
            permanent
            width="245px"
        >
            <v-list-item class="px-4 py-2">
                <v-spacer v-if="!user"></v-spacer>

                <v-list-item-avatar v-if="user" size="50">
                    <v-img :src="user.profile.image_ext" />
                </v-list-item-avatar>

                <v-list-item-content v-if="user">
                    <v-list-item-title>{{ user.name }}</v-list-item-title>
                    <v-list-item-subtitle>User</v-list-item-subtitle>
                </v-list-item-content>
            </v-list-item>

            <v-list dense class="pt-10">
                <v-list-item
                    v-for="item in items"
                    :key="item.title"
                    link
                    :to="item.link"
                    :class="{ 'locked-item': item.locked }"
                >
                    <v-list-item-icon>
                        <img :src="`/assets/menu-icons/${item.icon}`" alt="" />
                    </v-list-item-icon>

                    <v-list-item-content>
                        <v-list-item-title>{{ item.title }}</v-list-item-title>
                    </v-list-item-content>

                    <v-list-item-icon v-if="item.locked">
                        <v-icon size="18" color="#363636">mdi-lock</v-icon>
                    </v-list-item-icon>
                </v-list-item>

                <v-list-item v-if="user" link to="/logout">
                    <v-list-item-icon>
                        <v-icon size="18" color="#363636">mdi-logout</v-icon>
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

<style lang="scss">
.app-sidebar {
    .v-list-item__content {
        padding: 5px 0 15px;

        .v-list-item__subtitle {
            font-style: italic;
            font-size: 0.75rem;
            color: #363636;
        }
    }
}

.v-list {
    .v-list-item--active {
        position: relative;

        &:before {
            display: none;
        }

        &:not(.locked-item) {
            &:after {
                content: ' ';
                width: 10px;
                height: 10px !important;
                color: #363636;
                background: black;
                border: 0;
                border-radius: 10px;
                line-height: 10px;
                position: absolute;
                top: 20px;
                right: 35px;
                max-height: 10px;
                min-height: 10px;
            }
        }
    }

    .v-list-item {
        padding: 0 16px 0 30px !important;
        height: 50px;

        .v-list-item__icon {
            width: 24px;
            display: flex;
            align-items: center;
            margin-right: 8px;
            height: 32px;
        }
    }
}
</style>
