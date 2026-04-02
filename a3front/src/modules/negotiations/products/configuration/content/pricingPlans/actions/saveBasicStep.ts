import { storeToRefs } from 'pinia';
import { usePricingPlanStore } from '../stores/usePricingPlanStore';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { saveBasicPricingUseCase } from '@/modules/negotiations/products/configuration/application/pricingPlans/saveBasicPricing.useCase';
import { resolveServiceDetailId } from '@/modules/negotiations/products/configuration/resolvers/serviceDetailId.resolver';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';

export async function saveBasicStep(state: any) {
  const configurationStore = useConfigurationStore();
  const supportResourcesStore = useSupportResourcesStore();
  const navigationStore = useNavigationStore();
  const pricingPlanStore = usePricingPlanStore();

  const { productSupplierType, productSupplierId } = storeToRefs(configurationStore);

  const { currentKey, currentCode } = storeToRefs(navigationStore);

  const { operationalSeasons, segmentations, markets, clients } =
    storeToRefs(supportResourcesStore);

  const serviceDetailId = resolveServiceDetailId(
    productSupplierType.value,
    currentKey.value ?? '',
    currentCode.value ?? ''
  );

  console.log('state basic step', state.sections.basic);

  const response = await saveBasicPricingUseCase(
    productSupplierType.value,
    productSupplierId.value ?? '',
    serviceDetailId ?? '',
    state.entityId ?? null,
    state.sections.basic,
    {
      operationalSeasons: operationalSeasons.value,
      segmentations: segmentations.value,
      markets: markets.value,
      clients: clients.value,
    }
  );

  console.log('response Basic Step', response);

  await pricingPlanStore.loadPricingPlan();

  return true;
}
