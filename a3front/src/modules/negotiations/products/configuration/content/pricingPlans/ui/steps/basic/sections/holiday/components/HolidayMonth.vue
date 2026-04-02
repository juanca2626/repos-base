<template>
  <div class="month-container">
    <div class="month-title">
      {{ capitalize(monthName) }}
    </div>

    <!-- Weekdays -->
    <div class="weekdays-row">
      <span v-for="day in weekdays" :key="day" class="weekday">
        {{ day }}
      </span>
    </div>

    <!-- Days Grid -->
    <div class="days-grid">
      <div v-for="n in firstDayOffset" :key="'empty-' + n" class="day empty" />

      <div
        v-for="day in daysInMonth"
        :key="day"
        class="day"
        :class="getDayClass(day)"
        @click="selectDay(day)"
      >
        {{ day }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  // Selecciona por días individuales usando `expandedDates`.

  const props = defineProps<{
    year: number;
    monthIndex: number;
    monthName: string;
    categories: any[];
    selectedCategoryId: string | null;
    editingItem: any | null;
    editMode: boolean;
    travelFrom: string;
    travelTo: string;
  }>();

  const emit = defineEmits(['selectDay']);

  const weekdays = ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'];

  const buildDate = (day: number): Dayjs => {
    return dayjs().year(props.year).month(props.monthIndex).date(day);
  };

  const firstDayOffset = computed(() => {
    return dayjs().year(props.year).month(props.monthIndex).date(1).day();
  });

  const daysInMonth = computed(() => {
    return dayjs().year(props.year).month(props.monthIndex).endOf('month').date();
  });

  const formatCellDate = (day: number) => buildDate(day).format('YYYY-MM-DD');

  const editingDatesSet = computed(() => {
    if (!props.editMode || !props.editingItem) return new Set<string>();
    return new Set<string>(props.editingItem.expandedDates ?? []);
  });

  const selectedDatesSet = computed(() => {
    if (!props.selectedCategoryId) return new Set<string>();

    const selectedCategory = props.categories.find(
      (c) => (c.id ?? c.uuid) === props.selectedCategoryId
    );
    if (!selectedCategory) return new Set<string>();

    const result = new Set<string>();
    for (const d of selectedCategory.dates ?? []) {
      for (const date of d.expandedDates ?? []) {
        result.add(date);
      }
    }
    return result;
  });

  const otherDatesSet = computed(() => {
    // Cuando no hay categoría seleccionada, todo se considera "plomo/disabled"
    if (!props.selectedCategoryId) {
      const result = new Set<string>();
      for (const c of props.categories ?? []) {
        for (const d of c.dates ?? []) {
          for (const date of d.expandedDates ?? []) {
            result.add(date);
          }
        }
      }
      return result;
    }

    const result = new Set<string>();
    for (const c of props.categories ?? []) {
      if ((c.id ?? c.uuid) === props.selectedCategoryId) continue;
      for (const d of c.dates ?? []) {
        for (const date of d.expandedDates ?? []) {
          result.add(date);
        }
      }
    }
    return result;
  });

  const getDayClass = (day: number) => {
    const date = buildDate(day);
    const dateStr = formatCellDate(day);

    if (isOutsideTravelRange(date)) return 'out-range';

    if (props.editMode && props.editingItem) {
      if (editingDatesSet.value.has(dateStr)) return 'editing';
      return 'disabled';
    }

    if (editingDatesSet.value.has(dateStr)) return 'editing';
    if (selectedDatesSet.value.has(dateStr)) return 'selected';
    if (otherDatesSet.value.has(dateStr)) return 'disabled';

    return '';
  };

  const selectDay = (day: number) => {
    if (!props.editMode || !props.editingItem) return;
    emit('selectDay', formatCellDate(day));
  };

  const isOutsideTravelRange = (date: Dayjs) => {
    const start = dayjs(props.travelFrom);
    const end = dayjs(props.travelTo);

    if (!start.isValid() || !end.isValid()) return false;

    return date.isBefore(start, 'day') || date.isAfter(end, 'day');
  };

  const capitalize = (text: string) => text.charAt(0).toUpperCase() + text.slice(1);
</script>

<style scoped>
  .month-container {
    padding: 12px;
  }

  .month-title {
    text-align: center;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 12px;
    color: #4f4b4b;
  }

  .weekdays-row {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
  }

  .weekday {
    text-align: center;
    font-size: 14px;
    color: #c4c4c4;
  }

  .days-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    user-select: none;
  }

  .day {
    text-align: center;
    padding: 3px 6px;
    border-radius: 6px;
    font-size: 14px;
    color: #575757;
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .day:hover {
    background: #f2f2f2;
    transform: scale(1.05);
  }

  .day.selected {
    background: #2f353a;
    color: white;
    border-radius: 50%;
  }

  .day.disabled {
    background: #babcbd;
    color: white;
    border-radius: 50%;
  }

  .day.editing {
    background: #1284ed;
    color: white;
    border-radius: 50%;
  }

  .day.empty {
    visibility: hidden;
  }

  .day.out-range {
    visibility: hidden;
    pointer-events: none;
  }
</style>
