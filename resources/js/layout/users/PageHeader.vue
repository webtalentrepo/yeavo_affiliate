<template>
    <v-row
        justify="center"
        align="center"
        class="mr-0 ml-0 text-lg-h3 text-md-h4 text-sm-h5 font-weight-thin page-header-title"
    >
        <v-icon size="3rem" color="#282828">{{ icon }}</v-icon>
        <div class="pt-5 pb-5 ml-3">
            {{ $route.name }}
        </div>
    </v-row>
</template>

<script>
import menuItems from '../../router/menuItem';

export default {
    name: 'PageHeader',
    data: () => ({
        icon: '',
    }),
    computed: {
        items: {
            get() {
                return menuItems;
            },
        },
    },
    mounted() {
        this.items.map((el) => {
            if (el.title === this.$route.name) {
                this.icon = el.icon;
            }
        });
    },
};
</script>

<style scoped lang="scss">
.page-header-title {
    color: #282828;

    @media (max-width: 480px) {
        .v-icon {
            font-size: 1.2rem !important;
        }
    }

    @media (max-width: 1024px) {
        .v-icon {
            font-size: 2rem !important;
        }
    }
}
</style>
