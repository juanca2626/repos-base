<template>
  <div v-if="formState.tariffType === TariffType.SPECIFIC">
    <div class="row">
      <div class="col-5">
        <a-form-item label="Segmentación de tarifa:">
          <a-select
            v-model:value="formState.tariffSegmentation"
            class="custom-multi-select"
            mode="multiple"
            placeholder="Selecciona"
            size="large"
            :options="segmentationOptions"
          />
        </a-form-item>
      </div>
    </div>
    <div class="row" v-if="formState.tariffSegmentation.length > 0">
      <div class="col-5">
        <a-form-item label="Especificación mercados:">
          <a-select
            v-model:value="formState.specificMarkets"
            class="custom-multi-select"
            mode="multiple"
            placeholder="Selecciona"
            size="large"
            :options="marketsOptions"
          />
        </a-form-item>
      </div>
    </div>
    <div class="row" v-if="formState.specificMarkets.length > 0">
      <div class="col-5">
        <a-form-item label="Especificación clientes:">
          <a-select
            v-model:value="formState.specificClients"
            class="custom-multi-select"
            mode="multiple"
            placeholder="Selecciona"
            size="large"
            :options="clientsOptions"
          />
        </a-form-item>
      </div>
    </div>
    <div class="row" v-if="formState.specificClients.length > 0">
      <div class="col-5">
        <a-form-item label="Especificación series:">
          <a-input
            type="text"
            v-model:value="formState.specificSeries"
            placeholder="Nombre de serie"
            size="large"
          />
        </a-form-item>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';

  interface Props {
    model: any;
    errors: any;
    segmentationOptions: any;
    marketsOptions: any;
    clientsOptions: any;
  }

  const props = defineProps<Props>();
  const formState = props.model;
</script>

<style scoped>
  .row {
    display: flex;
    gap: 24px;
  }

  .col-12 {
    flex: 0 0 100%;
    width: 100%;
  }

  .col-5 {
    flex: 0 0 41.66%;
  }

  :deep(.custom-multi-select .ant-select-selector) {
    border-radius: 8px !important;
    border: 1px solid #e5e7eb !important;
    padding: 2px 8px !important;
    min-height: 44px !important;
    display: flex;
    align-items: center;
  }

  /* Tags */
  :deep(.custom-multi-select .ant-select-selection-item) {
    background-color: #dcdcdc !important;
    border-radius: 6px !important;
    padding: 1px 8px !important;
    font-size: 14px !important;
    border: none !important;
    color: #2f353a !important;
    font-weight: 400 !important;
    height: 25px !important;
    align-items: center !important;
  }

  /* X pequeña */
  :deep(.custom-multi-select .ant-select-selection-item-remove) {
    color: #7e8285 !important;
    font-size: 12px !important;
  }

  /* Hover del tag */
  :deep(.custom-multi-select .ant-select-selection-item:hover) {
    background-color: #d1d5db !important;
  }

  /* Flecha */
  :deep(.custom-multi-select .ant-select-arrow) {
    color: #6b7280 !important;
  }

  /* Focus */
  :deep(.custom-multi-select.ant-select-focused .ant-select-selector) {
    border-color: #1677ff !important;
    box-shadow: 0 0 0 2px rgba(22, 119, 255, 0.1) !important;
  }
</style>
