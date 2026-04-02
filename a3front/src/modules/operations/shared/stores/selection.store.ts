// src/stores/selectionStore.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { notification } from 'ant-design-vue';

export const useSelectionStore = defineStore('selection', () => {
  // Estado de los ítems seleccionados
  const selectedItems = ref<{ type: string; item: any }[]>([]);

  // Agregar un ítem a la lista seleccionada
  const addItem = (type: string, item: any, isShowNotification: boolean) => {
    selectedItems.value.push({ type, item });

    if (isShowNotification) {
      notification.success({
        message: 'Item seleccionado',
        // description: `Se agregó el ítem de tipo ${type} a la selección.`,
        description: `Se agregó el ítem a la selección.`,
      });
    }
    console.log(selectedItems.value);
  };

  // Eliminar un ítem de la lista seleccionada
  const removeItem = (type: string, itemId: string | number, isShowNotification: boolean) => {
    selectedItems.value = selectedItems.value.filter(
      (selected) => selected.type !== type || selected.item._id !== itemId
    );

    if (isShowNotification) {
      notification.info({
        message: 'Item deseleccionado',
        // description: `Se eliminó el ítem de tipo ${type} de la selección.`,
        description: `Se eliminó el ítem de la selección.`,
      });
    }

    console.log(selectedItems.value);
  };

  const toggleItem = (
    type: string,
    record: any,
    isChecked: boolean,
    isShowNotification: boolean = false
  ) => {
    isChecked
      ? addItem(type, record, isShowNotification)
      : removeItem(type, record._id, isShowNotification);
  };

  const getItemsByType = (type: string) => {
    return selectedItems.value
      .filter((selected) => selected.type === type)
      .map((selected) => selected.item);
  };

  // Comprobar si un ítem está seleccionado
  // const isSelected = (itemId: string | number) => {
  //   return selectedItems.value.some((item) => item.id === itemId);
  // };

  // Restablecer la selección
  const resetSelection = () => {
    selectedItems.value = [];
    notification.warning({
      message: 'Selección reiniciada',
      description: 'Se reinició la lista de selección.',
    });
    console.log('🚀 ~ resetSelection ~ selectedItems:', selectedItems.value);
  };

  return {
    selectedItems,
    addItem,
    removeItem,
    toggleItem,
    getItemsByType,
    resetSelection,
  };
});
