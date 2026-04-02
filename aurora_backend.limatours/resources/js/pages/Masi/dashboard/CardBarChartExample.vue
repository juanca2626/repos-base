<script>
import { Bar } from 'vue-chartjs'
import { CustomTooltips } from '@coreui/coreui-plugin-chartjs-custom-tooltips'

export default {
  extends: Bar,
  props: ['height', 'data'],
  mounted () {

    const datasets4 = [
      {
        label: 'Cantidad',
        backgroundColor: 'rgba(255,255,255,.3)',
        borderColor: 'transparent',
        data: this.getValues(this.data) // [78, 81, 80, 45, 34, 12, 40, 75, 34, 89, 32, 68, 54, 72, 18, 98]
      }
    ]
    this.renderChart(
      {
        labels: ["1","2","3","4","5","6","7","8","9","10"],
        datasets: datasets4
      },
      {
        tooltips: {
          enabled: false,
          custom: CustomTooltips
        },
        maintainAspectRatio: false,
        legend: {
          display: false
        },
        scales: {
          xAxes: [{
            display: false,
            categoryPercentage: 1,
            barPercentage: 0.5
          }],
          yAxes: [{
            display: false
          }]
        }
      }
    )
  },
  methods: {
    getValues: function(values) {
      let vM = []
      for (let i = 0; i < 10; i++) {
        vM[i] = 0 
        values.forEach(function(v) {
          if(parseInt(v.PESPRE)==(i+1)) vM[i] = parseFloat(v.CNT)
        })
      }
      return vM
    }
  },
  watch:{
    data(newVal, oldVal) {
      const datasets4 = [
        {
          label: 'Cantidad',
          backgroundColor: 'rgba(255,255,255,.3)',
          borderColor: 'transparent',
          data: this.getValues(newVal) // [78, 81, 80, 45, 34, 12, 40, 75, 34, 89, 32, 68, 54, 72, 18, 98]
        }
      ]
      this.renderChart(
        {
          labels: ["1","2","3","4","5","6","7","8","9","10"],
          datasets: datasets4
        },
        {
          tooltips: {
            enabled: false,
            custom: CustomTooltips
          },
          maintainAspectRatio: false,
          legend: {
            display: false
          },
          scales: {
            xAxes: [{
              display: false,
              categoryPercentage: 1,
              barPercentage: 0.5
            }],
            yAxes: [{
              display: false
            }]
          }
        }
      )  
    }
  }
}
</script>
