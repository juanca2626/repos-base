<template>
  <div class="mb-5">
    <div class="filters-container">
      <div>
        <search-input-component />
      </div>

      <div class="group-more-filters">
        <add-filter-button-component />
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
  import SearchInputComponent from '@/modules/negotiations/suppliers/components/partials/search-input-component.vue';
  import AddFilterButtonComponent from '@/modules/negotiations/suppliers/components/partials/add-filter-button-component.vue';
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
