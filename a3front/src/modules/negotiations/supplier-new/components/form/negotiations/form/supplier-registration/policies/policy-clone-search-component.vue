<template>
  <a-drawer
    class="policy-clone-search-drawer"
    :open="showModal"
    placement="right"
    :width="500"
    :closable="false"
    @close="handleCancel"
  >
    <template #title>
      <div class="drawer-header">
        <font-awesome-icon :icon="['far', 'clone']" class="drawer-header-icon" />
        <span class="drawer-header-text">Copiar política para proveedor</span>
        <font-awesome-icon
          icon="fa-solid fa-xmark"
          class="drawer-close-icon"
          @click="handleCancel"
        />
      </div>
    </template>
    <div class="drawer-content">
      <spin-global-component :spinning="isLoading">
        <div class="drawer-field">
          <label class="drawer-label">Selecciona el proveedor</label>
          <a-select
            v-model:value="formState.supplierId"
            placeholder="Selecciona"
            show-search
            :options="suppliers"
            :filter-option="filterOption"
            class="w-full"
            @change="handleChangeSupplier"
          />
        </div>
        <div class="drawer-field">
          <label class="drawer-label">Política a copiar</label>
          <a-select
            v-model:value="formState.supplierPolicyId"
            placeholder="Selecciona"
            show-search
            allow-clear
            :options="supplierPolicies"
            :filter-option="filterOption"
            class="w-full"
          />
        </div>
      </spin-global-component>

      <div class="drawer-bottom-divider"></div>

      <div class="drawer-button-container">
        <a-button
          size="large"
          type="primary"
          class="btn-clone"
          :disabled="isDisabled"
          :loading="isCloning"
          @click="handleClone"
        >
          Copiar política
        </a-button>
      </div>
    </div>
  </a-drawer>
</template>

<script setup lang="ts">
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import { usePolicyCloneSearchComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-clone-search.composable';

  const emit = defineEmits(['update:showModal']);

  const props = defineProps({
    showModal: {
      type: Boolean,
      required: true,
    },
  });

  const {
    isLoading,
    isCloning,
    formState,
    suppliers,
    supplierPolicies,
    isDisabled,
    handleCancel,
    handleClone,
    filterOption,
    handleChangeSupplier,
  } = usePolicyCloneSearchComposable(emit, () => props.showModal);
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .drawer-header {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;

    .drawer-header-icon {
      font-size: 20px;
      color: $color-black;
    }

    .drawer-header-text {
      flex: 1;
      font-size: 16px;
      font-weight: 600;
      color: $color-black;
    }

    .drawer-close-icon {
      cursor: pointer;
      font-size: 20px;
      color: $color-black;
      transition: opacity 0.2s;

      &:hover {
        opacity: 0.7;
      }
    }
  }

  .drawer-content {
    padding: 0;
    margin: 0 -24px;
  }

  .drawer-bottom-divider {
    height: 1px;
    background-color: #e8e8e8;
    width: 100%;
  }

  .drawer-field {
    padding-left: 24px;
    padding-right: 24px;
    padding-top: 10px;
    padding-bottom: 20px;

    .drawer-label {
      display: block;
      margin-bottom: 12px;
      font-size: 14px;
      font-weight: 600;
      color: $color-black;
    }
  }

  .drawer-button-container {
    padding: 32px 24px;
    display: flex;
    justify-content: flex-end;

    .ant-btn {
      width: 225px;
      height: 48px;
      font-size: 16px;
      font-weight: 600;
    }

    .btn-clone:disabled {
      color: $color-white-4;
      background: $color-black-5;
      border-color: $color-black-5 !important;
    }
  }

  .w-full {
    width: 100%;
  }
</style>
