<template>
  <div class="mb-5">
    <div class="filters-container">
      <div class="search-wrapper">
        <search-input-component v-model="filterState.searchTerm" />
      </div>

      <div class="group-more-filters">
        <div class="tariff-wrapper">
          <tariff-validity-component />
        </div>

        <div class="action-wrapper">
          <add-filter-button-component @addFilter="handleAddFilter" />
        </div>
      </div>
    </div>

    <airline-filter-form-component v-model:showDrawerForm="showDrawerForm" />
  </div>
</template>
<script setup lang="ts">
  import TariffValidityComponent from '@/modules/negotiations/suppliers/components/partials/tariff-validity-component.vue';
  import AddFilterButtonComponent from '@/modules/negotiations/suppliers/components/partials/add-filter-button-component.vue';
  import SearchInputComponent from '@/modules/negotiations/suppliers/components/partials/search-input-component.vue';
  import AirlineFilterFormComponent from '@/modules/negotiations/suppliers/airlines/components/airline-filter-form-component.vue';
  import { useAirlineFilter } from '../composables/airline-filter.composable';

  const { handleAddFilter, showDrawerForm, filterState } = useAirlineFilter();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  .filters-container {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px 24px;
    flex-wrap: wrap;
  }

  .group-more-filters {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 16px;
    flex: 1 1 auto;
    min-width: 0;
    flex-wrap: wrap;
  }

  .search-wrapper {
    flex: 0 1 240px;
    max-width: 240px;
    min-width: 190px;
  }

  .tariff-wrapper {
    flex: 0 1 330px;
    max-width: 330px;
    min-width: 240px;
  }

  .action-wrapper {
    flex: 0 0 auto;
  }

  @media (max-width: 1200px) {
    .search-wrapper {
      flex-basis: 220px;
      max-width: 220px;
      min-width: 180px;
    }

    .group-more-filters {
      gap: 12px;
      justify-content: flex-start;
    }

    .tariff-wrapper {
      flex-basis: 300px;
      max-width: 300px;
      min-width: 220px;
    }
  }

  @media (max-width: 900px) {
    .search-wrapper,
    .group-more-filters,
    .tariff-wrapper,
    .action-wrapper {
      width: 100%;
      max-width: none;
      min-width: 0;
    }

    .group-more-filters {
      justify-content: flex-start;
    }
  }
</style>
