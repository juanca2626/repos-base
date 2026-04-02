<template>
  <a-drawer
    :open="open"
    :closable="false"
    :width="drawerWidth"
    :footer="null"
    placement="right"
    class="new-holiday-drawer"
    destroy-on-close
    @close="handleClose"
  >
    <!-- Header -->
    <template #title>
      <div class="drawer-header">
        <div class="drawer-title">
          <font-awesome-icon :icon="['fas', 'calendar-plus']" class="title-icon" />
          <span>Nuevo festivo</span>
        </div>
        <a-button type="text" class="close-button" @click="handleClose">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </a-button>
      </div>
    </template>

    <!-- Form Content -->
    <div class="drawer-content">
      <a-form layout="vertical" :model="formState">
        <!-- Tipo de festivo (Radio) -->
        <div class="holiday-type-section">
          <a-radio-group v-model:value="formState.holidayType" class="holiday-type-radio">
            <a-radio value="general">General</a-radio>
            <a-radio value="turistico">Turístico</a-radio>
            <a-radio value="ciudad" :disabled="cities.length === 0">Ciudad</a-radio>
          </a-radio-group>
        </div>

        <!-- Ciudad Select (only for Ciudad type) -->
        <div v-if="formState.holidayType === 'ciudad'" class="city-section">
          <label class="field-label">Ciudad</label>
          <a-select
            v-model:value="formState.ciudad"
            placeholder="Selecciona"
            :options="ciudadOptions"
            size="large"
            class="full-width-select"
            show-search
            :filter-change="false"
            :filter-option="
              (input: string, option: any) =>
                (option?.label ?? '').toLowerCase().includes(input.toLowerCase())
            "
          />
        </div>

        <!-- Tipo de fecha y Fecha -->
        <div class="date-row">
          <div class="date-type-col">
            <label class="field-label">Tipo de fecha</label>
            <a-select
              v-model:value="formState.dateType"
              class="date-type-select"
              :options="dateTypeOptions"
            />
          </div>
          <div class="date-col">
            <label class="field-label">Fecha</label>
            <a-date-picker
              v-if="formState.dateType === 'dia'"
              v-model:value="formState.date"
              placeholder="DD/MM/AAAA"
              format="DD/MM/YYYY"
              size="large"
              class="date-picker"
            />
            <a-range-picker
              v-else
              v-model:value="formState.dateRange"
              :placeholder="['DD/MM/AAAA', 'DD/MM/AAAA']"
              format="DD/MM/YYYY"
              size="large"
              class="date-picker"
            />
          </div>
        </div>

        <!-- Nombre de fecha festiva -->
        <div class="name-section">
          <label class="field-label">Nombre de fecha festiva</label>
          <a-input
            v-model:value="formState.name"
            placeholder="Escribe aquí..."
            :maxlength="500"
            size="large"
            class="name-input"
          />
        </div>

        <!-- Zona turística Select (only for Turístico type) -->
        <!-- <div v-if="formState.holidayType === 'turistico'" class="zone-section">
          <label class="field-label">Zona turística</label>
          <a-select
            v-model:value="formState.zonaTuristica"
            placeholder="Selecciona"
            :options="zonaTuristicaOptions"
            size="large"
            class="full-width-select"
            show-search
            :filter-change="false"
            :filter-option="
              (input: string, option: any) =>
                (option?.label ?? '').toLowerCase().includes(input.toLowerCase())
            "
          />
        </div> -->

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
      </a-form>
    </div>
  </a-drawer>
</template>

