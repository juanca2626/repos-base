<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :focusTriggerAfterClose="false"
      width="450px"
    >
      <div class="modal-container">
        <div class="modal-header">
          <div class="title-section">
            <span class="modal-title">
              <font-awesome-icon icon="grid-2" />
              Enviar seguimiento
            </span>
          </div>
        </div>
        <a-form :model="formState" ref="formRefFile" layout="vertical" :rules="rules">
          Estás a punto de enviar <strong>"Seguimiento 1"</strong>. ¿Estás seguro?
          <a-row :gutter="16">
            <a-col :span="24" class="text-center mt-5">
              <a-button @click="handleCancel" class="btn-cancel" :loading="false">
                Cancelar
              </a-button>
              <a-button @click="" class="btn-save" html-type="submit" :loading="false">
                Si, continuar
              </a-button>
            </a-col>
          </a-row>
        </a-form>
      </div>
    </a-modal>
  </div>
</template>
<script setup lang="ts">
  import { ref } from 'vue';

  const emit = defineEmits(['update:isOpen', 'submit']);
  const rules = {};

  // Propiedades
  defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const formState = ref({
    typeDoc: 'pdf',
    language: 'es',
    includeCoverage: true,
    includeLogoClient: '3',
    coverName: 'amazonas',
  });

  const handleCancel = () => {
    emit('update:isOpen', false);
  };
</script>
<style scoped lang="scss">
  .file-modal {
    .text-label {
      ::v-deep(label) {
        font-family: Montserrat, sans-serif;
      }

      ::v-deep(span) {
        font-family: Montserrat, sans-serif;
      }
    }

    .font-w-700 {
      font-weight: 700;
    }

    .modal-container {
      padding: 10px;
    }

    .modal-header {
      font-family: Montserrat, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 20px;
      background-color: #ffffff;
      color: #3d3d3d;
      flex-direction: row;
      padding: 10px !important;

      .title-section {
        display: flex;
        align-items: center;

        .modal-title {
          font-size: 24px;
          font-weight: 700;
        }
      }
    }

    .btn-save {
      font-family: Montserrat, sans-serif;
      height: 54px !important;
      font-size: 16px !important;
      font-weight: 500 !important;
      margin-left: 10px;
      width: auto !important;
      background-color: #eb5757 !important;

      &:hover {
        background-color: #c63838 !important;
        border: 1px solid #c63838 !important;
      }
    }

    .btn-cancel {
      font-family: Montserrat, sans-serif;
      margin-right: 10px;
      height: 54px !important;
      font-size: 16px !important;
      font-weight: 500 !important;
      width: auto !important;
      color: #eb5757 !important;
      background-color: #ffffff !important;
      border: 1px solid #eb5757 !important;

      &:hover {
        font-weight: 600;
        color: #c63838 !important;
        background-color: #fff6f6 !important;
        border: 1px solid #c63838 !important;
      }
    }

    .text-info {
      font-family: Montserrat, sans-serif;
    }
  }
</style>
