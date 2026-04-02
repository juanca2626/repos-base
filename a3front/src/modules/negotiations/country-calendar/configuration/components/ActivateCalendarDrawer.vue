<template>
  <a-drawer
    :open="open"
    :closable="false"
    :width="500"
    :footer="null"
    placement="right"
    class="activate-calendar-drawer"
    @close="handleClose"
  >
    <!-- Header -->
    <template #title>
      <div class="drawer-header">
        <div class="drawer-title">
          <span>Configurar vigencia de calendario</span>
        </div>
        <a-button type="text" class="close-button" @click="handleClose">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </a-button>
      </div>
    </template>

    <!-- Form Content -->
    <div class="drawer-content">
      <!-- Calendar Header Info -->
      <div class="header-info">
        <div class="country-info">
          <!-- Flag placeholder or icon if available -->
          <font-awesome-icon :icon="['fas', 'flag']" class="flag-icon" />
          <span class="country-name">{{ calendar?.country }}</span>
        </div>
        <div class="holiday-count">N° de Fechas festivas: {{ calendar?.holidaysCount || 0 }}</div>
      </div>

      <!-- Estado de la vigencia -->
      <div class="status-section">
        <label class="field-label">Estado de la vigencia</label>
        <div class="status-card">
          <div class="status-row">
            <font-awesome-icon
              :icon="['fas', 'circle-check']"
              class="check-icon"
              :class="{ active: isCalendarActive }"
            />
            <span class="status-text"
              >Vigencia actual: {{ isCalendarActive ? 'Activa' : 'Inactiva' }}</span
            >
          </div>
          <div class="status-date">
            Última modificación: {{ formatModificationDate(calendar?.createdAt) }}
          </div>
        </div>
      </div>

      <!-- Configurar fecha -->
      <div class="period-section">
        <label class="field-label">Configurar fecha</label>
        <div class="date-pickers-row">
          <div class="date-picker-col">
            <label class="date-label">Desde:</label>
            <a-date-picker
              v-model:value="dateFrom"
              placeholder="AAAA"
              format="YYYY"
              picker="year"
              size="large"
              class="date-picker date-picker--disabled"
              :disabled="true"
            />
          </div>
          <div class="date-picker-col">
            <label class="date-label">Hasta:</label>
            <a-date-picker
              v-model:value="dateTo"
              placeholder="AAAA"
              format="YYYY"
              picker="year"
              size="large"
              class="date-picker"
              :disabled-date="disabledDateTo"
            />
          </div>
        </div>
      </div>

      <!-- Alerta: año no mayor al actual -->
      <div v-if="showYearError" class="alert-section">
        <a-alert type="warning" show-icon closable @close="showYearError = false">
          <template #message>
            <span class="alert-message">
              El año de vigencia debe ser mayor al año actual ({{ calendar?.yearTo }}).
            </span>
          </template>
          <template #icon>
            <font-awesome-icon :icon="['fas', 'triangle-exclamation']" />
          </template>
        </a-alert>
      </div>

      <!-- Botones -->
      <div class="actions-section">
        <a-button class="cancel-button" @click="handleClose"> Cancelar </a-button>
        <a-button type="primary" class="submit-button" :disabled="!isValid" @click="handleSubmit">
          Guardar
        </a-button>
      </div>
    </div>
  </a-drawer>
</template>

