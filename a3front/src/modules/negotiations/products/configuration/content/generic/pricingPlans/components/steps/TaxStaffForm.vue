<template>
  <a-form layout="vertical" :model="formState">
    <!-- Section 1: Impuestos -->
    <div class="content-card">
      <div class="section-header">
        <span class="section-title">Impuestos</span>
        <span class="section-subtitle">Configure los impuestos aplicables al servicio</span>
      </div>

      <div class="row q-mb-md">
        <div class="checkbox-group">
          <a-checkbox v-model:checked="formState.affectedByIGV"
            >Afecto a IGV (18%)
            <a-tooltip
              placement="topLeft"
              title="Monto considerado en la factura"
              overlayClassName="tooltip-nowrap"
            >
              <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
            </a-tooltip>
          </a-checkbox>
          <a-checkbox v-model:checked="formState.igvRecovery">
            Recuperación IGV
            <a-tooltip
              placement="topLeft"
              title="Información sobre recuperación de IGV"
              overlayClassName="tooltip-nowrap"
            >
              <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
            </a-tooltip>
          </a-checkbox>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <span class="field-section-label q-mb-sm">Otros impuestos:</span>
        </div>
      </div>

      <div class="row q-mb-md">
        <div class="inline-field">
          <span class="inline-label">% de servicios</span>
          <a-input-number
            v-model:value="formState.servicePercentage"
            placeholder="0"
            size="large"
            class="percentage-input"
            :min="0"
            :max="100"
            :formatter="(value: number) => `% ${value}`"
            :parser="(value: string) => value.replace(/\%\s?/g, '')"
          />
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <a-checkbox v-model:checked="formState.additionalPercentage">
            Porcentaje adicional
          </a-checkbox>
        </div>
      </div>

      <!-- Campos adicionales si se marca porcentaje adicional -->
      <template v-if="formState.additionalPercentage">
        <div
          v-for="(item, index) in formState.additionalPercentages"
          :key="index"
          class="row q-mt-md items-center"
        >
          <div class="col-5">
            <div class="row full-width items-center" style="gap: 12px">
              <div class="col-7">
                <a-input
                  v-model:value="item.name"
                  placeholder="Nombre de porcentaje"
                  size="large"
                  class="full-width"
                />
              </div>
              <div class="col-3">
                <a-input-number
                  v-model:value="item.percentage"
                  placeholder="0"
                  size="large"
                  class="full-width"
                  :min="0"
                  :max="100"
                  :formatter="(value: number) => `% ${value}`"
                  :parser="(value: string) => value.replace(/\%\s?/g, '')"
                />
              </div>
              <div class="col-actions flex-center" style="gap: 12px">
                <a-tooltip title="Agregar porcentaje">
                  <svg
                    class="action-icon add-icon"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    @click="addPercentage"
                  >
                    <path
                      d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M12 8V16"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M8 12H16"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </a-tooltip>
                <a-tooltip
                  v-if="formState.additionalPercentages.length > 1"
                  title="Eliminar porcentaje"
                >
                  <font-awesome-icon
                    icon="fa-regular fa-trash-can"
                    style="font-size: 20px; color: #1284ed; cursor: pointer"
                    @click="removePercentage(index)"
                  />
                </a-tooltip>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Section 2: Staff -->
    <div class="content-card">
      <div class="section-header">
        <span class="section-title">Staff</span>
        <span class="section-subtitle">Seleccione el personal requerido para el tour</span>
      </div>

      <div class="row">
        <div class="col-5">
          <a-form-item label="Seleccionar personal:">
            <a-select
              v-model:value="formState.selectedStaff"
              mode="multiple"
              placeholder="Selecciona"
              size="large"
              :options="staffOptions"
              class="full-width"
              :max-tag-count="'responsive'"
            >
              <template #maxTagPlaceholder="omittedValues">
                <span style="color: #2f353a">+ {{ omittedValues.length }}</span>
              </template>
            </a-select>
          </a-form-item>
        </div>
      </div>

      <div v-if="formState.selectedStaff.length > 0" class="row q-mt-md">
        <div class="col-12">
          <span class="field-section-label q-mb-xs">Impuestos para Staff</span>
          <span class="section-subtitle q-mb-md">
            Activa el personal del Staff afecto a impuestos
          </span>

          <div class="staff-tax-table">
            <div class="tax-header">
              <div class="col-name">Tipo</div>
              <div v-if="formState.affectedByIGV" class="col-toggle">IGV (18%)</div>
              <div v-if="formState.igvRecovery" class="col-toggle">% Servicios</div>
              <div v-if="formState.additionalPercentage" class="col-toggle">% Otro</div>
            </div>
            <div v-for="staffId in formState.selectedStaff" :key="staffId" class="tax-row">
              <div class="col-name">{{ getStaffLabel(staffId) }}</div>
              <div v-if="formState.affectedByIGV" class="col-toggle">
                <a-switch v-model:checked="getStaffTax(staffId).igv" />
              </div>
              <div v-if="formState.igvRecovery" class="col-toggle">
                <a-switch v-model:checked="getStaffTax(staffId).services" />
              </div>
              <div v-if="formState.additionalPercentage" class="col-toggle">
                <a-switch v-model:checked="getStaffTax(staffId).other" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </a-form>
</template>

