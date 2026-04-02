<template>
  <div class="mt-4 summary-container">
    <div class="container-title">
      <span class="custom-title"> Información básica </span>
      <span class="btn-edit-form cursor-pointer" @click="handleEdit">
        Editar
        <font-awesome-icon :icon="['fas', 'pen-to-square']" />
      </span>
    </div>
    <div class="mt-2 container-data">
      <div class="container-item">
        <span class="title-item"> Nombre de política: </span>
        <span class="value-item">
          {{ getSanitizedPolicyName() }}
        </span>
      </div>
      <div class="container-item">
        <span class="title-item"> Periodo de vigencia: </span>
        <span class="value-item">
          {{ formatDate(formState.dateFrom) }} a {{ formatDate(formState.dateTo) }}
        </span>
      </div>
      <div class="container-item" v-if="formState.measurementUnit">
        <span class="title-item"> Unidad de medida: </span>
        <span class="value-item">
          {{ getMeasurementUnitLabel() }}
        </span>
      </div>
      <div class="container-item">
        <span class="title-item"> Aplica para: </span>
        <span class="value-item">
          {{ formState.businessGroup?.name }}
        </span>
      </div>
      <div class="container-item" v-if="shouldShowSegmentation()">
        <span class="title-item"> Segmentación de política: </span>
        <span class="value-item">
          {{ formState.segmentationNamesSummary }}
        </span>
      </div>
      <!-- <div class="container-item" v-if="formState.segmentations.length > 0">
        <span class="title-item"> Especificación de segmentación: </span>
        <span class="value-item">
          {{ segmentationsText }}
        </span>
      </div> -->
      <div class="container-item">
        <span class="title-item"> Cantidad de personas permitida: </span>
        <span class="value-item"> De {{ formState.paxMin }} a {{ formState.paxMax }} </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { usePolicyInformationBasicSummaryComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-information-basic-summary.composable';

  const {
    formState,
    handleEdit,
    formatDate,
    getMeasurementUnitLabel,
    getSanitizedPolicyName,
    shouldShowSegmentation,
  } = usePolicyInformationBasicSummaryComposable();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .summary-container {
    border: 1px solid $color-black-4;
    border-radius: 8px;
    padding: 16px;
    gap: 16px;
  }

  .container-title {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .container-data {
    display: block;
    padding-left: 16px;
    gap: 8px;
  }

  .custom-title {
    font-size: 16px;
    font-weight: 600;
    color: $color-black;
  }

  .btn-edit-form {
    font-size: 16px;
    font-weight: 500;
    color: $color-blue;
  }

  .container-item {
    padding-top: 7px;
  }

  .title-item {
    font-weight: 600;
    font-size: 14px;
    line-height: 24px;
    color: $color-black-3;
  }

  .value-item {
    font-weight: 400;
    font-size: 14px;
    line-height: 24px;
    color: $color-black-3;
    padding-left: 2px;
  }
</style>
