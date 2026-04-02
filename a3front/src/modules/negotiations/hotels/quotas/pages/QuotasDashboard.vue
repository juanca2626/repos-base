<template>
  <div>
    <a-row :gutter="16" class="margin-bottom-20">
      <a-col :span="24">
        <h3><strong>Dashboard</strong></h3>
      </a-col>
    </a-row>

    <!-- Dos cards con gráficos -->
    <a-row :gutter="16">
      <a-col :span="24" :md="10" class="margin-bottom-20">
        <a-card
          class="chart-card"
          :style="{ backgroundColor: 'white', border: '1px solid #BABCBD', borderRadius: '8px' }"
        >
          <a-spin :spinning="loadingChart1" tip="Cargando...">
            <div class="chart-wrapper">
              <PieChart
                :id="'hotels-chart'"
                :data="hotelsChartData"
                :caption="'Hoteles en el sistema'"
                :total="totalHotels"
                :size="'250'"
                :loading="loadingChart1"
              >
              </PieChart>
            </div>
          </a-spin>
        </a-card>
      </a-col>
      <a-col :span="24" :md="14" class="margin-bottom-20">
        <a-card
          class="chart-card"
          :style="{ backgroundColor: 'white', border: '1px solid #BABCBD', borderRadius: '8px' }"
        >
          <a-spin :spinning="loadingChart2" tip="Cargando...">
            <div class="chart-wrapper">
              <HalfDoughnutChart
                :id="'hotels-chart-2'"
                :data="hotelsChartData2"
                :caption="'Preferentes en el sistema'"
                :total="totalHotels2"
                :size="'250'"
                :loading="loadingChart2"
              >
              </HalfDoughnutChart>
            </div>
          </a-spin>
        </a-card>
      </a-col>
    </a-row>

    <!-- Link a disponibilidad de hoteles -->
    <a-row :gutter="16" class="margin-bottom-20">
      <a-col :span="24" class="availability-link-wrapper">
        <router-link :to="{ name: 'hotelsQuotasHotelAvailability' }">
          <a-button
            type="link"
            style="color: #1284ed; padding: 0; font-weight: 500; font-size: 16px"
          >
            <template #icon>
              <IconBuilding class="margin-right-5" />
            </template>
            Disponibilidad de hoteles
          </a-button>
        </router-link>
      </a-col>
    </a-row>

    <!-- Tabla -->
    <a-row :gutter="16">
      <a-col :span="24">
        <a-table
          :columns="columns"
          :data-source="tableData"
          :pagination="false"
          :loading="loading"
          row-key="id"
          class="custom-table"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'city'">
              {{ record.city }}
            </template>
            <template v-else-if="column.key === 'total_hotels'">
              <span class="total-hotels-span">
                {{ record.total_hotels }}
                <IconSearch
                  class="margin-left-5 cursor-pointer"
                  @click="handleShowDrawerForm(record)"
                />
              </span>
            </template>
            <template v-else-if="column.key === 'hyperguest'">
              <span class="text-center"
                >{{ record.hyperguest }} hoteles ({{ Math.round(record.pct_hyperguest) }}%)</span
              >
            </template>
            <template v-else-if="column.key === 'aurora'">
              <span class="text-center"
                >{{ record.aurora }} hoteles ({{ Math.round(record.pct_aurora) }}%)</span
              >
            </template>
            <template v-else-if="column.key === 'ambos'">
              <span class="text-center"
                >{{ record.ambos }} hoteles ({{ Math.round(record.pct_ambos) }}%)</span
              >
            </template>
          </template>
          <template #emptyText>
            <span class="text-center">No hay datos disponibles</span>
          </template>
        </a-table>

        <!-- Paginador -->
        <div v-if="totalPages > 1" class="margin-top-20">
          <a-pagination
            v-model:current="currentPage"
            :total="totalItems"
            :page-size="itemsPerPage"
            :show-size-changer="false"
            @change="goToPage"
            class="text-center"
          >
          </a-pagination>
        </div>
      </a-col>
    </a-row>

    <HotelDrawerListDashboard
      :showDrawerForm="showDrawerForm"
      @closeDrawerForm="handleCloseDrawer"
      :drawerTitle="drawerTitle"
      :drawerSubTitle="drawerSubTitle"
      :hyperguestData="hyperguestData"
      :auroraData="auroraData"
      :bothData="ambosData"
      :percentageHyperguest="percentageHyperguest"
      :percentageAurora="percentageAurora"
      :percentageAmbos="percentageAmbos"
    />
  </div>
