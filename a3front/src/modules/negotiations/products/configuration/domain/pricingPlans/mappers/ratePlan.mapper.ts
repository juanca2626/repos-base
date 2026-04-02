import type { RatePlanResponseDto } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/dtos/ratePlanResponse.dto';
import type { RatePlan } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/ratePlan.types';

export function mapRatePlanDto(dto: RatePlanResponseDto): RatePlan {
  return {
    id: dto.id,
    productSupplierId: dto.productSupplierId,
    serviceDetailId: dto.serviceDetailId,
    name: dto.name,
    rateType: dto.rateType,
    status: dto.status,
    version: dto.version,

    bookingCode: dto.bookingCode,

    validity: dto.validity,

    bookingWindow: dto.bookingWindow,

    currencyConfig: dto.currencyConfig,

    rulesInput: dto.rulesInput ?? {
      seasons: [],
      specificDates: [],
    },

    segmentation: dto.segmentation,

    dayConfig: dto.dayConfig,

    holidayConfig: dto.holidayConfig,

    promotion: dto.promotion,

    rateVariations: dto.rateVariations ?? [],
  };
}
