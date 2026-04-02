<template>
  <a-input class="input-search" placeholder="Buscar proveedor o código" v-model:value="inputSearch">
    <template #suffix>
      <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
    </template>
  </a-input>
</template>
<script setup lang="ts">
  const inputSearch = defineModel<string>();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .input-search {
    width: min(100%, 308px);
    max-width: 100%;
    min-width: 0;
    height: 48px;
    border-radius: 4px;
    padding: 12px 16px;
    background: $color-white;
    border: 1px solid $color-black-2;
  }

  @media (max-width: 1200px) {
    .input-search {
      width: min(100%, 240px);
    }
  }

  @media (max-width: 992px) {
    .input-search {
      width: min(100%, 210px);
    }
  }

  @media (max-width: 768px) {
    .input-search {
      width: 100%;
    }
  }
</style>
