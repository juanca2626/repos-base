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
          <div v-else class="chart-container">
            <v-chart
              :option="chartOption"
              :style="{ height: chartHeight + 'px', width: chartWidth + 'px' }"
              autoresize
            />
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
        <div
          v-for="(item, index) in chartStats"
          :key="index"
          class="stat-item"
          @click="
            handleChartShowDrawerForm(
              'Hoteles preferentes',
              item.label,
              item.percentage,
              item.details
            )
          "
          style="cursor: pointer"
        >
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
    <HotelDrawerChartDashboard
      :showDrawerForm="showDrawerForm"
      @closeDrawerForm="handleCloseDrawer"
      :drawerIcon="drawerIcon"
      :drawerTitle="drawerTitle"
      :drawerSubTitle="drawerSubTitle"
      :chartDetails="chartDetails"
      :chartPercentage="chartPercentage"
    />
  </div>
</template>

<script setup>
  import { useQuotasDashboard } from '@/modules/negotiations/hotels/quotas/composables/useQuotasDashboard';
  import HotelDrawerChartDashboard from '@/modules/negotiations/hotels/quotas/components/HotelDrawerChartDashboard.vue';
  const {
    showDrawerForm,
    drawerIcon,
    drawerTitle,
    drawerSubTitle,
    chartDetails,
    chartPercentage,
    handleChartShowDrawerForm,
  } = useQuotasDashboard();

  const handleCloseDrawer = () => {
    showDrawerForm.value = false;
  };
</script>

<script>
  import { use } from 'echarts/core';
  import { CanvasRenderer } from 'echarts/renderers';
  import { PieChart } from 'echarts/charts';
  import { TitleComponent, TooltipComponent, LegendComponent } from 'echarts/components';
  import VChart from 'vue-echarts';

  use([CanvasRenderer, PieChart, TitleComponent, TooltipComponent, LegendComponent]);

  export default {
    components: {
      VChart,
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
            details: item.details,
          };
        });
      },
      sizeStyle() {
        if (!this.size) return {};
        const numericSize = parseFloat(this.size);
        if (!isNaN(numericSize)) {
          return {
            maxWidth: `${numericSize * 1.5}px`,
            width: `${numericSize * 1.5}px`,
            height: `${numericSize}px`, // Altura completa
          };
        }
        return {};
      },
      chartHeight() {
        // const numericSize = parseFloat(this.size);
        // if (!isNaN(numericSize)) {
        //   return numericSize; // Altura igual al tamaño
        // }
        return 350;
      },
      chartWidth() {
        // const numericSize = parseFloat(this.size);
        // if (!isNaN(numericSize)) {
        //   return numericSize * 1.5; // Aumentar el ancho del gráfico
        // }
        return 1000;
      },
      chartOption() {
        if (!this.data || this.data.length === 0) {
          return {};
        }

        const chartData = this.data.map((item, index) => ({
          value: parseFloat(String(item.value)),
          name: item.label,
          itemStyle: {
            color: this.colors[index] || '#74A7A3',
          },
        }));

        return {
          tooltip: {
            trigger: 'item',
            formatter: (params) => {
              return `${params.name}: ${params.value} hoteles (${params.percent}%)`;
            },
          },
          series: [
            {
              type: 'pie',
              radius: ['55%', '70%'],
              center: ['50%', '50%'], // Centrado normal
              startAngle: 180, // Comienza desde la izquierda
              endAngle: 0, // Termina en la derecha (semicírculo superior)
              avoidLabelOverlap: false,
              itemStyle: {
                borderRadius: 5,
                borderColor: '#fff',
                borderWidth: 0,
              },
              label: {
                show: false,
              },
              emphasis: {
                label: {
                  show: false,
                },
              },
              data: chartData,
            },
          ],
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
    justify-content: flex-start;
    gap: 20px;
  }

  .chart-section {
    display: flex;
    justify-content: center;
    flex: 0 0 auto;
  }

  .chart-wrapper {
    position: relative;
    width: 100%;
    max-width: 480px;
    height: 100%;
  }

  .chart-container {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: flex-start;
    justify-content: center;
  }

  .center-label-overlay {
    position: absolute;
    top: 35%;
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

  // Media query para pantallas menores a 1290px
  @media (max-width: 1290px) {
    .chart-card {
      padding: 8px;
    }

    .chart-title h5 {
      font-size: 14px;
    }

    .chart-content {
      gap: 15px;
    }

    .chart-wrapper {
      max-width: 400px;
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

    .chart-content {
      gap: 8px;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    .stats-section {
      width: 100%;
      padding: 0px 30px;
    }
  }
</style>
