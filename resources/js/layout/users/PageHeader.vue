<template>
    <v-row
        justify="center"
        align="center"
        class="mr-0 ml-0 text-lg-h3 text-md-h4 text-sm-h5 font-weight-thin page-header-title"
    >
        <img :src="`assets/icons/${icon}`" alt="" class="header-img" />
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

    .header-img {
        width: 45px;
        height: 45px;
    }

    @media (max-width: 1024px) {
        .header-img {
            width: 30px;
            height: 30px;
        }
    }

    @media (max-width: 480px) {
        .header-img {
            width: 20px;
            height: 20px;
        }
    }
}
</style>
