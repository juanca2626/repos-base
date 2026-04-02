<template>
  <div>
    <div class="section-header">
      <span class="section-title">Staff</span>
      <span class="section-subtitle"> Seleccione el personal </span>
    </div>

    <hr class="custom-hr" />

    <a-select
      v-model:value="model.staff.selectedStaff"
      mode="multiple"
      :options="staffSelectOptions"
      @change="handleChange"
      placeholder="Selecciona"
    />
  </div>
</template>

<script setup lang="ts">
  import { useStaffStep } from '../composables/useStaffStep';
  import { useStaffTaxMatrix } from '@/modules/negotiations/products/configuration/content/pricingPlans/composables/useStaffTaxMatrix';
  import type { StaffOption } from '@/modules/negotiations/products/configuration/content/pricingPlans/types/staff.types';

  interface Props {
    model: any;
    staffStep: ReturnType<typeof useStaffStep>;
    staffTaxMatrix: ReturnType<typeof useStaffTaxMatrix>;
    staffSelectOptions: StaffOption[];
  }

  const props = defineProps<Props>();

  function handleChange() {
    props.staffTaxMatrix.syncMatrix();
  }
</script>

<style scoped lang="scss">
  @import '@/modules/negotiations/products/configuration/content/pricingPlans/ui/styles/stepSections.scss';

  :deep(.ant-select) {
    width: 420px;
  }

  :deep(.ant-select-selector) {
    border-radius: 8px !important;
    min-height: 42px !important;
  }

  :deep(.ant-select-selection-item) {
    background: #dcdcdc !important;
    border-radius: 6px !important;
    color: #2f353a !important;
  }
</style>
