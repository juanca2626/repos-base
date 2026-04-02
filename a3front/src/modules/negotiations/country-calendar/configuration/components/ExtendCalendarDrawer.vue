<template>
  <a-drawer
    :open="open"
    :closable="false"
    :width="450"
    :footer="null"
    placement="right"
    class="extend-calendar-drawer"
    @close="handleClose"
  >
    <!-- Header -->
    <template #title>
      <div class="drawer-header">
        <div class="drawer-title">
          <font-awesome-icon :icon="['fas', 'calendar-plus']" class="title-icon" />
          <span>Extender vigencia</span>
        </div>
        <a-button type="text" class="close-button" @click="handleClose">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </a-button>
      </div>
    </template>

    <!-- Form Content -->
    <div class="drawer-content">
      <a-form layout="vertical" :model="formState">
        <!-- País (Readonly) -->
        <a-form-item label="País:" class="form-item">
          <a-input
            v-model:value="countryName"
            :disabled="true"
            size="large"
            class="read-only-input"
          />
        </a-form-item>

        <!-- Periodo actual (Readonly) -->
        <a-form-item label="Periodo actual:" class="form-item">
          <div class="period-display">
            <span class="period-value">{{ currentPeriodDisplay }}</span>
          </div>
        </a-form-item>

        <!-- Nuevo Periodo -->
        <div class="period-section">
          <div class="period-title">Ingresa el nuevo periodo de vigencia</div>
          <div class="date-pickers-row">
            <div class="date-picker-col">
              <label class="date-label">Desde:</label>
              <a-date-picker
                v-model:value="formState.dateFrom"
                placeholder="DD/MM/AAAA"
                format="DD/MM/YYYY"
                size="large"
                class="date-picker"
                :disabled="true"
              />
            </div>
            <div class="date-picker-col">
              <label class="date-label">Hasta:</label>
              <a-date-picker
                v-model:value="formState.dateTo"
                placeholder="DD/MM/AAAA"
                format="DD/MM/YYYY"
                size="large"
                class="date-picker"
              />
            </div>
          </div>
        </div>

        <!-- Botón Guardar -->
        <div class="submit-section">
          <a-button
            type="primary"
            class="submit-button"
            :disabled="!isFormValid"
            @click="handleSubmit"
          >
            Guardar
          </a-button>
        </div>
      </a-form>
    </div>
  </a-drawer>
</template>

<script lang="ts" setup>
  import { reactive, computed, watch, ref } from 'vue';
  import type { Dayjs } from 'dayjs';
  import dayjs from 'dayjs';

  interface FormState {
    dateFrom: Dayjs | null;
    dateTo: Dayjs | null;
  }

  const props = defineProps<{
    open: boolean;
    country: string;
    currentPeriodStart?: string; // YYYY-MM-DD
    currentPeriodEnd?: string; // YYYY-MM-DD
  }>();

  const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit', payload: { dateTo: Dayjs }): void;
  }>();

  const countryName = ref(props.country);

  // Computed for display
  const currentPeriodDisplay = computed(() => {
    if (props.currentPeriodStart && props.currentPeriodEnd) {
      const start = dayjs(props.currentPeriodStart).format('DD/MM/YYYY');
      const end = dayjs(props.currentPeriodEnd).format('DD/MM/YYYY');
      return `${start} - ${end}`;
    }
    return 'No disponible';
  });

  // Initialize dateFrom to the day after current period ends
  const formState = reactive<FormState>({
    dateFrom: props.currentPeriodEnd ? dayjs(props.currentPeriodEnd).add(1, 'day') : dayjs(),
    dateTo: null,
  });

  watch(
    () => props.country,
    (newVal) => {
      countryName.value = newVal;
    }
  );

  watch(
    () => props.currentPeriodEnd,
    (newVal) => {
      if (newVal) {
        formState.dateFrom = dayjs(newVal).add(1, 'day');
      }
    }
  );

  const isFormValid = computed(() => {
    return formState.dateTo;
  });

  const handleClose = () => {
    emit('update:open', false);
    // Reset form
    formState.dateTo = null;
  };

  const handleSubmit = () => {
    if (isFormValid.value) {
      emit('submit', { dateTo: formState.dateTo! });
      handleClose();
    }
  };
</script>

<style lang="scss" scoped>
  .extend-calendar-drawer {
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
    .form-item {
      margin-bottom: 16px;

      :deep(.ant-form-item-label) {
        padding-bottom: 4px;

        label {
          color: #2f353a;
          font-size: 14px;
        }
      }

      .read-only-input {
        background-color: #f5f5f5;
        color: #595959;
        border-color: #d9d9d9;
        cursor: not-allowed;
      }

      .period-display {
        padding: 8px 12px;
        background-color: #f5f5f5;
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        color: #595959;
        font-size: 14px;
      }
    }

    .period-section {
      margin-top: 24px;

      .period-title {
        font-size: 14px;
        font-weight: 500;
        color: #2f353a;
        margin-bottom: 8px;
      }

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

              &.ant-picker-disabled {
                background-color: #f5f5f5;
              }
            }
          }
        }
      }
    }

    .submit-section {
      display: flex;
      justify-content: flex-end;
      margin-top: 24px;

      .submit-button {
        min-width: 120px;
        height: 40px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;

        &:not(:disabled) {
          background-color: var(--color-primary);
          border-color: var(--color-primary);
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
