<template>
  <div>
    <div class="main-header">
      <div class="container-header">
        <div class="left-section">
          <span class="title-form">Tabla Maestra </span>

          <IndicatorCard label="Protegidos" :value="2" variant="warning" icon="shield" />

          <IndicatorCard label="Confirmados" :value="20" variant="success" icon="check" />
        </div>

        <a-button type="primary" class="button-new-service">
          <font-awesome-icon :icon="['fas', 'plus']" />
          Crear nuevo producto
        </a-button>
      </div>
    </div>

    <div class="container-body">
      <PricingPlansFilterComponent />
      <PricingPlansTable />
    </div>
  </div>
</template>

<script lang="ts" setup>
  import PricingPlansFilterComponent from '@/modules/negotiations/products/management/pricing-plans/components/PricingPlansFilterComponent.vue';
  import IndicatorCard from '@/modules/negotiations/products/management/pricing-plans/components/ui/IndicatorCard.vue';
  import PricingPlansTable from '@/modules/negotiations/products/management/pricing-plans/components/PricingPlansTable.vue';
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-body {
    padding: 24px 32px;
  }

  .main-header {
    .container-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 98px;
      padding: 24px 32px;
      border-bottom: 1px solid $color-gray-ligth-4;
    }

    .left-section {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .button-new-service {
      font-size: 16px !important;
      height: 50px;
      width: 245px;
      gap: 8px;
      background: $color-white;
      color: $color-black;
      border: 1px solid $color-black;
      box-shadow: none !important;

      &:hover {
        background: $color-white-2;
        border: 1px solid $color-black;
      }
    }
  }

  .title-form {
    font-weight: 700;
    font-size: 24px;
    color: $color-black;
  }
</style>
