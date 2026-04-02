<template>
  <ConfigurationDrawer
    :show-drawer-form="showDrawerForm"
    :supplier-original-id="supplierOriginalId"
    :product-supplier-id="productSupplierId"
    title="Configuración del servicio"
    :is-loading="isLoading"
    :step-number="stepNumber"
    :cancel-button-text="cancelButtonText"
    :next-button-text="nextButtonText"
    :is-next-button-disabled="isNextButtonDisabled"
    @close="handleClose"
    @cancel="handleCancelGoBack"
    @next="handleGoToConfiguration"
  >
    <a-form layout="vertical">
      <a-alert
        v-if="placeOperations.length === 0"
        type="warning"
        show-icon
        message="No hay mas lugares de operación para seleccionar"
        class="mb-3"
      />

      <a-form-item>
        <template #label> Lugares de operación: </template>
        <a-select
          placeholder="Selecciona"
          class="product-config-multi-select"
          mode="multiple"
          :options="placeOperations"
          :max-tag-count="4"
          v-model:value="formState.placeOperationIds"
          :disabled="placeOperations.length === 0"
        >
          <template #maxTagPlaceholder="omittedValues">
            <span>+ {{ omittedValues.length }} ...</span>
          </template>
          <template #option="{ value, label }">
            <div class="option-multi-select">
              <font-awesome-icon
                :class="[
                  isSelectedPlaceOperation(value)
                    ? 'icon-color-selected'
                    : 'icon-color-not-selected',
                ]"
                :icon="[
                  isSelectedPlaceOperation(value) ? 'fas' : 'far',
                  isSelectedPlaceOperation(value) ? 'square-check' : 'square',
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

      <a-form-item>
        <template #label> Tipos de tren del proveedor: </template>
        <a-select
          placeholder="Selecciona"
          class="product-config-multi-select"
          mode="multiple"
          :options="trainTypesWithSelectAll"
          :max-tag-count="3"
          :value="formState.trainTypeIds"
          @change="handleTrainTypeChange"
          :disabled="placeOperations.length === 0"
        >
          <template #maxTagPlaceholder="omittedValues">
            <span>+ {{ omittedValues.length }} ...</span>
          </template>
          <template #option="{ value, label }">
            <div class="option-multi-select">
              <template v-if="value !== SELECT_ALL_VALUE">
                <font-awesome-icon
                  :class="[
                    isSelectedTrainType(value) ? 'icon-color-selected' : 'icon-color-not-selected',
                  ]"
                  :icon="[
                    isSelectedTrainType(value) ? 'fas' : 'far',
                    isSelectedTrainType(value) ? 'square-check' : 'square',
                  ]"
                  size="xl"
                />
              </template>
              <span>
                {{ label }}
              </span>
            </div>
          </template>
          <template #tagRender="{ label, onClose, value }">
            <template v-if="value !== SELECT_ALL_VALUE">
              <a-tag class="tag-close-option" closable @close="onClose">
                <span>
                  {{ label }}
                </span>
              </a-tag>
            </template>
          </template>
          <template #menuItemSelectedIcon />
        </a-select>
      </a-form-item>
    </a-form>
  </ConfigurationDrawer>
</template>

<script setup lang="ts">
  import { ConfigurationDrawer } from '../../base';
  import { useTrainCreateDrawer } from '../composables/useTrainCreateDrawer';
  import type { BaseDrawerProps } from '../../base/interfaces';

  const props = defineProps<BaseDrawerProps>();

  const emit = defineEmits<{
    'update:showDrawerForm': [value: boolean];
  }>();

  const {
    isLoading,
    formState,
    placeOperations,
    trainTypesWithSelectAll,
    stepNumber,
    cancelButtonText,
    nextButtonText,
    isNextButtonDisabled,
    handleClose,
    handleCancelGoBack,
    isSelectedPlaceOperation,
    isSelectedTrainType,
    handleTrainTypeChange,
    SELECT_ALL_VALUE,
    handleGoToConfiguration,
  } = useTrainCreateDrawer(emit, props);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';
  @import '@/scss/components/negotiations/_productComponentScoped.scss';
</style>
