<template>
  <a-drawer
    :open="open"
    :closable="false"
    :width="500"
    :footer="null"
    placement="right"
    class="activate-holiday-drawer"
    @close="handleClose"
  >
    <!-- Header -->
    <template #title>
      <div class="drawer-header">
        <div class="drawer-title">
          <font-awesome-icon :icon="['fas', 'calendar-check']" class="title-icon" />
          <span>Activar fecha festiva</span>
        </div>
        <a-button type="text" class="close-button" @click="handleClose">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </a-button>
      </div>
    </template>

    <!-- Form Content -->
    <div class="drawer-content">
      <!-- Holiday Info Card -->
      <div class="info-card">
        <div class="info-row">
          <span class="info-label">Festividad</span>
          <span class="info-value">{{ holiday?.name }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Localidad</span>
          <span class="info-value">
            <font-awesome-icon :icon="['fas', 'location-dot']" class="location-icon" />
            {{ holiday?.ciudad || 'Nacional' }}
          </span>
        </div>
        <div class="info-row">
          <span class="info-label">Fecha</span>
          <span class="info-value">{{ formatDate(holiday?.date, holiday?.dateTo) }}</span>
        </div>
      </div>

      <!-- Historial de vigencia -->
      <div class="history-section">
        <label class="field-label">Historial de vigencia</label>

        <div v-if="loadingLogs" class="loading-container">
          <a-spin />
        </div>

        <template v-else>
          <div v-for="item in historyItems" :key="item.id" class="history-card">
            <div class="history-title">{{ item.title }}</div>
            <div v-if="item.reason" class="history-subtitle">Motivo: {{ item.reason }}</div>
          </div>
          <div v-if="historyItems.length === 0" class="history-empty">
            No hay historial registrado
          </div>
        </template>
      </div>

      <!-- Motivo de activación -->
      <div class="reason-section">
        <label class="field-label">Motivo de activación</label>
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
          :disabled="!reason.trim()"
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
  import { computed, watch, ref } from 'vue';

  interface Holiday {
    id: number;
    name: string;
    date: string;
    dateTo?: string;
    holidayType: 'general' | 'turistico' | 'ciudad';
    ciudad?: string;
    createdAt?: string;
    deactivationReason?: string;
    enabled?: boolean;
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
    loadingLogs?: boolean;
  }>();

  // Map logs to display items
  const historyItems = computed(() => {
    if (!props.logs || props.logs.length === 0) {
      // Fallback if no logs are present (e.g. only holiday creation info available via prop)
      if (props.holiday?.createdAt) {
        return [
          {
            id: 0,
            title: `Creada el ${formatCreatedAt(props.holiday.createdAt)}`,
            reason:
              props.holiday.deactivationReason ||
              (props.holiday.enabled ? 'Festivo activo' : 'Desactivado'),
          },
        ];
      }
      return [];
    }

    return props.logs.map((log) => {
      const actionUpper = log.action.toUpperCase();
      let label = 'Modificada el';
      if (actionUpper === 'DEACTIVATED' || actionUpper === 'DEACTIVATE') label = 'Desactivada el';
      else if (actionUpper === 'ACTIVATED' || actionUpper === 'ACTIVATE') label = 'Activada el';
      else if (actionUpper === 'CREATED' || actionUpper === 'CREATE') label = 'Creada el';

      return {
        id: log.id,
        title: `${label} ${formatCreatedAt(log.created_at)}`,
        reason:
          actionUpper === 'CREATED' || actionUpper === 'CREATE'
            ? null
            : log.reason || 'Sin motivo registrado',
      };
    });
  });

  const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit', reason: string): void;
  }>();

  const reason = ref('');

  watch(
    () => props.open,
    (newVal) => {
      if (newVal) {
        reason.value = '';
      }
    }
  );

  const formatDate = (dateStr?: string, dateToStr?: string) => {
    if (!dateStr) return '';

    const months = [
      'enero',
      'febrero',
      'marzo',
      'abril',
      'mayo',
      'junio',
      'julio',
      'agosto',
      'septiembre',
      'octubre',
      'noviembre',
      'diciembre',
    ];

    const formatSingleDate = (dStr: string) => {
      const date = new Date(dStr + 'T12:00:00');
      // If needed, we can prepend day name here too, but current requirement is just "completo con año"
      // mimicking the single date format at the bottom
      return `${date.getDate()} de ${months[date.getMonth()]} ${date.getFullYear()}`;
    };

    if (dateToStr && dateToStr !== dateStr) {
      return `${formatSingleDate(dateStr)} - ${formatSingleDate(dateToStr)}`;
    }

    return formatSingleDate(dateStr);
  };

  const formatCreatedAt = (dateStr?: string) => {
    if (!dateStr) return '';
    // Handle full timestamp vs just date
    const date =
      dateStr.includes(' ') || dateStr.includes('T')
        ? new Date(dateStr)
        : new Date(dateStr + 'T12:00:00');

    const months = [
      'enero',
      'febrero',
      'marzo',
      'abril',
      'mayo',
      'junio',
      'julio',
      'agosto',
      'septiembre',
      'octubre',
      'noviembre',
      'diciembre',
    ];
    return `${date.getDate()} de ${months[date.getMonth()]} del ${date.getFullYear()}`;
  };

  const handleClose = () => {
    emit('update:open', false);
  };

  const handleSubmit = () => {
    if (reason.value.trim()) {
      emit('submit', reason.value);
    }
  };
</script>

<style lang="scss" scoped>
  .activate-holiday-drawer {
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
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 18px;
      font-weight: 600;
      color: #2f353a;

      .title-icon {
        font-size: 20px;
      }
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
    .info-card {
      background: #f9fafb;
      border: 1px solid #e6e8eb;
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 24px;

      .info-row {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 12px;

        &:last-child {
          margin-bottom: 0;
        }

        .info-label {
          font-size: 12px;
          font-weight: 600;
          color: #5f6d7e;
        }

        .info-value {
          font-size: 14px;
          color: #2f353a;
          display: flex;
          align-items: center;
          gap: 6px;

          .location-icon {
            font-size: 12px;
            color: #5f6d7e;
          }
        }
      }
    }

    .field-label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #2f353a;
      margin-bottom: 8px;
    }

    .history-section {
      margin-bottom: 24px;

      .loading-container {
        display: flex;
        justify-content: center;
        padding: 24px 0;
      }

      .history-card {
        background: #fff;
        border: 1px solid #e6e8eb;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 8px;

        &:last-child {
          margin-bottom: 0;
        }

        .history-title {
          font-size: 14px;
          font-weight: 500;
          color: #2f353a;
        }

        .history-subtitle {
          font-size: 12px;
          color: #9aa6b8;
          margin-top: 4px;
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
