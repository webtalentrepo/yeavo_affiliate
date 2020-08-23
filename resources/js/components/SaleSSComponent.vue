<template>
    <div>
        <div>
            7 day EPC: ${{ item.seven_day_epc }}
        </div>
        <div>
            30 day EPC: ${{ item.three_month_epc }}
        </div>
        <div>
            30 day Average Sale: ${{ item.p_percent_sale }}
        </div>
        <div class="mt-2">
            Sale: {{ item.sale }}
        </div>
    </div>
</template>

<script>
    export default {
        name: "SaleSSComponent",
        props: ['item']
    }
</script>
