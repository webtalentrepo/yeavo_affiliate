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
                        display: false,
                        gridLines: {
                            color: 'transparent',
                            drawBorder: false,
                        },
                        ticks: {
                            display: false,
                        },
                    },
                ],
                xAxes: [
                    {
                        display: false,
                        gridLines: {
                            color: 'transparent',
                            display: false,
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
    width: 100px;
    height: 45px;

    div {
        height: 45px;
    }
}
</style>
