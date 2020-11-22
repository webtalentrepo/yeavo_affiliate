<template>
    <v-row
        justify="center"
        align="center"
        class="mr-0 ml-0 font-weight-thin page-header-title"
    >
        <img :src="`/assets/menu-icons/${icon}`" alt="" class="header-img" />
        <div class="pt-5 pb-5 ml-3 font-weight-bold">
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
                this.icon = el.headerIcon;
            }
        });
    },
};
</script>

<style scoped lang="scss">
.page-header-title {
    color: #282828;
    font-size: 2rem;

    .header-img {
        width: auto;
        height: 45px;
    }

    @media (max-width: 1024px) {
        font-size: 1.5rem;

        .header-img {
            width: 30px;
            height: 30px;
        }
    }

    @media (max-width: 480px) {
        font-size: 1.25rem;

        .header-img {
            width: 20px;
            height: 20px;
        }
    }
}
</style>
