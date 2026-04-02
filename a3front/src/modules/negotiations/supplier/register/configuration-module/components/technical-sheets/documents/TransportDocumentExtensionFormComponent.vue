<template>
  <a-drawer
    :open="showDrawerForm"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
  >
    <template #title>
      <span class="custom-primary-font">
        <template v-if="isUpdate"> Actualizar solicitudes de prórroga </template>
        <template v-else> Registrar solicitudes de prórroga </template>
      </span>
    </template>

    <p class="form-title custom-primary-font">Completa el formulario para solicitar prórrogas:</p>

    <a-spin :spinning="isLoading">
      <template v-for="(row, index) in formState.extensions">
        <DocumentExtensionFormComponent
          v-if="row.delete === undefined || row.delete === false"
          :index="index"
          ref="formRefsDocumentExtension"
        />
      </template>
      <a-row justify="end">
        <a-col>
          <a-button type="link" block class="add-extension-button" @click="addExtension">
            + Agregar prórroga
          </a-button>
        </a-col>
      </a-row>
    </a-spin>
    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <a-button type="primary" class="btn-secondary ant-btn-md w-100" @click="handleClose">
            Cancelar
          </a-button>
        </a-col>
        <a-col :span="12">
          <a-button
            type="primary"
            class="ant-btn-md w-100"
            @click="handleSubmit()"
            :disabled="isLoading"
          >
            Guardar
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>

  <ActiveExtensionNotifyComponent
    v-model:showModal="showActiveExtensionNotify"
    :documentExtensionInfo="documentExtensionInfo"
    :documentExtensionIds="documentExtensionIds"
    :typeTechnicalSheet="typeTechnicalSheet"
  />
</template>
<script setup lang="ts">
  import type { DocumentExtensionFormProps } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
  import DocumentExtensionFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/DocumentExtensionFormComponent.vue';
  import ActiveExtensionNotifyComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/ActiveExtensionNotifyComponent.vue';
  import { useTransportDocumentExtensionForm } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/documents/useTransportDocumentExtensionForm';

  const props = defineProps<DocumentExtensionFormProps>();

  const emit = defineEmits(['update:showDrawerForm']);

  const {
    formRefsDocumentExtension,
    formState,
    isLoading,
    showActiveExtensionNotify,
    documentExtensionInfo,
    documentExtensionIds,
    isUpdate,
    handleClose,
    handleSubmit,
    addExtension,
  } = useTransportDocumentExtensionForm(emit, props);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .custom-card {
    background-color: $color-gray-ultra-light;

    :deep(.ant-card-head) {
      border-bottom: 1px solid $color-gray-soft;
    }
  }

  .container-card-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .card-title {
    font-size: 14px;
  }

  .add-extension-button {
    cursor: pointer;
  }

  .form-title {
    font-size: 15px;
    font-weight: 400;
  }
</style>
