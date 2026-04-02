<template>
  <div class="routes-container">
    <!--  Filtros de búsqueda -->
    <div class="filter-box white-box">
      <div class="filter-header">
        <h4>Filtros de Búsqueda</h4>
        <a-button
          type="link"
          style="color: #eb5757; display: flex; align-items: center; gap: 6px"
          @click="borrarFiltros"
        >
          <template #icon>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              height="1em"
              viewBox="0 0 640 512"
              fill="#eb5757"
            >
              <path
                d="M497.9 142.1l-90-90c-12.5-12.5-32.8-12.5-45.3 0l-297 297c-12.5 12.5-12.5 32.8 0 45.3l90 90c12.5 12.5 32.8 12.5 45.3 0l297-297c12.5-12.5 12.5-32.8 0-45.3zM124.1 387.9L160 352h80l-80 80-35.9-35.9zM240 336h-80l240-240 80 80L240 336z"
              />
            </svg>
          </template>
          Borrar filtros
        </a-button>
      </div>

      <a-row :gutter="[16, 16]" align="middle">
        <a-col :xs="24" :sm="12" :md="7">
          <a-form-item label="Circuito ">
            <a-select
              v-model:value="selectedCircuit"
              placeholder="Selecciona circuito"
              @change="onCircuitChange"
              allowClear
            >
              <a-select-option v-for="c in circuitos" :key="c.id" :value="c.id">
                {{ c.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>

        <a-col :xs="24" :sm="12" :md="9">
          <a-form-item label="Ruta ">
            <a-select
              v-model:value="selectedRouteId"
              placeholder="Selecciona ruta"
              :disabled="!selectedCircuit"
              @change="onRouteChange"
              allowClear
            >
              <a-select-option v-for="r in rutasPorCircuito" :key="r.id" :value="r.id">
                {{ r.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>

        <a-col :xs="24" :sm="12" :md="4">
          <a-form-item label="Año">
            <a-select
              v-model:value="selectedYear"
              placeholder="Selecciona un año"
              @change="onMonthYearChange"
              :disabled="!selectedRouteId"
              allowClear
            >
              <a-select-option v-for="y in years" :key="y" :value="y">
                {{ y }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>

        <a-col :xs="24" :sm="12" :md="4">
          <a-form-item label="Mes">
            <a-select
              v-model:value="selectedMonth"
              placeholder="Selecciona un mes"
              @change="onMonthYearChange"
              :disabled="!selectedRouteId"
              allowClear
            >
              <a-select-option v-for="m in meses" :key="m.value" :value="m.value">
                {{ m.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
      </a-row>
    </div>

    <!-- Calendario -->
    <div v-if="loading" class="calendar-container white-box loading-container">
      <a-spin size="large" />
      <div class="loading-text">Cargando</div>
    </div>

    <div v-else-if="selectedCircuit && selectedRouteId" class="calendar-container white-box">
      <div class="calendar-header">
        <h4>
          <span class="calendar-icon">📅</span>
          {{ monthTitle }}
        </h4>
      </div>

      <div class="calendar-grid weekdays">
        <div v-for="wd in weekDays" :key="wd" class="calendar-weekday">
          {{ wd }}
        </div>
      </div>

      <div class="calendar-grid">
        <div
          v-for="(day, idx) in monthDays"
          :key="idx"
          class="calendar-day"
          :class="{ 'calendar-day--othermonth': !day.currentMonth }"
          @click="onClickDay(day)"
        >
          <div class="calendar-day-header">
            <span class="day-number" v-if="day.date">
              {{ getDayNumber(day.date) }}
            </span>
          </div>

          <div
            v-if="day.currentMonth && (!day.horarios || day.horarios.length === 0)"
            class="hour-pill"
            :class="{ 'not-available': isPastDay(day.date), zero: !isPastDay(day.date) }"
            style="margin-top: 10px; justify-content: center"
          >
            {{ isPastDay(day.date) ? 'NO DISPONIBLE' : 'AGOTADO' }}
          </div>

          <div class="hour-list" v-else-if="day.currentMonth">
            <div
              v-for="(h, hIdx) in day.horarios"
              :key="hIdx"
              class="hour-pill"
              :class="getSlotClass(h.slots)"
            >
              <span class="hour-time">{{ h.time }}</span>
              <span class="hour-slots">{{ h.slots }} boletos</span>
            </div>
          </div>
        </div>
      </div>

      <div class="legend">
        <div class="legend-item few">Últimos cupos (&lt;20)</div>
        <div class="legend-item zero">Agotado</div>
      </div>
    </div>

    <div v-else class="calendar-container white-box" style="text-align: center; padding: 60px">
      <div style="font-size: 40px; opacity: 0.4">📅</div>
      <h4 style="margin-top: 10px; color: #666">
        Selecciona un circuito y una ruta para ver la disponibilidad de horarios y cupos
      </h4>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed, onMounted, ref, watch } from 'vue';
  import { useRoutes } from '../composables/useRoutes';
  import { useCalendar } from '../composables/useCalendar';

  const { circuitos, fetchCircuitos, fetchRoutesByCircuit, getAvailabilityByFilters } = useRoutes();
  const {
    viewYear,
    viewMonth,
    monthDays,
    weekDays,
    getDayNumber,
    buildCalendar,
    clearCalendar,
    monthTitle,
  } = useCalendar();

  const loading = ref(false);
  const selectedCircuit = ref<string | null>(null);
  const selectedRouteId = ref<string | null>(null);
  const rutasPorCircuito = ref([]);
  const selectedDateISO = ref<string | null>(null);
  const selectedMonth = ref<number | null>(null);
  const selectedYear = ref<number | null>(null);

  const years = [new Date().getFullYear(), new Date().getFullYear() + 1];

  const meses = computed(() => {
    const allMonths = [
      { name: 'Enero', value: 1 },
      { name: 'Febrero', value: 2 },
      { name: 'Marzo', value: 3 },
      { name: 'Abril', value: 4 },
      { name: 'Mayo', value: 5 },
      { name: 'Junio', value: 6 },
      { name: 'Julio', value: 7 },
      { name: 'Agosto', value: 8 },
      { name: 'Setiembre', value: 9 },
      { name: 'Octubre', value: 10 },
      { name: 'Noviembre', value: 11 },
      { name: 'Diciembre', value: 12 },
    ];

    if (!selectedYear.value) return allMonths;

    const today = new Date();
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth() + 1;

    if (selectedYear.value === currentYear) {
      return allMonths.filter((m) => m.value >= currentMonth);
    }

    return allMonths;
  });

  onMounted(fetchCircuitos);

  async function onCircuitChange(id: string) {
    rutasPorCircuito.value = await fetchRoutesByCircuit(id);
    selectedRouteId.value = null;
    clearCalendar();
    selectedDateISO.value = null;
  }

  async function onRouteChange() {
    if (selectedMonth.value && selectedYear.value) {
      await loadMonthFromFilters();
    }
  }

  async function onMonthYearChange() {
    if (selectedMonth.value && selectedYear.value && selectedRouteId.value) {
      await loadMonthFromFilters();
    }
  }

  async function loadMonthFromFilters() {
    if (
      !selectedCircuit.value ||
      !selectedRouteId.value ||
      !selectedMonth.value ||
      !selectedYear.value
    ) {
      return;
    }

    viewYear.value = selectedYear.value;
    viewMonth.value = selectedMonth.value - 1;

    loading.value = true;
    try {
      const days = await getAvailabilityByFilters(
        selectedCircuit.value,
        selectedRouteId.value,
        selectedMonth.value,
        selectedYear.value
      );
      buildCalendar(days);
    } finally {
      loading.value = false;
    }
  }

  function onClickDay(day: any) {
    if (!day?.date) return;
    selectedDateISO.value = day.date;
  }

  function borrarFiltros() {
    selectedCircuit.value = null;
    selectedRouteId.value = null;
    selectedMonth.value = null;
    selectedYear.value = null;
    clearCalendar();
    selectedDateISO.value = null;
  }

  function getSlotClass(slots: number) {
    if (slots === 0) return 'zero';
    if (slots < 20) return 'few';
    return 'available';
  }

  function isPastDay(dateStr: string): boolean {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const date = new Date(dateStr);
    return date < today;
  }

  watch(selectedRouteId, () => {
    clearCalendar();
    selectedDateISO.value = null;
  });
</script>

<style scoped>
  .routes-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .white-box {
    background: white;
    border-radius: 10px;
    border: 1px solid #f0f0f0;
    padding: 20px;
  }

  .filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }

  .filter-header h4 {
    font-weight: 700;
    margin: 0;
  }

  /* ----- CALENDARIO ----- */
  .calendar-container {
    padding: 20px;
  }

  .calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }

  .calendar-icon {
    margin-right: 6px;
  }

  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
  }

  .calendar-weekday {
    text-align: center;
    font-weight: 600;
    color: #777;
    font-size: 13px;
    padding: 6px 0;
  }

  .calendar-day {
    min-height: 120px;
    border: 1px solid #eee;
    border-radius: 10px;
    background: #fff;
    padding: 6px;
    display: flex;
    flex-direction: column;
    transition: 0.2s ease;
  }

  .calendar-day--othermonth {
    background: #fafafa;
    color: #bbb;
  }

  .calendar-day-header {
    text-align: right;
    font-weight: 600;
  }

  .hour-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 6px;
  }

  .hour-pill {
    border-radius: 6px;
    padding: 4px 6px;
    font-size: 12px;
    border: 1px solid #eaeaea;
    display: flex;
    justify-content: space-between;
  }

  .hour-pill.available {
    background: #f6faff;
    border-color: #d6e4ff;
    color: #1e6efb;
  }

  .hour-pill.few {
    background: #fff7e6;
    border-color: #ffe7ba;
    color: #fa8c16;
  }

  .hour-pill.zero {
    background: #fff1f0;
    border-color: #ffa39e;
    color: #ff4d4f;
  }

  .hour-pill.not-available {
    background: #f5f5f5;
    border-color: #d9d9d9;
    color: #bfbfbf;
  }

  /* ----- LEYENDA SIMPLIFICADA ----- */
  .legend {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    font-size: 12px;
  }
  .legend-item {
    padding: 4px 8px;
    border-radius: 6px;
    background: #fafafa;
    border: 1px solid #eee;
  }
  .legend-item.few {
    border-color: #ffd591;
    background: #fff7e6;
    color: #fa8c16;
  }
  .legend-item.zero {
    border-color: #ffa39e;
    background: #fff1f0;
    color: #ff4d4f;
  }

  .loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 300px;
  }

  .loading-text {
    margin-top: 12px;
    font-size: 16px;
    color: #666;
  }
</style>
