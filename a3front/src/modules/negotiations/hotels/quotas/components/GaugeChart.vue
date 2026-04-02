<template>
  <div class="chart-card">
    <!-- Título superior -->
    <div class="chart-title">
      <h5>{{ caption }}</h5>
    </div>

    <!-- Contenedor horizontal para las tres secciones -->
    <div class="chart-content">
      <!-- Sección 1: Gráfico -->
      <div class="chart-section">
        <div class="chart-wrapper" :style="sizeStyle">
          <div v-if="loading" class="text-center p-4">
            <span>Cargando...</span>
          </div>
          <div v-else class="gauge-container">
            <apexchart
              :id="id || 'gauge-chart'"
              type="radialBar"
              :height="chartHeight"
              :options="chartOptions"
              :series="series"
            ></apexchart>
            <!-- Overlay para el texto central -->
            <div v-if="total" class="center-label-overlay">
              <div class="center-total">{{ total }}</div>
              <div class="center-text">Total hoteles<br />preferentes</div>
            </div>
          </div>
        </div>
      </div>
      <!-- Sección 2: Estadísticas -->
      <div class="stats-section">
        <div v-for="(item, index) in chartStats" :key="index" class="stat-item">
          <div class="stat-item-1">
            <span class="legend-dot" :style="{ backgroundColor: item.color }"> </span>
            <span class="legend-label">{{ item.label }}</span>
          </div>
          <div class="stat-item-2">
            <div class="stat-value">{{ item.value }} hoteles</div>
            <div class="stat-percentage">{{ item.percentage }}%</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import VueApexCharts from 'vue3-apexcharts';

  export default {
    components: {
      apexchart: VueApexCharts,
    },
    props: {
      data: {
        type: Array,
        default: () => [],
      },
      caption: {
        type: String,
        default: '',
      },
      id: {
        type: String,
        default: '',
      },
      total: {
        type: Number,
        default: 0,
      },
      size: {
        type: String,
        default: '250',
      },
      loading: {
        type: Boolean,
        default: false,
      },
    },
    data() {
      return {
        colors: ['#74A7A3', '#6798E8', '#BBADE4'],
      };
    },
    computed: {
      chartStats() {
        if (!this.data || this.data.length === 0) {
          return [];
        }

        const total = this.data.reduce((sum, item) => sum + parseFloat(String(item.value)), 0);

        return this.data.map((item, index) => {
          const value = parseFloat(String(item.value));
          const percentage = total > 0 ? Math.round((value / total) * 100) : 0;

          return {
            label: item.label,
            value: value,
            percentage: percentage,
            color: this.colors[index] || '#74A7A3',
          };
        });
      },
      sizeStyle() {
        if (!this.size) return {};
        const numericSize = parseFloat(this.size);
        if (!isNaN(numericSize)) {
          return {
            maxWidth: `${numericSize}px`,
            width: `${numericSize}px`,
            height: `${numericSize / 2}px`, // Altura de la mitad para mostrar solo el semicírculo
          };
        }
        return {};
      },
      chartHeight() {
        const numericSize = parseFloat(this.size);
        if (!isNaN(numericSize)) {
          // Altura completa del gráfico (círculo completo)
          return numericSize;
        }
        return 250;
      },
      series() {
        if (!this.data || this.data.length === 0) {
          return [0];
        }

        const total = this.data.reduce((sum, item) => sum + parseFloat(String(item.value)), 0);
        if (total === 0) return [0];

        // Para un gauge semicircular con múltiples segmentos apilados
        // Cada serie representa el porcentaje acumulativo desde el inicio
        // ApexCharts apila las series, así que necesitamos valores acumulativos
        const percentages = [];
        let accumulated = 0;

        this.data.forEach((item) => {
          const value = parseFloat(String(item.value));
          const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
          accumulated += percentage;
          percentages.push(accumulated);
        });

        // Asegurarnos de que el último valor no exceda 100
        if (percentages.length > 0 && percentages[percentages.length - 1] > 100) {
          percentages[percentages.length - 1] = 100;
        }

        return percentages;
      },
      chartOptions() {
        const numericSize = parseFloat(this.size);
        const chartSize = !isNaN(numericSize) ? numericSize : 250;

        // Calcular los colores
        const colors = this.data.map((item, index) => this.colors[index] || '#74A7A3');
        const labels = this.data.map((item) => item.label);

        return {
          chart: {
            type: 'radialBar',
            height: chartSize,
            offsetY: 0,
            sparkline: {
              enabled: false,
            },
          },
          plotOptions: {
            radialBar: {
              startAngle: -90,
              endAngle: 90,
              hollow: {
                size: '60%',
              },
              track: {
                background: '#f0f0f0',
                strokeWidth: '100%',
                margin: 5,
              },
              dataLabels: {
                show: false,
              },
              barLabels: {
                enabled: false,
              },
            },
          },
          colors: colors,
          labels: labels,
          legend: {
            show: false,
          },
          tooltip: {
            enabled: true,
            y: {
              formatter: (val, opts) => {
                const seriesIndex = opts.seriesIndex;
                const dataItem = this.data[seriesIndex];
                if (dataItem) {
                  const value = parseFloat(String(dataItem.value));
                  const total = this.data.reduce(
                    (sum, item) => sum + parseFloat(String(item.value)),
                    0
                  );
                  const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                  return `${value} hoteles (${percentage}%)`;
                }
                return `${val}%`;
              },
            },
          },
          fill: {
            type: 'solid',
          },
          stroke: {
            lineCap: 'round',
          },
        };
      },
    },
  };
