<template>
  <div class="container-form">
    <span class="title-form"> Creación de producto genérico </span>
    <a-spin :spinning="isLoading">
      <div class="main-form">
        <ProductFormHeaderComponent />

        <div v-show="showSummaryForm">
          <ProductFormSummaryComponent />
        </div>

        <template v-if="showEditableForm">
          <ProductFormProgressComponent />
        </template>

        <hr class="custom-separator" />

        <div class="mt-4" v-show="showSummaryForm">
          <SupplierAssignmentFormComponent v-show="isSupplierAssignmentForm" />
          <SupplierAssignmentListComponent v-show="!isSupplierAssignmentForm" />
        </div>

        <div v-show="showEditableForm">
          <ProductFormComponent />
        </div>
      </div>
    </a-spin>
  </div>
</template>

<script setup lang="ts">
  import ProductFormComponent from '@/modules/negotiations/products/general/components/form/ProductFormComponent.vue';
  import ProductFormHeaderComponent from '@/modules/negotiations/products/general/components/partials/ProductFormHeaderComponent.vue';
  import ProductFormProgressComponent from '@/modules/negotiations/products/general/components/partials/ProductFormProgressComponent.vue';
  import ProductFormSummaryComponent from '@/modules/negotiations/products/general/components/form/ProductFormSummaryComponent.vue';
  import SupplierAssignmentFormComponent from '@/modules/negotiations/products/general/components/form/SupplierAssignmentFormComponent.vue';
  import SupplierAssignmentListComponent from '@/modules/negotiations/products/general/components/form/SupplierAssignmentListComponent.vue';
  import { useProductFormManager } from '@/modules/negotiations/products/general/composables/useProductFormManager';
  import { useSupplierAssignmentStore } from '@/modules/negotiations/products/general/store/useSupplierAssignmentStore';
  import { useProductFormStoreFacade } from '@/modules/negotiations/products/general/composables/form/useProductFormStoreFacade';
  import { storeToRefs } from 'pinia';
  import { useRoute } from 'vue-router';
  import { onBeforeMount } from 'vue';

  const { showEditableForm, showSummaryForm } = useProductFormManager();
  const supplierAssignmentStore = useSupplierAssignmentStore();
  const { isSupplierAssignmentForm } = storeToRefs(supplierAssignmentStore);

  const { isLoading, setProductId } = useProductFormStoreFacade();

  const route = useRoute();
  const productId = route.params.id ? String(route.params.id) : null;

  onBeforeMount(() => {
    setProductId(productId);
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .custom-separator {
    border: none;
    border-bottom: 1px solid $color-black-4;
    height: 0;
    margin: 0;
  }

  .container-form {
    padding: 24px 32px;
  }

  .title-form {
    font-weight: 700;
    font-size: 24px;
    color: $color-black;
  }

  .main-form {
    margin-top: 24px;
    padding: 24px;
    border-radius: 8px;
    border: 1px solid $color-black-4;
  }
</style>
