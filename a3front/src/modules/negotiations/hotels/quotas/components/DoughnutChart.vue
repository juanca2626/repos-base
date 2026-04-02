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
        <div
          class="chart-wrapper"
          :class="[sizeClass, { 'half-circle-wrapper': halfCircle }]"
          :style="sizeStyle"
        >
          <div
            v-if="halfCircle"
            class="half-circle-chart-container"
            :style="halfCircleContainerStyle"
          >
            <Doughnut v-if="!loading" ref="chart" :data="chartData" :options="chartOptions">
            </Doughnut>
          </div>
          <template v-else>
            <Doughnut v-if="!loading" ref="chart" :data="chartData" :options="chartOptions">
            </Doughnut>
          </template>
          <div v-if="loading" class="text-center p-4">
            <span>Cargando...</span>
          </div>
          <!-- Overlay para el texto central -->
          <div
            v-if="total && !loading"
            class="center-label-overlay"
            :class="{ 'half-circle': halfCircle }"
          >
            <div class="center-total">{{ total || 1550 }}</div>
            <div class="center-text">
              <template v-if="halfCircle">Total hoteles<br />preferentes</template>
              <template v-else>Total hoteles<br />cargados</template>
            </div>
          </div>
        </div>
      </div>
      <!-- Sección 3: Estadísticas -->
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
  import { Doughnut } from 'vue-chartjs';
  import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

  ChartJS.register(ArcElement, Tooltip, Legend);

  export default {
    components: {
      Doughnut,
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
        default: '',
      },
      halfCircle: {
        type: Boolean,
        default: false,
      },
    },
    data() {
      return {
        loading: true,
        colors: ['#74A7A3', '#6798E8', '#BBADE4'],
        chartData: {
          labels: [],
          datasets: [
            {
              backgroundColor: ['#74A7A3', '#6798E8', '#BBADE4'],
              data: [],
            },
          ],
        },
        chartOptions: {},
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
      sizeClass() {
        if (!this.size) return '';
        // Si es un tamaño predefinido (small, medium, large)
        if (['small', 'medium', 'large'].includes(this.size.toLowerCase())) {
          return `chart-size-${this.size.toLowerCase()}`;
        }
        return '';
      },
      sizeStyle() {
        if (!this.size) return {};
        // Si es un número, asumimos que es píxeles
        const numericSize = parseFloat(this.size);
        if (!isNaN(numericSize)) {
          return {
            maxWidth: `${numericSize}px`,
            width: `${numericSize}px`,
            height: this.halfCircle ? `${numericSize / 2}px` : `${numericSize}px`,
          };
        }
        return {};
      },
      halfCircleContainerStyle() {
        if (!this.halfCircle || !this.size) return {};
        const numericSize = parseFloat(this.size);
        if (!isNaN(numericSize)) {
          return {
            height: `${numericSize * 2}px`,
            width: `${numericSize}px`,
          };
        }
        return {};
      },
    },
    mounted() {
      this.loading = false;
      this.chartOptions = this.getChartOptions();
      setTimeout(() => {
        this.updateChart();
      }, 10);
    },
    watch: {
      data: {
        handler() {
          this.updateChart();
        },
        deep: true,
      },
      total() {
        this.updateChart();
      },
      caption() {
        this.updateChart();
      },
      halfCircle() {
        this.chartOptions = this.getChartOptions();
        this.updateChart();
      },
    },
    methods: {
      updateChart() {
        if (this.data && this.data.length > 0) {
          const chartDataResult = this.getChartData(this.data);

          // Asegurar que tenemos suficientes colores
          const colorsArray = ['#74A7A3', '#6798E8', '#BBADE4'];

          // Crear un nuevo objeto en lugar de mutar el existente para evitar bucles infinitos
          this.chartData = {
            labels: chartDataResult.labels,
            datasets: [
              {
                backgroundColor: colorsArray.slice(0, chartDataResult.labels.length),
                data: chartDataResult.values,
                borderAlign: 'inner',
                borderRadius: 10,
              },
            ],
          };
        }
      },
      getChartData(values) {
        const labels = [];
        const valuesArray = [];
        values?.forEach((item) => {
          labels.push(item.label);
          valuesArray.push(parseFloat(String(item.value)));
        });
        return { labels, values: valuesArray };
      },
      getChartOptions() {
        const baseOptions = {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false,
            },
            tooltip: {
              enabled: true,
            },
          },
          cutout: '75%',
        };

        // Si es semicírculo, agregar rotation y circumference para mostrar la parte superior (arcoíris)
        if (this.halfCircle) {
          baseOptions.rotation = -Math.PI / 2;
          baseOptions.circumference = Math.PI;
        }

        return baseOptions;
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
  }

  .chart-wrapper.half-circle-wrapper {
    overflow: hidden;
    position: relative;
  }

  .half-circle-chart-container {
    position: absolute;
    top: -100%;
    left: 0;
    right: 0;
    display: flex;
    align-items: flex-end;
    justify-content: center;
  }

  .half-circle-chart-container :deep(canvas) {
    width: 100% !important;
    height: 100% !important;
    display: block !important;
  }

  .chart-size-small {
    max-width: 200px !important;
    width: 200px !important;
    height: 200px !important;
  }

  .chart-size-medium {
    max-width: 320px !important;
    width: 320px !important;
    height: 320px !important;
  }

  .chart-size-large {
    max-width: 450px !important;
    width: 450px !important;
    height: 450px !important;
  }

  .center-label-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
    z-index: 10;
    width: auto;
    min-width: 120px;
  }

  .center-label-overlay.half-circle {
    top: auto;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 20;
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

  .chart-wrapper :deep(canvas) {
    width: 100% !important;
    height: 100% !important;
  }

  .legend-section {
    display: flex;
    flex-direction: column;
    gap: 15px;
    flex: 1;
    padding: 0 20px;
    justify-content: center;
    align-items: flex-start;
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
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

    .chart-size-small {
      max-width: 180px !important;
      width: 180px !important;
      height: 180px !important;
    }

    .chart-size-medium {
      max-width: 280px !important;
      width: 280px !important;
      height: 280px !important;
    }

    .chart-size-large {
      max-width: 380px !important;
      width: 380px !important;
      height: 380px !important;
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
