<template>
  <div class="row">
    <div class="col-5">
      <a-form-item>
        <template #label>
          <span class="label-text">Estatus de tarifa: <span class="required">*</span></span>
        </template>
        <a-select
          v-model:value="model.pricingStatus"
          size="large"
          placeholder="Selecciona"
          :options="options"
        />
      </a-form-item>
    </div>

    <div class="col-6" v-if="isTrainService">
      <a-form-item>
        <template #label>
          <span class="label-text"
            >Selecciona la Frecuencia: <span class="required mr-2">*</span></span
          >
          <a-tooltip placement="topLeft" overlay-class-name="tooltip-nowrap">
            <template #title>
              <span
                ><strong>Creación automática de variación:</strong><br />Al seleccionar frecuencias,
                si quedan algunas sin cubrir, se crearán automáticamente nuevas variaciones para
                incluirlas.<br
              /></span>
              <span
                ><strong>Eliminación automática de variación:</strong><br />Cuando no se selecciona
                ninguna frecuencia y otra variación cubre todas, la variación actual se eliminará
                automáticamente si fue creada por el sistema.</span
              >
            </template>
            <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
          </a-tooltip>
        </template>
        <a-select
          v-model:value="model.frequencies"
          size="large"
          placeholder="Selecciona"
          mode="multiple"
          :options="frequencyOptions"
        />
      </a-form-item>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { StatusTariff } from '@/modules/negotiations/products/configuration/enums/statusTariff.enum';

  interface Props {
    model: any;
    errors?: Record<string, string>;
    isTrainService: boolean;
    frequencyOptions: any[];
  }

  defineProps<Props>();

  const options = [
    { value: StatusTariff.CONFIRMED, label: 'Confirmada' },
    { value: StatusTariff.PROTECTED, label: 'Protegida' },
    { value: StatusTariff.CLOSED, label: 'Cerrada' },
    { value: StatusTariff.FINALIZED, label: 'Finalizada' },
    { value: StatusTariff.DYNAMIC, label: 'Dinámica' },
  ];
</script>

<style scoped>
  @import '@/modules/negotiations/products/configuration/content/pricingPlans/ui/styles/stepSections.scss';

  :deep(.ant-select-selector) {
    border-radius: 8px !important;
    min-height: 40px !important;
  }

  :deep(.ant-select-selection-overflow-item .ant-select-selection-item) {
    background: #dcdcdc !important;
    border-radius: 6px !important;
    color: #2f353a !important;
  }
</style>
