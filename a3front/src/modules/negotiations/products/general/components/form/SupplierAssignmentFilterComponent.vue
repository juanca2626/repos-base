<template>
  <div class="filter-container">
    <div class="filter-row">
      <div class="filter-row-left">
        <a-select
          v-model:value="formState.supplierClassificationId"
          placeholder="Tipo de proveedor"
          :options="supplierClassifications"
          show-search
          class="filter-select"
          size="large"
          :filter-option="filterOption"
          allow-clear
          @change="handleChangeClassification"
        />

        <a-select
          v-model:value="formState.locationKey"
          placeholder="Ciudad"
          :options="locations"
          show-search
          size="large"
          class="filter-select"
          :filter-option="false"
          allow-clear
          @search="handleSearchCity"
          @change="handleChangeLocation"
        >
          <template #option="option">
            <div class="location-option">
              <span class="main-text">
                {{ option.city.name }}
              </span>
              <span class="second-text"> {{ option.state.name }}, {{ option.country.name }} </span>
            </div>
          </template>

          <template v-if="searchCityLoading" #notFoundContent>
            <a-spin size="small" />
          </template>
        </a-select>
      </div>
      <a-input
        v-model:value="formState.searchTerm"
        size="large"
        class="input-search"
        placeholder="Buscar proveedor"
        @input="handleInputSearchTerm"
      >
        <template #suffix>
          <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="icon" />
        </template>
      </a-input>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { useSupplierAssignmentFilter } from '@/modules/negotiations/products/general/composables/form/useSupplierAssignmentFilter';

  const {
    supplierClassifications,
    locations,
    formState,
    searchCityLoading,
    handleSearchCity,
    filterOption,
    handleChangeClassification,
    handleChangeLocation,
    handleInputSearchTerm,
  } = useSupplierAssignmentFilter();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .filter-container {
    margin-top: 20px;

    .filter-row {
      display: flex;
      align-items: center;
      justify-content: space-between;

      .filter-row-left {
        display: flex;
        gap: 12px;
      }
    }
  }

  .filter-select {
    width: 200px;

    :deep(.ant-select-selector) {
      border-radius: 4px;
    }

    :deep(.ant-select-selector:hover) {
      box-shadow: none !important;
    }

    :deep(.ant-select-selection-placeholder) {
      font-size: 14px;
    }

    :deep(.ant-select-selection-item) {
      font-size: 14px;
    }

    :deep(.ant-select-selection-search-input) {
      font-size: 14px;
    }
  }

  :deep(.filter-select.ant-select) {
    border-radius: 4px;
  }

  :deep(.filter-select.ant-select:hover) {
    box-shadow: none !important;
  }

  .location-option {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
    padding-top: 6px;
    padding-bottom: 6px;

    .main-text {
      color: $color-black;
      font-weight: 600;
      font-size: 14px;
    }

    .second-text {
      color: $color-black-3;
      font-weight: 400;
      font-size: 12px;
    }
  }

  .input-search {
    width: 300px;
    height: 40px;
    border-radius: 4px;
    padding: 8px 16px;
    background: $color-white;

    :deep(.ant-input::placeholder) {
      font-size: 14px;
    }

    :deep(.ant-input) {
      font-size: 14px !important;
    }

    .icon {
      color: $color-black-4 !important;
    }

    :deep(.ant-input:hover) {
      box-shadow: none !important;
    }
  }
</style>
