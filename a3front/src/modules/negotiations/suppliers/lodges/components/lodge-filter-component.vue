<template>
  <div class="mb-5 supplier-common-filter">
    <div class="filters-container">
      <div>
        <search-input-component v-model="filterState.searchTerm" />
      </div>

      <div class="group-more-filters">
        <tariff-validity-component />

        <add-filter-button-component @addFilter="handleAddFilter" />
      </div>
    </div>

    <lodge-filter-form-component v-model:showDrawerForm="showDrawerForm" />
  </div>
</template>
<script setup lang="ts">
  import TariffValidityComponent from '@/modules/negotiations/suppliers/components/partials/tariff-validity-component.vue';
  import AddFilterButtonComponent from '@/modules/negotiations/suppliers/components/partials/add-filter-button-component.vue';
  import SearchInputComponent from '@/modules/negotiations/suppliers/components/partials/search-input-component.vue';
  import LodgeFilterFormComponent from '@/modules/negotiations/suppliers/lodges/components/lodge-filter-form-component.vue';
  import { useLodgeFilter } from '@/modules/negotiations/suppliers/lodges/composables/lodge-filter.composable';

  const { handleAddFilter, showDrawerForm, filterState } = useLodgeFilter();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierList.scss';
</style>
