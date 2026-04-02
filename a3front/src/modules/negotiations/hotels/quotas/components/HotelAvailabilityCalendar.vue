<template>
  <div class="hotel-availability-calendar">
    <!-- Header con navegación y nombre del mes -->
    <div class="calendar-header">
      <div class="header-left">
        <button type="button" class="nav-button" @click="previousMonth">
          <font-awesome-icon :icon="['fas', 'chevron-left']" />
        </button>
        <span class="month-name">{{ monthName }}</span>
        <button type="button" class="nav-button" @click="nextMonth">
          <font-awesome-icon :icon="['fas', 'chevron-right']" />
        </button>
      </div>
      <div class="header-right">
        <!-- Leyenda -->
        <div class="legend">
          <div class="legend-item legend-item-soldout">
            <font-awesome-icon
              :icon="['fas', 'circle-exclamation']"
              class="legend-icon legend-icon-soldout"
            />
            <span class="legend-text">Agotado</span>
          </div>
          <div class="legend-item legend-item-available">
            <IconCheck class="legend-icon legend-icon-available" />
            <span class="legend-text">Disponible</span>
          </div>
          <div class="legend-item legend-item-blocked">
            <font-awesome-icon
              :icon="['fas', 'circle-xmark']"
              class="legend-icon legend-icon-blocked"
            />
            <span class="legend-text">Bloqueado</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Grid del calendario -->
    <div class="calendar-grid">
      <!-- Días de la semana -->
      <div class="weekdays">
        <div v-for="day in weekdays" :key="day" class="weekday">{{ day }}</div>
      </div>

      <!-- Días del mes -->
      <div class="calendar-days">
        <div
          v-for="day in calendarDays"
          :key="day.key"
          :class="[
            'calendar-day',
            { 'other-month': !day.isCurrentMonth, selected: isSelected(day.date) },
          ]"
          @click="(e) => handleDayClick(day.date, e)"
        >
          <div v-if="day.isCurrentMonth" class="day-number">{{ day.day }}</div>
          <div v-if="day.isCurrentMonth && getDayData(day.date)" class="day-badges">
            <div v-if="(getDayData(day.date)?.agotados ?? 0) > 0" class="badge badge-soldout">
              <font-awesome-icon :icon="['fas', 'circle-exclamation']" class="badge-icon" />
              {{ getDayData(day.date)?.agotados }} agotados
            </div>
            <div v-if="(getDayData(day.date)?.disponibles ?? 0) > 0" class="badge badge-available">
              <IconCheck class="badge-icon" />
              {{ getDayData(day.date)?.disponibles }} disponibles
            </div>
            <div v-if="(getDayData(day.date)?.bloqueados ?? 0) > 0" class="badge badge-blocked">
              <font-awesome-icon :icon="['fas', 'circle-xmark']" class="badge-icon" />
              {{ getDayData(day.date)?.bloqueados }} bloqueados
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Popup para mostrar detalles -->
    <CalendarBadgeModal
      v-model:visible="modalVisible"
      :date="selectedBadgeDate"
      :hotel-details="selectedDayAllHotels"
      :totals="selectedBadgeTotals || undefined"
      :x="popupX"
      :y="popupY"
    />
  </div>
</template>

