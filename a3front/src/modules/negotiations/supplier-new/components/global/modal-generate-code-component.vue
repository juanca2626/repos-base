<template>
  <a-modal
    class="commercial-location-component-modal"
    v-model:open="showModalCode"
    @ok="handleShowModalCode(false)"
    :force-render="true"
  >
    <div class="modal-title">¿Deseas actualizar el código de proveedor?</div>
    <div class="generate-code">
      <div>
        <a-form-item>
          <template v-slot:label>
            <div class="form-label">Código actual</div>
          </template>
          <a-input
            style="width: 102px"
            v-model:value="formState.code"
            placeholder="Visualiza el código actual"
            disabled
          />
        </a-form-item>
      </div>
      <div>
        <a-form-item>
          <template v-slot:label>
            <div class="form-label">Nuevo código</div>
          </template>
          <a-input
            style="width: 102px"
            v-model:value="formState.new_code"
            placeholder="Ingresa el nuevo código"
          />
        </a-form-item>
      </div>
    </div>
    <template #footer>
      <div class="options-button">
        <a-button size="large" type="primary" @click="handleUpdateCode">Cambiar</a-button>
      </div>
    </template>
  </a-modal>
</template>

<script setup lang="ts">
  import { useCommercialLocationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/commercial-location.composable';

  defineOptions({
    name: 'ModalGenerateCodeComponent',
  });

  const { formState, showModalCode, handleShowModalCode, handleUpdateCode } =
    useCommercialLocationComposable();
</script>

<style lang="scss">
  .commercial-location-component-modal {
    .ant-modal-content {
      width: 634px;
    }

    .ant-modal-close svg {
      fill: #575b5f;
    }

    .modal-title {
      font-weight: 700;
      font-size: 24px;
      line-height: 36px;
      color: #7e8285;
    }

    .ant-modal-body {
      padding-top: 1rem;

      .form-label {
        font-weight: 700;
        font-size: 20px;
        line-height: 20px;
        color: #7e8285;
      }

      .generate-code {
        padding-top: 0.75rem;
        display: flex;
        gap: 2rem;
      }

      .ant-row {
        display: block;
      }
    }

    .ant-modal-footer {
      padding: 0 !important;
      margin-top: 0 !important;
    }

    .options-button {
      display: flex;
      gap: 1rem;

      .ant-btn-default {
        width: 118px;
        height: 48px;
        gap: 8px;
        border-radius: 5px;
        border-width: 1px;
        color: #7e8285;
        background: #ffffff;
        border-color: #7e8285 !important;

        &:hover,
        &:active {
          color: #7e8285 !important;
          background: #ffffff !important;
          border-color: #7e8285 !important;
        }
      }

      .ant-btn-primary {
        width: 159px;
        height: 48px;
        gap: 8px;
        border-radius: 5px;
        border-width: 1px;
        color: #ffffff;
        background: #7e8285;
        border-color: #7e8285 !important;

        &:hover,
        &:active {
          color: #ffffff;
          background: #7e8285;
          border-color: #7e8285 !important;
        }
      }

      .ant-btn-primary:disabled {
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
</style>
