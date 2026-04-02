<template>
  <div
    class="category-card"
    :class="{ selected }"
    @click="$emit('selectCategory', category.id ?? category.uuid)"
  >
    <!-- HEADER -->
    <div class="category-header">
      <span class="category-wrapper" :class="{ disabled: editMode }">
        <EartIcon v-if="category.key === 'GENERAL'" :color="editMode ? '#BABCBD' : '#2F353A'" />
        <PlaneIcon v-if="category.key === 'TOURIST'" :color="editMode ? '#BABCBD' : '#2F353A'" />
        <MapIcon v-if="category.key === 'CITY'" :color="editMode ? '#BABCBD' : '#2F353A'" />
        <TagIcon v-if="category.key === 'CUSTOM'" :color="editMode ? '#BABCBD' : '#2F353A'" />
        <span class="category-name-text">{{ category.label }}</span>
      </span>

      <span class="badge-count" :class="{ disabled: editMode }">
        {{ totalCheckedItems }} fechas
        <ConfettiIcon :color="editMode ? '#BABCBD' : '#2F353A'" />
      </span>
    </div>
    <div class="category-header">
      <!-- Check General -->
      <a-checkbox
        :checked="category.isActive"
        @change="$emit('toggleCategoryCheck', category.id ?? category.uuid)"
        :disabled="!selected || editMode"
      />

      <!-- category wrapper -->
      <span class="category-wrapper" :class="{ disabled: editMode }">
        <span>Seleccionar todos</span>
      </span>
      <!-- Contador -->
    </div>

    <div class="items-wrapper">
      <template v-if="hasItems">
        <template v-for="(_group, month) in groupedItems" :key="month">
          <div class="month-title">
            <CalendarDayIcon />
            {{ month }}
          </div>

          <draggable
            v-model="groupedItems[month]"
            :group="{ name: 'holidays', pull: true, put: true }"
            item-key="externalId"
            :disabled="editMode"
            ghost-class="ghost"
            @change="handleDragChange"
            :animation="150"
            :swapThreshold="0.5"
            :fallbackOnBody="true"
            :forceFallback="true"
          >
            <template #item="{ element }">
              <HolidayItem
                :item="element"
                :editMode="editMode"
                :disabled="!selected"
                :editingItem="editingItem"
                @updateCheck="handleItemCheck"
                @toggleEdit="$emit('toggleEdit', element.externalId)"
                @saveItem="$emit('saveItem', element.externalId)"
              />
            </template>
          </draggable>
        </template>
      </template>

      <template v-else>
        <draggable
          v-model="emptyDropBuffer"
          :group="{ name: 'holidays', pull: true, put: true }"
          item-key="externalId"
          :disabled="editMode"
          ghost-class="ghost"
          @change="handleEmptyDropChange"
          :animation="150"
          :swapThreshold="0.5"
          :fallbackOnBody="true"
          :forceFallback="true"
        >
          <template #item="{ element }">
            <div style="display: none">{{ element.name }}</div>
          </template>

          <div class="empty-drop-zone">Arrastra fechas aquí para esta categoría</div>
        </draggable>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed, ref } from 'vue';
  import draggable from 'vuedraggable';
  import {
    EartIcon,
    PlaneIcon,
    MapIcon,
    ConfettiIcon,
    CalendarDayIcon,
    TagIcon,
  } from '@/modules/negotiations/products/configuration/content/pricingPlans/icons';
  import HolidayItem from './HolidayItem.vue';

  const props = defineProps<{
    category: any;
    selected: boolean;
    editMode: boolean;
    editingItem: string | number | null;
  }>();

  const emit = defineEmits([
    'toggleCategoryCheck',
    'toggleEdit',
    'saveItem',
    'selectCategory',
    'moveItemToCategory',
  ]);

  const groupedItems = computed(() => {
    const groups: Record<string, any[]> = {};

    props.category.dates.forEach((item: any) => {
      const month = getMonthFromItem(item);

      if (!groups[month]) groups[month] = [];

      groups[month].push(item);
    });

    return groups;
  });

  const getMonthFromItem = (item: any) => {
    // Nueva regla: cuando seleccionamos por día, el UI se guía por `expandedDates`.
    // Si no existe, caemos a `ranges.from` (fallback) para mantener compatibilidad.
    const expandedDates: string[] = item.expandedDates ?? [];
    if (expandedDates.length) {
      const sorted = [...expandedDates].sort(
        (a, b) => new Date(a).getTime() - new Date(b).getTime()
      );
      const date = new Date(sorted[0]);
      const month = date.toLocaleString('es-ES', { month: 'long' });
      return `${month.charAt(0).toUpperCase() + month.slice(1)} (${date.getFullYear()})`;
    }

    if (!item.apiDateRange?.from) return 'Sin fecha';
    const date = new Date(item.apiDateRange.from);
    const month = date.toLocaleString('es-ES', { month: 'long' });
    return `${month} (${date.getFullYear()})`;
  };

  const totalCheckedItems = computed(
    () => props.category.dates.filter((i: any) => i.isActive).length
  );

  const hasItems = computed(() => props.category.dates.length > 0);

  const emptyDropBuffer = ref<any[]>([]);

  const handleDragChange = (event: any) => {
    if (event.added) {
      const item = event.added.element;
      emit('moveItemToCategory', {
        itemId: item.externalId,
        toCategoryId: props.category.id ?? props.category.uuid,
      });
    }
  };

  const handleEmptyDropChange = (event: any) => {
    if (event.added) {
      const item = event.added.element;
      emit('moveItemToCategory', {
        itemId: item.externalId,
        toCategoryId: props.category.id ?? props.category.uuid,
      });
      emptyDropBuffer.value = [];
    }
  };

  const handleItemCheck = () => {
    emit('toggleCategoryCheck', props.category.id ?? props.category.uuid);
  };
</script>

<style scoped>
  .category-card {
    background: white;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 16px;
    border: 1px solid #e5e5e5;
  }

  .category-card.selected {
    border-color: #1284ed;
  }

  .category-header {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    margin-bottom: 10px;
  }

  .category-header-count {
    margin-left: auto;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-bottom: 10px;
  }

  .category-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .category-wrapper.disabled {
    color: #babcbd;
  }

  .badge-count {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    font-weight: 600;
    color: #666;
    background-color: #f9f9f9;
    border-radius: 6px;
    padding: 4px 8px;
  }

  .badge-count.disabled {
    color: #babcbd;
  }

  .month-title {
    margin-top: 4px;
    font-size: 13px;
    font-weight: 400;
    color: #babcbd;
  }

  .items-wrapper {
    margin-top: 8px;
  }

  .ghost {
    opacity: 0.4;
  }

  .empty-drop-zone {
    margin-top: 8px;
    padding: 10px;
    border-radius: 8px;
    border: 1px dashed #d0d0d0;
    font-size: 13px;
    color: #7e8285;
    text-align: center;
    background-color: #fafafa;
  }
</style>