<script setup lang="ts">
  import { watch } from 'vue';
  import { usePricingPlansStore } from '@/modules/negotiations/products/configuration/stores/usePricingPlansStore';

  defineOptions({
    name: 'TaxStaffForm',
  });

  const store = usePricingPlansStore();
  const formState = store.taxAndStaff;

  // Opciones de staff
  const staffOptions = [
    { value: 'guide_representative', label: 'Guía o representante' },
    { value: 'scort_tour_conductor', label: 'Scort o Tour Conductor' },
    { value: 'driver', label: 'Chofer' },
  ];

  const addPercentage = () => {
    formState.additionalPercentages.push({ name: '', percentage: 0 });
  };

  const removePercentage = (index: number) => {
    formState.additionalPercentages.splice(index, 1);
  };

  watch(
    () => formState.additionalPercentage,
    (val) => {
      if (val && formState.additionalPercentages.length === 0) {
        addPercentage();
      }
    }
  );

  const getStaffLabel = (value: string) => {
    const option = staffOptions.find((opt) => opt.value === value);
    return option ? option.label : value;
  };

  const getStaffTax = (staffId: string) => {
    if (!formState.staffTaxes[staffId]) {
      formState.staffTaxes[staffId] = { igv: false, services: false, other: false };
    }
    return formState.staffTaxes[staffId];
  };

  // Exponer el estado del formulario para el componente padre
  defineExpose({
    formState,
  });
</script>

<style lang="scss" scoped>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  .content-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
  }

  .section-header {
    margin-bottom: 16px;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 12px;
  }

  .section-title {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #2f353a;
    margin-bottom: 4px;
  }

  .section-subtitle {
    display: block;
    font-weight: 400;
    font-size: 12px;
    color: #7e8285;
  }

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
    width: 41.66%;
    max-width: 422px;
  }

  .col-3 {
    flex: 0 0 25%;
    width: 25%;
  }

  .full-width {
    width: 100%;
  }

  .checkbox-group {
    display: flex;
    gap: 24px;
    align-items: center;
  }

  .q-mb-sm {
    margin-bottom: 8px;
  }

  .q-mb-md {
    margin-bottom: 16px;
  }

  .q-mt-md {
    margin-top: 16px;
  }

  .info-icon-inline {
    margin-left: 8px;
    color: #2f353a;
    cursor: pointer;
  }

  .field-section-label {
    font-size: 14px;
    font-weight: 500;
    color: #2f353a;
    display: block;
  }

  :deep(.ant-input-number) {
    width: 100%;
  }

  :deep(.ant-input-number-input) {
    text-align: left;
  }

  .inline-field {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .inline-label {
    font-size: 14px;
    font-weight: 500;
    color: #595959;
    white-space: nowrap;
  }

  .percentage-input {
    width: 120px;
  }

  /* Estilos para los tags del multiselect */
  :deep(.ant-select-multiple) {
    .ant-select-selection-item {
      background-color: #dcdcdc;
      border-color: #dcdcdc;
      color: #2f353a;
      border-radius: 4px;
    }

    .ant-select-selection-item-remove {
      color: #2f353a;
    }
  }

  .action-icon {
    font-size: 24px;
    cursor: pointer;
    transition: color 0.3s;
  }

  .add-icon {
    color: #1890ff;
  }

  .add-icon:hover {
    color: #40a9ff;
  }

  .delete-icon {
    color: #ff4d4f;
  }

  .delete-icon:hover {
    color: #ff7875;
  }

  .flex-center {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 40px; /* Match input height */
  }

  .items-center {
    align-items: center;
  }

  .col-7 {
    flex: 0 0 58.33%;
    width: 58.33%;
  }

  .col-8 {
    flex: 0 0 66.66%;
    width: 66.66%;
  }

  .col-3 {
    flex: 0 0 25%;
    width: 25%;
  }

  .col-2 {
    flex: 0 0 16.66%;
    width: 16.66%;
  }

  .col-actions {
    flex: 0 0 50px;
    width: 50px;
  }

  /* Staff Tax Table */
  .staff-tax-table {
    margin-top: 16px;
    border-top: 1px solid #f0f0f0;
  }

  .tax-header {
    display: flex;
    padding: 12px 0;
    font-weight: 500;
    color: #595959;
    border-bottom: 1px solid #f0f0f0;
  }

  .tax-row {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
  }

  .tax-row:last-child {
    border-bottom: none;
  }

  .col-name {
    flex: 2;
    color: #595959;
  }

  .col-toggle {
    flex: 1;
    display: flex;
    color: #595959;
  }

  .q-mb-xs {
    margin-bottom: 4px;
  }
</style>

<style lang="scss">
  /* Global overrides for this component to force red focus */
  .ant-input:focus,
  .ant-input-focused,
  .ant-input:hover,
  .ant-input-number:focus,
  .ant-input-number-focused,
  .ant-input-number:hover,
  .ant-select:not(.ant-select-disabled):hover .ant-select-selector,
  .ant-select-focused:not(.ant-select-disabled).ant-select:not(.ant-select-customize-input)
    .ant-select-selector {
    border-color: #cb202d !important;
    box-shadow: 0 0 0 2px rgba(203, 32, 45, 0.2) !important;
  }

  .ant-checkbox-checked .ant-checkbox-inner {
    background-color: #cb202d;
    border-color: #cb202d;
  }

  .ant-switch-checked {
    background-color: #c63838 !important;
  }

  /* Remove focus outline from icons */
  .action-icon:focus,
  .action-icon:active,
  .action-icon:focus-visible,
  .col-actions *:focus,
  .col-actions *:active,
  .col-actions *:focus-visible,
  .info-icon-inline:focus,
  .info-icon-inline:active,
  .info-icon-inline:focus-visible {
    outline: none !important;
    box-shadow: none !important;
    border: none;
  }

  .tooltip-nowrap .ant-tooltip-inner {
    white-space: nowrap;
    max-width: none;
  }
</style>