<script setup lang="ts">
  import { ref, computed } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { useHotelAvailabilityCalendar } from '@/modules/negotiations/hotels/quotas/composables/useHotelAvailabilityCalendar';
  import { useHotelAvailabilityCalendarStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-calendar.store';
  import IconCheck from '@/modules/negotiations/hotels/quotas/icons/icon-check.vue';
  import CalendarBadgeModal from './CalendarBadgeModal.vue';
  import type {
    HotelAvailabilityFilters,
    FilterOption,
  } from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';

  interface Props {
    filters?: { value: HotelAvailabilityFilters };
    hotelCategories?: { value: FilterOption[] };
  }

  const props = defineProps<Props>();

  const calendarStore = useHotelAvailabilityCalendarStore();

  const {
    weekdays,
    monthName,
    calendarDays,
    previousMonth,
    nextMonth,
    selectDay,
    isSelected,
    getDayData,
  } = useHotelAvailabilityCalendar(props.filters, props.hotelCategories);

  const modalVisible = ref(false);
  const selectedBadgeDate = ref('');
  const popupX = ref(0);
  const popupY = ref(0);

  const selectedDayAllHotels = computed(() => {
    if (!selectedBadgeDate.value) return [];
    const dayData = calendarStore.getDayData(selectedBadgeDate.value);
    return dayData?.details || [];
  });

  const selectedBadgeTotals = computed(() => {
    if (!selectedBadgeDate.value) return undefined;
    return calendarStore.getDayTotals(selectedBadgeDate.value);
  });

  const handleDayClick = (date: string, event: MouseEvent) => {
    // Prevenir que el evento se propague para evitar que handleClickOutside se ejecute
    event.stopPropagation();

    const dayData = calendarStore.getDayData(date);
    if (dayData && dayData.details.length > 0) {
      // Actualizar la fecha seleccionada (esto actualizará el popup automáticamente)
      selectedBadgeDate.value = date;
      selectDay(date);

      // Obtener la posición del elemento clickeado
      const target = event.currentTarget as HTMLElement;
      const rect = target.getBoundingClientRect();

      // Calcular la posición del centro del día
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 2;

      // Determinar si el día está en la parte izquierda o derecha de la pantalla
      // Si el centro está en la mitad derecha de la pantalla, mostrar popup a la izquierda
      // Si el centro está en la mitad izquierda, mostrar popup a la derecha
      const screenWidth = window.innerWidth;
      const screenHeight = window.innerHeight;
      const isOnRightSide = centerX > screenWidth / 2;

      // Ajustar la posición según el lado
      if (isOnRightSide) {
        // Si está a la derecha de la pantalla, posicionar el popup a la izquierda del día
        popupX.value = rect.left;
      } else {
        // Si está a la izquierda de la pantalla, posicionar el popup a la derecha del día
        popupX.value = rect.right;
      }

      // Calcular la posición Y: si el día está cerca del borde inferior, usar la parte superior
      // para dar más espacio hacia abajo al popup
      const popupMaxHeight = 500; // max-height del popup
      const spaceBelow = screenHeight - rect.bottom;
      const spaceAbove = rect.top;

      // Si hay poco espacio abajo pero suficiente arriba, usar la parte superior del día
      if (spaceBelow < popupMaxHeight && spaceAbove > spaceBelow) {
        popupY.value = rect.top;
      } else {
        // Usar el centro del día
        popupY.value = centerY;
      }
      // Siempre mostrar el popup (se actualizará automáticamente con la nueva fecha)
      modalVisible.value = true;
    }
  };
</script>

<style lang="scss" scoped>
  @import '@/scss/_variables.scss';

  .hotel-availability-calendar {
    background: #ffffff;
    border: 1px solid #dcdcdc;
    border-radius: 8px;
  }

  .calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px;
    flex-wrap: wrap;
    gap: 16px;
  }

  .header-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .month-name {
    font-size: 20px;
    font-weight: 400;
    color: #2f353a;
    min-width: 150px;
    padding: 8px 12px;
    text-align: center;
  }

  .nav-button {
    background: transparent;
    border: none;
    border-radius: 4px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #575b5f;
    transition: all 0.2s;

    &:hover {
      background-color: #f5f5f5;
      border-color: #babcbd;
    }

    &:active {
      background-color: #e8e8e8;
    }

    svg {
      width: 12px;
      height: 12px;
    }
  }

  .header-right {
    display: flex;
    align-items: center;
  }

  .legend {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .legend-icon {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
  }

  .legend-icon-soldout {
    color: #d80404;
  }

  .legend-icon-available {
    color: #00a15b;

    :deep(path) {
      fill: currentColor;
    }
  }

  .legend-icon-blocked {
    color: #636363;
  }

  .legend-text {
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
  }

  .legend-item-soldout .legend-text {
    color: #d80404;
  }

  .legend-item-available .legend-text {
    color: #00a15b;
  }

  .legend-item-blocked .legend-text {
    color: #636363;
  }

  .calendar-grid {
    width: 100%;
  }

  .weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
  }

  .weekday {
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    color: #7e8285;
    padding: 10px 8px;
    border: 1px solid #e9e9e9;
    color: #c4c4c4;
  }

  .calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0px;
  }

  .calendar-day {
    min-height: 100px;
    border: 1px solid #dcdcdc;
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: all 0.2s;
    background-color: #ffffff;
    position: relative;

    &:hover:not(.other-month) {
      background-color: #f9f9f9;
    }

    &.other-month {
      background-color: #fafafa;
      cursor: default;
      opacity: 0.5;
    }

    &.selected {
      background-color: #f5f5f5;
    }
  }

  .day-number {
    font-size: 12px;
    font-weight: 400;
    color: #2f353a;
    margin-bottom: 4px;
  }

  .day-badges {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
    justify-content: flex-end;
  }

  .badge {
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 6px;
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
  }

  .badge-icon {
    width: 12px;
    height: 12px;
    flex-shrink: 0;
  }

  .badge-soldout {
    background-color: #ffe1e1;
    color: #d80404;
  }

  .badge-available {
    background-color: #dfffe9;
    color: #00a15b;

    .badge-icon {
      :deep(path) {
        fill: currentColor;
      }
    }
  }

  .badge-blocked {
    background-color: #dcdcdc;
    color: #636363;
    font-weight: 600;
  }

  // Responsive
  @media (max-width: 768px) {
    .calendar-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .header-right {
      width: 100%;
    }

    .legend {
      width: 100%;
      flex-direction: column;
      gap: 12px;
    }

    .calendar-day {
      min-height: 80px;
      padding: 6px 3px;
    }

    .badge {
      font-size: 9px;
      padding: 2px 4px;
    }
  }
</style>
