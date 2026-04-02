<template>
    <div>
        <fusioncharts
            v-if="!loading"
            :ref="id"
            :type="type"
            :width="width"
            :height="height"
            :dataformat="dataFormat"
            :datasource="dataSource">
        </fusioncharts>
    </div>
</template>

<script>
    export default {
        props: ['data', 'caption', 'id'],
        data(){
            return {
                loading: true,
                type: 'pie2d',
                width: '100%',
                height: '400',
                dataFormat: 'json',
                dataSource: {
                    chart: {
                        caption: 'Testing',
                        showLegend: "1",
                        showPercentValues: "1",
                        legendPosition: "bottom",
                        useDataPlotColorForLabels: "1",
                        enableMultiSlicing: "0",
                        theme: "fusion",
                        showlegend: "0",
                        exportEnabled: '1'
                    },
                    data: []
                },
                data2: []
            }
        },
        mounted () {

        },
        created () {
            let vm = this
            vm.loading = false

            setTimeout(() => {
                vm.dataSource.data = vm.getValues(vm.data)
                vm.dataSource.chart.caption = vm.caption
            }, 10)
        },
        methods: {
            getValues: function(values) {
                let vM = []
                values.forEach((item, i) => {
                    vM[i] = {
                        label: item.LABEL,
                        value: item.VALUE
                    }
                })
                return vM
            }
        },
        watch:{
            /*
            data2(newVal, oldVal) {
                this.data2 = this.getValues(this.data2)
                this.renderChart(
                    {
                        labels: ['Nada satisfecho', 'Poco satisfecho', 'Neutral', 'Muy satisfecho', 'Totalmente satisfecho'],
                        datasets: [
                            {
                                backgroundColor: ['#fa5563','#fb834f','#fbce3e','#9dd676','#6ac96d'],
                                data: this.data2
                            }
                        ]
                    },
                    {
                        responsive: true,
                        maintainAspectRatio: true,
                        tooltips: {
                            enabled: true,
                            mode: 'point',
                            intersect: false
                        },
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                )

            }
            */
        }
    }
</script>
