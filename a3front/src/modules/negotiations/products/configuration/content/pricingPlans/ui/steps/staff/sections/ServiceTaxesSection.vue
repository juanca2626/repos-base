<template>
  <div class="content-card">
    <div class="section-header">
      <span class="section-title">Impuestos</span>
      <span class="section-subtitle"> Configure los impuestos aplicables al servicio </span>
    </div>

    <div class="row">
      <div class="tax-row">
        <a-checkbox v-model:checked="formState.taxes.affectedByIGV">
          <span class="mr-2"
            >Afecto a {{ countryTaxSetting.taxCode }} ({{ countryTaxSetting.taxPercentage }}%)</span
          >
          <a-tooltip
            placement="topLeft"
            title="Monto considerado en la factura"
            overlayClassName="tooltip-nowrap"
          >
            <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
          </a-tooltip>
        </a-checkbox>

        <a-input-number
          v-if="formState.taxes.affectedByIGV"
          v-model:value="formState.taxes.igvPercent"
          :min="0"
          :max="100"
          :formatter="(value: number) => `% ${value}`"
          :parser="(value: string) => value.replace('%', '')"
          class="tax-input"
        />
      </div>

      <div class="tax-row">
        <a-checkbox v-model:checked="formState.taxes.igvRecovery">
          <span class="mr-2">Recuperación IGV</span>
          <a-tooltip
            placement="topLeft"
            title="Monto que se recupera como crédito fiscal"
            overlayClassName="tooltip-nowrap"
          >
            <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
          </a-tooltip>
        </a-checkbox>

        <!-- <a-input-number
          v-if="formState.taxes.igvRecovery"
          v-model:value="formState.taxes.igvRecoveryPercent"
          :min="0"
          :max="100"
          :formatter="(value: number) => `% ${value}`"
          :parser="(value: string) => value.replace('%', '')"
          class="tax-input"
        /> -->
      </div>
    </div>
    <hr class="custom-hr" />

    <div class="section-header mt-3">
      <span class="section-title">Otros impuestos:</span>
    </div>

    <div class="row">
      <span class="inline-label">% de servicios</span>

      <a-input-number
        v-model:value="formState.taxes.servicePercentage"
        :min="0"
        :max="100"
        :formatter="(value: number) => `% ${value}`"
        :parser="(value: string) => value.replace('%', '')"
        class="tax-input"
      />
    </div>

    <hr class="custom-hr" />

    <div class="row mt-3">
      <a-checkbox v-model:checked="formState.taxes.additionalPercentage">
        Porcentaje adicional
      </a-checkbox>
    </div>

    <div
      v-if="formState.taxes.additionalPercentage"
      v-for="(item, index) in formState.taxes.additionalPercentages"
      :key="item.id"
      class="row"
    >
      <a-input v-model:value="item.name" placeholder="Nombre porcentaje" />

      <a-input-number
        v-model:value="item.percentage"
        :min="0"
        :max="100"
        :formatter="(value: number) => `% ${value}`"
        :parser="(value: string) => value.replace('%', '')"
        class="tax-input"
      />

      <PlusCircleIcon color="#1284ED" class="add-icon" @click="addPercentage" />

      <TrashIcon
        v-if="formState.taxes.additionalPercentages.length > 1"
        class="delete-icon"
        @click="removePercentage(index as number)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { nanoid } from 'nanoid';
  import {
    PlusCircleIcon,
    TrashIcon,
  } from '@/modules/negotiations/products/configuration/content/pricingPlans/icons';

  interface Props {
    model: any;
    countryTaxSetting: any;
  }

  const props = defineProps<Props>();

  const formState = props.model;
  const countryTaxSetting = props.countryTaxSetting;

  function addPercentage() {
    const id = nanoid();
    formState.taxes.additionalPercentages.push({
      id: `additional_${id}`,
      name: '',
      percentage: null,
    });
  }

  function removePercentage(index: number) {
    formState.taxes.additionalPercentages.splice(index, 1);
  }
</script>

<style scoped lang="scss">
  @import '@/modules/negotiations/products/configuration/content/pricingPlans/ui/styles/stepSections.scss';
  .row {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-top: 16px;
    margin-bottom: 16px;
  }

  .inline-label {
    font-size: 14px;
    font-weight: 500;
    color: #595959;
  }

  :deep(.ant-input-number) {
    width: 120px;
  }

  :deep(.ant-input) {
    width: 220px;
  }

  :deep(.ant-checkbox-wrapper) {
    font-size: 14px;
  }

  .tax-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
  }

  .inline-label {
    font-size: 14px;
    font-weight: 500;
    color: #595959;
  }

  :deep(.ant-input-number) {
    border-radius: 8px;
  }
</style>
