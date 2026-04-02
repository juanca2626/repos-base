<template>
  <ConfigurationDrawer
    :show-drawer-form="showDrawerForm"
    :supplier-original-id="supplierOriginalId"
    :product-supplier-id="productSupplierId"
    title="Editar configuración del servicio"
    :is-loading="isLoading"
    :step-number="stepNumber"
    :cancel-button-text="cancelButtonText"
    :next-button-text="nextButtonText"
    :is-next-button-disabled="isNextButtonDisabled"
    @close="handleClose"
    @cancel="handleCancelGoBack"
    @next="handleGoToConfiguration"
  >
    <template #steps>
      <ConfigurationDrawerSteps :step-number="stepNumber" />
    </template>
    <a-form layout="vertical">
      <template v-if="stepNumber === 1">
        <a-alert
          v-if="hasNoMorePlaceOperations"
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
            :max-tag-count="3"
            :disabled="hasNoMorePlaceOperations"
            v-model:value="formState.placeOperationIds"
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
          <template #label> Categorías del proveedor: </template>
          <a-select
            placeholder="Selecciona"
            class="product-config-multi-select"
            mode="multiple"
            :options="supplierCategoriesWithDisabled"
            :max-tag-count="3"
            :disabled="hasNoMorePlaceOperations"
            :value="formState.supplierCategoryCodes"
            @change="handleSupplierCategoryChange"
          >
            <template #maxTagPlaceholder="omittedValues">
              <span>+ {{ omittedValues.length }} ...</span>
            </template>
            <template #option="{ value, label, disabled }">
              <div class="option-multi-select" :class="{ 'option-disabled': disabled }">
                <font-awesome-icon
                  :class="[
                    isSelectedSupplierCategory(value)
                      ? 'icon-color-selected'
                      : 'icon-color-not-selected',
                  ]"
                  :icon="[
                    isSelectedSupplierCategory(value) ? 'fas' : 'far',
                    isSelectedSupplierCategory(value) ? 'square-check' : 'square',
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
      </template>
      <template v-else>
        <div
          class="general-behavior"
          v-if="selectedPlaceOperations.length > 1 || selectedSupplierCategories.length > 1"
        >
          <a-checkbox v-model:checked="formState.isGeneral" class="custom-checkbox">
            <span class="text"> Comportamiento para todos </span>
          </a-checkbox>

          <template v-if="formState.isGeneral">
            <a-radio-group v-model:value="formState.type" class="custom-radio">
              <a-radio value="1">
                <span class="text">Simple</span>
              </a-radio>
              <a-radio value="2">
                <span class="text">Compuesto</span>
              </a-radio>
            </a-radio-group>
          </template>
        </div>

        <div class="mt-3">
          <table class="service-behavior-table">
            <thead>
              <tr>
                <th class="text-th">Ciudad</th>
                <th class="text-th category-th">Categoría</th>
                <th class="text-th" v-if="!formState.isGeneral">Simple</th>
                <th class="text-th" v-if="!formState.isGeneral">Compuesto</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="location in selectedPlaceOperations" :key="location.key">
                <template
                  v-for="category in selectedSupplierCategories"
                  :key="`${location.key}-${category.value}`"
                >
                  <tr>
                    <td class="city-td">
                      {{ getLocationDisplayName(location) }}
                    </td>
                    <td class="category-td">{{ category.label }}</td>
                    <template v-if="!formState.isGeneral">
                      <td class="simple-td">
                        <a-radio
                          :name="`behavior-${location.key}-${String(category.value)}`"
                          :checked="
                            getCurrentMode(location.key, String(category.value)) === 'simple'
                          "
                          @change="
                            setBehaviorMode(
                              location.key,
                              String(category.value),
                              ProductSupplierBehaviorModeEnum.SIMPLE
                            )
                          "
                        ></a-radio>
                      </td>
                      <td class="composed-td">
                        <a-radio
                          :name="`behavior-${location.key}-${String(category.value)}`"
                          :checked="
                            getCurrentMode(location.key, String(category.value)) === 'component'
                          "
                          @change="
                            setBehaviorMode(
                              location.key,
                              String(category.value),
                              ProductSupplierBehaviorModeEnum.COMPONENT
                            )
                          "
                        ></a-radio>
                      </td>
                    </template>
                  </tr>
                </template>
              </template>
            </tbody>
          </table>
        </div>
      </template>
    </a-form>
  </ConfigurationDrawer>
