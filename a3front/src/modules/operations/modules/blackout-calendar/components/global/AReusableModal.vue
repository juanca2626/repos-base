<template>
  <a-modal
    class="module-operations"
    :open="isModalVisible"
    centered
    :closable="false"
    :width="488"
    @ok="handleModalOk"
    @cancel="handleModalCancel"
  >
    <template #title>
      <div class="custom-header">
        <h5>{{ modalTitle }}</h5>
        <div class="icon-close" @click="handleModalCancel">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 31 30">
            <path
              d="M23 7.5L8 22.5"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M8 7.5L23 22.5"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
      </div>
    </template>
    <template #footer>
      <a-button key="back" @click="handleModalCancel" size="large"> Cancelar </a-button>
      <a-button key="submit" type="primary" :loading="loading" @click="handleModalOk" size="large">
        Guardar
      </a-button>
    </template>
    <slot
      :type="modalType"
      :formUpdateLock="formUpdateLock"
      :blockingReasonsOptions="blockingReasonsOptions"
      :selectedLocksSummary="selectedLocksSummary"
    />
  </a-modal>
</template>

<script lang="ts" setup>
  import { storeToRefs } from 'pinia';
  import { useModalStore } from '@operations/modules/blackout-calendar/store/modal.store';

  const modalStore = useModalStore();
  const {
    isModalVisible,
    modalTitle,
    modalType,
    formUpdateLock,
    blockingReasonsOptions,
    selectedLocksSummary,
    loading,
  } = storeToRefs(modalStore);

  const handleModalOk = modalStore.handleModalOk;
  const handleModalCancel = modalStore.handleModalCancel;
</script>

<style lang="scss" scoped>
  .custom-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .icon-close {
    cursor: pointer;
  }
  .custom-tag {
    font-weight: 600;
    font-size: 14px;
  }
</style>
