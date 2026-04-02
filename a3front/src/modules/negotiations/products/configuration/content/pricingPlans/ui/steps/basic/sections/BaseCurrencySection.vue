<template>
  <div class="row section-header-row">
    <div class="col-12">
      <label class="field-section-label">
        Moneda base:
        <span class="required mr-2">*</span>
      </label>
    </div>
  </div>

  <div class="row mt-2">
    <div class="col-5">
      <div class="row">
        <div class="col-6">
          <a-form-item>
            <template #label>
              <span>Compra: <span class="required mr-2">*</span></span>
              <a-tooltip
                title="Moneda para tarifario del proveedor"
                placement="topLeft"
                overlay-class-name="tooltip-nowrap"
              >
                <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
              </a-tooltip>
            </template>
            <a-select
              v-model:value="formState.currencyBuy"
              placeholder="Selecciona"
              size="large"
              :options="currencyOptions"
            />
          </a-form-item>
        </div>

        <div class="col-6">
          <a-form-item>
            <template #label>
              <span>Venta: <span class="required mr-2">*</span></span>
              <a-tooltip
                title="Trato con el cliente"
                placement="topLeft"
                overlay-class-name="tooltip-nowrap"
              >
                <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
              </a-tooltip>
            </template>
            <a-select
              v-model:value="formState.currencySell"
              placeholder="Selecciona"
              size="large"
              :options="currencyOptions"
            />
          </a-form-item>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  interface Props {
    model: any;
    errors: any;
  }

  const props = defineProps<Props>();
  const formState = props.model;

  const currencyOptions = [
    { value: 'USD', label: 'Dólar americano - USD' },
    { value: 'EUR', label: 'Euro - EUR' },
    { value: 'GBP', label: 'Libra esterlina - GBP' },
  ];
</script>

<style scoped>
  .content-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
  }

  .row {
    display: flex;
    gap: 24px;
  }

  .col-12 {
    flex: 0 0 100%;
    width: 100%;
  }

  .col-6 {
    flex: 0 0 50%;
  }

  .col-5 {
    flex: 0 0 41.66%;
  }

  .full-width {
    width: 100%;
  }

  .required {
    color: #ff4d4f;
    margin-left: 4px;
  }

  .flex-center {
    display: flex;
    align-items: center;
  }

  .label-text {
    font-weight: 500;
  }

  .field-section-label {
    font-weight: 500;
  }

  .mr-2 {
    margin-right: 8px;
  }

  .mr-3 {
    margin-right: 12px;
  }
</style>
