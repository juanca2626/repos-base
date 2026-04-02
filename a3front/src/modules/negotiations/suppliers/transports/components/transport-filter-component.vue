<template>
  <div class="mb-5">
    <div class="filters-container">
      <div>
        <search-input-component v-model="filterState.searchTerm" />
      </div>

      <div class="group-more-filters">
        <tariff-validity-component />

        <add-filter-button-component @addFilter="handleAddFilter" />
      </div>
    </div>

    <transport-filter-form-component v-model:showDrawerForm="showDrawerForm" />
  </div>
</template>
<script setup lang="ts">
  import TariffValidityComponent from '@/modules/negotiations/suppliers/components/partials/tariff-validity-component.vue';
  import AddFilterButtonComponent from '@/modules/negotiations/suppliers/components/partials/add-filter-button-component.vue';
  import SearchInputComponent from '@/modules/negotiations/suppliers/components/partials/search-input-component.vue';
  import TransportFilterFormComponent from '@/modules/negotiations/suppliers/transports/components/transport-filter-form-component.vue';
  import { useTransportFilter } from '@/modules/negotiations/suppliers/transports/composables/transport-filter.composable';

  const { handleAddFilter, showDrawerForm, filterState } = useTransportFilter();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  .filters-container {
    display: flex;
    justify-content: space-between;
  }

  .group-more-filters {
    display: flex;
    gap: 2.5rem;
  }
</style>
