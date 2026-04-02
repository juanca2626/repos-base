<template>
  <a-spin
    :spinning="isLoading"
    :tip="isInitialLoading ? 'Cargando datos...' : 'Buscando hoteles...'"
    :delay="0"
  >
    <div>
      <a-row :gutter="16" class="margin-bottom-20">
        <a-col :span="12">
          <h3><strong>Disponibilidad de hoteles</strong></h3>
        </a-col>
        <a-col :span="12" align="end">
          <hotel-add-filter-button-component @addFilter="handleAddFilter" />
        </a-col>
      </a-row>

      <a-row :gutter="[8, 0]" class="margin-bottom-20">
        <a-col :span="24">
          <a-form :model="filters" layout="horizontal" class="inline-form">
            <a-row :gutter="8" align="bottom">
              <a-col :span="5">
                <a-form-item label="Ciudad" :label-col="{ span: 24 }" :wrapper-col="{ span: 24 }">
                  <div
                    class="city-select-wrapper select-wrapper-full"
                    :class="{ 'field-required': isDestinationRequired }"
                  >
                    <v-select
                      :options="destinations"
                      v-model="filters.destination"
                      :placeholder="isLoadingDestinations ? 'Cargando...' : 'Todas'"
                      autocomplete="true"
                      :reduce="(option: any) => option"
                      label="label"
                      :disabled="isLoadingDestinations"
                      :class="{ loading: isLoadingDestinations }"
                      :clearable="false"
                    >
                    </v-select>
                    <div v-if="isLoadingDestinations" class="loading-indicator-bottom">
                      <a-spin size="small" />
                    </div>
                  </div>
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item
                  label="Categoría Interna"
                  :label-col="{ span: 24 }"
                  :wrapper-col="{ span: 24 }"
                >
                  <div class="select-wrapper select-wrapper-full">
                    <v-select
                      :options="internalCategories"
                      v-model="filters.internalCategory"
                      :placeholder="isLoadingCategories ? 'Cargando...' : 'Todas'"
                      autocomplete="true"
                      :disabled="isLoadingCategories"
                      :class="{ loading: isLoadingCategories }"
                    >
                    </v-select>
                    <div v-if="isLoadingCategories" class="loading-indicator-bottom">
                      <a-spin size="small" />
                    </div>
                  </div>
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item label="Conexión" :label-col="{ span: 24 }" :wrapper-col="{ span: 24 }">
                  <div class="select-wrapper select-wrapper-full">
                    <v-select
                      :options="connections"
                      v-model="filters.connection"
                      :placeholder="isLoadingConnections ? 'Cargando...' : 'Todas'"
                      autocomplete="true"
                      :disabled="isLoadingConnections"
                      :class="{ loading: isLoadingConnections }"
                    >
                    </v-select>
                    <div v-if="isLoadingConnections" class="loading-indicator-bottom">
                      <a-spin size="small" />
                    </div>
                  </div>
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item label="Periodo" :label-col="{ span: 24 }" :wrapper-col="{ span: 24 }">
                  <DateRangeCalendar
                    v-model="filters.dateRange"
                    :availability-data="availabilityData"
                    :is-required="isPeriodRequired"
                  >
                  </DateRangeCalendar>
                </a-form-item>
              </a-col>
              <a-col :span="1"></a-col>
              <a-col :span="3">
                <a-form-item :label-col="{ span: 0 }" :wrapper-col="{ span: 24 }">
                  <a-button
                    type="primary"
                    @click="handleSearch"
                    :loading="isLoadingHotelsRooms"
                    block
                  >
                    <template #icon>
                      <font-awesome-icon :icon="['fas', 'search']" />
                    </template>
                    Buscar
                  </a-button>
                </a-form-item>
              </a-col>
            </a-row>
          </a-form>
        </a-col>
      </a-row>
      <a-row :gutter="16" class="margin-bottom-20">
        <a-col :span="24">
          <a-card class="availability-card">
            <div class="availability-summary">
              <div class="summary-header">
                <span class="summary-title">Resumen de disponibilidad</span>
                <div class="total-rooms-section">
                  <span class="total-rooms-label">Total de habitaciones</span>
                  <span class="total-rooms-value">{{ totalRooms }}</span>
                  <a-select
                    v-model:value="roomType"
                    placeholder="Seleccione tipo de habitación"
                    style="width: 200px; margin-left: 16px"
                    allow-clear
                    :loading="isLoadingRoomTypes"
                    @change="handleRoomTypeChange"
                  >
                    <a-select-option
                      v-for="roomTypeOption in roomTypes"
                      :key="roomTypeOption.code"
                      :value="roomTypeOption.code"
                    >
                      {{ roomTypeOption.label }}
                    </a-select-option>
                  </a-select>
                </div>
              </div>
              <div class="legend-section">
                <div class="legend-item legend-available">
                  <span class="legend-text">
                    <span class="legend-value">{{ availableRooms }}</span> Habitaciones disponibles
                  </span>
                </div>
                <div class="legend-item legend-soldout">
                  <span class="legend-text">
                    <span class="legend-value">{{ soldoutRooms }}</span> Habitaciones agotadas
                  </span>
                </div>
                <div class="legend-item legend-blocked">
                  <span class="legend-text">
                    <span class="legend-value">{{ blockedRooms }}</span> Habitaciones bloqueadas
                  </span>
                </div>
              </div>

              <div class="availability-line-chart-wrapper">
                <availability-line-chart
                  :data="chartData"
                  :colors="chartColors"
                  :x-labels="chartXLabels"
                />
              </div>
              <!-- Leyenda con cuadritos de colores para activar/desactivar líneas -->
              <div class="chart-legend">
                <div
                  v-for="(label, index) in chartLabels"
                  :key="`legend-${index}`"
                  class="legend-item"
                  @click="toggleLine(index)"
                >
                  <div
                    class="legend-color-box"
                    :style="{ backgroundColor: chartColors[index] || '#246337' }"
                    :class="{ 'legend-disabled': !isLineVisible(index) }"
                  >
                    <span v-if="isLineVisible(index)" class="legend-check">✓</span>
                  </div>
                  <span class="legend-label">{{ label }}</span>
                </div>
              </div>
            </div>
          </a-card>
        </a-col>
      </a-row>

      <a-row :gutter="16" class="margin-bottom-20">
        <a-col :span="24">
          <div class="view-controls-section">
            <div class="view-controls-left">
              <span class="view-label">Vista:</span>
              <div class="view-buttons-group">
                <view-type-button
                  label="Tabla"
                  :is-active="activeView === 'table'"
                  @click="activeView = 'table'"
                />
                <view-type-button
                  label="Calendario"
                  :is-active="activeView === 'calendar'"
                  @click="handleCalendarViewClick"
                />
              </div>
              <span class="results-text">
                Se encontraron: <strong>{{ totalHotels }}</strong> hoteles /
                <strong>{{ totalRoomsCount }}</strong> habitaciones
              </span>
            </div>
            <div class="view-controls-right">
              <a-button
                class="download-button"
                :loading="isDownloading"
                :disabled="isDownloading"
                @click="handleDownload"
              >
                <template #icon>
                  <IconDownload :width="18" :height="18" />
                </template>
                <span class="download-button-text"> Descargar </span>
              </a-button>
            </div>
          </div>
        </a-col>
      </a-row>

      <hotel-availability-table v-if="activeView === 'table'" />
      <hotel-availability-calendar
        v-if="activeView === 'calendar'"
        :filters="{ value: filters }"
        :hotel-categories="{ value: hotelCategories }"
      />

      <hotel-availability-filter-form-component v-model:showDrawerForm="showDrawerForm" />
    </div>
  </a-spin>
