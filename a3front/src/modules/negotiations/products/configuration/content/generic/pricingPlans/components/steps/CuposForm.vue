<template>
  <div class="cupos-container">
    <h3 class="cupos-main-title">Cupos del servicio</h3>

    <div class="cupos-layout">
      <!-- Columna izquierda: Seleccionar tarifas -->
      <div class="cupos-left">
        <span class="cupos-col-label">1. Seleccionar tarifas</span>

        <div
          v-for="(tariff, index) in tariffs.slice(0, 3)"
          :key="index"
          class="tariff-card"
          :class="{ 'tariff-card--selected': selectedTariff === index }"
          @click="selectTariff(index)"
        >
          <div class="tariff-card__header">
            <div class="tariff-card__header-left">
              <svg
                width="16"
                height="16"
                viewBox="0 0 16 16"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                class="tariff-card__calendar-icon"
              >
                <path
                  d="M12.6667 2.66667H3.33333C2.59695 2.66667 2 3.26362 2 4V13.3333C2 14.0697 2.59695 14.6667 3.33333 14.6667H12.6667C13.403 14.6667 14 14.0697 14 13.3333V4C14 3.26362 13.403 2.66667 12.6667 2.66667Z"
                  stroke="#575B5F"
                  stroke-width="1.33"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M10.6667 1.33333V4"
                  stroke="#575B5F"
                  stroke-width="1.33"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M5.33333 1.33333V4"
                  stroke="#575B5F"
                  stroke-width="1.33"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M2 6.66667H14"
                  stroke="#575B5F"
                  stroke-width="1.33"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              <span class="tariff-card__name">Tarifa General</span>
            </div>
            <font-awesome-icon
              :icon="['fas', 'chevron-right']"
              class="tariff-card__chevron"
              size="2xs"
            />
          </div>

          <div class="tariff-card__dates">
            <div class="tariff-card__date-item">
              <span class="tariff-card__bullet">●</span>
              <span>01/01/2025 - 30/01/2025</span>
            </div>
            <div class="tariff-card__date-item">
              <span class="tariff-card__bullet">●</span>
              <span>04/02/2025 - 31/06/2025</span>
            </div>
            <div class="tariff-card__date-item">
              <span class="tariff-card__bullet">●</span>
              <span>01/07/2025 - 30/09/2025</span>
            </div>
          </div>

          <div class="tariff-card__footer">
            <span class="tariff-card__type-label">Tipo de tarifa</span>
            <span class="tariff-card__amount">184,5.00 USD</span>
          </div>
        </div>
      </div>

      <!-- Columna derecha: Asignar cupos de servicio -->
      <div class="cupos-right">
        <span class="cupos-col-label">2. Asignar cupos de servicio</span>

        <!-- Estado Vacío -->
        <div v-if="selectedTariff === null" class="cupos-empty-panel">
          <div class="cupos-empty-content">
            <svg
              width="48"
              height="48"
              viewBox="0 0 48 48"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              class="cupos-empty-icon"
            >
              <rect x="8" y="6" width="32" height="36" rx="3" stroke="#D9D9D9" stroke-width="2" />
              <path d="M16 6V2" stroke="#D9D9D9" stroke-width="2" stroke-linecap="round" />
              <path d="M32 6V2" stroke="#D9D9D9" stroke-width="2" stroke-linecap="round" />
              <path d="M8 14H40" stroke="#D9D9D9" stroke-width="2" />
              <rect x="14" y="20" width="6" height="4" rx="1" fill="#D9D9D9" />
              <rect x="14" y="28" width="6" height="4" rx="1" fill="#D9D9D9" />
              <rect x="24" y="20" width="6" height="4" rx="1" fill="#D9D9D9" />
              <rect x="24" y="28" width="6" height="4" rx="1" fill="#D9D9D9" />
              <rect x="34" y="20" width="2" height="4" rx="1" fill="#D9D9D9" />
              <rect x="34" y="28" width="2" height="4" rx="1" fill="#D9D9D9" />
            </svg>
            <span class="cupos-empty-text"
              >Seleccione una tarifa y registre los cupos de servicio</span
            >
          </div>
        </div>

        <!-- Panel de Asignación -->
        <div v-else class="cupos-assignment-panel">
          <!-- Formulario Superior -->
          <div class="cupos-form-container">
            <div class="cupos-form-row mode-row">
              <span class="cupos-label">Aplicar cupo a:</span>
              <a-radio-group v-model:value="cupoMode" class="cupos-radio-group">
                <a-radio value="complete">Periodo completo</a-radio>
                <a-radio value="custom">Personalizado</a-radio>
              </a-radio-group>
              <a-range-picker
                v-if="cupoMode === 'custom'"
                v-model:value="customDateRange"
                format="DD/MM/YYYY"
                class="custom-date-picker"
                :placeholder="['01/11/2025', '04/11/2025']"
                separator=" a "
                size="large"
              />
            </div>

            <p class="cupos-info-text">
              {{
                cupoMode === 'complete'
                  ? 'El cupo se aplicará a todo el periodo de la tarifa : 150 días'
                  : 'Selecciona uno o varios días del mes, o define un rango'
              }}
            </p>

            <div class="cupos-form-row inputs-row">
              <div class="input-item">
                <span class="input-label">Cupo:</span>
                <a-input-number
                  v-model:value="cupoValue"
                  placeholder="00"
                  :min="0"
                  class="cupo-input"
                  :controls="false"
                />
              </div>
              <div class="input-item">
                <span class="input-label">Release:</span>
                <a-input-number
                  v-model:value="releaseValue"
                  placeholder="00"
                  :min="0"
                  class="cupo-input"
                  :controls="false"
                />
              </div>
              <div class="input-item select-item">
                <a-select
                  v-model:value="selectedUnit"
                  placeholder="Selecciona"
                  class="cupo-select"
                  :options="[
                    { value: 'dias', label: 'Días' },
                    { value: 'horas', label: 'Horas' },
                  ]"
                />
              </div>

              <div class="buttons-group">
                <a-button class="btn-apply" @click="handleApplyCupo">Aplicar periodo</a-button>
                <a-button class="btn-block">Bloquear periodo</a-button>
              </div>
            </div>
          </div>

          <!-- Calendario -->
          <div class="cupos-calendar-container">
            <div class="calendar-header">
              <div class="calendar-controls">
                <a-select
                  v-model:value="selectedMonth"
                  :options="months"
                  class="calendar-select-custom"
                  @change="handleMonthChange"
                  :bordered="false"
                  dropdownClassName="calendar-dropdown"
                />
                <a-select
                  v-model:value="selectedYear"
                  :options="years"
                  class="calendar-select-custom"
                  @change="handleYearChange"
                  :bordered="false"
                  dropdownClassName="calendar-dropdown"
                />
              </div>

              <div class="calendar-nav">
                <button class="nav-btn" @click="prevMonth">
                  <font-awesome-icon :icon="['fas', 'chevron-left']" />
                </button>
                <button class="nav-btn" @click="nextMonth">
                  <font-awesome-icon :icon="['fas', 'chevron-right']" />
                </button>
              </div>
            </div>

            <div class="calendar-grid-header">
              <div v-for="day in weekDays" :key="day" class="weekday-cell">{{ day }}</div>
            </div>

            <div class="calendar-grid-body">
              <div
                v-for="(day, index) in calendarDays"
                :key="index"
                class="day-cell"
                :class="{
                  'day-cell--empty': !day.date,
                  'day-cell--valid': day.date,
                }"
              >
                <span v-if="day.date" class="day-number">{{ day.dayNumber }}</span>
                <div v-if="day.cupo" class="day-cupo-value">{{ day.cupo }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed, watch } from 'vue';
  import dayjs from 'dayjs';
  import 'dayjs/locale/es'; // Import Spanish locale
  import { usePricingPlansStore } from '@/modules/negotiations/products/configuration/stores/usePricingPlansStore';

  dayjs.locale('es');

  defineOptions({
    name: 'CuposForm',
  });

  const store = usePricingPlansStore();
  const tariffs = computed(() => store.formattedPeriods);

  const selectedTariff = ref<number | null>(null);

  // Form State
  const cupoMode = ref('complete');
  const cupoValue = ref<number | null>(null);
  const releaseValue = ref<number | null>(null);
  const selectedUnit = ref(null);

  // Calendar State
  const currentDate = ref(dayjs('2025-11-01')); // Start with November 2025 as per image
  const weekDays = ['Dom', 'Lun', 'Mar', 'Mier', 'Jue', 'Vier', 'Sab'];
  const calendarData = ref<Record<string, number>>({});

  const customDateRange = ref<[dayjs.Dayjs, dayjs.Dayjs] | undefined>();

  // ... (months/years definition) ...

  // ... (handleMonth/YearChange) ...

  // Update handleApplyCupo
  const handleApplyCupo = () => {
    if (cupoValue.value === null) return;

    let start: dayjs.Dayjs;
    let end: dayjs.Dayjs;

    if (cupoMode.value === 'complete') {
      // Mock: Apply to current month for visualization
      start = currentDate.value.startOf('month');
      end = currentDate.value.endOf('month');
    } else if (cupoMode.value === 'custom' && customDateRange.value) {
      start = customDateRange.value[0];
      end = customDateRange.value[1];
    } else {
      return;
    }

    let curr = start;
    while (curr.isBefore(end) || curr.isSame(end, 'day')) {
      calendarData.value[curr.format('YYYY-MM-DD')] = cupoValue.value;
      curr = curr.add(1, 'day');
    }
  };

  // ...

  const calendarDays = computed(() => {
    const startOfMonth = currentDate.value.startOf('month');
    const endOfMonth = currentDate.value.endOf('month');
    const startDay = startOfMonth.day(); // 0 (Sunday) to 6 (Saturday)
    const totalDays = endOfMonth.date();

    const days = [];

    // Empty cells for days before start of month
    for (let i = 0; i < startDay; i++) {
      days.push({ date: null });
    }

    // Days of the month
    for (let i = 1; i <= totalDays; i++) {
      const date = startOfMonth.date(i);
      const dateKey = date.format('YYYY-MM-DD');
      days.push({
        date: date,
        dayNumber: i < 10 ? `0${i}` : `${i}`,
        cupo: calendarData.value[dateKey],
      });
    }

    // Fill remaining cells to complete the last row (optional, 35 or 42 cells grid)
    // For now, just leaving it as is, or can fill up to nearest multiple of 7
    return days;
  });

  const months = [
    { value: 0, label: 'ENERO' },
    { value: 1, label: 'FEBRERO' },
    { value: 2, label: 'MARZO' },
    { value: 3, label: 'ABRIL' },
    { value: 4, label: 'MAYO' },
    { value: 5, label: 'JUNIO' },
    { value: 6, label: 'JULIO' },
    { value: 7, label: 'AGOSTO' },
    { value: 8, label: 'SEPTIEMBRE' },
    { value: 9, label: 'OCTUBRE' },
    { value: 10, label: 'NOVIEMBRE' },
    { value: 11, label: 'DICIEMBRE' },
  ];

  const years = computed(() => {
    const range = [];
    // Ensure 2025 is included if outside range, but +/- 5 from current usually covers it.
    // Let's hardcode a wider range or relative to currentDate if needed.
    // For safety, let's use 2020-2030 or similar dynamic.
    const baseYear = currentDate.value.year();
    for (let i = baseYear - 5; i <= baseYear + 5; i++) {
      range.push({ value: i, label: i.toString() });
    }
    return range;
  });

  const selectedMonth = ref(currentDate.value.month());
  const selectedYear = ref(currentDate.value.year());

  const handleMonthChange = (value: number) => {
    currentDate.value = currentDate.value.month(value);
  };

  const handleYearChange = (value: number) => {
    currentDate.value = currentDate.value.year(value);
  };

  watch(currentDate, (newVal) => {
    selectedMonth.value = newVal.month();
    selectedYear.value = newVal.year();
  });

  const selectTariff = (index: number) => {
    selectedTariff.value = index;
    loadCupos(index);
  };

  // Load cupos for selected tariff
  const loadCupos = (index: number) => {
    const periodId = tariffs.value[index].id.toString();
    const cupoData = store.cupos[periodId];
    if (cupoData) {
      cupoMode.value = cupoData.mode;
      cupoValue.value = cupoData.cupoVal;
      releaseValue.value = cupoData.releaseVal;
    } else {
      // Default / Reset
      cupoMode.value = 'complete';
      cupoValue.value = null;
      releaseValue.value = null;
    }
  };

  // Watch for changes to save to store (simple auto-save for demo)
  watch([cupoMode, cupoValue, releaseValue], () => {
    if (selectedTariff.value !== null) {
      const periodId = tariffs.value[selectedTariff.value].id.toString();
      store.cupos[periodId] = {
        mode: cupoMode.value as 'complete' | 'custom',
        cupoVal: cupoValue.value,
        releaseVal: releaseValue.value,
        dates: store.cupos[periodId]?.dates || {},
      };
    }
  });

  // Calendar Computed

  const prevMonth = () => {
    currentDate.value = currentDate.value.subtract(1, 'month');
  };

  const nextMonth = () => {
    currentDate.value = currentDate.value.add(1, 'month');
  };
