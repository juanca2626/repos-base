<template>
  <div class="date-range-calendar-wrapper" :class="{ 'field-required': isRequired }">
    <div class="ant-picker-input-wrapper" @click.stop="toggleCalendar" ref="inputRef">
      <div class="ant-picker-input">
        <input
          type="text"
          class="ant-picker-input-input"
          :value="displayValue"
          placeholder="Seleccione un rango de fechas"
          readonly
        />
        <span class="ant-picker-suffix">
          <span
            v-if="displayValue"
            class="ant-picker-clear"
            @click.stop="clearDate"
            title="Limpiar fecha"
          >
            <CloseOutlined />
          </span>
          <CalendarOutlined />
        </span>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showCalendar" class="calendar-popup" :style="popupStyle" @click.stop>
        <!-- Leyenda -->
        <div class="calendar-legend">
          <span class="legend-title">Disponibilidad:</span>
          <span class="badge badge-available">Disponible</span>
          <span class="badge badge-soldout">Agotado</span>
          <span class="badge badge-blocked">Bloqueado</span>
        </div>

        <!-- Calendarios -->
        <a-spin :spinning="isLoadingCalendar" tip="Cargando disponibilidad...">
          <div class="calendar-container">
            <!-- Primer mes -->
            <div class="calendar-month">
              <div class="calendar-header">
                <button type="button" class="btn btn-sm btn-link" @click="previousMonth">
                  <font-awesome-icon :icon="['fas', 'chevron-left']" />
                </button>
                <span class="month-year">{{ currentMonth.format('MMMM YYYY') }}</span>
                <span style="width: 30px"></span>
              </div>
              <div class="calendar-weekdays">
                <div v-for="day in weekdays" :key="day" class="weekday">{{ day }}</div>
              </div>
              <div class="calendar-days">
                <div
                  v-for="day in firstMonthDays"
                  :key="day.date || `empty-${firstMonthDays.indexOf(day)}`"
                  :class="getDayClasses(day)"
                  @click="day.date ? selectDate(day.date) : null"
                >
                  {{ day.day || '' }}
                </div>
              </div>
            </div>

            <!-- Segundo mes -->
            <div class="calendar-month">
              <div class="calendar-header">
                <span style="width: 30px"></span>
                <span class="month-year">{{ secondMonth.format('MMMM YYYY') }}</span>
                <button type="button" class="btn btn-sm btn-link" @click="nextMonth2">
                  <font-awesome-icon :icon="['fas', 'chevron-right']" />
                </button>
              </div>
              <div class="calendar-weekdays">
                <div v-for="day in weekdays" :key="day" class="weekday">{{ day }}</div>
              </div>
              <div class="calendar-days">
                <div
                  v-for="day in secondMonthDays"
                  :key="day.date || `empty-${secondMonthDays.indexOf(day)}`"
                  :class="getDayClasses(day)"
                  @click="day.date ? selectDate(day.date) : null"
                >
                  {{ day.day || '' }}
                </div>
              </div>
            </div>
          </div>
        </a-spin>

        <!-- Botón confirmar -->
        <div class="calendar-footer">
          <a-button
            type="primary"
            @click="confirmSelection"
            class="confirm-button"
            :disabled="isLoadingCalendar"
          >
            Confirmar fecha
          </a-button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { CalendarOutlined, CloseOutlined } from '@ant-design/icons-vue';
  import moment from 'moment';
  import { useDateRangeCalendar } from '@/modules/negotiations/hotels/quotas/composables/useDateRangeCalendar';
  import { useHotelAvailability } from '@/modules/negotiations/hotels/quotas/composables/useHotelAvailability';
  import { useHotelAvailabilityFilterStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-filter.store';

  const props = defineProps<{
    modelValue?: { from: string | null; to: string | null };
    availabilityData?: Record<string, string>;
    isRequired?: boolean;
  }>();

  const emit = defineEmits<{
    (e: 'update:modelValue', value: { from: string | null; to: string | null }): void;
  }>();

  // Usar el composable del calendario
  const {
    currentMonth,
    secondMonth,
    selectedStart,
    selectedEnd,
    isLoadingCalendar,
    fetchAvailabilityForVisibleMonths,
    previousMonth,
    nextMonth,
    getDaysForMonth,
    isInRange,
    resetCalendarPosition,
  } = useDateRangeCalendar();

  // Obtener availabilityData y filters del composable compartido
  const { availabilityData, filters, clearAvailabilityData } = useHotelAvailability();

  // Obtener el store de filtros
  const filterStore = useHotelAvailabilityFilterStore();

  // Estado de UI del calendario
  const showCalendar = ref(false);
  const weekdays = ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'];
  const inputRef = ref<HTMLElement | null>(null);
  const popupStyle = ref<{ top: string; left: string }>({ top: '0px', left: '0px' });

  // Forzar reactividad cuando cambie availabilityData
  watch(
    () => availabilityData.value,
    (newData) => {
      if (newData) {
        // Forzar actualización del calendario
        currentMonth.value = currentMonth.value.clone();
      }
    },
    { deep: true, immediate: true }
  );

  const firstMonthDays = computed(() => {
    // Incluir availabilityData en la dependencia para que se recalcule cuando cambie
    if (availabilityData.value) {
      Object.keys(availabilityData.value).length; // Forzar dependencia
    }
    return getDaysForMonth(currentMonth.value);
  });

  const secondMonthDays = computed(() => {
    // Incluir availabilityData en la dependencia para que se recalcule cuando cambie
    if (availabilityData.value) {
      Object.keys(availabilityData.value).length; // Forzar dependencia
    }
    return getDaysForMonth(secondMonth.value);
  });

  const displayValue = computed(() => {
    if (props.modelValue?.from && props.modelValue?.to) {
      return `${moment(props.modelValue.from).format('DD/MM/YYYY')} - ${moment(props.modelValue.to).format('DD/MM/YYYY')}`;
    } else if (props.modelValue?.from) {
      return moment(props.modelValue.from).format('DD/MM/YYYY');
    }
    return '';
  });

  watch(
    () => props.modelValue,
    (newValue) => {
      if (newValue?.from) {
        selectedStart.value = moment(newValue.from);
      } else {
        selectedStart.value = null;
      }
      if (newValue?.to) {
        selectedEnd.value = moment(newValue.to);
      } else {
        selectedEnd.value = null;
      }
    },
    { immediate: true, deep: true }
  );

  // Watcher para resetear el cache cuando cambie la categoría interna (o se limpie)
  watch(
    () => filters.value.internalCategory,
    (newCategory, oldCategory) => {
      // Detectar cambio (incluyendo cuando se limpia el combo: newCategory es null)
      const hasChanged = oldCategory?.code !== newCategory?.code;
      if (hasChanged) {
        // Limpiar los datos de disponibilidad
        clearAvailabilityData();
        // Resetear la posición del calendario y limpiar el cache
        resetCalendarPosition();
        // No cargar inmediatamente, se cargará cuando se abra el calendario
      }
    }
  );

  // Watcher para resetear el cache cuando cambie la conexión (o se limpie)
  watch(
    () => filters.value.connection,
    (newConnection, oldConnection) => {
      // Detectar cambio (incluyendo cuando se limpia el combo: newConnection es null)
      const hasChanged = oldConnection?.code !== newConnection?.code;
      if (hasChanged) {
        // Limpiar los datos de disponibilidad
        clearAvailabilityData();
        // Resetear la posición del calendario y limpiar el cache
        resetCalendarPosition();
        // No cargar inmediatamente, se cargará cuando se abra el calendario
      }
    }
  );

  // Watcher para resetear el cache cuando cambie la ciudad (o se limpie)
  watch(
    () => filters.value.city,
    (newCity, oldCity) => {
      // Detectar cambio (incluyendo cuando se limpia el combo: newCity es null)
      const hasChanged = oldCity?.code !== newCity?.code;
      if (hasChanged) {
        // Limpiar los datos de disponibilidad
        clearAvailabilityData();
        // Resetear la posición del calendario y limpiar el cache
        resetCalendarPosition();
        // No cargar inmediatamente, se cargará cuando se abra el calendario
      }
    }
  );

  // Watcher para resetear el cache cuando cambie la cadena de hoteles (o se limpie)
  watch(
    () => filterStore.filterState.hotelChain,
    (newChain, oldChain) => {
      // Detectar cambio (incluyendo cuando se limpia el combo: newChain es null)
      const hasChanged = oldChain !== newChain;
      if (hasChanged) {
        // Limpiar los datos de disponibilidad
        clearAvailabilityData();
        // Resetear la posición del calendario y limpiar el cache
        resetCalendarPosition();
        // No cargar inmediatamente, se cargará cuando se abra el calendario
      }
    }
  );

  const calculatePopupPosition = () => {
    if (inputRef.value) {
      const rect = inputRef.value.getBoundingClientRect();
      const viewportWidth = window.innerWidth;
      const viewportHeight = window.innerHeight;
      const popupWidth = 700; // max-width del popup
      const popupHeight = 500; // altura estimada del popup

      let left = rect.left;
      let top = rect.bottom + 5;

      // Ajustar si se sale por la derecha
      if (left + popupWidth > viewportWidth) {
        left = viewportWidth - popupWidth - 10;
      }

      // Ajustar si se sale por abajo
      if (top + popupHeight > viewportHeight) {
        top = rect.top - popupHeight - 5;
        // Si tampoco cabe arriba, ponerlo en la parte superior de la ventana
        if (top < 0) {
          top = 10;
        }
      }

      // Asegurar que no se salga por la izquierda
      if (left < 0) {
        left = 10;
      }

      popupStyle.value = {
        top: `${top}px`,
        left: `${left}px`,
      };
    }
  };

  const toggleCalendar = async () => {
    if (!showCalendar.value) {
      // Calcular posición antes de mostrar
      showCalendar.value = true;
      nextTick(() => {
        calculatePopupPosition();
      });
      // Consultar disponibilidad de los meses visibles al abrir
      await fetchAvailabilityForVisibleMonths();
    } else {
      showCalendar.value = false;
    }
  };

  const closeCalendar = () => {
    showCalendar.value = false;
  };

  const getDayClasses = (day: {
    date: string;
    day: number;
    isCurrentMonth: boolean;
    isToday: boolean;
  }) => {
    const classes: string[] = ['calendar-day'];

    // Si es un espacio vacío, agregar clase y retornar
    if (!day.date || day.day === 0) {
      classes.push('empty-day');
      return classes.join(' ');
    }

    const dateStr = day.date;
    const dateMoment = moment(dateStr);

    if (!day.isCurrentMonth) {
      classes.push('other-month');
    }

    if (day.isToday) {
      classes.push('today');
    }

    // Estado de disponibilidad
    const availability = getDateAvailability(dateStr);
    if (availability === 'blocked') {
      classes.push('blocked');
    } else if (availability === 'soldout') {
      classes.push('soldout');
    } else if (availability === 'available') {
      classes.push('available');
    }

    // Selección
    if (selectedStart.value && dateMoment.isSame(selectedStart.value, 'day')) {
      classes.push('selected-start');
    }
    if (selectedEnd.value && dateMoment.isSame(selectedEnd.value, 'day')) {
      classes.push('selected-end');
    }
    if (isInRange(dateMoment)) {
      classes.push('in-range');
    }

    return classes.join(' ');
  };

  const getDateAvailability = (dateStr: string) => {
    // Normalizar el formato de fecha para asegurar coincidencia
    const normalizedDate = moment(dateStr).format('YYYY-MM-DD');

    // Usar availabilityData del composable compartido
    if (availabilityData.value) {
      // Buscar con el formato normalizado
      const status = availabilityData.value[normalizedDate];
      if (status) {
        return status;
      }
    }

    // Por defecto, todas las fechas están disponibles hasta que se carguen los datos del servidor
    return 'available';
  };

  const selectDate = (dateStr: string) => {
    const dateMoment = moment(dateStr);

    if (!selectedStart.value || (selectedStart.value && selectedEnd.value)) {
      // Iniciar nueva selección
      selectedStart.value = dateMoment.clone();
      selectedEnd.value = null;
    } else {
      // Completar rango
      if (dateMoment.isBefore(selectedStart.value)) {
        selectedEnd.value = selectedStart.value.clone();
        selectedStart.value = dateMoment.clone();
      } else {
        selectedEnd.value = dateMoment.clone();
      }
    }
  };

  // Las funciones previousMonth y nextMonth ya están en el composable
  // nextMonth2 se usa para navegar desde el segundo mes
  const nextMonth2 = async () => {
    await nextMonth();
  };

  const confirmSelection = () => {
    if (selectedStart.value && selectedEnd.value) {
      emit('update:modelValue', {
        from: selectedStart.value.format('YYYY-MM-DD'),
        to: selectedEnd.value.format('YYYY-MM-DD'),
      });
      showCalendar.value = false;
    } else if (selectedStart.value) {
      // Si solo hay fecha inicial, usar la misma como final
      emit('update:modelValue', {
        from: selectedStart.value.format('YYYY-MM-DD'),
        to: selectedStart.value.format('YYYY-MM-DD'),
      });
      showCalendar.value = false;
    }
  };

  const clearDate = () => {
    emit('update:modelValue', {
      from: null,
      to: null,
    });
    selectedStart.value = null;
    selectedEnd.value = null;
  };

  onMounted(() => {
    document.addEventListener('click', closeCalendar);
  });

  onBeforeUnmount(() => {
    document.removeEventListener('click', closeCalendar);
  });
</script>

<style lang="scss" scoped>
  @import '@/scss/_variables.scss';

  .date-range-calendar-wrapper {
    position: relative;
    width: 100%;
    max-width: 310px;
  }

  .ant-picker-input-wrapper {
    position: relative;
    width: 100%;
  }

  .ant-picker-input {
    position: relative;
    display: inline-flex;
    align-items: center;
    width: 100%;
    height: 40px;
    min-height: 40px;
    border: 1px solid #d9d9d9;
    border-radius: 6px;
    transition: all 0.3s;
    background-color: #fff;
    cursor: pointer;

    &:hover {
      border-color: #4096ff;
    }

    &.ant-picker-focused,
    &:focus-within {
      border-color: #4096ff;
      box-shadow: 0 0 0 2px rgba(5, 145, 255, 0.1);
      outline: 0;
    }
  }

  .field-required {
    .ant-picker-input {
      border: 1px solid #ff4d4f !important;
    }

    .ant-picker-input:hover {
      border-color: #ff4d4f !important;
    }

    .ant-picker-input.ant-picker-focused,
    .ant-picker-input:focus-within {
      border-color: #ff4d4f !important;
      box-shadow: 0 0 0 2px rgba(255, 77, 79, 0.2) !important;
    }
  }

  .ant-picker-input-input {
    flex: 1;
    min-width: 0;
    padding: 4px 11px;
    padding-right: 60px;
    height: 100%;
    color: rgba(0, 0, 0, 0.88);
    font-size: 14px;
    line-height: 1.5715;
    background-color: transparent;
    border: 0;
    border-radius: 6px;
    outline: 0;
    cursor: pointer;
    box-sizing: border-box;

    &::placeholder {
      color: rgba(0, 0, 0, 0.25);
    }

    &:focus {
      outline: 0;
    }
  }

  .ant-picker-suffix {
    position: absolute;
    top: 50%;
    right: 11px;
    transform: translateY(-50%);
    color: rgba(0, 0, 0, 0.45);
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
  }

  .ant-picker-clear {
    pointer-events: auto;
    cursor: pointer;
    color: rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    transition: color 0.2s;

    &:hover {
      color: rgba(0, 0, 0, 0.75);
    }
  }

  .calendar-popup {
    position: fixed;
    z-index: 1050;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 15px;
    min-width: 600px;
    max-width: 700px;
    transform: translateX(0);
    max-height: 90vh;
    overflow-y: auto;
  }

  .calendar-legend {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 10px;
  }

  .legend-title {
    font-weight: 600;
    margin-right: 5px;
  }

  .badge {
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
  }

  .badge-available {
    background-color: #c9f2e3;
    color: #636363;
    border: none;
  }

  .badge-soldout {
    background-color: #ffcccc;
    color: #636363;
    border: none;
  }

  .badge-blocked {
    background-color: #dcdcdc;
    color: #636363;
    border: none;
  }

  .calendar-container {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
  }

  .calendar-month {
    flex: 1;
  }

  .calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 5px 0;
    background: transparent !important;
    background-color: transparent !important;
  }

  .calendar-header .btn-link {
    color: #333 !important;
    text-decoration: none;
    background: transparent !important;
    background-color: transparent !important;
    border: none !important;

    &:hover {
      color: #1284ed !important;
      background: transparent !important;
      background-color: transparent !important;
    }

    &:focus {
      background: transparent !important;
      background-color: transparent !important;
    }

    &:active {
      background: transparent !important;
      background-color: transparent !important;
    }
  }

  .month-year {
    font-weight: 600;
    font-size: 16px;
    color: #333 !important;
  }

  .calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    margin-bottom: 5px;
  }

  .weekday {
    text-align: center;
    font-weight: 600;
    font-size: 12px;
    color: #666;
    padding: 5px;
  }

  .calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
  }

  .calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 1px solid #e0e0e0;
    border-radius: 3px;
    font-size: 14px;
    transition: all 0.2s;
    background-color: white;
    color: #333;

    &:hover:not(.selected-start):not(.selected-end):not(.in-range) {
      background-color: #f5f5f5;
    }

    &.other-month {
      color: #ccc;
      background-color: #fafafa;
      cursor: default;
      pointer-events: none;
    }

    // Espacios vacíos para alinear los días
    &.empty-day {
      cursor: default;
      pointer-events: none;
      border: none;
      background-color: transparent;

      &:hover {
        background-color: transparent;
      }
    }

    &.today {
      font-weight: normal; // Removido bold para evitar números en negrita
    }

    &.available {
      background-color: #c9f2e3;
      color: #636363;
      border-color: #636363;
    }

    &.soldout {
      background-color: #ffcccc;
      color: #636363;
      border-color: #636363;
    }

    &.blocked {
      background-color: transparent;
      color: #c4c4c4;
      border-color: #c4c4c4;
      cursor: pointer;
    }

    &.selected-start,
    &.selected-end {
      border: 1px solid #1284ed;
      // font-weight: bold; // Removido para evitar números en negrita
      // Mantener el color de fondo según el estado de disponibilidad
      &.available {
        background-color: #1284ed33;
        color: #636363;
      }
      &.soldout {
        background-color: #ffcccc;
        color: #636363;
      }
      &.blocked {
        background-color: #dcdcdc;
        color: #636363;
      }
    }

    &.in-range {
      border: 1px solid #1284ed;
      // Mantener el color de fondo según el estado de disponibilidad
      &.available {
        background-color: #1284ed33;
        color: #636363;
      }
      &.soldout {
        background-color: #ffcccc;
        color: #636363;
      }
      &.blocked {
        background-color: #dcdcdc;
        color: #636363;
      }
    }

    &.in-range:hover {
      // Mantener el hover según el estado
      &.available {
        background-color: #b0e8d1;
      }
      &.soldout {
        background-color: #ffb3b3;
      }
    }
  }

  .calendar-footer {
    display: flex;
    justify-content: flex-end;
    padding-top: 10px;
  }

  .confirm-button {
    width: 25%;
    min-width: 174px;
    min-height: 48px;
    font-size: 16px;
    font-weight: 600;
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    .date-range-calendar-wrapper {
      max-width: 280px;
    }

    .ant-picker-input {
      height: 36px;
      min-height: 36px;
    }

    .ant-picker-input-input {
      font-size: 13px;
      padding-right: 55px;
    }

    .calendar-popup {
      min-width: 550px;
      max-width: 600px;
      padding: 12px;
    }

    .calendar-legend {
      gap: 12px;
      margin-bottom: 12px;
      padding-bottom: 8px;
    }

    .legend-title {
      font-size: 14px;
    }

    .badge {
      padding: 4px 8px;
      font-size: 11px;
    }

    .calendar-container {
      gap: 15px;
      margin-bottom: 12px;
    }

    .month-year {
      font-size: 14px;
    }

    .weekday {
      font-size: 11px;
      padding: 4px;
    }

    .calendar-day {
      font-size: 13px;
    }

    .confirm-button {
      min-width: 150px;
      min-height: 42px;
      font-size: 14px;
    }
  }
</style>
