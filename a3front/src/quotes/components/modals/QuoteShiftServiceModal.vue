<script setup lang="ts">
  import { defineProps, defineEmits } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
    serviceName: {
      type: String,
      default: '',
    },
    insertionDate: {
      type: String,
      default: '',
    },
    mode: {
      type: String,
      default: 'shift', // 'shift' or 'date_conflict'
    },
    conflictingServices: {
      type: Array as () => Array<{ id: number; name: string; type: string; dateIn: string }>,
      default: () => [],
    },
    previousDate: {
      type: String,
      default: '',
    },
  });

  const emit = defineEmits(['confirm', 'close']);

  const handleShift = () => {
    emit('confirm', true);
  };

  const handleAddHere = () => {
    emit('confirm', false);
  };

  const handleClose = () => {
    emit('close');
  };
</script>

<template>
  <a-modal
    :visible="isOpen"
    :footer="null"
    :closable="true"
    @cancel="handleClose"
    width="500px"
    class="quote-shift-modal-wrapper"
    :bodyStyle="{ padding: '32px' }"
  >
    <template #closeIcon>
      <div class="custom-close-btn">
        <svg
          width="12"
          height="12"
          viewBox="0 0 14 14"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M1 1L13 13M1 13L13 1"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </div>
    </template>

    <template #title>
      <div class="modal-header-title">
        {{
          mode === 'shift'
            ? t('quote.modal.shift_service_title')
            : t('quote.modal.date_conflict_title')
        }}
      </div>
    </template>

    <div class="shift-content">
      <div class="warning-container">
        <div class="warning-circle">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2L1 21h22L12 2zm1 14h-2v-2h2v2zm0-4h-2V8h2v4z" />
          </svg>
        </div>
      </div>

      <p class="message" v-if="mode === 'shift'">
        {{ t('quote.modal.shift_service_message') }}
      </p>

      <div v-else class="conflict-message">
        <p class="message mb-0">
          {{ t('quote.modal.date_conflict_message', { date: insertionDate }) }}
        </p>
        <!-- div class="conflicting-services">
          <div v-for="s in conflictingServices" :key="s.id" class="service-tag">
            <span class="type-badge">{{
              s.type === 'hotel' ? t('global.label.hotel') : t('global.label.service')
            }}</span>
            <span class="name">{{ s.name }}</span>
          </div>
        </div -->
      </div>

      <div class="actions">
        <button class="action-card" @click="handleShift">
          <div class="icon-circle icon-shift m-0">
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </div>
          <div class="text-content">
            <span class="action-title">{{ t('quote.modal.action_shift_title') }}</span>
            <span class="action-desc">{{ t('quote.modal.action_shift_desc') }}</span>
          </div>
        </button>

        <button class="action-card" @click="handleAddHere">
          <div class="icon-circle icon-add m-0">
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </div>
          <div class="text-content">
            <span class="action-title">{{ t('quote.modal.action_add_here_title') }}</span>
            <span class="action-desc">{{ t('quote.modal.action_add_here_desc') }}</span>
          </div>
        </button>
      </div>
    </div>
  </a-modal>
</template>

<style lang="scss">
  .quote-shift-modal-wrapper {
    .ant-modal-content {
      border-radius: 20px;
      overflow: hidden;
      box-shadow:
        0 10px 25px -5px rgba(0, 0, 0, 0.1),
        0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
    .ant-modal-close {
      top: 24px;
      right: 24px;
      color: #eb5757;
      background: #fdf2f2;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      &:hover {
        background: #fee2e2;
        color: #ef4444;
        transform: rotate(90deg) scale(1.1);
      }
      .ant-modal-close-x {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
      }
    }
    .modal-header-title {
      font-family: 'Montserrat', sans-serif;
      font-weight: 600;
      font-size: 26px;
      color: #111827;
      text-align: center;
      width: 100%;
      letter-spacing: -0.04em;
    }
    .custom-close-btn {
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }
</style>

<style lang="scss" scoped>
  .shift-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-family: 'Montserrat', sans-serif;

    .warning-container {
      margin-bottom: 24px;
      .warning-circle {
        width: 80px;
        height: 80px;
        background-color: #fefce8;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #eab308;
      }
    }

    .message {
      text-align: center;
      font-size: 17px;
      font-weight: 500;
      line-height: 1.6;
      color: #6b7280;
      margin-bottom: 32px;
      max-width: 420px;
    }

    .conflict-message {
      width: 100%;
      margin-bottom: 32px;

      .conflicting-services {
        margin-top: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 160px;
        overflow-y: auto;
        padding: 4px;

        .service-tag {
          display: flex;
          align-items: center;
          gap: 14px;
          padding: 12px 16px;
          background: #f9fafb;
          border-radius: 12px;
          border: 1px solid #f3f4f6;
          transition: border-color 0.2s;

          &:hover {
            border-color: #d1d5db;
          }

          .type-badge {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            color: #ef4444;
            background: #fef2f2;
            padding: 3px 10px;
            border-radius: 6px;
            white-space: nowrap;
            letter-spacing: 0.05em;
          }

          .name {
            font-size: 14px;
            color: #374151;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
          }
        }
      }
    }

    .actions {
      display: flex;
      flex-direction: column;
      gap: 18px;
      width: 100%;

      .action-card {
        display: flex;
        align-items: center;
        gap: 28px;
        padding: 10px 20px;
        background: #ffffff;
        border: 1.5px solid #f3f4f6;
        border-radius: 24px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        text-align: left;
        outline: none;

        &:hover {
          border-color: #f59e0b;
          background-color: #fffbf0;
          transform: translateY(-5px);
          box-shadow:
            0 20px 25px -5px rgba(245, 158, 11, 0.15),
            0 10px 10px -5px rgba(245, 158, 11, 0.04);
        }

        .icon-circle {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          background: #f3f4f6;
          color: #9ca3af;
          transition: all 0.3s ease;

          &:before {
            display: none !important;
          }
        }

        .text-content {
          display: flex;
          flex-direction: column;
          gap: 8px;

          .action-title {
            font-weight: 500;
            font-size: 16px;
            color: #111827;
            letter-spacing: -0.02em;
          }

          .action-desc {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
          }
        }

        &:hover {
          .icon-circle {
            background: #f59e0b;
            color: #ffffff;
            transform: scale(1.1);
          }
        }

        &:active {
          transform: translateY(0);
        }
      }
    }
  }
</style>
