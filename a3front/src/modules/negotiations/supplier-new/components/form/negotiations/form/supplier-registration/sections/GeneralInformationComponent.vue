<template>
  <div class="general-information-component b-bottom">
    <!-- Loading overlay bloqueante (solo al guardar) -->
    <div v-if="isLoading" class="loading-overlay">
      <a-spin size="small" />
    </div>

    <h2 v-if="isEditMode" class="title-section">Información general</h2>

    <!-- Modo lectura -->
    <ReadModeComponent v-if="!isEditMode" title="Información general" @edit="handleEditMode">
      <div class="read-item">
        <span class="read-item-label">Código</span>
        <span class="read-item-value">{{ readData.code }}</span>
      </div>
      <div class="read-item">
        <span class="read-item-label">Nombre comercial</span>
        <span class="read-item-value">{{ readData.businessName }}</span>
      </div>
      <div v-if="!isStaffClassification" class="read-item">
        <span class="read-item-label">Cadena</span>
        <span class="read-item-value">{{ readData.chain }}</span>
      </div>
      <div class="read-item">
        <span class="read-item-label">Razón social</span>
        <span class="read-item-value">{{ readData.companyName }}</span>
      </div>
      <div class="read-item">
        <span class="read-item-label">Número de RUC</span>
        <span class="read-item-value">{{ readData.rucNumber }}</span>
      </div>
      <div v-if="isStaffClassification" class="read-item">
        <span class="read-item-label">Número de DNI</span>
        <span class="read-item-value">{{ readData.dniNumber }}</span>
      </div>
      <div class="read-item">
        <span class="read-item-label">¿Proveedor autorizado por gerencia?</span>
        <span class="read-item-value">{{ readData.authorizedManagement }}</span>
      </div>
      <div class="read-item">
        <span class="read-item-label">Estado</span>
        <span class="read-item-value">{{ readData.status }}</span>
      </div>
      <div v-if="shouldShowReasonField && readData.reason_state !== '-'" class="read-item">
        <span class="read-item-label">Motivo</span>
        <span class="read-item-value">{{ readData.reason_state }}</span>
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <div v-else class="general-information-form">
      <a-form ref="formRef" :model="formState" :rules="rules" layout="vertical">
        <a-row>
          <a-col class="gutter-row" :span="5">
            <a-form-item name="code">
              <template #label>
                <required-label class="form-label" label="Código:" />
              </template>
              <a-input
                v-model:value="formState.code"
                placeholder="Ingrese el código"
                :maxlength="6"
                @input="handleCodeInput"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row>
          <a-col class="gutter-row" :span="12">
            <a-form-item name="businessName">
              <template #label>
                <required-label class="form-label" label="Nombre comercial:" />
              </template>
              <a-input
                v-model:value="formState.businessName"
                placeholder="Ingrese el nombre comercial"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row v-if="!isStaffClassification">
          <a-col class="gutter-row" :span="12">
            <a-form-item name="chainId">
              <template #label>
                <required-label class="form-label" label="Cadena:" />
              </template>
              <a-select
                v-model:value="formState.chainId"
                placeholder="Seleccione la cadena"
                popupClassName="custom-dropdown-backend"
                :options="chains"
                :disabled="hasNoChains"
                show-search
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row>
          <a-col class="gutter-row" :span="12">
            <a-form-item name="companyName">
              <template #label>
                <required-label class="form-label" label="Razón social:" />
              </template>
              <a-input
                v-model:value="formState.companyName"
                placeholder="Ingrese la razón social"
                @input="handleCompanyNameInput"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row>
          <a-col class="gutter-row" :span="12">
            <a-form-item name="rucNumber">
              <template #label>
                <required-label class="form-label" label="Número de RUC:" />
              </template>
              <a-input
                v-model:value="formState.rucNumber"
                placeholder="Ingrese el número de RUC"
                :maxlength="11"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row v-if="isStaffClassification">
          <a-col class="gutter-row" :span="12">
            <a-form-item name="dniNumber">
              <template #label>
                <required-label class="form-label" label="Número de DNI:" />
              </template>
              <a-input
                v-model:value="formState.dniNumber"
                placeholder="Ingrese el número de DNI"
                :maxlength="8"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row>
          <a-col class="gutter-row" :span="12">
            <a-form-item name="authorizedManagement">
              <template #label>
                <required-label class="form-label" label="¿Proveedor autorizado por gerencia?:" />
              </template>
              <a-radio-group v-model:value="formState.authorizedManagement">
                <a-radio :value="true">Sí</a-radio>
                <a-radio :value="false">No</a-radio>
              </a-radio-group>
            </a-form-item>
          </a-col>
        </a-row>

        <a-row class="status-reason-row" :class="{ 'single-field': !shouldShowReasonField }">
          <a-col class="gutter-row" :span="shouldShowReasonField ? 11 : 12">
            <a-form-item name="status">
              <template #label>
                <required-label class="form-label" label="Estado:" />
              </template>
              <a-select
                v-model:value="formState.status"
                placeholder="Seleccione el estado"
                popupClassName="custom-dropdown-backend"
                :options="supplierStatusOptions"
              />
            </a-form-item>
          </a-col>

          <a-col v-if="shouldShowReasonField" class="gutter-row" :span="11" :offset="1">
            <a-form-item name="reason_state">
              <template #label>
                <span class="form-label">Motivo:</span>
              </template>
              <a-input v-model:value="formState.reason_state" placeholder="Ingrese el motivo" />
            </a-form-item>
          </a-col>
        </a-row>

        <div class="form-actions">
          <a-button @click="handleCancel">Cancelar</a-button>
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
  import { useGeneralInformationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/v2/useGeneralInformationComposable';

  defineOptions({
    name: 'GeneralInformationComponent',
  });

  const {
    formState,
    formRef,
    isEditMode,
    isLoading,
    isStaffClassification,
    chains,
    hasNoChains,
    shouldShowReasonField,
    supplierStatusOptions,
    readData,
    isFormValid,
    rules,
    handleEditMode,
    handleCancel,
    handleCodeInput,
    handleCompanyNameInput,
    handleSave,
  } = useGeneralInformationComposable();
</script>

<style lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .general-information-component {
    position: relative;
    margin-bottom: 0;

    .title-section {
      font-size: 16px !important;
      line-height: 23px !important;
      font-weight: 600;
      color: #2f353a;
      margin-bottom: 16px;
    }
  }

  // Eliminar el espacio después del separador
  .general-information-component::v-deep .custom-separator {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }

  .general-information-component::v-deep .read-mode-container {
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

  .general-information-form {
    background-color: $color-white;

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

    .ant-row {
      display: block;

      &.status-reason-row:not(.single-field) {
        display: flex !important;

        .ant-col {
          &:first-child {
            flex: 0 0 auto;
            margin-right: 1rem;
          }

          &:last-child {
            flex: 1;
            margin-right: 0;
          }
        }
      }
    }

    .ant-form-item-control-input {
      width: 422px;
    }

    .ant-input,
    .ant-select {
      width: 422px !important;
    }

    .status-reason-row {
      // Solo cuando NO es single-field, aplicar ancho flexible al segundo campo
      &:not(.single-field) {
        .ant-col:last-child {
          .ant-form-item-control-input {
            width: 100%;
          }

          .ant-input {
            width: 100% !important;
          }
        }
      }
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
