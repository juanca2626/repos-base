import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

import type { SupportResourceKey, ServiceType } from '../types/index';
import type {
  SupportResource,
  SupplierCategory,
  Profile,
  PointType,
  TrainType,
  ProgramDuration,
  OperationalSeason,
  Activity,
  Requirement,
  Inclusion,
  TextType,
} from '../infrastructure/supportResource/dtos/supportResource.interface';
import type { ServiceSubType } from '../infrastructure/supportResource/dtos/serviceSubType.interface';
import type { PickupPoint } from '../infrastructure/supportResource/dtos/pickupPoint.interface';

import { SUPPORT_RESOURCE_KEYS } from '../domain/supportResource/supportResourceKeys';
import { fetchSupportResourceUseCase } from '../application/supportResource/fetchSupportResource.useCase';
import { fetchSubTypeUseCase } from '../application/supportResource/fetchSubType.useCase';
import { fetchPickupPointUseCase } from '../application/supportResource/fetchPickupPoint.useCase';

export const useSupportResourcesStore = defineStore('supportResources', () => {
  const isLoading = ref(false);

  const subTypes = ref<ServiceSubType[]>([]);
  const supplierCategories = ref<SupplierCategory[]>([]);
  const profiles = ref<Profile[]>([]);
  const pointTypes = ref<PointType[]>([]);
  const trainTypes = ref<TrainType[]>([]);
  const programDurations = ref<ProgramDuration[]>([]);
  const operationalSeasons = ref<OperationalSeason[]>([]);
  const activities = ref<Activity[]>([]);
  const requirements = ref<Requirement[]>([]);
  const inclusions = ref<Inclusion[]>([]);
  const textTypes = ref<TextType[]>([]);
  const pickupPoints = ref<PickupPoint[]>([]);

  // segmentacion, mercados, clientes

  const segmentations = ref([
    { code: '1', name: 'Mercados' },
    { code: '2', name: 'Clientes' },
    { code: '3', name: 'Series' },
    { code: '4', name: 'Eventos' },
    { code: '5', name: 'Tipos de servicio' },
    { code: '6', name: 'Temporadas' },
  ]);

  const markets = ref([
    { code: '1', name: 'Canada' },
    { code: '2', name: 'USA' },
    { code: '3', name: 'México' },
    { code: '4', name: 'Europa' },
    { code: '5', name: 'Japón' },
    { code: '6', name: 'Corea' },
  ]);

  const clients = ref([
    { code: '1', name: '(2TJAVU) Travel Ja Vu' },
    { code: '2', name: 'Viajes Express' },
    { code: '3', name: 'Turismo Global' },
    { code: '4', name: 'Turismo Premium' },
  ]);

  const days = ref([
    { code: 'MON', name: 'Lunes', label: 'L' },
    { code: 'TUE', name: 'Martes', label: 'M' },
    { code: 'WED', name: 'Miércoles', label: 'X' },
    { code: 'THU', name: 'Jueves', label: 'J' },
    { code: 'FRI', name: 'Viernes', label: 'V' },
    { code: 'SAT', name: 'Sábado', label: 'S' },
    { code: 'SUN', name: 'Domingo', label: 'D' },
  ]);

  const countryTaxSettings = ref([
    {
      code: 'AR',
      isoCode: 'AR',
      money: 'Peso Argentino',
      isoMoney: 'ARS',
      taxCode: 'IVA',
      taxPercentage: 10,
    },
    {
      code: 'BO',
      isoCode: 'BO',
      money: 'Boliviano',
      isoMoney: 'BOB',
      taxCode: 'IVA',
      taxPercentage: 10,
    },
    {
      code: 'BR',
      isoCode: 'BR',
      money: 'Real',
      isoMoney: 'BRL',
      taxCode: 'IVA',
      taxPercentage: 10,
    },
    {
      code: 'CL',
      isoCode: 'CL',
      money: 'Peso Chileno',
      isoMoney: 'CLP',
      taxCode: 'IVA',
      taxPercentage: 10,
    },
    {
      code: 'CO',
      isoCode: 'CO',
      money: 'Peso Colombiano',
      isoMoney: 'COP',
      taxCode: 'IVA',
      taxPercentage: 10,
    },
    {
      code: 'EC',
      isoCode: 'EC',
      money: 'Dólar Estadounidense',
      isoMoney: 'USD',
      taxCode: 'IVA',
      taxPercentage: 10,
    },
    {
      code: 'PY',
      isoCode: 'PY',
      money: 'Guaraní',
      isoMoney: 'PYG',
      taxCode: 'IVA',
      taxPercentage: 10,
    },
    { code: 'PE', isoCode: 'PE', money: 'Sol', isoMoney: 'PEN', taxCode: 'IGV', taxPercentage: 18 },
    {
      code: 'UY',
      isoCode: 'UY',
      money: 'Peso Uruguayo',
      isoMoney: 'UYU',
      taxCode: 'IVA',
      taxPercentage: 10,
    },
    {
      code: 'VE',
      isoCode: 'VE',
      money: 'Bolívar',
      isoMoney: 'VEF',
      taxCode: 'IVA',
      taxPercentage: 10,
    },
  ]);

  const assignResources = (data: SupportResource) => {
    supplierCategories.value = data.supplierCategories ?? [];
    profiles.value = data.profiles ?? [];
    pointTypes.value = data.pointTypes ?? [];
    trainTypes.value = data.trainTypes ?? [];
    programDurations.value = data.programDurations ?? [];
    operationalSeasons.value = data.operationalSeasons ?? [];
    activities.value = data.activities ?? [];
    requirements.value = data.requirements ?? [];
    inclusions.value = data.inclusions ?? [];
    textTypes.value = data.textTypes ?? [];
  };

  const loadResources = async ({
    serviceType,
    keys,
  }: {
    serviceType: ServiceType;
    keys?: SupportResourceKey[];
  }) => {
    try {
      isLoading.value = true;

      const requestedKeys = keys ?? SUPPORT_RESOURCE_KEYS[serviceType];

      const data = await fetchSupportResourceUseCase({
        serviceType,
        keys: requestedKeys,
      });

      assignResources(data);
    } finally {
      isLoading.value = false;
    }
  };

  const loadSubTypes = async ({
    serviceTypeId,
    serviceType,
  }: {
    serviceTypeId: string;
    serviceType: ServiceType;
  }) => {
    try {
      isLoading.value = true;
      const data = await fetchSubTypeUseCase({ serviceTypeId, serviceType });
      subTypes.value = data;
    } finally {
      isLoading.value = false;
    }
  };

  const loadPickupPoints = async ({
    serviceType,
    types,
  }: {
    serviceType: ServiceType;
    types: string[];
  }) => {
    try {
      isLoading.value = true;

      const data = await fetchPickupPointUseCase(serviceType, types);

      pickupPoints.value = data;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,

    supplierCategories: computed(() => supplierCategories.value),
    profiles: computed(() => profiles.value),
    pointTypes: computed(() => pointTypes.value),
    trainTypes: computed(() => trainTypes.value),
    programDurations: computed(() => programDurations.value),
    operationalSeasons: computed(() => operationalSeasons.value),
    activities: computed(() => activities.value),
    requirements: computed(() => requirements.value),
    inclusions: computed(() => inclusions.value),
    textTypes: computed(() => textTypes.value),
    subTypes: computed(() => subTypes.value),
    pickupPoints: computed(() => pickupPoints.value),

    segmentations: computed(() => segmentations.value),
    markets: computed(() => markets.value),
    clients: computed(() => clients.value),

    days: computed(() => days.value),
    countryTaxSettings: computed(() => countryTaxSettings.value),

    loadResources,
    loadSubTypes,
    loadPickupPoints,
  };
});
