<template>
  <div class="staff-step">
    <ServiceTaxesSection :model="model" :countryTaxSetting="countryTaxSetting" />

    <CommissionSection v-if="isPackageService" :model="model" />

    <div class="content-card">
      <StaffSelectorSection
        :model="model"
        :staff-step="staffStep"
        :staff-select-options="staffSelectOptions"
        :staff-tax-matrix="staffTaxMatrix"
      />

      <StaffTaxesTable
        v-if="model.staff.selectedStaff.length"
        :model="model"
        :staffStep="staffStep"
        :staffTaxMatrix="staffTaxMatrix"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import ServiceTaxesSection from './sections/ServiceTaxesSection.vue';
  import StaffSelectorSection from './sections/StaffSelectorSection.vue';
  import StaffTaxesTable from './sections/StaffTaxesTable.vue';
  import CommissionSection from './sections/CommissionSection.vue';
  import { ServiceTypeEnum } from '@/modules/negotiations/products/configuration/enums/ServiceType.enum';
  import { useStaffStep } from './composables/useStaffStep';
  import { useStaffTaxMatrix } from '@/modules/negotiations/products/configuration/content/pricingPlans/composables/useStaffTaxMatrix';
  import { useCountryTaxSetting } from '@/modules/negotiations/products/configuration/content/pricingPlans/composables/useCountryTaxSetting';

  interface Props {
    model: any;
    serviceType: ServiceTypeEnum;
  }

  const props = defineProps<Props>();

  const staffStep = useStaffStep(props);

  const { isPackageService } = staffStep;

  const staffSelectOptions = computed(() => staffStep.staffOptions.value);

  const { countryTaxSetting } = useCountryTaxSetting({ model: props.model });

  const staffTaxMatrix = useStaffTaxMatrix({ model: props.model });
</script>

<style scoped>
  @import '@/modules/negotiations/products/configuration/content/pricingPlans/ui/styles/stepSections.scss';
  .staff-step {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }
</style>
