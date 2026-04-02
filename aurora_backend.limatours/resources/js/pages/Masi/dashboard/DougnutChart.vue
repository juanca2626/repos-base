<script>
import { Doughnut } from 'vue-chartjs'

export default {
  extends: Doughnut,
  props: ['data'],
  data(){
    return {
      data2: []
    }
  },
  mounted () {
    this.data2 = this.getValues(this.data)
    this.renderChart({
      labels: ['Nada satisfecho' , 'Poco satisfecho', 'Neutral', 'Muy satisfecho', 'Totalmente satisfecho'],
      datasets: [
        {
          backgroundColor : ['#fa5563','#fb834f','#fbce3e','#9dd676','#6ac96d'],
          data : this.data2
        }
      ]
    },
    {
      responsive: true,
      maintainAspectRatio: true,
      tooltips: {
        enabled : true,
        mode : 'point',
        intersect : false
      },
      legend: {
          display : true,
          position : 'bottom'
      }
    })
  },
  methods: {
    getValues: function(values) {
      let vM = []
      for (let i = 0; i < 5; i++) {
        vM[i] = 0
        values.forEach(function(v) {
          if(parseInt(v.PESPRE)==(i+1)) vM[i] = parseFloat(v.PERCENT)
        })
      }
      return vM
    }
  },
  watch:{
    data(newVal, oldVal) {
      this.data2 = this.getValues(this.data)
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
  }
}
</script>
