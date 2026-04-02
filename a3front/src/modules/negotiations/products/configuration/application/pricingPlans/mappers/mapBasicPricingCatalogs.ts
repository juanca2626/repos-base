import type {
  SaveBasicPricingRequest,
  SaveBasicPricingRequestBeforeMapping,
} from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveBasicPricingRequest.types';
import type { BasicPricingCatalogs } from './mapBasePricingCatalogs.mapper.interface';

export function mapBasicPricingCatalogs(
  payload: SaveBasicPricingRequestBeforeMapping,
  catalogs: BasicPricingCatalogs
): SaveBasicPricingRequest {
  const seasonsMap = Object.fromEntries(catalogs.operationalSeasons.map((s) => [s.id, s]));

  const marketsMap = Object.fromEntries(catalogs.markets.map((m) => [m.code, m]));

  const clientsMap = Object.fromEntries(catalogs.clients.map((c) => [c.code, c]));

  const segmentationsMap = Object.fromEntries(catalogs.segmentations.map((s) => [s.code, s]));

  return {
    ...payload,

    periods: payload.periods.map((period) => {
      const season = seasonsMap[period.periodId];

      return {
        ...period,
        periodId: season?.id ?? '',
        periodType: season?.code ?? '',
        periodName: season?.name ?? '',
      };
    }),

    tariffSegmentation: payload.tariffSegmentation.map((id: any) => {
      const seg = segmentationsMap[id];

      return {
        id: seg?.id ?? '',
        code: seg?.code ?? '',
        name: seg?.name ?? '',
      };
    }),

    specificMarkets: payload.specificMarkets.map((id: any) => {
      const market = marketsMap[id];

      return {
        id: market?.id ?? '',
        code: market?.code ?? '',
        name: market?.name ?? '',
      };
    }),

    specificClients: payload.specificClients.map((id: any) => {
      const client = clientsMap[id];

      return {
        id: client?.id ?? '',
        code: client?.code ?? '',
        name: client?.name ?? '',
      };
    }),
  };
}