</template>

<script setup lang="ts">
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import vSelect from 'vue-select';
  import 'vue-select/dist/vue-select.css';
  import DateRangeCalendar from '@/modules/negotiations/hotels/quotas/components/DateRangeCalendar.vue';
  import HotelAddFilterButtonComponent from '@/modules/negotiations/hotels/quotas/components/HotelAddFilterButtonComponent.vue';
  import HotelAvailabilityFilterFormComponent from '@/modules/negotiations/hotels/quotas/components/HotelAvailabilityFilterFormComponent.vue';
  import HotelAvailabilityTable from '@/modules/negotiations/hotels/quotas/components/HotelAvailabilityTable.vue';
  import HotelAvailabilityCalendar from '@/modules/negotiations/hotels/quotas/components/HotelAvailabilityCalendar.vue';
  import ViewTypeButton from '@/modules/negotiations/hotels/quotas/components/ViewTypeButton.vue';
  import AvailabilityLineChart from '@/modules/negotiations/hotels/quotas/components/AvailabilityLineChart.vue';
  import { useHotelAvailability } from '@/modules/negotiations/hotels/quotas/composables/useHotelAvailability';
  import { useHotelAvailabilityFilter } from '@/modules/negotiations/hotels/quotas/composables/useHotelAvailabilityFilter';
  import { useHotelAvailabilityPage } from '@/modules/negotiations/hotels/quotas/composables/useHotelAvailabilityPage';
  import IconDownload from '@/modules/negotiations/hotels/quotas/icons/icon-download.vue';
  const {
    filters,
    availabilityData,
    destinations,
    isLoadingDestinations,
    isLoadingCategories,
    isLoadingConnections,
    isLoadingHotelChains,
    isLoadingHotelCategories,
    isLoadingRateTypes,
    isLoadingCalendar,
    internalCategories,
    connections,
    hotelCategories,
    totalRooms,
    roomType,
    roomTypes,
    isLoadingRoomTypes,
    availableRooms,
    soldoutRooms,
    blockedRooms,
    activeView,
    totalHotels,
    totalRoomsCount,
    isLoadingHotelsRooms,
    search,
    handleCalendarViewClick,
  } = useHotelAvailability();

  const { showDrawerForm, handleAddFilter } = useHotelAvailabilityFilter();

  const {
    chartData,
    chartXLabels,
    chartColors,
    chartLabels,
    isInitialLoading,
    isLoading,
    isDestinationRequired,
    isPeriodRequired,
    isDownloading,
    handleSearch,
    handleDownload,
    toggleLine,
    handleRoomTypeChange,
    isLineVisible,
  } = useHotelAvailabilityPage(
    filters,
    search,
    isLoadingCategories,
    isLoadingConnections,
    isLoadingHotelChains,
    isLoadingHotelCategories,
    isLoadingRateTypes,
    isLoadingRoomTypes,
    isLoadingCalendar,
    roomType,
    roomTypes,
    hotelCategories,
    totalRooms,
    availableRooms,
    soldoutRooms,
    blockedRooms,
    activeView
  );
