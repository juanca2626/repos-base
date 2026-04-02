<template>
  <a-drawer
    :open="open"
    :closable="false"
    :width="500"
    :footer="null"
    placement="right"
    class="modify-holiday-drawer"
    @close="handleClose"
  >
    <!-- Header -->
    <template #title>
      <div class="drawer-header">
        <div class="drawer-title">
          <span>Configurar nombre del festivo</span>
        </div>
        <a-button type="text" class="close-button" @click="handleClose">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </a-button>
      </div>
    </template>

    <!-- Form Content -->
    <div class="drawer-content">
      <!-- Vigencia fecha festiva -->
      <div class="vigencia-section">
        <label class="field-label">Vigencia fecha festiva</label>
        <div class="vigencia-card">
          <div class="vigencia-status">
            <span class="status-dot active"></span>
            <span class="status-text">Vigencia actual: Activa</span>
          </div>
          <div class="vigencia-date">
            Fecha de creación del festivo: {{ formattedCreationDate }}
          </div>
        </div>
      </div>

      <!-- Cambiar nombre del festivo -->
      <div class="name-section">
        <label class="field-label">Cambiar nombre del festivo</label>
        <a-input
          v-model:value="newName"
          placeholder="Escribe aquí..."
          size="large"
          class="name-input"
        />
      </div>

      <!-- Motivo de modificación nombre -->
      <div class="reason-section">
        <label class="field-label">Motivo de modificación nombre</label>
        <a-textarea
          v-model:value="reason"
          placeholder="Escribe aquí..."
          :maxlength="500"
          :rows="3"
          :show-count="true"
          class="reason-input"
        />
      </div>

      <!-- Botones -->
      <div class="actions-section">
        <a-button class="cancel-button" @click="handleClose"> Cancelar </a-button>
        <a-button
          type="primary"
          class="submit-button"
          :disabled="!isFormValid"
          :loading="loading"
          @click="handleSubmit"
        >
          Guardar
        </a-button>
      </div>
    </div>
  </a-drawer>
</template>

<script lang="ts" setup>
  import { ref, computed, watch } from 'vue';

  interface Holiday {
    id: number;
    name: string;
    date: string;
    holidayType: 'general' | 'turistico' | 'ciudad';
    ciudad?: string;
    createdAt?: string;
  }

  interface HolidayLog {
    id: number;
    action: string;
    reason?: string;
    created_at: string;
  }

  const props = defineProps<{
    open: boolean;
    holiday: Holiday | null;
    logs?: HolidayLog[];
    loading?: boolean;
  }>();

  const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit', newName: string, reason: string): void;
  }>();

  const newName = ref('');
  const reason = ref('');

  watch(
    () => props.open,
    (newVal) => {
      if (newVal && props.holiday) {
        newName.value = props.holiday.name;
        reason.value = '';
      }
    }
  );

  const isFormValid = computed(() => {
    return newName.value.trim().length > 0 && reason.value.trim().length > 0;
  });

  const formattedCreationDate = computed(() => {
    // 1. Try holiday property
    let dateStr = props.holiday?.createdAt;

    // 2. Try logs
    if (!dateStr && props.logs && props.logs.length > 0) {
      // Sort ascending to find the first event (Creation)
      const sortedLogs = [...props.logs].sort((a, b) => {
        return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
      });
      dateStr = sortedLogs[0].created_at;
    }

    const formattedDate = formatCreatedAt(dateStr);
    return formattedDate || 'Fecha no disponible';
  });

  const formatCreatedAt = (dateStr?: string) => {
    if (!dateStr) return '';
    try {
      const date =
        dateStr.includes('T') || dateStr.includes(' ')
          ? new Date(dateStr)
          : new Date(dateStr + 'T12:00:00');

      if (isNaN(date.getTime())) return '';
      return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
    } catch (e) {
      console.log(e);
      return '';
    }
  };

  const handleClose = () => {
    emit('update:open', false);
  };

  const handleSubmit = () => {
    if (isFormValid.value) {
      emit('submit', newName.value, reason.value);
    }
  };
</script>

<style lang="scss" scoped>
  .modify-holiday-drawer {
    :deep(.ant-drawer-header) {
      padding: 16px 24px;
      border-bottom: 1px solid #f0f0f0;
    }

    :deep(.ant-drawer-body) {
      padding: 24px;
    }
  }

  .drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;

    .drawer-title {
      font-size: 18px;
      font-weight: 600;
      color: #2f353a;
    }

    .close-button {
      color: #595959;
      font-size: 18px;
      padding: 4px 8px;

      &:hover {
        color: #2f353a;
      }
    }
  }

  .drawer-content {
    .field-label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #2f353a;
      margin-bottom: 8px;
    }

    .vigencia-section {
      margin-bottom: 24px;

      .vigencia-card {
        background: #fff;
        border: 1px solid #e6e8eb;
        border-radius: 8px;
        padding: 12px 16px;

        .vigencia-status {
          display: flex;
          align-items: center;
          gap: 8px;
          margin-bottom: 4px;

          .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;

            &.active {
              background-color: #22c55e;
            }
          }

          .status-text {
            font-size: 14px;
            font-weight: 500;
            color: #2f353a;
          }
        }

        .vigencia-date {
          font-size: 12px;
          color: #9aa6b8;
        }
      }
    }

    .name-section {
      margin-bottom: 24px;

      .name-input {
        width: 100%;
        height: 40px;

        :deep(.ant-input) {
          height: 40px;
          border-radius: 4px;
        }
      }
    }

    .reason-section {
      margin-bottom: 24px;

      .reason-input {
        border-radius: 4px;

        :deep(.ant-input) {
          resize: none;
        }
      }
    }

    .actions-section {
      display: flex;
      gap: 12px;
      margin-top: 24px;

      .cancel-button {
        height: 40px;
        padding: 0 24px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid #d9d9d9;
        color: #2f353a;
        background: #fff;

        &:hover {
          border-color: #2f353a;
        }
      }

      .submit-button {
        height: 40px;
        padding: 0 24px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;

        &:not(:disabled) {
          background-color: #d80424;
          border-color: #d80424;
          color: #fff;
        }

        &:disabled {
          background-color: #d9d9d9;
          border-color: #d9d9d9;
          color: #fff;
          cursor: not-allowed;
        }
      }
    }
  }
</style>
