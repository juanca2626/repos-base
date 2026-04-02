<template>
  <div class="policy-information-basic-component">
    <!-- <div class="mb-3">
      <a-checkbox v-model:checked="isHotel">Es Hotel?</a-checkbox>
    </div> -->
    <div class="mt-4">
      <div class="section-title">Información básica</div>

      <a-form :model="formState" ref="formRef" :rules="formRules">
        <a-form-item name="name">
          <template v-slot:label>
            <div class="form-label container-name">
              Nombre
              <a-tooltip
                placement="topLeft"
                :overlayInnerStyle="{ width: '300px', padding: '12px' }"
              >
                <template #title>
                  <span class="title-tooltip">Nombre automático</span>
                  <div class="text-content-tooltip mt-1">
                    Se configura con la suma de las selecciones realizadas
                  </div>
                </template>
                <font-awesome-icon :icon="['fa', 'circle-info']" class="icon-info" />
              </a-tooltip>
            </div>
          </template>
          <a-input
            placeholder="Nombre de la política"
            :value="formState.name ? formState.name.replace(/:\s*$/, '').trim() : ''"
            disabled
          />
        </a-form-item>

        <a-form-item name="businessGroupId">
          <template #label>
            <required-label class="form-label" label="Aplica para:" />
          </template>
          <a-select
            placeholder="Selecciona"
            v-model:value="formState.businessGroupId"
            :options="businessGroups"
            @change="handleChangeBusinessGroup"
          />
        </a-form-item>

        <!-- Selector de unidad de medida (solo para Alojamiento o si es Hotel) -->
        <!-- TODO: HOTEL FEATURE - Por el momento se oculta ya que no hay forma de validar si es hotel. Se controla desde el composable. -->
        <a-form-item name="measurementUnit" v-if="showMeasurementUnitSelector">
          <template #label>
            <required-label class="form-label" label="Unidad de medida" />
          </template>
          <a-select
            placeholder="Selecciona"
            v-model:value="formState.measurementUnit"
            :options="measurementUnits"
          />
        </a-form-item>

        <div>
          <div class="mt-3">
            <required-label class="form-label" :label="paxLabel" />
          </div>
          <div class="date-range-container">
            <div class="date-picker-wrapper">
              <a-form-item name="paxMin">
                <template v-slot:label>
                  <div>Desde:</div>
                </template>
                <div>
                  <a-input-number
                    placeholder="0"
                    v-model:value="formState.paxMin"
                    class="w-full"
                    :step="1"
                    :precision="0"
                    :min="1"
                    @change="handleChangePaxMin"
                  />
                </div>
              </a-form-item>
            </div>
            <div class="date-picker-wrapper">
              <a-form-item name="paxMax">
                <template v-slot:label>
                  <div>Hasta:</div>
                </template>
                <div>
                  <a-input-number
                    placeholder="0"
                    v-model:value="formState.paxMax"
                    class="w-full"
                    :step="1"
                    :precision="0"
                    :min="1"
                    @change="handleChangePaxMax"
                  />
                </div>
              </a-form-item>
            </div>
          </div>
        </div>

        <div>
          <div>
            <required-label class="form-label" label="Periodo de vigencia" />
          </div>
          <div class="date-range-container mt-2">
            <div class="date-picker-wrapper">
              <a-form-item name="dateFrom">
                <template v-slot:label>
                  <div>Desde:</div>
                </template>
                <div>
                  <a-date-picker
                    class="w-full"
                    v-model:value="formState.dateFrom"
                    placeholder="DD/MM/AAAA"
                    format="DD/MM/YYYY"
                    value-format="YYYY-MM-DD"
                    :locale="esES"
                    @change="handleChangeDateFrom"
                    @keydown="handleDateInputKeydown('dateFrom', $event)"
                    @blur="handleDateInputBlur('dateFrom', $event)"
                    :disabled-date="disableDateFrom"
                    :max-tag-count="4"
                  >
                  </a-date-picker>
                </div>
              </a-form-item>
            </div>
            <div class="date-picker-wrapper">
              <a-form-item name="dateTo">
                <template v-slot:label>
                  <div>Hasta:</div>
                </template>
                <div>
                  <a-date-picker
                    class="w-full"
                    v-model:value="formState.dateTo"
                    placeholder="DD/MM/AAAA"
                    format="DD/MM/YYYY"
                    value-format="YYYY-MM-DD"
                    :locale="esES"
                    @change="handleChangeDateTo"
                    @keydown="handleDateInputKeydown('dateTo', $event)"
                    @blur="handleDateInputBlur('dateTo', $event)"
                    :disabled-date="disableDateTo"
                  >
                  </a-date-picker>
                </div>
              </a-form-item>
            </div>
          </div>
        </div>

        <a-form-item name="policySegmentationIds" v-if="showPolicySegmentation">
          <template v-slot:label>
            <div class="form-label">Segmentación de política:</div>
          </template>
          <a-select
            ref="segmentationSelectRef"
            placeholder="Selecciona"
            class="custom-select-multiple"
            mode="multiple"
            v-model:value="formState.policySegmentationIds"
            :options="segmentations"
            :max-tag-count="3"
            @change="handleChangeSegmentation"
            :filter-option="filterOption"
            @select="handleSelectSegmentation"
          >
            <template #maxTagPlaceholder="omittedValues">
              <span>+ {{ omittedValues.length }} ...</span>
            </template>
            <template #option="{ value, label }">
              <div class="custom-options-select-multiple">
                <font-awesome-icon
                  :class="[
                    isSelectedSegmentation(value)
                      ? 'icon-color-selected'
                      : 'icon-color-not-selected',
                  ]"
                  :icon="[
                    isSelectedSegmentation(value) ? 'fas' : 'far',
                    isSelectedSegmentation(value) ? 'square-check' : 'square',
                  ]"
                  size="xl"
                />
                <span>
                  {{ label }}
                </span>
              </div>
            </template>
            <template #tagRender="{ label, onClose }">
              <a-tag class="tag-close-option" closable @close="onClose">
                <span>
                  {{ label }}
                </span>
              </a-tag>
            </template>
            <template #menuItemSelectedIcon />
          </a-select>
        </a-form-item>

        <template v-for="(row, index) in formState.segmentationSpecifications">
          <a-form-item
            :name="[
              'segmentationSpecifications',
              index,
              isSeriesSegmentation(row.segmentationId) ? 'inputValue' : 'objectIds',
            ]"
            :rules="
              isSeriesSegmentation(row.segmentationId) ? formRules.inputValue : formRules.objectIds
            "
          >
            <template v-slot:label>
              <div class="form-label">
                Especificación {{ getSegmentationLabel(row.segmentationId) }}:
              </div>
            </template>

            <template v-if="isSeriesSegmentation(row.segmentationId)">
              <a-input
                placeholder="Nombre de serie"
                v-model:value="row.inputValue"
                @change="handleChangeSpecification"
              />
            </template>
            <template v-else>
              <a-select
                :key="`spec-${row.segmentationId}-${(specificationOptionsMap[row.segmentationId] || []).length}`"
                placeholder="Selecciona"
                class="custom-select-multiple"
                mode="multiple"
                v-model:value="row.objectIds"
                :options="specificationOptionsMap[row.segmentationId] || []"
                :max-tag-count="getSpecificationTagCount(row.segmentationId)"
                :filter-option="filterOption"
                :loading="isSpecificationSelectLoading(row.segmentationId)"
                @change="handleChangeSpecification"
              >
                <template v-if="isSpecificationSelectLoading(row.segmentationId)" #notFoundContent>
                  <div style="text-align: center; padding: 12px">
                    <a-spin size="small" />
                  </div>
                </template>

                <template #maxTagPlaceholder="omittedValues">
                  <span>+ {{ omittedValues.length }} ...</span>
                </template>
                <template #option="{ value, label }">
                  <div class="custom-options-select-multiple">
                    <font-awesome-icon
                      :class="[
                        isSelectedObjectId(value, index)
                          ? 'icon-color-selected'
                          : 'icon-color-not-selected',
                      ]"
                      :icon="[
                        isSelectedObjectId(value, index) ? 'fas' : 'far',
                        isSelectedObjectId(value, index) ? 'square-check' : 'square',
                      ]"
                      size="xl"
                    />
                    <span>
                      {{ label }}
                    </span>
                  </div>
                </template>
                <template #tagRender="{ label, onClose }">
                  <a-tag class="tag-close-option" closable @close="onClose">
                    <span>
                      {{ label }}
                    </span>
                  </a-tag>
                </template>
                <template #menuItemSelectedIcon />
              </a-select>
            </template>
          </a-form-item>
        </template>

        <div class="mb-1 mt-1">
          <a-button
            size="large"
            type="primary"
            :loading="loadingButton"
            :disabled="!isFormBasicValid"
            @click="handleSave"
            class="button-action-primary-strong btn-continue"
          >
            Continuar
          </a-button>
        </div>
      </a-form>
    </div>
  </div>
