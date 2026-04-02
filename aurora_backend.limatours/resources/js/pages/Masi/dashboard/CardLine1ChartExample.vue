<script>
import { Line } from 'vue-chartjs'
import { CustomTooltips } from '@coreui/coreui-plugin-chartjs-custom-tooltips'
import { getStyle } from '@coreui/coreui/dist/js/coreui-utilities'

export default {
  extends: Line,
  props: ['height', 'width', 'historial'],
  mounted () {
    
    const brandPrimary = getStyle('--primary') || '#20a8d8'

    const datasets1 = [
      {
        label: 'Encuestas realizadas',
        backgroundColor: brandPrimary,
        borderColor: 'rgba(255,255,255,1)',
        data: this.valueByMonth(this.historial) // [65, 59, 84, 84, 51, 55, 40, 45]
      }
    ]

    this.renderChart(
      {
        labels: ['Abril', 'Mayo', 'Junio', 'Julio', 'Agosto'],
        datasets: datasets1
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
          xAxes: [
            {
              gridLines: {
                color: 'transparent',
                zeroLineColor: 'transparent'
              },
              ticks: {
                fontSize: 2,
                fontColor: 'transparent'
              }
            }
          ],
          yAxes: [
            {
              display: false,
              ticks: {
                display: false,
                min: Math.min.apply(Math, datasets1[0].data) - 5,
                max: Math.max.apply(Math, datasets1[0].data) + 5
              }
            }
          ]
        },
        elements: {
          line: {
            borderWidth: 1
          },
          point: {
            radius: 4,
            hitRadius: 10,
            hoverRadius: 4
          }
        }
      }
    )
  },
  methods: {
    valueByMonth: function(values) {
      const vM = []
      values.forEach(function(v,k) {
        vM.push(v.CNT)
      })
      vM.unshift(0, 0, 0)
      return vM 
    }
  },
  watch:{
    historial(newVal, oldVal) {

      const brandPrimary = getStyle('--primary') || '#20a8d8'
      
      const datasets1 = [
        {
          label: 'Encuestas realizadas',
          backgroundColor: brandPrimary,
          borderColor: 'rgba(255,255,255,1)',
          data: this.valueByMonth(newVal) // [65, 59, 84, 84, 51, 55, 40, 45]
        }
      ]

      this.renderChart(
        {
          labels: ['Abril', 'Mayo', 'Junio', 'Julio', 'Agosto'],
          datasets: datasets1
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
            xAxes: [
              {
                gridLines: {
                  color: 'transparent',
                  zeroLineColor: 'transparent'
                },
                ticks: {
                  fontSize: 2,
                  fontColor: 'transparent'
                }
              }
            ],
            yAxes: [
              {
                display: false,
                ticks: {
                  display: false,
                  min: Math.min.apply(Math, datasets1[0].data) - 5,
                  max: Math.max.apply(Math, datasets1[0].data) + 5
                }
              }
            ]
          },
          elements: {
            line: {
              borderWidth: 1
            },
            point: {
              radius: 4,
              hitRadius: 10,
              hoverRadius: 4
            }
          }
        }
      )  
    }
  }
}
</script>
