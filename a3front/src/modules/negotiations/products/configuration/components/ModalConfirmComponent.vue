<template>
  <a-modal
    :open="visible"
    :closable="true"
    :footer="null"
    :width="500"
    @cancel="handleCancel"
    centered
  >
    <div class="modal-content">
      <h3 class="modal-title">{{ title }}</h3>

      <div class="modal-body">
        <slot name="content">
          <p v-if="text" class="modal-text" v-html="text"></p>
        </slot>
      </div>

      <div class="modal-actions">
        <a-button class="btn-cancel" @click="handleCancel"> Cancelar </a-button>
        <a-button type="primary" class="btn-action" @click="handleConfirm">
          {{ actionButtonText }}
        </a-button>
      </div>
    </div>
  </a-modal>
</template>

<script setup lang="ts">
  defineOptions({
    name: 'ModalConfirmComponent',
  });

  interface Props {
    visible: boolean;
    title: string;
    text?: string;
    actionButtonText?: string;
  }

  withDefaults(defineProps<Props>(), {
    actionButtonText: 'Confirmar',
  });

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
  }

  .modal-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .modal-title {
    font-size: 22px !important;
    font-weight: 700;
    line-height: 36px;
    color: #2f353a;
    margin: 0;
    text-align: left;
  }

  .modal-body {
    padding: 0 !important;

    .modal-text {
      font-size: 16px;
      line-height: 24px;
      color: #2f353a;
      margin: 0;

      :deep(strong),
      :deep(b) {
        font-weight: 700;
      }
    }
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

  .btn-action {
    min-width: 130px;
    height: 48px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 14px;
    color: #ffffff;
    background: #bd0d12;
    border-color: #bd0d12;

    &:hover:not(:disabled) {
      background: #d54247;
      border-color: #d54247;
    }

    &:disabled {
      color: #ffffff;
      background: #acaeb0;
      border-color: #acaeb0;
      cursor: not-allowed;
    }
  }
</style>
