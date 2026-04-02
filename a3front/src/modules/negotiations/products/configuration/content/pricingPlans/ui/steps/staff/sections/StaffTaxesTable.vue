<template>
  <div v-if="taxDefinitions.length" class="mt-5">
    <div class="section-header">
      <span class="section-title">Impuestos para Staff</span>
      <span class="section-subtitle"> Activa el personal del Staff afecto a impuestos </span>
    </div>

    <hr class="custom-hr" />

    <div class="tax-header">
      <div class="col-name">Tipo</div>

      <div v-for="definition in taxDefinitions" :key="definition.key" class="col-toggle">
        {{ definition.label }}
      </div>
    </div>

    <div v-for="staffId in model.staff.selectedStaff" :key="staffId" class="tax-row">
      <div
        class="col-name-staff"
        :class="{ 'col-name-selected': staffTaxMatrix.hasAnyTaxSelected(staffId) }"
      >
        {{ staffStep.getStaffLabel(staffId) }}
      </div>

      <div v-for="definition in taxDefinitions" :key="definition.key" class="col-toggle">
        <a-switch
          :checked="staffTaxMatrix.isTaxSelected(staffId, definition.key)"
          @update:checked="
            (checked: boolean) =>
              staffTaxMatrix.setTaxSelected(staffId, definition.key, Boolean(checked))
          "
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useStaffStep } from '../composables/useStaffStep';
  import { useStaffTaxMatrix } from '@/modules/negotiations/products/configuration/content/pricingPlans/composables/useStaffTaxMatrix';

  interface Props {
    model: any;
    staffStep: ReturnType<typeof useStaffStep>;
    staffTaxMatrix: ReturnType<typeof useStaffTaxMatrix>;
  }

  const props = defineProps<Props>();

  const { taxDefinitions } = props.staffTaxMatrix;
</script>

<style scoped lang="scss">
  @import '@/modules/negotiations/products/configuration/content/pricingPlans/ui/styles/stepSections.scss';

  .staff-tax-table {
    margin-top: 12px;
  }

  .tax-header {
    display: flex;
    padding: 12px 0;
    font-size: 13px;
    font-weight: 500;
    color: #7e8285;
    border-bottom: 1px solid #f0f0f0;
    gap: 12px;
  }

  .tax-row {
    display: flex;
    align-items: center;
    padding: 12px 0;
    gap: 12px;
  }

  .tax-row:last-child {
    border-bottom: none;
  }

  .col-name {
    flex: 2;
    font-size: 14px;
    color: #2f353a;
  }

  .col-name-staff {
    flex: 2;
    font-size: 14px;
    color: #e7e7e7;
  }

  .col-name-selected {
    color: #2f353a;
  }

  .col-toggle {
    flex: 1;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    min-width: 110px;
  }

  :deep(.ant-switch) {
    transform: scale(0.9);
  }
</style>
