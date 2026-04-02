<template>
  <a-modal
    class="base-modal"
    :open="open"
    :width="width"
    :footer="null"
    :focusTriggerAfterClose="false"
    @cancel="onCancel"
  >
    <div class="modal-container">
      <!-- Header -->
      <div
        v-if="$slots.header || title"
        class="modal-header"
        :class="{
          'is-left': props.titleAlign === 'left',
          'has-icon': props.titleIcon,
        }"
      >
        <slot name="header">
          <div class="title-section">
            <font-awesome-icon
              v-if="props.titleIcon"
              :icon="['fas', props.titleIcon]"
              class="title-icon"
            />
            <span class="modal-title">{{ title }}</span>
          </div>
        </slot>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <slot />
      </div>

      <!-- Footer -->
      <div class="" v-if="$slots.footer || showFooter">
        <slot name="footer">
          <div class="default-footer">
            <a-button @click="onCancel" class="btn-cancel" :loading="cancelLoading" size="large">
              {{ cancelText }}
            </a-button>

            <a-button
              @click="onConfirm"
              class="btn-save"
              html-type="submit"
              :loading="okLoading"
              size="large"
              type="primary"
            >
              {{ okText }}
            </a-button>
          </div>
        </slot>
      </div>
    </div>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import type { FormInstance } from 'ant-design-vue';

  const props = defineProps<{
    open: boolean;
    title?: string;
    titleIcon?: string; // Font Awesome icon name, e.g., 'plus'
    titleAlign?: 'left' | 'center';
    okText?: string;
    cancelText?: string;
    width?: string | number;
    showFooter?: boolean;
    okLoading?: boolean;
    cancelLoading?: boolean;
    validateForm?: boolean;
  }>();

  const emit = defineEmits<{
    (e: 'ok'): void;
    (e: 'cancel'): void;
  }>();

  const formRef = ref<FormInstance>();

  const onCancel = () => emit('cancel');

  const onConfirm = async () => {
    if (props.validateForm && formRef.value) {
      try {
        await formRef.value.validateFields();
        emit('ok');
      } catch (err) {
        console.log('🚀 ~ onConfirm ~ err:', err);
        // No emitir si hay errores
      }
    } else {
      emit('ok');
    }
  };

  defineExpose({ formRef });
</script>

<style scoped lang="scss">
  .base-modal {
    .modal-container {
      padding: 0px !important;
    }

    .modal-header {
      font-family: Montserrat, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #ffffff;
      color: #3d3d3d;
      //   padding: 10px;
      padding: 0 10px !important;

      &.is-left {
        justify-content: flex-start;
        padding-left: 30px;
      }

      .title-section {
        display: flex;
        align-items: center;
        gap: 10px;

        .title-icon {
          color: #eb5757;
          font-size: 20px;
        }

        .modal-title {
          font-size: 24px;
          font-weight: 700;
          font-family: Montserrat, sans-serif;
        }
      }
    }

    .modal-body {
      font-family: Montserrat, sans-serif;
      font-size: 14px;
      text-align: center;
      padding: 30px 0 !important;
      color: #3d3d3d;

      strong {
        font-weight: 700;
      }
    }

    .modal-footer {
      display: flex;
      justify-content: center;
      gap: 16px;
      padding-bottom: 20px;
    }

    .btn-cancel,
    .btn-save {
      flex: 1;
      height: 54px;
      font-size: 16px;
      font-weight: 500;
      font-family: Montserrat, sans-serif;
      border-radius: 10px;
      width: auto !important;
    }

    .btn-cancel {
      background-color: #f8f8f8 !important;
      color: #3d3d3d !important;
      border: 1px solid #e0e0e0 !important;
      margin-right: 10px;

      &:hover {
        background-color: #f0f0f0 !important;
        border-color: #d2d2d2 !important;
      }
    }

    .btn-save {
      background-color: #eb5757 !important;
      color: #ffffff !important;
      border: none !important;
      margin-left: 10px;

      &:hover {
        background-color: #c63838 !important;
      }
    }

    .default-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
  }
</style>
