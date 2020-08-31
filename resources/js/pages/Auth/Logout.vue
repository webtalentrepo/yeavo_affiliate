<template>
    <div></div>
</template>

<script>
export default {
    name: 'Logout',
    created() {
        this.$store.state.userData = null;
        this.$store.dispatch('destroyToken').then(() => {
            // console.log(response);
            window.location.replace('/login');
        });
    },
};
</script>