</template>

<script setup lang="ts">
  import { computed, onMounted } from 'vue';
  import PieChart from '@/modules/negotiations/hotels/quotas/components/PieChart.vue';
  import HalfDoughnutChart from '@/modules/negotiations/hotels/quotas/components/HalfDoughnutChart.vue';
  import { useQuotasDashboard } from '@/modules/negotiations/hotels/quotas/composables/useQuotasDashboard';
  import type { TableColumnsType } from 'ant-design-vue';
  import IconBuilding from '@/modules/negotiations/hotels/quotas/icons/icon-building.vue';
  import IconSearch from '@/modules/negotiations/hotels/quotas/icons/icon-search.vue';
  import HotelDrawerListDashboard from '@/modules/negotiations/hotels/quotas/components/HotelDrawerListDashboard.vue';

  const {
    tableData,
    currentPage,
    itemsPerPage,
    totalItems,
    loading,
    hotelsChartData,
    totalHotels,
    loadingChart1,
    hotelsChartData2,
    totalHotels2,
    loadingChart2,
    showDrawerForm,
    hyperguestData,
    auroraData,
    ambosData,
    percentageHyperguest,
    percentageAurora,
    percentageAmbos,
    drawerTitle,
    drawerSubTitle,
    loadData,
    goToPage,
    handleShowDrawerForm,
  } = useQuotasDashboard();

  const totalPages = computed(() => {
    return Math.ceil(totalItems.value / itemsPerPage.value);
  });

  const handleCloseDrawer = () => {
    showDrawerForm.value = false;
  };

  const columns: TableColumnsType = [
    {
      title: 'Ciudad',
      key: 'city',
      dataIndex: 'city',
    },
    {
      title: 'Total de Hoteles',
      key: 'total_hotels',
      dataIndex: 'total_hotels',
      align: 'center',
    },
    {
      title: 'Hyperguest',
      key: 'hyperguest',
      dataIndex: 'hyperguest',
      align: 'center',
    },
    {
      title: 'Aurora',
      key: 'aurora',
      dataIndex: 'aurora',
      align: 'center',
    },
    {
      title: 'Ambos',
      key: 'ambos',
      dataIndex: 'ambos',
      align: 'center',
    },
  ];

  onMounted(() => {
    loadData();
  });
</script>

<style lang="scss" scoped>
  @import '@/scss/_variables.scss';

  .margin-bottom-20 {
    margin-bottom: 20px;
  }

  .margin-top-20 {
    margin-top: 20px;
  }

  .margin-right-5 {
    margin-right: 5px;
  }

  .chart-wrapper {
    min-height: 300px;
  }

  .chart-card {
    :deep(.ant-card) {
      background-color: white !important;
      border: 1px solid #babcbd !important;
      border-radius: 8px !important;
    }
  }

  :deep(.chart-card.ant-card) {
    background-color: white !important;
    border: 1px solid #babcbd !important;
    border-radius: 8px !important;
  }

  .text-center {
    text-align: center;
  }

  .text-right {
    text-align: right;
  }

  .availability-link-wrapper {
    display: flex;
    justify-content: flex-end;
    align-items: center;

    :deep(a) {
      display: inline-block;
    }

    :deep(.ant-btn-link) {
      font-weight: 500 !important;
      font-size: 16px !important;
    }
  }

  .custom-table {
    :deep(.ant-table-thead > tr > th) {
      background-color: #e6ebf2;
      color: #565a5f;
      font-weight: 600;
      border: 1px solid #e4e5e6;
    }

    :deep(.ant-table-tbody > tr > td) {
      color: #565a5f;
      border: 1px solid #e4e5e6;
    }

    :deep(.ant-table-tbody > tr:hover > td) {
      background-color: #f5f7fa;
    }
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    h3 {
      font-size: 20px;
    }

    .chart-wrapper {
      min-height: 250px;
    }

    .availability-link-wrapper {
      :deep(.ant-btn-link) {
        font-size: 14px !important;
      }
    }

    .custom-table {
      :deep(.ant-table-thead > tr > th) {
        font-size: 13px;
        padding: 10px 8px;
      }

      :deep(.ant-table-tbody > tr > td) {
        font-size: 13px;
        padding: 10px 8px;
      }
    }
  }

  .total-hotels-span {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
  }

  .cursor-pointer {
    cursor: pointer;
  }
</style>
