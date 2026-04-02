import { ref, computed, type ComputedRef } from 'vue';

export const useServiceDetailsEditMode = (getSectionsItemActive: ComputedRef<any>) => {
  const showEditModal = ref(false);

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? false;
  });

  const handleEditMode = () => {
    showEditModal.value = true;
  };

  const handleConfirmEdit = () => {
    getSectionsItemActive.value.editing = true;
    showEditModal.value = false;
  };

  const handleCancelEdit = () => {
    showEditModal.value = false;
  };

  return {
    isEditingContent,
    showEditModal,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,
  };
};
