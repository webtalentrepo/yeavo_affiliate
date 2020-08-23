<template>
    <div>
        <div>
            Initial $/Sale: ${{ item.three_month_epc }}
        </div>
        <div>
            Avg $/Sale: ${{ item.seven_day_epc }}
        </div>
        <div class="mt-2">
            Avg %/Sale: {{ item.sale }}
        </div>
    </div>
</template>

<script>
    export default {
        name: "SaleCBComponent",
        props: ['item']
    }
</script>