</template>

<script setup lang="ts">
  import esES from 'ant-design-vue/es/date-picker/locale/es_ES';
  import RequiredLabel from '@/modules/negotiations/supplier-new/components/required-label.vue';
  import { usePolicyInformationBasicComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-information-basic.composable';

  defineOptions({
    name: 'PoliciesInformationBasicComponent',
  });

  const {
    formState,
    formRef,
    formRules,
    businessGroups,
    segmentations,
    specificationOptionsMap,
    loadingButton,
    handleSave,
    handleChangeBusinessGroup,
    handleChangePaxMin,
    handleChangePaxMax,
    handleChangeDateFrom,
    handleChangeDateTo,
    handleDateInputBlur,
    handleDateInputKeydown,
    disableDateFrom,
    disableDateTo,
    isSelectedSegmentation,
    isSelectedObjectId,
    handleChangeSegmentation,
    getSegmentationLabel,
    isSeriesSegmentation,
    // getSpecificationData,
    filterOption,
    isSpecificationSelectLoading,
    handleSelectSegmentation,
    getSpecificationTagCount,
    handleChangeSpecification,
    isFormBasicValid,
    measurementUnits,
    showMeasurementUnitSelector,
    paxLabel,
    showPolicySegmentation,
    segmentationSelectRef,
    // isHotel,
  } = usePolicyInformationBasicComposable();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  :deep(.ant-form-item-label > label::after) {
    content: '' !important;
  }

  .container-name {
    display: flex;
    align-items: center;

    .icon-info {
      margin-left: 6px;
    }
  }

  .title-tooltip {
    font-size: 12px;
    font-weight: 500;
    color: #ffffff;
  }

  .text-content-tooltip {
    font-size: 12px;
    font-weight: 400;
    color: #ffffff;
  }

  .btn-continue {
    min-width: 125px;
    font-weight: 600;

    &:disabled {
      background-color: #d9d9d9 !important;
      border-color: #d9d9d9 !important;
      color: rgba(0, 0, 0, 0.25) !important;
      cursor: not-allowed;
      opacity: 0.6;
    }
  }

  .custom-select-multiple {
    gap: 8px;
  }

  .custom-options-select-multiple {
    display: flex;
    gap: 1px;
    align-items: center;

    .icon-color-selected {
      color: $color-primary-strong;
      border-radius: 20px !important;
    }

    .icon-color-not-selected {
      color: $color-gray-ligth;
    }

    span {
      font-weight: 400;
      color: $color-black;
    }
  }

  .tag-close-option {
    background-color: $color-gray-ligth-4;

    span {
      font-size: 14px;
      font-weight: 400;
      color: $color-black;
    }

    :deep(.ant-tag-close-icon) {
      color: $color-black-3 !important;
    }
  }
</style>

<style lang="scss">
  .policy-information-basic-component {
    margin-bottom: 1rem;

    .date-range-container {
      display: flex;
      gap: 20px;
    }

    .date-picker-wrapper {
      width: 201px !important;
    }
  }

  .segmentations {
    .ant-tooltip-arrow:before {
      background: red !important;
    }

    .ant-tooltip-inner {
      background: red !important;
    }
  }
</style>
