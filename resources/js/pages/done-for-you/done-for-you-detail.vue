<template>
    <v-app>
        <v-container>
            <v-row justify="center" class="search-box-row">
                <v-col cols="8">
                    <done-for-you-header></done-for-you-header>
                </v-col>
            </v-row>
        </v-container>
    </v-app>
</template>

<script>
import DoneForYouHeader from '../../components/DoneForYouHeader';

export default {
    name: 'DoneForYouDetail',
    components: { DoneForYouHeader },
};
</script>

<style scoped></style>
