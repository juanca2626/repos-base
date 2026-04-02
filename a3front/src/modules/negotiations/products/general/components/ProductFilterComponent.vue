<template>
  <div>
    <a-input
      class="input-search"
      placeholder="Buscar producto"
      v-model:value="formState.searchTerm"
      @input="handleSearchTerm"
    >
      <template #suffix>
        <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="icon" />
      </template>
    </a-input>
  </div>
</template>
<script setup lang="ts">
  import { useProductFilter } from '@/modules/negotiations/products/general/composables/useProductFilter';

  const { formState, handleSearchTerm } = useProductFilter();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .input-search {
    width: 308px;
    height: 40px;
    border-radius: 4px;
    padding: 8px 16px;
    background: $color-white;
    border: 1px solid $color-black-4;

    .icon {
      color: $color-black-4 !important;
    }

    :deep(.ant-input:hover) {
      box-shadow: none !important;
    }
  }
</style>
