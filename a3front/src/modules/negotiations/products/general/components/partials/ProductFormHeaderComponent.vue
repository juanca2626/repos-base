<template>
  <div class="container-title">
    <div class="title-action">
      <span class="general-title"> Información general </span>
      <template v-if="showSummaryForm">
        <span class="btn-edit-form cursor-pointer" @click="handleEdit">
          Editar
          <font-awesome-icon :icon="['fas', 'pen-to-square']" />
        </span>
      </template>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { useProductFormStoreFacade } from '@/modules/negotiations/products/general/composables/form/useProductFormStoreFacade';
  import { useProductFormManager } from '@/modules/negotiations/products/general/composables/useProductFormManager';

  const { showSummaryForm, openEditableMode } = useProductFormManager();

  const { setIsEditMode } = useProductFormStoreFacade();

  const handleEdit = () => {
    openEditableMode();
    setIsEditMode(true);
  };
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-title {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .title-action {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .general-title {
    font-size: 20px;
    font-weight: 600;
    color: $color-black-dark-2;
  }

  .btn-edit-form {
    font-size: 16px;
    font-weight: 500;
    color: $color-blue;
  }
</style>
