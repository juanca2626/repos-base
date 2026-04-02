<template>
  <a-drawer
    :open="open"
    :closable="false"
    :width="450"
    :footer="null"
    placement="right"
    class="new-calendar-drawer"
    @close="handleClose"
  >
    <!-- Header -->
    <template #title>
      <div class="drawer-header">
        <div class="drawer-title">
          <font-awesome-icon :icon="['fas', 'calendar-plus']" class="title-icon" />
          <span>Nuevo calendario</span>
        </div>
        <a-button type="text" class="close-button" @click="handleClose">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </a-button>
      </div>
    </template>

    <!-- Form Content -->
    <div class="drawer-content">
      <a-form layout="vertical" :model="formState">
        <!-- País -->
        <a-form-item label="Seleccionar país:" class="form-item">
          <a-select
            v-model:value="formState.country"
            placeholder="Selecciona"
            :options="countryOptions"
            size="large"
            class="country-select"
            show-search
            :filter-change="false"
            :filter-option="
              (input: string, option: any) =>
                (option?.label ?? '').toLowerCase().includes(input.toLowerCase())
            "
          />
        </a-form-item>

        <!-- Periodo de vigencia -->
        <div class="period-section">
          <div class="period-title">Periodo de vigencia</div>
          <div class="date-pickers-row">
            <div class="date-picker-col">
              <label class="date-label">Desde:</label>
              <a-date-picker
                v-model:value="formState.dateFrom"
                picker="year"
                placeholder="AAAA"
                format="YYYY"
                size="large"
                class="date-picker"
                :disabled-date="disabledPastYears"
              />
            </div>
            <div class="date-picker-col">
              <label class="date-label">Hasta:</label>
              <a-date-picker
                v-model:value="formState.dateTo"
                picker="year"
                placeholder="AAAA"
                format="YYYY"
                size="large"
                class="date-picker"
                :disabled-date="disabledDateTo"
              />
            </div>
          </div>
        </div>

        <!-- Alerta Amarilla: Fecha ya creada -->
        <div v-if="showYellowAlert" class="alert-section">
          <a-alert type="warning" show-icon closable @close="showYellowAlert = false">
            <template #message>
              <span class="alert-message">{{ errorMessage }}</span>
            </template>
            <template #icon>
              <font-awesome-icon :icon="['fas', 'triangle-exclamation']" />
            </template>
          </a-alert>
        </div>

        <!-- Alerta Roja: Periodo caducó -->
        <div v-if="showRedAlert" class="alert-section">
          <a-alert type="error" show-icon closable @close="showRedAlert = false">
            <template #message>
              <div class="error-alert-content">
                <span class="alert-message">El periodo vigente para este país ya caducó</span>
                <a class="extend-link" @click="handleExtendValidity">
                  Extender vigencia
                  <font-awesome-icon :icon="['fas', 'arrow-right']" class="link-arrow" />
                </a>
              </div>
            </template>
            <template #icon>
              <font-awesome-icon :icon="['fas', 'circle-exclamation']" />
            </template>
          </a-alert>
        </div>

        <!-- Botón Siguiente - Dentro del contenido, no en footer -->
        <div class="submit-section">
          <a-button
            type="primary"
            class="submit-button"
            :disabled="!isFormValid"
            :loading="loading"
            @click="handleSubmit"
          >
            Siguiente
          </a-button>
        </div>
      </a-form>
    </div>
  </a-drawer>
</template>

<script lang="ts" setup>
  import { reactive, computed, ref, watch } from 'vue';
  import dayjs from 'dayjs';
  import type { Dayjs } from 'dayjs';

  interface FormState {
    country: number | null;
    dateFrom: Dayjs | null;
    dateTo: Dayjs | null;
  }

  defineProps<{
    open: boolean;
    loading?: boolean;
  }>();

  const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit', data: FormState): void;
    (e: 'extend', country: string): void;
  }>();

  const formState = reactive<FormState>({
    country: null,
    dateFrom: null,
    dateTo: null,
  });

  // Alert states
  const showYellowAlert = ref(false);
  const errorMessage = ref('Esta fecha ya fue creada para este país');
  const showRedAlert = ref(false);

  import { onMounted } from 'vue';
  import { useSupportStore } from '../store/useSupportStore';
  import { storeToRefs } from 'pinia';

  // Use store
  const supportStore = useSupportStore();
  const { countries } = storeToRefs(supportStore);

  onMounted(() => {
    supportStore.fetchResources();
  });

  const countryOptions = computed(() => {
    return countries.value.map((c) => ({ value: c.id, label: c.name }));
  });

  const isFormValid = computed(() => {
    return formState.country && formState.dateFrom && formState.dateTo;
  });

  // Deshabilitar años anteriores al actual
  const currentYear = dayjs().startOf('year');

  const disabledPastYears = (date: Dayjs) => {
    return date.isBefore(currentYear, 'year');
  };

  const disabledDateTo = (date: Dayjs) => {
    if (formState.dateFrom) {
      return date.isBefore(formState.dateFrom, 'year');
    }
    return date.isBefore(currentYear, 'year');
  };

  // Method exposed to parent to show error from backend (yellow alert)
  const setError = (message: string) => {
    errorMessage.value = message;
    showYellowAlert.value = true;
  };

  // Method to show expired period alert (red alert) - from backend
  const setExpiredError = (message?: string) => {
    if (message) {
      errorMessage.value = message;
    }
    showRedAlert.value = true;
  };

  // Expose methods
  defineExpose({ setError, setExpiredError });

  // Reset alerts when form changes
  watch([() => formState.country, () => formState.dateFrom, () => formState.dateTo], () => {
    if (showYellowAlert.value) showYellowAlert.value = false;
    if (showRedAlert.value) showRedAlert.value = false;
  });

  const handleClose = () => {
    emit('update:open', false);
    // Reset form and alerts
    formState.country = null;
    formState.dateFrom = null;
    formState.dateTo = null;
    showYellowAlert.value = false;
    showRedAlert.value = false;
  };

  const handleSubmit = () => {
    if (isFormValid.value && !showRedAlert.value) {
      emit('submit', { ...formState });
      // Do not close automatically, wait for parent to close on success or set error
    }
  };

  const handleExtendValidity = () => {
    if (formState.country) {
      const countryLabel =
        countryOptions.value.find((c) => c.value === formState.country)?.label || '';
      emit('extend', countryLabel);
      handleClose();
    }
  };
</script>

<style lang="scss" scoped>
  .new-calendar-drawer {
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
    }

    .country-select {
      width: 100%;

      :deep(.ant-select-selector) {
        height: 40px;
        border-radius: 4px;
        border: 1px solid #d9d9d9;

        .ant-select-selection-placeholder {
          line-height: 38px;
          color: #bfbfbf;
        }

        .ant-select-selection-item {
          line-height: 38px;
        }
      }
    }

    .period-section {
      margin-top: 8px;

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
            }
          }
        }
      }
    }

    .alert-section {
      margin-top: 16px;

      .alert-message {
        font-size: 14px;
        color: #2f353a;
      }

      .error-alert-content {
        display: flex;
        flex-direction: column;
        gap: 4px;

        .extend-link {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          color: #d80424; // Red color for error link
          font-weight: 600;
          font-size: 12px;
          margin-top: 4px;
          cursor: pointer;

          &:hover {
            text-decoration: underline;
          }

          .link-arrow {
            font-size: 10px;
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
          background-color: #d80424;
          border-color: #d80424;
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
