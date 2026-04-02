<template>
  <div class="h-12 bg-gray-800 justify-center items-center gap-[15px] inline-flex p-2">
    <div
      class="w-[35px] h-9 px-[5px] pt-px flex-col justify-center items-center text-white"
      v-for="day in Array.from({ length: monthInfo.daysInMonth }, (_, i) => i + 1)"
      :key="day"
    >
      <div class="text-center text-xs">
        {{ getDayNames(new Date(monthInfo.year, monthInfo.month - 1, day)) }}
      </div>
      <div class="text-center text-sm font-semibold">
        {{ day }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { watch, onMounted } from 'vue';
  import { useBlockCalendarStore } from '@operations/modules/blackout-calendar/store/blockCalendar.store';
  import { storeToRefs } from 'pinia';

  const blockCalendarStore = useBlockCalendarStore();
  const { monthInfo } = storeToRefs(blockCalendarStore);
  const { getMonthInfo, getDayNames } = blockCalendarStore;

  const updateMonthInfo = () => {
    getMonthInfo(monthInfo.value.month, monthInfo.value.year);
  };

  watch(
    () => [monthInfo.value.month, monthInfo.value.year],
    ([newMonth, newYear], [oldMonth, oldYear]) => {
      if (newMonth !== oldMonth || newYear !== oldYear) {
        updateMonthInfo();
      }
    }
  );

  onMounted(() => {
    updateMonthInfo();
  });
</script>

<style scoped>
  @import '@/modules/operations/shared/styles/tailwind.css';
</style>
