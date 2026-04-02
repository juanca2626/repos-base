<template>
  <a-card :class="['custom-card', { 'mt-4': index > 0 }]">
    <template #title>
      <div class="container-card-title">
        <div>
          <span class="custom-primary-font card-title"> Datos de la prórroga </span>
        </div>
        <div>
          <a-button type="link" v-if="index > 0" @click="deleteExtension(index)">
            <font-awesome-icon :icon="['far', 'trash-can']" />
          </a-button>
        </div>
      </div>
    </template>
    <a-flex gap="middle" vertical>
      <a-form
        layout="vertical"
        :model="formState.extensions[index]"
        ref="formRefDocumentExtension"
        :rules="formRules"
        class="mt-3"
      >
        <a-row :gutter="16">
          <a-col class="gutter-row" :span="24" v-if="index === 0">
            <a-checkbox
              v-model:checked="formState.applyDateAll"
              class="mb-3"
              @change="applyDateAllExtensions"
            >
              <span class="custom-primary-font"> Aplicar la misma fecha para todos </span>
            </a-checkbox>
          </a-col>

          <a-col class="gutter-row" :span="24">
            <a-form-item name="extensionDateRange">
              <template #label>
                <span class="custom-primary-font"> Fecha de vigencia: </span>
              </template>
              <a-range-picker
                :placeholder="['Fecha inicio', 'Fecha fin']"
                class="full-width w-100"
                v-model:value="formState.extensions[index].extensionDateRange"
                format="DD/MM/YYYY"
                value-format="YYYY-MM-DD"
                :disabled-date="disabledExtensionDateRange"
              />
            </a-form-item>
          </a-col>
          <a-col class="gutter-row" :span="24">
            <a-form-item name="typeDocumentId">
              <template #label>
                <span class="custom-primary-font"> Tipo de documento: </span>
              </template>
              <a-select
                class="full-width"
                v-model:value="formState.extensions[index].typeDocumentId"
                :options="typeDocuments"
                show-search
                :filter-option="filterOption"
                @change="handleTypeDocument(index)"
              />
            </a-form-item>
          </a-col>

          <a-col class="gutter-row" :span="24">
            <a-form-item name="reason">
              <template #label>
                <span class="custom-primary-font"> Motivo de la solicitud: </span>
              </template>
              <a-textarea
                v-model:value="formState.extensions[index].reason"
                :rows="3"
                :maxlength="50"
                placeholder="Ingresar el motivo de prorroga"
                class="textarea-custom"
              />
              <div class="observations-info custom-textarea-observation reason">
                <span>{{ formState.extensions[index].reason?.length ?? 0 }} / 50</span>
              </div>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
    </a-flex>
  </a-card>
</template>
<script setup lang="ts">
  import { defineProps, defineExpose } from 'vue';
  import { filterOption } from '@/modules/negotiations/supplier/register//helpers/supplierFormHelper';
  import { useDocumentExtensionForm } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/documents/useDocumentExtensionForm';

  defineProps({
    index: {
      type: Number,
      required: true,
    },
  });

  const {
    formState,
    typeDocuments,
    formRules,
    formRefDocumentExtension,
    disabledExtensionDateRange,
    validateFields,
    resetFields,
    deleteExtension,
    applyDateAllExtensions,
    handleTypeDocument,
  } = useDocumentExtensionForm();

  // expose for parent
  defineExpose({ validateFields, resetFields });
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

  .reason {
    margin-bottom: -20px;
  }

  .add-extension-button {
    cursor: pointer;
  }
</style>