</script>

<style lang="scss" scoped>
  .cupos-container {
    width: 100%;
  }

  .cupos-main-title {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 16px;
    line-height: 24px;
    color: #2f353a;
    margin: 0 0 24px 0;
  }

  .cupos-layout {
    display: flex;
    gap: 24px;
    align-items: flex-start;
  }

  .cupos-left {
    width: 320px;
    flex-shrink: 0;
  }

  .cupos-right {
    flex: 1;
    min-width: 0;
  }

  .cupos-col-label {
    display: block;
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    line-height: 20px;
    color: #2f353a;
    margin-bottom: 16px;
  }

  /* Tariff Cards */
  .tariff-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;

    &:last-child {
      margin-bottom: 0;
    }

    &--selected {
      border-color: #1284ed;
      box-shadow: 0 0 0 1px #1284ed;
    }

    &__header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    &__header-left {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    &__calendar-icon {
      flex-shrink: 0;
    }

    &__name {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 14px;
      color: #2f353a;
    }

    &__chevron {
      color: #9aa0a6;
    }

    &__dates {
      margin-bottom: 16px;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    &__date-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      font-size: 13px;
      color: #2f353a;
      line-height: 20px;
    }

    &__bullet {
      font-size: 6px;
      color: #2f353a;
      display: flex;
      align-items: center;
    }

    &__footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    &__type-label {
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      font-size: 13px;
      color: #575b5f;
    }

    &__amount {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 14px;
      color: #2f353a;
    }
  }

  /* Empty Panel */
  .cupos-empty-panel {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #ffffff;
    min-height: 450px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .cupos-empty-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
  }

  .cupos-empty-icon {
    opacity: 0.6;
  }

  .cupos-empty-text {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    color: #7e8285;
    text-align: center;
    max-width: 300px;
  }

  /* Assignment Panel */
  .cupos-assignment-panel {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  /* Form Container */
  .cupos-form-container {
    background: #f2f7fa; /* Light blue background as in image */
    border-radius: 8px;
    padding: 24px;
  }

  .cupos-form-row {
    display: flex;
    align-items: center;
    margin-bottom: 16px;

    &.mode-row {
      gap: 16px;
    }

    &.inputs-row {
      gap: 16px;
      margin-bottom: 0;
      flex-wrap: wrap;
    }
  }

  .cupos-label {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 14px;
    color: #2f353a;
  }

  .cupos-info-text {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #575b5f;
    margin-bottom: 24px;
  }

  .input-item {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .input-label {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: #2f353a;
    white-space: nowrap;
  }

  .cupo-input {
    width: 60px;
    text-align: center;

    :deep(.ant-input-number-input) {
      text-align: center;
    }
  }

  .select-item {
    min-width: 120px;
  }

  .cupo-select {
    width: 100%;
  }

  .buttons-group {
    display: flex;
    gap: 12px;
    margin-left: auto;
  }

  .btn-apply {
    background: #ffffff;
    border: 1px solid #2f353a;
    color: #2f353a;
    font-weight: 600;
    font-size: 14px;
    height: 32px;

    &:hover {
      border-color: #1284ed;
      color: #1284ed;
    }
  }

  .btn-block {
    background: #ffffff;
    border: 1px solid #2f353a;
    color: #2f353a;
    font-weight: 600;
    font-size: 14px;
    height: 32px;

    &:hover {
      border-color: #d32f2f;
      color: #d32f2f;
    }
  }

  /* Calendar */
  .cupos-calendar-container {
    background: #ffffff;
    /* No border per image, just header */
  }

  .calendar-header {
    background: #2f353a;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .calendar-controls {
    display: flex;
    gap: 16px;
  }

  .calendar-select-custom {
    min-width: 130px;
    background: #ffffff;
    border-radius: 4px;

    :deep(.ant-select-selector) {
      height: 32px !important;
      padding: 0 11px !important;
      display: flex;
      align-items: center;
    }

    :deep(.ant-select-selection-item) {
      font-weight: 600;
      font-size: 13px;
      color: #2f353a;
      line-height: 32px;
    }

    :deep(.ant-select-arrow) {
      color: #2f353a;
      font-size: 12px;
    }
  }

  .custom-date-picker {
    width: 250px;
  }

  .calendar-nav {
    display: flex;
    gap: 8px;
  }

  .nav-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: #2f353a;

    &:hover {
      background: #e0e0e0;
    }
  }

  .calendar-grid-header {
    background: #2f353a;
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    padding: 0 16px 12px 16px;
  }

  .weekday-cell {
    color: #ffffff;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
  }

  .calendar-grid-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border: 1px solid #e0e0e0; /* Outer border */
    border-top: none;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    overflow: hidden;
  }

  .day-cell {
    height: 100px;
    border-right: 1px solid #e0e0e0;
    border-bottom: 1px solid #e0e0e0;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 7px;

    &:nth-child(7n) {
      border-right: none;
    }

    &--empty {
      background: #fafafa;
    }

    &--valid {
      background: #ffffff;
      cursor: pointer;

      &:hover {
        background: #f0f7ff;
      }
    }
  }

  .day-number {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: #2f353a;
    display: block;
    align-self: flex-start;
  }

  .day-cupo-value {
    width: 44px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;

    background: #f8f9fa;
    border-radius: 4px;

    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 13px;
    color: #5f6368;
  }
</style>
