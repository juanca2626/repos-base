<template>
  <supplier-filter-form-component
    :showDrawerForm="showDrawerForm"
    @closeDrawerForm="handleClose"
    @cleanFilters="handleCleanFilters"
    @applyFilters="handleApplyFilters"
  >
    <template #body>
      <a-spin :spinning="isLoading">
        <div class="main-container mt-2">
          <a-collapse
            v-model:activeKey="collapseSectionKeys"
            expand-icon-position="end"
            ghost
            class="custom-filter-collapse"
          >
            <template #expandIcon="{ isActive }">
              <font-awesome-icon
                :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']"
              />
            </template>

            <!-- panel hotel chain -->
            <a-collapse-panel key="hotel-chain" class="custom-collapse-panel mt-3">
              <template #header>
                <span class="title-section"> Cadena de hoteles </span>
              </template>

              <div>
                <a-spin :spinning="isLoadingHotelChains" tip="Cargando cadenas de hoteles...">
                  <v-select
                    :options="hotelChains"
                    v-model="filterState.hotelChain"
                    placeholder="Seleccione una cadena de hoteles"
                    autocomplete="true"
                    :disabled="isLoadingHotelChains"
                    class="w-100-select"
                  >
                  </v-select>
                </a-spin>
              </div>
            </a-collapse-panel>

            <!-- panel hotel categories -->
            <a-collapse-panel key="hotel-categories" class="custom-collapse-panel">
              <template #header>
                <span class="title-section"> Categorías en hoteles </span>
              </template>

              <div>
                <a-spin
                  :spinning="isLoadingHotelCategories"
                  tip="Cargando categorías de hoteles..."
                >
                  <a-checkbox-group
                    v-model:value="filterState.hotelCategories"
                    class="custom-filter-checkbox"
                    :disabled="isLoadingHotelCategories"
                  >
                    <div
                      v-for="(category, index) in hotelCategories"
                      :key="`hotel-category-${index}-${category.code}`"
                      class="mb-2 w-100"
                    >
                      <a-checkbox :value="category.code">
                        <span class="checkbox-label">{{ category.label }}</span>
                      </a-checkbox>
                    </div>
                  </a-checkbox-group>
                  <div
                    v-if="isLoadingHotelCategories && hotelCategories.length === 0"
                    class="loading-message"
                  >
                    Cargando categorías de hoteles...
                  </div>
                </a-spin>
              </div>
            </a-collapse-panel>

            <!-- panel rate types -->
            <a-collapse-panel key="rate-types" class="custom-collapse-panel">
              <template #header>
                <span class="title-section"> Tipo de Tarifa </span>
              </template>

              <div>
                <a-spin :spinning="isLoadingRateTypes" tip="Cargando tipos de tarifa...">
                  <a-checkbox-group
                    v-model:value="filterState.rateTypes"
                    class="custom-filter-checkbox"
                    :disabled="isLoadingRateTypes"
                  >
                    <div v-for="rateType in rateTypes" :key="rateType.code" class="mb-2 w-100">
                      <a-checkbox :value="rateType.code">
                        <span class="checkbox-label">{{ rateType.label }}</span>
                      </a-checkbox>
                    </div>
                  </a-checkbox-group>
                  <div v-if="isLoadingRateTypes && rateTypes.length === 0" class="loading-message">
                    Cargando tipos de tarifa...
                  </div>
                </a-spin>
              </div>
            </a-collapse-panel>
          </a-collapse>
        </div>
      </a-spin>
    </template>
  </supplier-filter-form-component>
</template>
<script setup lang="ts">
  import SupplierFilterFormComponent from '@/modules/negotiations/suppliers/components/supplier-filter-form-component.vue';
  import { useHotelAvailabilityFilterForm } from '@/modules/negotiations/hotels/quotas/composables/useHotelAvailabilityFilterForm';
  import { useHotelAvailability } from '@/modules/negotiations/hotels/quotas/composables/useHotelAvailability';
  import vSelect from 'vue-select';
  import 'vue-select/dist/vue-select.css';

  defineProps({
    showDrawerForm: {
      type: Boolean,
      default: false,
    },
  });

  const emit = defineEmits(['update:showDrawerForm']);

  const {
    isLoading,
    collapseSectionKeys,
    filterState,
    handleClose,
    handleCleanFilters,
    handleApplyFilters,
  } = useHotelAvailabilityFilterForm(emit);

  const {
    hotelChains,
    isLoadingHotelChains,
    hotelCategories,
    isLoadingHotelCategories,
    rateTypes,
    isLoadingRateTypes,
  } = useHotelAvailability();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .main-container {
    padding-right: 5px;
  }

  .custom-collapse-panel {
    :deep(.ant-collapse-content-box) {
      display: flex;
      flex-direction: column;
      max-height: 290px !important;
    }
  }

  .custom-filter-checkbox {
    .checkbox-label {
      font-size: 16px;
      font-weight: 400;
      color: $color-black;
    }
  }

  .custom-filter-collapse {
    :deep(.ant-collapse-header) {
      display: flex !important;
      align-items: center !important;
    }
  }

  .title-section {
    font-size: 20px;
    font-weight: 600;
  }

  .w-100 {
    width: 100%;
  }

  .w-100-select {
    width: 100% !important;

    :deep(.vs__dropdown-toggle) {
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
  }

  .loading-message {
    padding: 16px;
    text-align: center;
    color: #999;
    font-size: 14px;
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    .title-section {
      font-size: 18px;
    }

    .custom-filter-checkbox {
      .checkbox-label {
        font-size: 14px;
      }
    }

    .w-100-select {
      :deep(.vs__dropdown-toggle) {
        min-height: 36px;
        height: 36px;
      }

      :deep(.vs__selected-options) {
        min-height: 36px;
      }
    }
  }
</style>
