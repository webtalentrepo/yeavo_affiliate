<template>
    <v-row
        justify="center"
        align="center"
        class="mr-0 ml-0 text-h3 font-weight-thin page-header-title"
    >
        <v-icon size="3rem" color="#282828">{{ icon }}</v-icon>
        <div class="pt-8 pb-8 ml-3">
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
}
</style>
