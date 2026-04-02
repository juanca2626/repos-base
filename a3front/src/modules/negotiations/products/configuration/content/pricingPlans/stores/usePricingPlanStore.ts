import { reactive, watch } from 'vue';
import { storeToRefs } from 'pinia';
import {
  createInitialBasicState,
  type BasicSection,
} from '@/modules/negotiations/products/configuration/content/pricingPlans/state/createInitialBasicState';
import type { StaffState } from '@/modules/negotiations/products/configuration/content/pricingPlans/types/staff.types';
import { createInitialStaffState } from '@/modules/negotiations/products/configuration/content/pricingPlans/state/createInitialStaffState';
import { createInitialPriceState } from '@/modules/negotiations/products/configuration/content/pricingPlans/state/createInitialPriceState';
import type {
  StepKey,
  ErrorsState,
} from '@/modules/negotiations/products/configuration/content/pricingPlans/types/pricingPlan.types';
import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';
import type { PriceState } from '../types/price.types';
import type { EnginePeriod } from '../types/period.types';
import type { RateVariation } from '../domain/models/RateVariation';
import { fetchPricingUseCase } from '@/modules/negotiations/products/configuration/application/pricingPlans/fetchPricing.useCase';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
import { resolveServiceDetailId } from '../../../resolvers/serviceDetailId.resolver';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';
import { buildTariffName } from '../shared/buildTariffName';
import { resolveNames } from '../shared/resolveCatalogNames';
import { getRateVariationsUseCase } from '../application/getRateVariationsUseCase';
import { saveRateVariationsUseCase } from '../application/saveRateVariationsUseCase';
import { useFrequencyOptions } from '../composables/useFrequencyOptions';

type StepErrors = Record<string, string>;

type SectionsState = {
  basic: BasicSection;
  staff: StaffState;
  prices: PriceState;
  quota: Record<string, unknown>;
};

const supportResourcesStore = useSupportResourcesStore();

const initialSections = (serviceType: ServiceType | null = null): SectionsState => ({
  basic: createInitialBasicState(serviceType),
  staff: createInitialStaffState(serviceType),
  prices: createInitialPriceState(serviceType),
  quota: {},
});

const initialErrors = () => ({
  basic: {} as StepErrors,
  staff: {} as StepErrors,
  prices: {} as StepErrors,
  quota: {} as StepErrors,
});

const state = reactive<{
  entityId: string | null;
  serviceType: string | null;
  periods: EnginePeriod[];
  sections: SectionsState;
  errors: ErrorsState;
}>({
  entityId: null,
  serviceType: null,
  periods: [],
  sections: initialSections(),
  errors: initialErrors() as ErrorsState,
});

async function init(serviceType: ServiceType) {
  state.serviceType = serviceType;

  state.sections.basic = createInitialBasicState(serviceType);
  state.sections.staff = createInitialStaffState(serviceType);
  state.sections.prices = createInitialPriceState(serviceType);

  await loadPricingPlan();
}

function reset() {
  state.entityId = null;
  state.serviceType = null;

  state.sections = initialSections();
  state.errors = initialErrors();
}

function setStepErrors(step: StepKey, errors: StepErrors) {
  state.errors[step] = errors;
}

function clearStepErrors(step: StepKey) {
  state.errors[step] = {};
}

async function loadPricingPlan() {
  const configurationStore = useConfigurationStore();
  const navigationStore = useNavigationStore();

  const { productSupplierId, productSupplierType } = storeToRefs(configurationStore);
  const { currentKey, currentCode } = storeToRefs(navigationStore);

  const serviceDetailId = resolveServiceDetailId(
    productSupplierType.value,
    currentKey.value ?? '',
    currentCode.value ?? ''
  );

  const pricingPlan = await fetchPricingUseCase(
    productSupplierId.value ?? '',
    serviceDetailId ?? '',
    state.entityId ?? null,
    state.sections
  );

  const { entityId, basic, staff, prices, quota } = pricingPlan;

  state.entityId = entityId ?? null;
  state.sections.basic = basic as BasicSection;
  state.sections.staff = staff as StaffState;
  state.sections.prices = prices as PriceState;
  state.sections.quota = quota as Record<string, unknown>;

  console.log('state', state);
}

async function loadRateVariations() {
  state.sections.prices.isLoadingRateVariations = true;

  try {
    const data = await getRateVariationsUseCase(state.entityId ?? '');

    console.log('data loadRateVariations', data);

    state.sections.prices.rateVariations = data;

    if (!state.sections.prices.selectedRateVariationId && data.length) {
      const pendingVariation = data.find(
        (v) => v.status === 'NOT_STARTED' || v.status === 'IN_PROGRESS'
      );

      if (!pendingVariation) {
        state.sections.prices.selectedRateVariationId = data[0].id;
      } else {
        state.sections.prices.selectedRateVariationId = pendingVariation?.id ?? null;
      }
    }
  } finally {
    state.sections.prices.isLoadingRateVariations = false;
  }
}

function selectVariation(id: string) {
  state.sections.prices.selectedRateVariationId = id;
}

async function saveCurrentRateVariation(rateVariation: RateVariation) {
  const { frequencies } = useFrequencyOptions(state.sections.prices);

  const catalogs = {
    frequencies: frequencies.value,
  };

  await saveRateVariationsUseCase(rateVariation, catalogs);
}

function shouldLoadRateVariations() {
  return state.sections.prices.rateVariations.length === 0;
}

watch(
  () => ({
    tariffType: state.sections.basic.tariffType,
    promotionName: state.sections.basic.promotionName,
    markets: state.sections.basic.specificMarkets,
    clients: state.sections.basic.specificClients,
  }),
  (value) => {
    const marketNames = resolveNames(value.markets, supportResourcesStore.markets);
    const clientNames = resolveNames(value.clients, supportResourcesStore.clients);

    state.sections.basic.name = buildTariffName({
      tariffType: value.tariffType as TariffType,
      promotionName: value.promotionName,
      markets: marketNames,
      clients: clientNames,
    });
  },
  {
    deep: true,
    immediate: true,
  }
);

export function usePricingPlanStore() {
  return {
    state,
    init,
    reset,
    setStepErrors,
    clearStepErrors,
    loadPricingPlan,
    loadRateVariations,
    selectVariation,
    saveCurrentRateVariation,
    shouldLoadRateVariations,
  };
}
