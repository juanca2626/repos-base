<template>
  <div class="holiday-item">
    <div class="item-header">
      <div class="item-header-left">
        <a-checkbox
          v-model:checked="item.isActive"
          :disabled="disabled || editMode"
          @change="handleDateSelection"
        />
        <span
          class="item-name"
          :class="{
            editing: editMode && item.externalId === editingItem,
            disabled: (disabled || editMode) && item.externalId !== editingItem,
          }"
          >{{ item.name }}</span
        >
        <span v-if="item.isNewFromApi" class="new-badge">Nuevo</span>
      </div>

      <div class="item-header-right">
        <span v-if="!editMode && !disabled" class="edit-btn" @click.stop="$emit('toggleEdit')">
          <EditPencilIcon />
        </span>
        <span
          class="edit-btn"
          v-if="editMode && item.externalId === editingItem"
          @click.stop="$emit('saveItem')"
        >
          <SaveIcon />
        </span>
      </div>
    </div>

    <div
      v-if="!(editMode && item.id === editingItem)"
      class="item-range"
      :class="{ disabled: editMode }"
    >
      • {{ formattedRange }}
    </div>

    <div v-if="editMode && item.id === editingItem" class="item-range-picker-wrapper">
      <a-range-picker
        v-model:value="rangeValue"
        format="DD/MM/YYYY"
        :allow-clear="false"
        class="item-range-picker"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import dayjs from 'dayjs';
  import customParseFormat from 'dayjs/plugin/customParseFormat';
  import {
    EditPencilIcon,
    SaveIcon,
  } from '@/modules/negotiations/products/configuration/content/pricingPlans/icons';

  const props = defineProps<{
    item: any;
    editMode: boolean;
    disabled: boolean;
    editingItem: string | number | null;
  }>();

  const formatDate = (dateString: string) => {
    const date = dayjs(dateString, 'YYYY-MM-DD');

    const weekday = date.locale('es').format('dddd');
    const day = date.format('DD');

    return `${capitalize(weekday)} ${day}`;
  };

  const capitalize = (text: string) => text.charAt(0).toUpperCase() + text.slice(1);

  const handleDateSelection = (event: { target?: { checked?: boolean } }) => {
    if (!event?.target?.checked) return;

    props.item.isNewFromApi = false;
  };

  const formattedRange = computed(() => {
    const dates: string[] = props.item.expandedDates ?? [];

    if (!dates.length) {
      return '';
    }

    // Ordenamos las fechas por día ascendente
    const sorted = [...dates].sort((a, b) => dayjs(a, 'YYYY-MM-DD').diff(dayjs(b, 'YYYY-MM-DD')));

    type Range = { start: string; end: string };
    const ranges: Range[] = [];

    let currentStart = sorted[0];
    let previous = sorted[0];

    for (let i = 1; i < sorted.length; i++) {
      const current = sorted[i];
      const isConsecutive =
        dayjs(current, 'YYYY-MM-DD').diff(dayjs(previous, 'YYYY-MM-DD'), 'day') === 1;

      if (!isConsecutive) {
        ranges.push({ start: currentStart, end: previous });
        currentStart = current;
      }

      previous = current;
    }

    // Último rango
    ranges.push({ start: currentStart, end: previous });

    // Formateamos: rangos como "Del X al Y", días sueltos solo "X", separados por coma
    const parts = ranges.map(({ start, end }) => {
      if (start === end) {
        return formatDate(start);
      }

      return `${formatDate(start)} - ${formatDate(end)}`;
    });

    return parts.join(', ');
  });

  dayjs.extend(customParseFormat);

  const rangeValue = computed({
    get() {
      const ranges = props.item.apiDateRange;

      if (!ranges || !ranges.from || !ranges.to) {
        return [];
      }

      return [dayjs(ranges.from, 'YYYY-MM-DD'), dayjs(ranges.to, 'YYYY-MM-DD')];
    },
    set(value) {
      if (!value || value.length !== 2 || !value[0] || !value[1]) return;

      const [start, end] = value;

      const from = start.format('YYYY-MM-DD');
      const to = end.format('YYYY-MM-DD');

      // Actualizamos el rango principal
      props.item.apiDateRange = { from, to };

      // Recalculamos expandedDates:
      //  - generamos todas las fechas consecutivas entre from y to
      //  - preservamos las fechas existentes que estén fuera de ese rango
      const existing: string[] = props.item.expandedDates ?? [];

      const fromDay = dayjs(from, 'YYYY-MM-DD');
      const toDay = dayjs(to, 'YYYY-MM-DD');

      // Fechas nuevas del rango seleccionado
      const rangeDates: string[] = [];
      let cursor = fromDay.clone();

      while (cursor.isSameOrBefore(toDay, 'day')) {
        rangeDates.push(cursor.format('YYYY-MM-DD'));
        cursor = cursor.add(1, 'day');
      }

      // Preservamos las fechas existentes que NO caen dentro del nuevo rango
      const preserved = existing.filter((d) => {
        const date = dayjs(d, 'YYYY-MM-DD');
        return date.isBefore(fromDay, 'day') || date.isAfter(toDay, 'day');
      });

      // Combinamos preservadas + rango nuevo, sin duplicados y ordenado
      const merged = Array.from(new Set([...preserved, ...rangeDates])).sort((a, b) =>
        dayjs(a, 'YYYY-MM-DD').diff(dayjs(b, 'YYYY-MM-DD'))
      );

      props.item.expandedDates = merged;
    },
  });
</script>

<style scoped>
  .holiday-item {
    padding: 4px 8px;
    border-radius: 6px;
    cursor: grab;
  }

  .item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }

  .item-name {
    font-size: 14px;
    font-weight: 700;
    color: #2f353a;
  }

  .item-name.editing {
    color: #1284ed;
  }

  .item-name.disabled {
    color: #babcbd;
  }

  .item-range {
    font-size: 14px;
    color: #7e8285;
    margin-left: 24px;
    margin-top: 4px;
  }

  .item-range.disabled {
    color: #babcbd;
  }

  .edit-btn {
    margin-left: auto;
    cursor: pointer;
  }

  .item-range-picker-wrapper {
    margin-left: 24px;
    margin-top: 6px;
  }

  .item-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .item-header-right {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: flex-end;
  }

  .new-badge {
    padding: 2px 8px;
    border-radius: 999px;
    background: #e8f6ee;
    color: #198754;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.4;
  }
</style>
