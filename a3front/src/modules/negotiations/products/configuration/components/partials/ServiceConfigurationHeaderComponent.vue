<template>
  <div class="service-configuration-header">
    <div class="header-container-title">
      <span class="custom-title">
        {{ title }}
      </span>
      <div class="custom-tag">
        <span class="text">
          {{ tagText }}
        </span>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { computed } from 'vue';
  import { storeToRefs } from 'pinia';
  import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';

  const configurationStore = useConfigurationStore();
  const { productSummary, supplierSummary } = storeToRefs(configurationStore);

  const title = computed(() => {
    return productSummary.value?.name || 'Cargando...';
  });

  const tagText = computed(() => {
    return supplierSummary.value?.name || 'Cargando...';
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .service-configuration-header {
    height: 80px;

    .header-container-title {
      display: flex;
      align-items: center;
      gap: 24px;
      padding: 0 42px;
      height: 100%;
    }

    .custom-title {
      font-size: 24px;
      font-weight: 700;
    }

    .custom-tag {
      background-color: $color-black-8;
      border-radius: 4px;
      padding: 4px 8px;

      .text {
        font-size: 16px;
        font-weight: 500;
        color: $color-black-dark-2;
      }
    }
  }
</style>
