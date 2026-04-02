<template>
  <div class="module-negotiations-supplier-form">
    <div class="container-form" :style="{ '--button-top': buttonTopPosition }">
      <div class="sidebar-container" :class="{ collapsed: isSidebarCollapsed }">
        <ItemCollapseComponent
          :is-collapsed="isSidebarCollapsed"
          @height-change="handleHeightChange"
        />
      </div>
      <button class="sidebar-toggle-btn" @click="toggleSidebar" v-show="sidebarContentHeight > 0">
        <font-awesome-icon :icon="['fas', isSidebarCollapsed ? 'chevron-right' : 'chevron-left']" />
      </button>
      <div class="content-wrapper">
        <a-spin
          v-if="isLoadingSupplierData"
          class="container-spin"
          :spinning="true"
          size="small"
          tip="Cargando..."
        />
        <transition name="fade">
          <div v-show="!isLoadingSupplierData" class="form-content">
            <SupplierFormComponent
              v-show="showSupplierForm"
              :key="`supplier-form-${supplierId || 'new'}`"
            />
            <PolicyManagerComponent
              v-show="showPolicyManager"
              :key="`policy-manager-${supplierId || 'new'}`"
            />
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed, onMounted, ref } from 'vue';
  import ItemCollapseComponent from '@/modules/negotiations/supplier-new/components/item-collapse-component.vue';
  import SupplierFormComponent from '@/modules/negotiations/supplier-new/components/form/supplier-form-component.vue';
  import { useSelectedSupplierClassificationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/selected-supplier-classification.composable';
  import PolicyManagerComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/policies/policy-manager-component.vue';
  import { useSupplierViewComposable } from '@/modules/negotiations/supplier-new/composables/supplier-view.composable';
  import { useSupplierCompleteData } from '@/modules/negotiations/supplier-new/composables/form/negotiations/use-supplier-complete-data.composable';
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';

  defineOptions({
    name: 'Form',
  });

  const { showSupplierForm, showPolicyManager } = useSupplierViewComposable();

  const { initSupplierClassificationId } = useSelectedSupplierClassificationComposable();

  const { isEditMode, supplierId } = useSupplierGlobalComposable();

  const { isLoading: isLoadingCompleteData } = useSupplierCompleteData();

  const isSidebarCollapsed = ref(false);
  const sidebarContentHeight = ref(0);

  const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
  };

  const handleHeightChange = (height: number) => {
    sidebarContentHeight.value = height;
  };

  const buttonTopPosition = computed(() => {
    const margin = isSidebarCollapsed.value ? 50 : 100;
    return `${sidebarContentHeight.value + margin}px`;
  });

  const isLoadingSupplierData = computed(() => {
    if (!isEditMode.value) {
      return false;
    }

    return isLoadingCompleteData.value && isEditMode.value;
  });

  onMounted(() => {
    initSupplierClassificationId();
  });
</script>

<style scoped>
  .module-negotiations-supplier-form {
    min-height: 70vh;
    position: relative;
    margin: -24px -48px;
    padding: 24px 48px;

    .container-form {
      width: 100%;
      display: grid;
      grid-template-columns: 258px 1fr;
      gap: 0;
      position: relative;
      transition: grid-template-columns 0.3s ease;
    }

    .sidebar-container {
      border-right: 1px solid #e7e7e7;
      min-height: calc(100vh - 64px);
      position: relative;
      width: 100%;
      overflow: hidden;

      &.collapsed {
        width: 100%;
      }
    }

    .container-form:has(.sidebar-container.collapsed) {
      grid-template-columns: 60px 1fr;
    }

    .content-wrapper {
      position: relative;
      min-height: 500px;
      width: 100%;
      min-width: 0; /* Previene desbordamiento en grids con 1fr */
      overflow-x: clip; /* Oculta contenido desbordado pero permite elementos positioned */
    }

    .sidebar-toggle-btn {
      position: absolute;
      top: var(--button-top, 49px);
      left: 226px; /* 258px (sidebar width) - 32px (button width) */
      width: 32px;
      height: 32px;
      border-radius: 24px 0 0 24px;
      background: #ebeff2;
      border: 0;
      cursor: pointer;
      z-index: 10;
      transition:
        background 0.3s ease,
        top 0.3s ease,
        left 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;

      &:hover {
        background: #d0d0d0;
      }

      svg {
        color: #595959;
        font-size: 16px;
      }
    }

    .container-form:has(.sidebar-container.collapsed) .sidebar-toggle-btn {
      left: 28px; /* 60px (collapsed sidebar) - 32px (button width) */
    }

    .container-spin {
      position: absolute;
      top: 200px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1000;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
    }
  }

  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.3s ease;
  }

  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }

  .content-wrapper > .fade-enter-active,
  .content-wrapper > .fade-leave-active {
    position: absolute;
    width: 100%;
    top: 0;
    left: 0;
  }

  .content-wrapper > div {
    width: 100%;
  }

  .form-content {
    padding: 32px 24px;
  }
</style>
