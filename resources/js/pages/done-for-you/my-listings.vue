<template>
    <v-app>
        <v-container>
            <v-row justify="center" class="search-box-row">
                <v-col cols="8">
                    <done-for-you-header></done-for-you-header>
                </v-col>
            </v-row>
            <PageHeader :icons="false"></PageHeader>
        </v-container>
    </v-app>
</template>

<script>
import DoneForYouHeader from '../../components/DoneForYouHeader';
import PageHeader from '../../layout/users/PageHeader';

export default {
    name: 'MyListings',
    components: { PageHeader, DoneForYouHeader },
};
</script>

<style scoped></style>
