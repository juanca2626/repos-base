import { fetchPricingService } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/fetchPricing.service';
import { mapRatePlanToState } from './mappers/mapRatePlanToState';

export async function fetchPricingUseCase(
  productSupplierId: string,
  serviceDetailId: string,
  ratePlanId: string | null,
  state: any
) {
  try {
    const response = await fetchPricingService(productSupplierId, serviceDetailId, ratePlanId);

    return mapRatePlanToState(response.data, state);
  } catch (error) {
    if ((error as any)?.response?.status === 404) {
      return {
        entityId: null,
        basic: state.basic,
        staff: state.staff,
        prices: state.prices,
        quota: state.quota,
      };
    }

    throw error;
  }
}
