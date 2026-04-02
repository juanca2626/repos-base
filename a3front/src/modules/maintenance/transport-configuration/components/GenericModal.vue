<template>
  <a-modal
    v-model:open="isOpen"
    :footer="null"
    :closable="false"
    :width="width"
    centered
    :class="['generic-custom-modal', customClass]"
    :body-style="{ padding: 0 }"
  >
    <div
      class="modal-content-wrapper"
      :style="{
        height: height + 'px',
        borderRadius: borderRadius + 'px',
        padding: customPadding,
      }"
    >
      <div class="close-icon-wrapper" @click="handleCancel">
        <CloseModalIcon />
      </div>

      <div class="modal-body">
        <h2 v-if="title" class="modal-title">{{ title }}</h2>
        <div class="modal-description" v-html="body"></div>
      </div>

      <div class="modal-actions">
        <button class="btn-cancel" @click="handleCancel">
          {{ cancelText }}
        </button>
        <button class="btn-confirm" @click="handleConfirm">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </a-modal>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import CloseModalIcon from '../icons/CloseModalIcon.vue';

  const props = defineProps({
    open: Boolean,
    title: String,
    body: String,
    width: { type: Number, default: 632 },
    height: { type: Number, default: 274 },
    borderRadius: { type: Number, default: 8 },
    customPadding: { type: String, default: '' },
    customClass: { type: String, default: '' },
    cancelText: { type: String, default: 'Cancelar' },
    confirmText: { type: String, default: 'Eliminar' },
  });

  const emit = defineEmits(['update:open', 'confirm', 'cancel']);

  const isOpen = computed({
    get: () => props.open,
    set: (val) => emit('update:open', val),
  });

  const handleCancel = () => {
    emit('cancel');
    isOpen.value = false;
  };

  const handleConfirm = () => {
    emit('confirm');
    isOpen.value = false;
  };
</script>

<style lang="scss">
  .generic-custom-modal {
    .ant-modal-content {
      padding: 0 !important;
      overflow: hidden;
      box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .ant-modal-body {
      padding: 0px !important;
    }

    .modal-content-wrapper {
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      background: #ffffff;
      box-sizing: border-box;
    }

    .close-icon-wrapper {
      position: absolute;
      top: 16px;
      right: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
      transition: opacity 0.2s;

      &:hover {
        opacity: 0.7;
      }
    }

    .modal-title {
      font-family: 'Inter';
      font-weight: 700;
      font-size: 24px !important;
      line-height: 36px;
      color: #2f353a;
    }

    .modal-description {
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      font-size: 20px;
      line-height: 32px;
      letter-spacing: 0%;
      color: #2f353a;
      margin: 0;

      b {
        font-weight: 700;
        text-transform: capitalize;
      }
    }

    &.deactivate-variant {
      .modal-description {
        font-size: 20px !important;
      }
      .modal-body {
        /* padding: 1.5em 1.3em !important; */
      }
    }

    &.delete-variant {
      .modal-body {
        // padding: 1.5em 1.3em !important;
      }
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 16px;

      button {
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 5px;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: all 0.2s ease;
        padding: 0 24px;
        box-sizing: border-box;

        &:hover {
          opacity: 0.8;
        }
      }

      .btn-cancel {
        width: 118px;
        height: 50px;
        background: white;
        color: #2f353a;
        font-weight: 600;
        font-size: 16px;
        line-height: 24px;
        letter-spacing: 0%;
        text-align: center;
        text-transform: capitalize;
        border: 1px solid #2f353a;
      }

      .btn-confirm {
        min-width: 110px;
        height: 50px;
        background: #bd0d12;
        color: #ffffff;
        font-weight: 600;
        font-size: 16px;
        border: none;
      }
    }
  }
</style>
