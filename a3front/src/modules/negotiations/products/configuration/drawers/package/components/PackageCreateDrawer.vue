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
        v-if="programsToOperate.length === 0"
        type="warning"
        show-icon
        message="No hay mas programas a operar para seleccionar"
        class="mb-3"
      />

      <a-form-item>
        <template #label> Programas a operar: </template>
        <a-select
          placeholder="Selecciona"
          class="product-config-multi-select"
          mode="multiple"
          :options="programsToOperate"
          :max-tag-count="3"
          v-model:value="formState.programsToOperateIds"
        >
          <template #maxTagPlaceholder="omittedValues">
            <span>+ {{ omittedValues.length }} ...</span>
          </template>
          <template #option="{ value, label }">
            <div class="option-multi-select">
              <font-awesome-icon
                :class="[
                  isSelectedProgramToOperate(value)
                    ? 'icon-color-selected'
                    : 'icon-color-not-selected',
                ]"
                :icon="[
                  isSelectedProgramToOperate(value) ? 'fas' : 'far',
                  isSelectedProgramToOperate(value) ? 'square-check' : 'square',
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
        <template #label> Temporadas de operación: </template>
        <a-select
          placeholder="Selecciona"
          class="product-config-multi-select"
          mode="multiple"
          :options="operatingSeasons"
          :max-tag-count="3"
          v-model:value="formState.operatingSeasonIds"
        >
          <template #maxTagPlaceholder="omittedValues">
            <span>+ {{ omittedValues.length }} ...</span>
          </template>
          <template #option="{ value, label }">
            <div class="option-multi-select">
              <font-awesome-icon
                :class="[
                  isSelectedOperatingSeason(value)
                    ? 'icon-color-selected'
                    : 'icon-color-not-selected',
                ]"
                :icon="[
                  isSelectedOperatingSeason(value) ? 'fas' : 'far',
                  isSelectedOperatingSeason(value) ? 'square-check' : 'square',
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
    </a-form>
  </ConfigurationDrawer>
</template>
<script setup lang="ts">
  import { ConfigurationDrawer } from '../../base';
  import { usePackageCreateDrawer } from '../composables/usePackageCreateDrawer';
  import type { BaseDrawerProps } from '@/modules/negotiations/products/configuration/drawers/base/interfaces';

  const props = defineProps<BaseDrawerProps>();

  const emit = defineEmits<{
    'update:showDrawerForm': [value: boolean];
  }>();

  const {
    isLoading,
    formState,
    programsToOperate,
    operatingSeasons,
    stepNumber,
    cancelButtonText,
    nextButtonText,
    isNextButtonDisabled,
    handleClose,
    handleCancelGoBack,
    handleGoToConfiguration,
    isSelectedProgramToOperate,
    isSelectedOperatingSeason,
  } = usePackageCreateDrawer(emit, props);
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';
  @import '@/scss/components/negotiations/_productComponentScoped.scss';
</style>
