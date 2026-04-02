<template>
  <div class="tributary-information">
    <div class="custom-tab-content">
      <a-spin :spinning="isLoadingForm">
        <a-alert
          type="error"
          banner
          closable
          :style="{ display: 'flex', alignItems: 'center' }"
          v-if="showBannerAlert"
          @close="closeAlert"
        >
          <template #icon> <font-awesome-icon :icon="['fas', 'triangle-exclamation']" /> </template>
          <template #message>
            <b>Observación de contabilidad:</b> {{ state.observationMessage }}
          </template>
        </a-alert>

        <a-form layout="vertical">
          <div class="form-information">
            <div>
              <a-form-item label="Tipo de documento:" v-bind="validateInfos.types_tax_documents_id">
                <a-select
                  class="w-100"
                  placeholder="Selecciona"
                  v-model:value="formStateTributaryInformation.types_tax_documents_id"
                >
                  <a-select-option
                    v-for="item in state.typeTaxDocument"
                    :key="item.id"
                    :value="item.id"
                  >
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </div>
            <div>
              <a-form-item label="Ubicación geográfica:" v-bind="validateInfos.city_id">
                <a-select
                  class="w-100"
                  placeholder="Selecciona"
                  v-model:value="formStateTributaryInformation.city_id"
                >
                  <a-select-option v-for="items in state.cities" :key="items.id" :value="items.id">
                    {{ items.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </div>
          </div>

          <div class="form-buttons">
            <a-button class="btn-secondary ant-btn-md" size="large" @click.prevent="handleCancel">
              Cancelar
            </a-button>
            <a-button
              type="primary"
              class="ant-btn-md"
              size="large"
              @click.prevent="handleOk('NEG')"
              :loading="state.isLoadingButton"
            >
              Guardar cambios
            </a-button>
          </div>
        </a-form>
      </a-spin>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { UseSupplierTributaryInformation } from '@/modules/negotiations/supplier/register/configuration-module/composables/useSupplierTributaryInformation';

  const {
    state,
    handleOk,
    handleCancel,
    validateInfos,
    formStateTributaryInformation,
    isLoadingForm,
    showBannerAlert,
    closeAlert,
  } = UseSupplierTributaryInformation();
</script>

<style>
  .tributary-information {
    .custom-tab-content {
      .ant-alert-error {
        background: #fff2f2;
        border: 1px solid #ff3b3b !important;
      }

      .fa-triangle-exclamation {
        color: #ff3b3b !important;
      }
    }

    .form-information {
      display: grid;
      grid-template-columns: 50% auto;
      grid-gap: 2rem;
    }
  }
</style>
