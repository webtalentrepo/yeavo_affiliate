<template>
    <div class="keywords-trends">
        <line-chart :chart-data="chartData" :options="options"></line-chart>
    </div>
</template>

<script>
import LineChart from '../utils/LineChart';

export default {
    name: 'KeywordTrends',
    components: { LineChart },
    props: {
        chartData: {
            type: Object,
            // eslint-disable-next-line vue/require-valid-default-prop
            default: {},
        },
    },
    data: () => ({
        options: {
            responsive: true,
            maintainAspectRatio: false,
            tooltip: {
                position: 'right',
                mode: 'index',
                intersect: false,
                yPadding: 1,
                xPadding: 2,
                titleMarginBottom: 1,
                caretSize: 0,
                borderWidth: 0,
            },
            legend: {
                display: false,
            },
            scales: {
                yAxes: [
                    {
                        gridLines: {
                            color: 'transparent',
                        },
                        ticks: {
                            display: false,
                        },
                    },
                ],
                xAxes: [
                    {
                        gridLines: {
                            color: 'transparent',
                        },
                        ticks: {
                            display: false,
                        },
                    },
                ],
            },
        },
    }),
};
</script>

<style scoped lang="scss">
.keywords-trends {
    width: 100%;
    height: 70px;

    div {
        height: 50px;
    }
}
</style>
