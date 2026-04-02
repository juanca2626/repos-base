<template>
  <span class="title-form">
    {{ listTitle }}
  </span>
</template>
<script setup lang="ts">
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { listTitle } = useSupplier();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .title-form {
    font-weight: 700;
    font-size: 24px;
    line-height: 32px;
    color: $color-black;
  }
</style>