<script lang="ts" setup>
  import { ref, watch, computed } from 'vue';
  import type { Dayjs } from 'dayjs';
  import dayjs from 'dayjs';

  interface CountryCalendarItem {
    id: number;
    createdAt: string;
    country: string;
    yearFrom: number;
    yearTo: number;
    enabled?: boolean;
    deactivationReason?: string;
    holidaysCount?: number;
  }

  const props = defineProps<{
    open: boolean;
    calendar: CountryCalendarItem | null;
  }>();

  const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit', payload: { dateFrom: Dayjs; dateTo: Dayjs }): void;
  }>();

  // Computed para determinar si la vigencia está activa
  const isCalendarActive = computed(() => {
    if (!props.calendar?.yearTo) return false;
    const currentYear = new Date().getFullYear();
    return props.calendar.yearTo >= currentYear;
  });

  const dateFrom = ref<Dayjs | null>(null);
  const dateTo = ref<Dayjs | null>(null);
  const showYearError = ref(false);

  watch(
    () => props.open,
    (newVal) => {
      if (newVal) {
        // Pre-fill Desde with the existing yearFrom from the record
        dateFrom.value = props.calendar?.yearFrom
          ? dayjs().year(props.calendar.yearFrom).startOf('year')
          : null;
        dateTo.value = null;
      }
    }
  );

  const isValid = computed(() => dateTo.value !== null);

  // Deshabilita en el picker todos los años <= yearTo actual del registro
  const disabledDateTo = (current: Dayjs): boolean => {
    if (!props.calendar?.yearTo) return false;
    return current.year() <= props.calendar.yearTo;
  };

  const formatModificationDate = (dateStr?: string) => {
    if (!dateStr) return '--/--/----';
    // Mocking last modification as createdAt for now since backend mapping doesn't have updated_at in interface yet
    return new Date(dateStr).toLocaleDateString('es-PE', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  };

  const handleClose = () => {
    showYearError.value = false;
    emit('update:open', false);
  };

  // Ocultar alerta cuando cambia dateTo
  watch(dateTo, () => {
    if (showYearError.value) showYearError.value = false;
  });

  const handleSubmit = () => {
    if (!isValid.value) return;

    const newYearTo = dateTo.value!.year();
    const currentYearTo = props.calendar?.yearTo;

    if (currentYearTo !== undefined && newYearTo <= currentYearTo) {
      showYearError.value = true;
      return;
    }

    emit('submit', { dateFrom: dateFrom.value!, dateTo: dateTo.value! });
    handleClose();
  };
</script>

<style lang="scss" scoped>
  .activate-calendar-drawer {
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
    .header-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;

      .country-info {
        display: flex;
        align-items: center;
        gap: 8px;

        .flag-icon {
          font-size: 20px;
          // Placeholder color for flag
          color: #d80404;
        }

        .country-name {
          font-size: 16px;
          font-weight: 500;
          color: #2f353a;
        }
      }

      .holiday-count {
        font-size: 14px;
        color: #2f353a;
        font-weight: 500;
      }
    }

    .field-label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #2f353a;
      margin-bottom: 8px;
    }

    .status-section {
      margin-bottom: 24px;

      .status-card {
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        padding: 12px 16px;

        .status-row {
          display: flex;
          align-items: center;
          gap: 8px;
          margin-bottom: 4px;

          .check-icon {
            color: #bfbfbf; // Gray check by default (inactive)
            font-size: 16px;

            &.active {
              color: #52c41a; // Green check when active
            }
          }

          .status-text {
            font-size: 14px;
            font-weight: 500;
            color: #2f353a;
          }
        }

        .status-date {
          font-size: 12px;
          color: #8c8c8c; // Gray text for date
          margin-left: 24px; // Align with text
        }
      }
    }

    .period-section {
      margin-bottom: 24px;

      .date-pickers-row {
        display: flex;
        gap: 16px;

        .date-picker-col {
          flex: 1;

          .date-label {
            display: block;
            font-size: 14px;
            color: #2f353a;
            margin-bottom: 4px;
          }

          .date-picker {
            width: 100%;
            height: 40px;

            :deep(&.ant-picker) {
              width: 100%;
              height: 40px;
              border-radius: 4px;
              border: 1px solid #d9d9d9;

              .ant-picker-input > input {
                height: 38px;
              }
            }

            &.date-picker--disabled {
              :deep(&.ant-picker) {
                background-color: #f5f5f5;
                cursor: not-allowed;

                .ant-picker-input > input {
                  color: #8c8c8c;
                  cursor: not-allowed;
                }
              }
            }
          }
        }
      }
    }

    .alert-section {
      margin-bottom: 16px;

      .alert-message {
        font-size: 14px;
        color: #2f353a;
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
        border: none;

        &:not(:disabled) {
          background-color: #d80424;
          border-color: #d80424;
          color: #fff;
        }
        // Actually let's use standard primary logic, better UX unless specified.
        // Screenshot button looks disabled/gray.
      }
    }
  }
</style>