</script>

<style lang="scss" scoped>
  @import '@/scss/_variables.scss';

  .chart-card {
    width: 100%;
    display: flex;
    flex-direction: column;
    padding: 10px;
  }

  .chart-title {
    margin-bottom: 20px;
    text-align: left;
  }

  .chart-title h5 {
    font-size: 16px;
    font-weight: bold;
    color: #2f353a;
    margin: 0;
  }

  .chart-content {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 10px;
  }

  .chart-section {
    display: flex;
    justify-content: center;
    flex: 1.5;
  }

  .chart-wrapper {
    position: relative;
    width: 100%;
    max-width: 320px;
    overflow: hidden;
  }

  .gauge-container {
    position: absolute;
    width: 100%;
    height: 200%;
    top: -100%;
    left: 0;
    right: 0;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    overflow: visible;
  }

  .gauge-container :deep(.apexcharts-canvas) {
    position: relative !important;
    width: 100% !important;
    height: 100% !important;
    display: block !important;
  }

  .gauge-container :deep(.apexcharts-svg) {
    width: 100% !important;
    height: 100% !important;
  }

  .center-label-overlay {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    pointer-events: none;
    z-index: 20;
    width: auto;
    min-width: 120px;
  }

  .center-total {
    font-size: 32px;
    font-weight: bold;
    color: #333333;
    line-height: 1.2;
    margin-bottom: 8px;
    text-align: center;
  }

  .center-text {
    font-size: 14px;
    color: #666666;
    line-height: 1.4;
    text-align: center;
    white-space: pre-line;
  }

  .stats-section {
    display: flex;
    flex-direction: column;
    gap: 15px;
    flex: 1;
    justify-content: center;
  }

  .stat-item {
    display: flex;
  }

  .stat-item-1 {
    flex: 1;
  }

  .stat-item-2 {
    flex: 1;
    text-align: right;
  }

  .stat-value {
    font-size: 14px;
    font-weight: 600;
    color: #575b5f;
    margin-bottom: 5px;
  }

  .stat-percentage {
    font-size: 14px;
    color: #7e8285;
    font-weight: 600;
  }

  .legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
  }

  .legend-label {
    font-size: 14px;
    color: #767676;
    font-weight: 400;
    margin-left: 5px;
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    .chart-card {
      padding: 8px;
    }

    .chart-title h5 {
      font-size: 14px;
    }

    .chart-content {
      gap: 8px;
    }

    .chart-wrapper {
      max-width: 280px;
    }

    .center-total {
      font-size: 28px;
    }

    .center-text {
      font-size: 12px;
    }

    .stats-section {
      gap: 12px;
    }

    .stat-value,
    .stat-percentage {
      font-size: 13px;
    }

    .legend-label {
      font-size: 13px;
    }
  }
</style>
