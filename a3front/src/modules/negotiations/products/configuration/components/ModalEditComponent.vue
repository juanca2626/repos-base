<template>
  <a-modal
    :open="visible"
    :closable="true"
    :footer="null"
    :width="480"
    @cancel="handleCancel"
    centered
  >
    <div class="modal-content">
      <h3 class="modal-title">¿Estás seguro de editar los datos?</h3>

      <div class="modal-actions">
        <a-button class="btn-cancel" @click="handleCancel"> Cancelar </a-button>
        <a-button type="primary" class="btn-edit" @click="handleConfirm"> Editar datos </a-button>
      </div>
    </div>
  </a-modal>
</template>

<script setup lang="ts">
  defineOptions({
    name: 'ModalEditComponent',
  });

  interface Props {
    visible: boolean;
  }

  defineProps<Props>();

  const emit = defineEmits<{
    (e: 'confirm'): void;
    (e: 'cancel'): void;
  }>();

  const handleConfirm = () => {
    emit('confirm');
  };

  const handleCancel = () => {
    emit('cancel');
  };
</script>

<style scoped lang="scss">
  :deep(.ant-modal) {
    .ant-modal-content {
      border-radius: 8px;
      padding: 0;
    }

    .ant-modal-close {
      top: 16px;
      right: 16px;
      width: 24px;
      height: 24px;
      color: #8c8c8c;

      &:hover {
        color: #262626;
        background-color: transparent;
      }

      .ant-modal-close-x {
        width: 24px;
        height: 24px;
        line-height: 24px;
        font-size: 16px;
      }
    }

    .ant-modal-body {
      padding: 24px;
    }
  }

  .modal-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .modal-title {
    font-size: 24px !important;
    font-weight: 700;
    line-height: 36px;
    color: #2f353a;
    margin: 0;
    text-align: left;
  }

  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
  }

  .btn-cancel {
    min-width: 100px;
    height: 48px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 14px;
    color: #2f353a;
    background: #ffffff;
    border: 1px solid #d9d9d9;

    &:hover {
      color: #2f353a;
      border-color: #d9d9d9;
      background: #ffffff;
    }
  }

  .btn-edit {
    min-width: 130px;
    height: 48px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 14px;
    color: #ffffff;
    background: #bd0d12;
    border-color: #bd0d12;

    &:hover:not(:disabled) {
      background: #bd0d12;
      border-color: #bd0d12;
    }

    &:disabled {
      color: #ffffff;
      background: #acaeb0;
      border-color: #acaeb0;
      cursor: not-allowed;
    }
  }
</style>
