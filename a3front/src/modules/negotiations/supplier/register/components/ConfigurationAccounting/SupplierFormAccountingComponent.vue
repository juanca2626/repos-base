<template>
  <div class="tributary-information">
    <div class="custom-tab-content">
      <a-spin :spinning="isLoadingForm">
        <div class="tittle-accounting">Información SUNAT</div>
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

          <div v-if="state.showObservation">
            <a-form-item label="Ingresa las observaciones:" v-bind="validateInfos.observations">
              <a-textarea
                placeholder="Observaciones"
                :rows="6"
                :allowClear="true"
                v-model:value="formStateTributaryInformation.observations"
              />
            </a-form-item>
          </div>

          <div class="form-buttons">
            <a-button class="btn-secondary ant-btn-md" size="large" @click.prevent="handleCancel">
              Cancelar
            </a-button>
            <a-button
              type="primary"
              class="ant-btn-md btn-observation"
              size="large"
              @click.prevent="showObservation"
              :loading="state.isLoadingButtonObservation"
            >
              <font-awesome-icon :icon="['fas', 'triangle-exclamation']" /> Observar
            </a-button>
            <a-button
              type="primary"
              class="ant-btn-md"
              size="large"
              @click.prevent="handleOk"
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
    showObservation,
  } = UseSupplierTributaryInformation();
</script>

<style>
  .tributary-information {
    .custom-tab-content {
      border: 1px solid #e7e7e7;
      border-radius: 5px;
      padding: 24px;
    }

    .form-information {
      display: grid;
      grid-template-columns: 50% auto;
      grid-gap: 2rem;
    }

    .tittle-accounting {
      font-weight: 700;
      font-size: 16px;
      line-height: 20px;
      text-align: center;
      margin-bottom: 2rem;
    }

    .btn-observation {
      gap: 5px;
      border-radius: 5px;
      color: #ff3b3b;
      border: 1px solid #fff2f2;
      background: #fff2f2;

      &:hover {
        color: #ff3b3b;
        border: 1px solid #ffcccc;
        background: #ffcccc;
      }

      &:active {
        color: #ff3b3b;
        border: 1px solid #ffcccc;
        background: #ffcccc;
      }

      &:focus {
        color: #ff3b3b;
        border: 1px solid #ffcccc;
        background: #ffcccc;
      }
    }
  }
</style>
