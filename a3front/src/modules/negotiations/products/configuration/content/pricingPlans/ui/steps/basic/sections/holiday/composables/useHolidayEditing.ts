import { type Ref } from 'vue';
import dayjs from 'dayjs';

interface UseHolidayEditingProps {
  uiState: any;
  editingItem: Ref<any>;
  selectedItem: Ref<any>;
}

export function useHolidayEditing({ uiState, editingItem, selectedItem }: UseHolidayEditingProps) {
  const toggleEdit = (itemId: string) => {
    uiState.editMode = true;
    uiState.editingItemId = itemId;

    console.log('editingItemId', {
      uiState,
      itemId,
    });
  };

  const exitEditMode = () => {
    uiState.editMode = false;

    const item = selectedItem.value;

    if (item) {
      item.isEditing = false;
    }
  };

  const selectCalendarDay = (date: string) => {
    if (!uiState.editMode || !editingItem.value) return;

    const expanded: string[] = editingItem.value.expandedDates ?? [];
    const isAlreadySelected = expanded.includes(date);

    let nextExpanded: string[];

    if (!expanded.length) {
      nextExpanded = isAlreadySelected ? [] : [date];
    } else if (isAlreadySelected) {
      nextExpanded = expanded.filter((d) => d !== date);
    } else {
      nextExpanded = Array.from(new Set([...expanded, date]));
    }

    const nextSortedExpanded = [...nextExpanded].sort((a, b) =>
      dayjs(a, 'YYYY-MM-DD').diff(dayjs(b, 'YYYY-MM-DD'))
    );

    editingItem.value.expandedDates = nextSortedExpanded;
  };

  const updateItemRange = ({ from, to }: { from: string; to: string }) => {
    if (!editingItem.value) return;

    editingItem.value.range = {
      from: dayjs(from).format('YYYY-MM-DD'),
      to: dayjs(to).format('YYYY-MM-DD'),
    };

    editingItem.value.isModified = true;
  };

  const saveEditingItem = () => {
    if (!uiState.editingItemId) return;

    uiState.editMode = false;
    uiState.editingItemId = null;
  };

  return {
    toggleEdit,
    exitEditMode,
    selectCalendarDay,
    updateItemRange,
    saveEditingItem,
  };
}
