import { storeToRefs } from 'pinia';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { usePricingPlanStore } from '../stores/usePricingPlanStore';
import { saveStaffPricingUseCase } from '@/modules/negotiations/products/configuration/application/pricingPlans/saveStaffPricing.useCase';

export async function saveStaffStep(state: any) {
  const configurationStore = useConfigurationStore();
  const pricingPlanStore = usePricingPlanStore();

  const { productSupplierType } = storeToRefs(configurationStore);

  const response = await saveStaffPricingUseCase(
    productSupplierType.value,
    state.entityId,
    state.sections.staff
  );

  console.log('response saveStaffStep', response);

  await pricingPlanStore.loadPricingPlan();

  await pricingPlanStore.loadRateVariations();

  return true;
}
