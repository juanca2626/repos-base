<template>
  <div
    class="classification-component b-top b-bottom"
    :key="componentKey"
    :class="{ 'loading-height': isLoading, 'read-mode-height': justSaved }"
  >
    <!-- Loading overlay bloqueante (solo al guardar) -->
    <div v-if="isSaving" class="loading-overlay">
      <a-spin size="small" />
    </div>

    <!-- Modo lectura -->
    <ReadModeComponent v-if="!isEditMode" title="Clasificación" @edit="handleEditMode">
      <!-- Una sola línea: TRN, ACU (sin sub-subtipos) -->
      <template v-if="isSingleLineReadMode">
        <div class="read-item">
          <span class="read-item-label">Tipo de proveedor</span>
          <span class="read-item-value">
            {{ readData.supplierSubClassification?.name || '-' }}
          </span>
        </div>
      </template>
      <!-- Dos líneas: caso general y sub-subtipos -->
      <template v-else>
        <div class="read-item">
          <span class="read-item-label">Tipo de proveedor</span>
          <span class="read-item-value">
            {{ readData.supplierClassification?.name || '-' }}
          </span>
        </div>
        <div class="read-item">
          <span class="read-item-label">Subtipo de proveedor</span>
          <span class="read-item-value">
            {{ readData.supplierSubClassification?.name || '-' }}
          </span>
        </div>
      </template>
    </ReadModeComponent>

    <!-- Estado de carga mientras se obtienen los datos del backend -->
    <div v-else-if="!justSaved && !isDataReady" class="classification-loading">
      <a-spin size="small" />
    </div>

    <!-- Modo edición - Mostrar cuando los datos estén listos -->
    <div v-else-if="!justSaved && isDataReady" class="classification-form">
      <a-form ref="formRef" :model="formState" :rules="rules" layout="vertical">
        <!-- ONE_LEVEL: Trenes, Lanchas — solo "Tipo de proveedor" (hoja directa, sin subtipo) -->
        <template v-if="scenario === ScenarioType.ONE_LEVEL">
          <a-form-item>
            <template #label>
              <required-label class="form-label" label="Tipo de proveedor:" />
            </template>
            <a-select :value="formState.supplier_sub_classification_subtypeCode" disabled>
              <a-select-option :value="formState.supplier_sub_classification_subtypeCode">
                {{ preSelectedSubtypeName || readData.supplierSubClassification?.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </template>

        <!-- THREE_LEVELS_REMAPPED: Transporte → Aerolíneas → Doméstico/Internacional -->
        <!-- Visualmente igual a TWO_LEVELS pero el 1er nivel (TRP) está oculto -->
        <!-- "Tipo" = 2do nivel (AER, ACU…), "Subtipo" = 3er nivel (DOM, INT…) -->
        <template v-else-if="scenario === ScenarioType.THREE_LEVELS_REMAPPED">
          <a-form-item
            name="supplier_sub_classification_subtypeCode"
            :validate-status="formState.supplier_sub_classification_subtypeCode ? 'success' : ''"
          >
            <template #label>
              <required-label class="form-label" label="Tipo de proveedor:" />
            </template>
            <a-select
              v-model:value="formState.supplier_sub_classification_subtypeCode"
              placeholder="Seleccionar"
              :loading="isLoading"
              :disabled="isLoading || isSubClassificationDisabled"
              show-search
              option-filter-prop="label"
            >
              <a-select-option
                v-for="sub in supplierSubClassifications"
                :key="sub.subtypeCode"
                :value="sub.subtypeCode"
                :label="sub.name"
              >
                {{ sub.name }}
              </a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item
            name="supplier_sub_sub_classification_subtypeCode"
            :validate-status="
              formState.supplier_sub_sub_classification_subtypeCode ? 'success' : ''
            "
          >
            <template #label>
              <required-label class="form-label" label="Subtipo de proveedor:" />
            </template>
            <a-select
              v-model:value="formState.supplier_sub_sub_classification_subtypeCode"
              placeholder="Seleccionar"
              :loading="isLoading"
              :disabled="isLoading || !formState.supplier_sub_classification_subtypeCode"
              show-search
              option-filter-prop="label"
            >
              <a-select-option
                v-for="subSub in supplierSubSubClassifications"
                :key="subSub.subtypeCode"
                :value="subSub.subtypeCode"
                :label="subSub.name"
              >
                {{ subSub.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </template>

        <!-- TWO_LEVELS: Staff, Restaurante, Atractivos — "Tipo" + "Subtipo" activos -->
        <template v-else>
          <a-form-item
            name="supplier_classification_typeCode"
            :validate-status="formState.supplier_classification_typeCode ? 'success' : ''"
          >
            <template #label>
              <required-label class="form-label" label="Tipo de proveedor:" />
            </template>
            <a-select
              v-model:value="formState.supplier_classification_typeCode"
              placeholder="Seleccionar"
              :loading="isLoading"
              :disabled="isClassificationDisabled"
              show-search
              option-filter-prop="label"
            >
              <a-select-option
                v-for="c in supplierClassifications"
                :key="c.typeCode"
                :value="c.typeCode"
                :label="c.name"
              >
                {{ c.name }}
              </a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item
            name="supplier_sub_classification_subtypeCode"
            :validate-status="formState.supplier_sub_classification_subtypeCode ? 'success' : ''"
          >
            <template #label>
              <required-label class="form-label" label="Subtipo de proveedor:" />
            </template>
            <a-select
              v-model:value="formState.supplier_sub_classification_subtypeCode"
              placeholder="Seleccionar"
              :loading="isLoading"
              :disabled="
                isLoading ||
                !formState.supplier_classification_typeCode ||
                isSubClassificationDisabled
              "
              show-search
              option-filter-prop="label"
            >
              <a-select-option
                v-for="sub in supplierSubClassifications"
                :key="sub.subtypeCode"
                :value="sub.subtypeCode"
                :label="sub.name"
              >
                {{ sub.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </template>

        <div class="form-actions">
          <a-button type="primary" :disabled="!isFormValid" @click="handleSave"
            >Guardar datos</a-button
          >
        </div>
      </a-form>
    </div>
  </div>
</template>

<script setup lang="ts">
  import ReadModeComponent from '@/modules/negotiations/products/configuration/components/ReadModeComponent.vue';
  import RequiredLabel from '@/modules/negotiations/supplier-new/components/required-label.vue';
  import { useClassificationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/v2/useClassificationComposable';

  defineOptions({
    name: 'ClassificationComponent',
  });

  const {
    formState,
    formRef,
    isEditMode,
    isLoading,
    isSaving,
    isDataReady,
    isClassificationDisabled,
    ScenarioType,
    scenario,
    supplierClassifications,
    supplierSubClassifications,
    supplierSubSubClassifications,
    preSelectedSubtypeName,
    isSingleLineReadMode,
    readData,
    isFormValid,
    rules,
    isSubClassificationDisabled,
    componentKey,
    justSaved,
    handleEditMode,
    handleSave,
  } = useClassificationComposable();
</script>

<style lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .b-top {
    border-top: 1px solid $color-black-4;
  }

  .classification-component {
    position: relative;
    margin-bottom: 20px;

    h2 {
      font-size: 20px;
      font-weight: 600;
      color: $color-black;
      margin-bottom: 16px;
    }

    // Mantener altura mínima cuando está cargando (formulario)
    &.loading-height {
      min-height: 200px;
    }

    // Altura del modo lectura (después de guardar)
    &.read-mode-height {
      min-height: 154px;
      max-height: 154px;
      overflow: hidden;
    }
  }

  // Eliminar el espacio después del separador
  .classification-component::v-deep .custom-separator {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }

  .classification-component::v-deep .read-mode-container {
    padding-bottom: 0 !important;
  }

  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
  }

  .loading-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background-color: #f0f5ff;
    border: 1px solid #d6e4ff;
    border-radius: 4px;
    margin-bottom: 12px;
    font-size: 14px;
    color: #1890ff;
  }

  .classification-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 40px 20px;
    margin-top: 20px;
    background-color: $color-white;

    .loading-text {
      font-size: 14px;
      color: #7e8285;
    }
  }

  .classification-form {
    background-color: $color-white;
    margin-top: 20px;

    .ant-form-item-required::before {
      display: none !important;
    }

    .ant-form-item-required::after {
      content: '' !important;
    }

    .ant-form-item {
      margin-bottom: 10px !important;
    }

    .ant-form-item-label > label {
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      color: #2f353a;
    }

    .form-label {
      font-weight: 700;
      font-size: 14px;
      line-height: 20px;
      color: #2f353a;
    }

    .ant-select {
      width: 382px;
    }

    .form-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-start;
      margin-top: 24px;

      .ant-btn {
        height: 48px;
        font-weight: 600;
        font-size: 16px;
        line-height: 24px;
        padding: 0 24px;
      }

      .ant-btn-default {
        width: 118px;
        color: #2f353a;
        background: #ffffff;
        border-color: #2f353a !important;

        &:hover,
        &:active {
          color: #2f353a !important;
          background: #ffffff !important;
          border-color: #2f353a !important;
        }
      }

      .ant-btn-primary {
        width: 159px;
        background: #2f353a;
        border-color: #2f353a;
        color: #ffffff;

        &:hover,
        &:active {
          background: #2f353a;
          border-color: #2f353a;
        }

        &:disabled {
          color: #ffffff !important;
          background: #acaeb0 !important;
          border-color: #acaeb0 !important;

          &:hover,
          &:active {
            color: #ffffff !important;
            background: #acaeb0 !important;
            border-color: #acaeb0 !important;
          }
        }
      }
    }
  }
</style>
