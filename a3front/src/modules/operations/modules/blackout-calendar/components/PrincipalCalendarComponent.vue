<template>
  <div class="calendar-wrapper" ref="calendarWrapper">
    <div
      class="flex-col justify-center items-start inline-flex gap-2.5"
      @mousedown="onMouseDown"
      @mousemove="onMouseMove"
      @mouseup="onMouseUp"
      @mouseleave="onMouseUp"
    >
      <HeaderCalendarComponent :year="monthInfo.year" :month="monthInfo.month" />

      <div
        v-for="item in locksByMonthList"
        :key="item"
        class="flex-col justify-start items-start flex"
      >
        <div class="justify-start items-start inline-flex h-14">
          <DayCalendarComponent
            v-for="day in monthInfo.daysInMonth"
            :key="day"
            :provider="item.provider"
            :locks="item.locks"
            :day="day"
            :month="monthInfo.month"
            :year="monthInfo.year"
            @click="handleClickDayCalendar(item, `${monthInfo.year}-${monthInfo.month}-${day}`)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import { ref, onMounted } from 'vue';
  import HeaderCalendarComponent from '@operations/modules/blackout-calendar/components/HeaderCalendarComponent.vue';
  import DayCalendarComponent from '@operations/modules/blackout-calendar/components/DayCalendarComponent.vue';
  import { useBlockCalendarStore } from '../store/blockCalendar.store';
  import { storeToRefs } from 'pinia';

  const blockCalendarStore = useBlockCalendarStore();
  const { locksByMonthList, monthInfo } = storeToRefs(blockCalendarStore);

  const { handleClickDayCalendar } = blockCalendarStore;
  const calendarWrapper = ref<HTMLElement | null>(null);
  const isDragging = ref(false);
  const startX = ref(0);
  const scrollLeft = ref(0);

  const onMouseDown = (e: MouseEvent) => {
    if (calendarWrapper.value) {
      isDragging.value = true;
      startX.value = e.pageX - calendarWrapper.value.offsetLeft;
      scrollLeft.value = calendarWrapper.value.scrollLeft;
    }
  };

  const onMouseMove = (e: MouseEvent) => {
    if (!isDragging.value || !calendarWrapper.value) return;
    e.preventDefault();
    const x = e.pageX - calendarWrapper.value.offsetLeft;
    const walk = (x - startX.value) * 3; // Ajusta la velocidad del desplazamiento
    calendarWrapper.value.scrollLeft = scrollLeft.value - walk;
  };

  const onMouseUp = () => {
    isDragging.value = false;
  };

  // Función para obtener el día actual
  const getCurrentDay = () => {
    const today = new Date();
    return today.getDate();
  };

  onMounted(() => {
    // Ajustar el desplazamiento inicial
    if (calendarWrapper.value) {
      const currentDay = getCurrentDay();
      if (currentDay > 15) {
        // Desplazar hacia la derecha
        calendarWrapper.value.scrollTo({
          left: calendarWrapper.value.scrollWidth,
          behavior: 'smooth',
        });
      } else {
        // Desplazar al inicio
        calendarWrapper.value.scrollTo({
          left: 0,
          behavior: 'smooth',
        });
      }
    }
  });
</script>

<style scoped>
  @import '@/modules/operations/shared/styles/tailwind.css';
  .calendar-wrapper {
    overflow: hidden;
    cursor: grab;
    width: 100%;
    user-select: none; /* Evita la selección de texto al arrastrar */
  }

  .calendar-wrapper .flex-col {
    display: flex;
    cursor: grabbing;
    width: max-content;
  }
</style>