</template>

<script setup lang="ts">
  import { ConfigurationDrawer } from '../../base';
  import ConfigurationDrawerSteps from './ConfigurationDrawerSteps.vue';
  import { useEditDrawer } from '../composables/useEditDrawer';
  import { ProductSupplierBehaviorModeEnum } from '@/modules/negotiations/products/configuration/drawers/generic/enums/product-supplier-behavior-mode.enum';
  import type { BaseDrawerProps } from '../../base/interfaces';

  const props = defineProps<BaseDrawerProps>();

  const emit = defineEmits<{
    'update:showDrawerForm': [value: boolean];
  }>();

  const {
    isLoading,
    formState,
    placeOperations,
    supplierCategoriesWithDisabled,
    stepNumber,
    selectedPlaceOperations,
    selectedSupplierCategories,
    cancelButtonText,
    nextButtonText,
    isNextButtonDisabled,
    hasNoMorePlaceOperations,
    handleClose,
    isSelectedPlaceOperation,
    isSelectedSupplierCategory,
    handleSupplierCategoryChange,
    handleGoToConfiguration,
    setBehaviorMode,
    getLocationDisplayName,
    getCurrentMode,
    handleCancelGoBack,
  } = useEditDrawer(emit, props);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';
  @import '@/scss/components/negotiations/_productComponentScoped.scss';

  .service-behavior-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 20px;

    .text-th {
      color: $color-black-2;
      font-size: 10px;
      font-weight: 500;
    }

    .category-th {
      text-align: left;
      padding-left: 30px;
    }

    .city-td {
      width: 25%;
      background-color: $color-black-8;
      border-radius: 6px 0 0 6px;
      text-align: center;
      padding: 8px 0;
      color: #17181a;
      font-size: 16px;
      font-weight: 500;
    }

    .category-td {
      width: 45%;
      text-align: left;
      padding: 8px 0;
      border-top: 1px solid $color-white-2;
      border-bottom: 1px solid $color-white-2;
      color: #17181a;
      font-size: 16px;
      font-weight: 500;
      padding-left: 30px;
    }

    .simple-td {
      width: 15%;
      text-align: center;
      padding: 8px 0;
      border-top: 1px solid $color-white-2;
      border-bottom: 1px solid $color-white-2;
      text-align: center;
    }

    .composed-td {
      width: 15%;
      text-align: center;
      padding: 8px 0;
      border-top: 1px solid $color-white-2;
      border-bottom: 1px solid $color-white-2;
      border-right: 1px solid $color-white-2;
      border-radius: 0 6px 6px 0;
      text-align: center;
    }
  }

  .general-behavior {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .custom-checkbox {
    :deep(.ant-checkbox-inner) {
      display: flex;
      align-items: center;
      justify-content: center;

      width: 24px;
      height: 24px;

      &::after {
        transform: scale(1.1) rotate(45deg);
        position: relative;
        top: -6%;
        inset-inline-start: 0;
      }
    }

    .text {
      font-size: 16px;
      font-weight: 400;
      color: $color-black-2;
    }
  }

  .custom-radio {
    :deep(.ant-radio-wrapper) {
      flex-direction: row-reverse;
      gap: 8px;
    }

    :deep(.ant-radio-inner) {
      width: 24px;
      height: 24px;
    }

    .text {
      font-size: 10px;
      font-weight: 500;
      color: $color-black-2;
    }
  }

  .option-disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
  }
</style>
