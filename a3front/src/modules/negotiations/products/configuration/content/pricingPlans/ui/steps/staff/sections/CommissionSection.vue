<template>
  <div class="content-card">
    <div class="section-header">
      <span class="section-title">Comisiones</span>
    </div>

    <div class="row">
      <span class="inline-label"> % Comisión </span>

      <a-tooltip
        placement="topLeft"
        title="Porcentaje de comisión aplicado al servicio"
        overlayClassName="tooltip-nowrap"
      >
        <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
      </a-tooltip>

      <a-input-number
        v-model:value="model.commissions.percentage"
        :min="0"
        :max="100"
        :formatter="(value: number) => `% ${value}`"
        :parser="(value: string) => value.replace('%', '')"
        class="tax-input"
      />
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
  @import '@/modules/negotiations/products/configuration/content/pricingPlans/ui/styles/stepSections.scss';

  .row {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .inline-label {
    font-size: 14px;
    font-weight: 500;
    color: #595959;
  }

  :deep(.ant-input-number) {
    width: 120px;
  }
</style>
