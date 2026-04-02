<template>
  <div class="title-wrapper">
    <div class="configuration-title">
      <h2 class="form-title">
        {{ title }}
      </h2>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { useActiveSectionTitle } from '@/modules/negotiations/products/configuration/navigation/selectors/useActiveSectionTitle';

  const { title } = useActiveSectionTitle();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .title-wrapper {
    position: sticky;
    top: 0;
    z-index: 1;
  }

  .configuration-title {
    background-color: $color-white;

    .form-title {
      font-weight: 600;
      font-size: 24px !important;
      line-height: 28px;
      color: $color-black-dark-2;
    }
  }
</style>