<script lang="ts" setup>
  import { reactive, computed } from 'vue';
  import type { Dayjs } from 'dayjs';
  import dayjs from 'dayjs';
  import { notification } from 'ant-design-vue';

  interface FormState {
    holidayType: 'general' | 'turistico' | 'ciudad';
    dateType: 'dia' | 'rango';
    date: Dayjs | null;
    dateRange: [Dayjs, Dayjs] | null;
    name: string;
    zonaTuristica: number | null;
    ciudad: number | null;
  }

  const props = defineProps<{
    open: boolean;
    loading?: boolean;
    yearFrom?: number | null;
    yearTo?: number | null;
  }>();

  const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit', data: FormState): void;
  }>();

  const formState = reactive<FormState>({
    holidayType: 'general',
    dateType: 'dia',
    date: null,
    dateRange: null,
    name: '',
    zonaTuristica: null,
    ciudad: null,
  });

  const dateTypeOptions = [
    { value: 'dia', label: 'Día' },
    { value: 'rango', label: 'Rango' },
  ];

  import { useSupportStore } from '../store/useSupportStore';
  import { storeToRefs } from 'pinia';

  // Use store
  const supportStore = useSupportStore();
  const { cities } = storeToRefs(supportStore);

  // Removed onMounted fetching as it is handled by the parent view
  // onMounted(() => {
  //   supportStore.fetchResources();
  // });

  const ciudadOptions = computed(() => {
    return cities.value.map((c) => ({ value: c.id, label: c.name }));
  });

  // const zonaTuristicaOptions = computed(() => {
  //   return zones.value.map((c) => ({ value: c.id, label: c.name }));
  // });

  const drawerWidth = computed(() => {
    return formState.dateType === 'rango' ? 605 : 500;
  });

  const isFormValid = computed(() => {
    const hasDate = formState.dateType === 'dia' ? formState.date : formState.dateRange;
    const hasName = formState.name.trim().length > 0;

    // Validate type-specific required fields
    if (formState.holidayType === 'ciudad' && !formState.ciudad) return false;
    // if (formState.holidayType === 'turistico' && !formState.zonaTuristica) return false;

    return hasDate && hasName;
  });

  import { watch } from 'vue';

  const resetFormFields = () => {
    formState.dateType = 'dia';
    formState.date = null;
    formState.dateRange = null;
    formState.name = '';
    formState.zonaTuristica = null;
    formState.ciudad = null;
  };

  const resetForm = () => {
    formState.holidayType = 'general';
    resetFormFields();
  };

  watch(
    () => formState.holidayType,
    () => {
      resetFormFields();
    }
  );

  watch(
    () => props.open,
    (newVal) => {
      if (!newVal) {
        resetForm();
      }
    }
  );

  const handleClose = () => {
    emit('update:open', false);
  };

  const handleSubmit = () => {
    if (isFormValid.value) {
      // Validar que las fechas estén dentro del periodo de validez
      const yearFrom = props.yearFrom;
      const yearTo = props.yearTo;

      if (yearFrom != null && yearTo != null) {
        const startOfPeriod = dayjs().year(yearFrom).startOf('year');
        const endOfPeriod = dayjs().year(yearTo).endOf('year');

        let isOutOfRange = false;

        if (formState.dateType === 'dia' && formState.date) {
          if (formState.date.isBefore(startOfPeriod) || formState.date.isAfter(endOfPeriod)) {
            isOutOfRange = true;
          }
        } else if (formState.dateType === 'rango' && formState.dateRange) {
          const [start, end] = formState.dateRange;
          if (start.isBefore(startOfPeriod) || end.isAfter(endOfPeriod)) {
            isOutOfRange = true;
          }
        }

        if (isOutOfRange) {
          notification.error({
            message: `La fecha ingresada se encuentra fuera del periodo de vigencia ${yearFrom}-${yearTo}`,
            duration: 4,
          });
          return;
        }
      }

      emit('submit', { ...formState });
    }
  };
</script>

<style lang="scss" scoped>
  .new-holiday-drawer {
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
    .holiday-type-section {
      margin-bottom: 24px;
      display: flex;
      justify-content: flex-end;

      .holiday-type-radio {
        display: flex;

        :deep(.ant-radio-wrapper) {
          font-size: 14px;
          color: #2f353a;
        }
      }
    }

    .city-section,
    .zone-section {
      margin-bottom: 24px;
    }

    .full-width-select {
      width: 100%;

      :deep(.ant-select-selector) {
        height: 40px;
        border-radius: 4px;

        .ant-select-selection-item {
          line-height: 38px;
        }

        .ant-select-selection-placeholder {
          line-height: 38px;
        }
      }
    }

    .field-label {
      display: block;
      font-size: 14px;
      color: #2f353a;
      margin-bottom: 8px;
    }

    .date-row {
      display: flex;
      gap: 16px;
      margin-bottom: 24px;

      .date-type-col {
        width: 140px;

        .date-type-select {
          width: 100%;

          :deep(.ant-select-selector) {
            height: 40px;
            border-radius: 4px;

            .ant-select-selection-item {
              line-height: 38px;
            }
          }
        }
      }

      .date-col {
        flex: 1;

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

    .name-section {
      margin-bottom: 24px;

      .name-input {
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
