<template>
  <div class="read-mode-container">
    <div class="read-mode-header">
      <h3 class="read-mode-title">{{ title }}</h3>
      <a-button v-if="!readOnly" type="link" class="edit-button" @click="handleEdit">
        Editar
        <svg
          width="20"
          height="21"
          viewBox="0 0 20 21"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <g clip-path="url(#clip0_10405_21358)">
            <path
              d="M9.16699 3.74726H3.33366C2.89163 3.74726 2.46771 3.92285 2.15515 4.23541C1.84259 4.54797 1.66699 4.9719 1.66699 5.41393V17.0806C1.66699 17.5226 1.84259 17.9465 2.15515 18.2591C2.46771 18.5717 2.89163 18.7473 3.33366 18.7473H15.0003C15.4424 18.7473 15.8663 18.5717 16.1788 18.2591C16.4914 17.9465 16.667 17.5226 16.667 17.0806V11.2473M15.417 2.49726C15.7485 2.16574 16.1982 1.97949 16.667 1.97949C17.1358 1.97949 17.5855 2.16574 17.917 2.49726C18.2485 2.82878 18.4348 3.27842 18.4348 3.74726C18.4348 4.2161 18.2485 4.66574 17.917 4.99726L10.0003 12.9139L6.66699 13.7473L7.50033 10.4139L15.417 2.49726Z"
              stroke="#1284ED"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </g>
          <defs>
            <clipPath id="clip0_10405_21358">
              <rect width="20" height="20" fill="white" transform="translate(0 0.414062)" />
            </clipPath>
          </defs>
        </svg>
      </a-button>
    </div>

    <div class="read-mode-content">
      <slot></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
  defineOptions({
    name: 'ReadModeComponent',
  });

  interface Props {
    title?: string;
    readOnly?: boolean;
  }

  withDefaults(defineProps<Props>(), {
    title: 'Detalles del servicio',
    readOnly: false,
  });

  const emit = defineEmits<{
    (e: 'edit'): void;
  }>();

  const handleEdit = () => {
    emit('edit');
  };
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .read-mode-container {
    background-color: $color-white;
    border-radius: 8px;
  }

  .read-mode-header {
    display: flex;
    align-items: center;
    margin-top: 24px;
    padding-bottom: 16px;
  }

  .read-mode-title {
    font-size: 16px !important;
    line-height: 24px;
    font-weight: 600;
    color: $color-black;
    margin: 0;
  }

  .edit-button {
    display: flex;
    align-items: center;
    gap: 6px;
    color: $color-blue;
    font-size: 16px;
    line-height: 24px;
    font-weight: 500;
    padding: 4px 16px;

    &:hover {
      color: $color-blue;
    }

    :deep(.anticon) {
      font-size: 14px;
    }
  }

  .read-mode-content {
    padding-left: 16px;

    :deep(ul) {
      list-style: none;
      padding: 0;
      margin: 0;

      li {
        display: flex;
        align-items: flex-start;
        margin-bottom: 8px;
        font-size: 14px;
        line-height: 22px;

        &:last-child {
          margin-bottom: 0;
        }

        &::before {
          content: '•';
          color: #262626;
          font-weight: bold;
          margin-right: 12px;
          flex-shrink: 0;
        }
      }
    }

    :deep(.read-item) {
      display: flex;
      align-items: flex-start;
      margin-bottom: 8px;
      font-size: 14px;
      line-height: 22px;

      &:last-child {
        margin-bottom: 0;
      }

      .read-item-label {
        font-weight: 600;
        color: $color-black-3;
        margin-right: 6px;
        flex-shrink: 0;

        &::before {
          content: '•';
          color: $color-black-3;
          font-weight: bold;
          margin-right: 5px;
        }

        &::after {
          content: ':';
        }
      }

      .read-item-value {
        color: $color-black-3;
        word-break: break-word;
        font-size: 14px;
        line-height: 24px;
        font-weight: 400;
      }
    }

    :deep(.read-alert) {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 0 16px;
      background-color: #fff7e6;
      border: 1px solid $color-warning-light;
      border-radius: 6px;
      margin-bottom: 16px;
      width: 480px;

      &:last-child {
        margin-bottom: 0;
      }

      .alert-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;

        svg {
          width: 100%;
          height: 100%;
        }
      }

      .alert-text {
        font-size: 14px;
        line-height: 20px;
        font-weight: 400;
        color: $color-warning-dark;
      }
    }

    :deep(.read-badge) {
      display: inline-flex;
      align-items: center;
      padding: 4px 12px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 500;

      &.badge-success {
        background-color: #f6ffed;
        color: #52c41a;
        border: 1px solid #b7eb8f;
      }

      &.badge-warning {
        background-color: #fff7e6;
        color: #fa8c16;
        border: 1px solid #ffd591;
      }

      &.badge-error {
        background-color: #fff2f0;
        color: #ff4d4f;
        border: 1px solid #ffccc7;
      }

      &.badge-info {
        background-color: #e6f7ff;
        color: #1890ff;
        border: 1px solid #91d5ff;
      }

      &.badge-default {
        background-color: #fafafa;
        color: #8c8c8c;
        border: 1px solid $color-gray-soft;
      }
    }

    :deep(.read-divider) {
      height: 1px;
      background-color: #f0f0f0;
      margin: 16px 0;
      border: none;
    }

    :deep(.read-section) {
      margin-bottom: 24px;

      &:last-child {
        margin-bottom: 0;
      }

      .section-title {
        font-size: 14px;
        font-weight: 600;
        color: #262626;
        margin-bottom: 12px;
      }

      .section-content {
        padding-left: 0;
      }
    }
  }
</style>
