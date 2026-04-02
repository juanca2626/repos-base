<template>
  <div class="tariff-input-row">
    <div class="left-content">
      <span class="field-section-label mr-2"> Ingreso de tarifas: </span>
      <a-radio-group v-model:value="model.typeMode">
        <a-radio value="UNIQUE"> Única </a-radio>
        <a-radio value="RANGE"> Rangos </a-radio>
      </a-radio-group>
    </div>
    <div class="right-content">
      <a-checkbox v-model:checked="model.includeTaxes"> Incluye impuestos en tarifa </a-checkbox>
    </div>
  </div>
</template>

<script setup lang="ts">
  interface Props {
    model: any;
  }
  defineProps<Props>();
</script>
<style scoped lang="scss">
  @import '@/modules/negotiations/products/configuration/content/pricingPlans/ui/styles/price.scss';
  .tariff-input-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    .left-content {
      display: flex;
      align-items: center;
    }
  }
</style>