</script>

<style lang="scss" scoped>
  @import '@/scss/_variables.scss';

  .margin-bottom-20 {
    margin-bottom: 20px;
  }

  .inline-form {
    :deep(.ant-form-item) {
      margin-bottom: 0;
    }

    :deep(.ant-form-item-label) {
      padding-bottom: 4px;
      label {
        font-size: 13px;
        font-weight: 500;
      }
    }
  }

  .availability-card {
    :deep(.ant-card) {
      border: 1px solid #babcbd !important;
      background-color: #ffffff !important;
    }

    :deep(.ant-card-body) {
      background-color: #ffffff !important;
      padding: 24px !important;
    }
  }

  .availability-summary {
    .summary-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
    }

    .summary-title {
      font-size: 16px;
      font-weight: 600;
      margin: 0;
      color: $color-black;
    }

    .total-rooms-section {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .availability-line-chart-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      height: 120px;
      margin-top: 24px;
      margin-bottom: 12px;
    }

    .total-rooms-label {
      font-size: 12px;
      font-weight: 400;
      color: $color-black;
    }

    .total-rooms-value {
      font-size: 14px;
      font-weight: 600;
      color: $color-black;
      min-width: 40px;
      text-align: center;
    }

    .legend-section {
      display: flex;
      margin-top: 30px;
      width: 100%;
      gap: 16px;
      justify-content: flex-start;
      align-items: flex-start;
      text-align: left;
    }

    .legend-item {
      display: flex;
      padding-bottom: 8px;

      .legend-text {
        font-size: 12px;
        font-weight: 400;
        color: #2f353a;

        .legend-value {
          font-weight: 600;
          font-size: 20px;
          color: #2f353a;
        }
      }
    }

    .legend-available {
      width: 30%;
      border-bottom: 4px solid #246337;
    }

    .legend-soldout {
      width: 30%;
      border-bottom: 4px solid #a93030;
    }

    .legend-blocked {
      width: 40%;
    }
  }

  .view-controls-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
  }

  .view-controls-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .view-label {
    font-size: 16px;
    font-weight: 400;
    color: $color-black;
  }

  .view-buttons-group {
    display: flex;
  }

  .view-controls-right {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .results-text {
    font-size: 20px;
    font-weight: 500;
    color: $color-black;
  }

  .download-button {
    background-color: #ffffff !important;
    border: 1px solid #2f353a !important;
    color: #2f353a !important;
    padding: 24px;
    &:hover {
      background-color: #f5f5f5 !important;
      border-color: #2f353a !important;
      color: #2f353a !important;
    }

    &:focus {
      background-color: #ffffff !important;
      border-color: #2f353a !important;
      color: #2f353a !important;
    }
  }

  .download-button-text {
    font-size: 16px;
    font-weight: 600;
    color: #2f353a;
    margin-left: 4px;
  }

  .city-select-wrapper,
  .select-wrapper {
    position: relative;
    max-width: 210px;
    width: 100%;

    .loading-indicator-bottom {
      position: absolute;
      top: 50%;
      right: 35px;
      transform: translateY(-50%);
      z-index: 10;
      pointer-events: none;
      opacity: 0.7;
    }

    :deep(.vs__dropdown-toggle) {
      position: relative;
      min-height: 40px;
      height: 40px;
      display: flex;
      align-items: center;
    }

    :deep(.vs__selected-options) {
      min-height: 40px;
      display: flex;
      align-items: center;
    }

    :deep(.vs__search) {
      display: flex;
      align-items: center;
    }

    :deep(.vs__actions) {
      position: relative;
    }

    // Estilos minimalistas para el scrollbar del dropdown
    :deep(.vs__dropdown-menu) {
      // Webkit browsers (Chrome, Safari, Edge)
      &::-webkit-scrollbar {
        width: 8px;
      }

      &::-webkit-scrollbar-track {
        background: #f5f5f5;
        border-radius: 4px;
      }

      &::-webkit-scrollbar-thumb {
        background: #d0d0d0;
        border-radius: 4px;
        transition: background 0.2s;

        &:hover {
          background: #b0b0b0;
        }
      }
    }

    :deep(.loading) {
      .vs__actions {
        &::after {
          content: '';
          position: absolute;
          right: 30px;
          top: 50%;
          transform: translateY(-50%);
          width: 14px;
          height: 14px;
          border: 2px solid #d9d9d9;
          border-top-color: #1890ff;
          border-radius: 50%;
          animation: spin 0.8s linear infinite;
        }
      }
    }

    &.field-required {
      :deep(.vs__dropdown-toggle) {
        border: 1px solid #ff4d4f !important;
        border-radius: 4px;
      }

      :deep(.vs__dropdown-toggle):hover {
        border-color: #ff4d4f !important;
      }

      :deep(.vs__dropdown-toggle):focus,
      :deep(.vs--open .vs__dropdown-toggle) {
        border-color: #ff4d4f !important;
        box-shadow: 0 0 0 2px rgba(255, 77, 79, 0.2) !important;
      }
    }
  }

  .select-wrapper-full {
    max-width: none;
  }

  @keyframes spin {
    to {
      transform: translateY(-50%) rotate(360deg);
    }
  }

  .chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 16px;
    padding: 0;
    justify-content: center;
    align-items: center;
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    user-select: none;
    padding: 4px 0;
    transition: opacity 0.2s;

    &:hover {
      opacity: 0.8;
    }
  }

  .legend-color-box {
    width: 14px;
    height: 14px;
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.2s;
    flex-shrink: 0;

    &.legend-disabled {
      opacity: 0.25;
    }
  }

  .legend-check {
    color: #fff;
    font-size: 10px;
    font-weight: bold;
    line-height: 1;
  }

  .legend-label {
    font-size: 12px;
    color: #2f353a;
    font-weight: 400;
  }

  // Estilos para a-select de Ant Design
  :deep(.ant-select) {
    .ant-select-selector {
      height: 40px !important;
      min-height: 40px !important;
      display: flex !important;
      align-items: center !important;

      .ant-select-selection-item {
        line-height: 1.5715;
        display: flex;
        align-items: center;
      }

      .ant-select-selection-placeholder {
        line-height: 1.5715;
        display: flex;
        align-items: center;
      }

      .ant-select-selection-search {
        display: flex;
        align-items: center;
      }
    }
  }

  // Estilos para inputs en general
  :deep(input[type='text']),
  :deep(input[type='number']),
  :deep(input[type='date']),
  :deep(.ant-input) {
    height: 40px !important;
    min-height: 40px !important;
    line-height: 1.5715 !important;
    padding: 4px 11px !important;
    box-sizing: border-box !important;
  }

  // Estilos para DateRangeCalendar
  :deep(.date-range-calendar-wrapper) {
    max-width: 310px;
    width: 100%;

    .ant-picker-input {
      height: 40px !important;
      min-height: 40px !important;
    }

    .ant-picker-input-input {
      height: 100% !important;
      line-height: 38px !important;
    }
  }

  // Estilos para el botón Buscar
  .inline-form {
    :deep(.ant-btn-primary) {
      height: 48px !important;
      min-height: 48px !important;
      font-weight: 600 !important;
      font-size: 16px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    .filter-section {
      gap: 12px;
    }

    .filter-row {
      gap: 12px;
    }

    .filter-item {
      :deep(.ant-select-selector) {
        height: 38px !important;
        min-height: 38px !important;
      }

      :deep(.ant-select-selection-item) {
        line-height: 36px !important;
        font-size: 13px !important;
      }

      :deep(.ant-select-selection-placeholder) {
        line-height: 36px !important;
        font-size: 13px !important;
      }
    }

    :deep(input[type='text']),
    :deep(input[type='number']),
    :deep(input[type='date']),
    :deep(.ant-input) {
      height: 38px !important;
      min-height: 38px !important;
      font-size: 13px !important;
    }

    :deep(.date-range-calendar-wrapper) {
      max-width: 280px;

      .ant-picker-input {
        height: 38px !important;
        min-height: 38px !important;
      }

      .ant-picker-input-input {
        line-height: 36px !important;
        font-size: 13px !important;
      }
    }

    .inline-form {
      :deep(.ant-btn-primary) {
        height: 42px !important;
        min-height: 42px !important;
        font-size: 14px !important;
      }
    }
  }
</style>
