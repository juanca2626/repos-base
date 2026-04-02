<template>
  <div class="calendar-panel">
    <div class="calendar-header">
      <a-select v-model:value="selectedYear" :options="yearOptions" />
    </div>

    <div class="calendar-body" v-if="selectedYear !== null">
      <div class="calendar-grid">
        <HolidayMonth
          v-for="month in months"
          :key="month.monthIndex"
          :year="selectedYear"
          :monthIndex="month.monthIndex"
          :monthName="month.name"
          :categories="categories"
          :selectedCategoryId="selectedCategoryId"
          :editingItem="editingItem"
          :editMode="editMode"
          :travelFrom="travelFrom"
          :travelTo="travelTo"
          @rangeSelected="emit('rangeSelected', $event)"
          @selectDay="emit('selectDay', $event)"
        />
      </div>
    </div>
    <div v-else class="calendar-empty-state">No hay anio seleccionado.</div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import HolidayMonth from './HolidayMonth.vue';

  const props = defineProps<{
    months: any[];
    categories: any[];
    selectedCategoryId: string | null;
    editingItem: any | null;
    editMode: boolean;
    loadingHoliday: boolean;
    years: number[];
    selectedYear: number | null;
    travelFrom: string;
    travelTo: string;
  }>();

  const emit = defineEmits(['selectDay', 'rangeSelected', 'update:selectedYear']);

  const selectedYear = computed({
    get: () => props.selectedYear,
    set: (value: number) => emit('update:selectedYear', value),
  });

  const yearOptions = computed(() =>
    (props.years || []).map((y) => ({
      label: String(y),
      value: y,
    }))
  );
</script>

<style scoped>
  .calendar-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
  }

  .calendar-header {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 8px;
  }

  .calendar-body {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding-right: 8px; /* evita que el scroll tape el contenido */
  }

  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    max-width: 580px;
    width: 100%;
  }

  .calendar-empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    color: #8c8c8c;
    font-size: 13px;
  }
</style>
