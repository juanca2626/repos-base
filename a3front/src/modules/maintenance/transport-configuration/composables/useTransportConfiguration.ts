import { ref } from 'vue';
import type { TransportUnitInterface } from '../interfaces/transport-unit.interface';

const units = ref<TransportUnitInterface[]>([]);
const loading = ref(false);

const filterOptions = ref([
  { value: 'a', label: 'filter a' },
  { value: 'b', label: 'filter b' },
  { value: 'c', label: 'filter c' },
]);

const cityOptions = ref([
  { value: 'lima', label: 'Lima' },
  { value: 'cajamarca', label: 'Cajamarca' },
  { value: 'huanuco', label: 'Huánuco' },
  { value: 'trujillo', label: 'Trujillo' },
  { value: 'piura', label: 'Piura' },
]);

const segmentationOptions = ref([
  { value: 'general', label: 'General' },
  { value: 'cliente', label: 'Cliente' },
  { value: 'mercado', label: 'Mercado' },
  { value: 'series', label: 'Series' },
]);

const activityOptions = ref([
  { value: 'alimentacion', label: 'Alimentacion' },
  { value: 'actividades', label: 'Actividades' },
  { value: 'excursiones', label: 'Excursiones' },
  { value: 'traslados', label: 'Traslados' },
]);

const clientOptions = ref([
  { value: 'cliente_a', label: 'Cliente A' },
  { value: 'cliente_b', label: 'Cliente B' },
  { value: 'cliente_c', label: 'Cliente C' },
  { value: 'travel_ja_vu', label: 'Travel Ja Vu' },
]);

const transportCodeOptions = ref([
  { value: 1, label: 'AUT - Auto' },
  { value: 2, label: 'VAN - Van' },
  { value: 3, label: 'SPC - Sprinter Corta' },
  { value: 4, label: 'SPL - Sprinter Larga' },
]);

const transportUsageOptions = ref([
  { value: 1, label: 'Pasajero' },
  { value: 2, label: 'Maletero' },
]);

const configuredCities = ref([
  { id: 1, name: 'Lima' },
  { id: 2, name: 'Cusco' },
  { id: 3, name: 'Trujillo' },
  { id: 4, name: 'Puno' },
  { id: 5, name: 'Moquegua' },
  { id: 6, name: 'Arequipa' },
  { id: 7, name: 'Tacna' },
  { id: 8, name: 'Ica' },
]);

const selectedCityId = ref(1);

const rawGroups = ref<any[]>([
  {
    groupId: 'g1',
    segmentation: 'general',
    activity: 'traslados',
    since: '01/03/2026',
    until: '30/03/2026',
    product: 2,
    productsItem: ['alimentación', 'menú turístico', 'menú a la carta'],
    items: [
      {
        id: 1,
        code: 1,
        name: 'Auto',
        units: 2,
        usage: 1,
        minCapacity: '1',
        maxCapacity: '4',
        includeRepresentative: true,
        representativeQty: '1',
        productsItem: ['traslado', 'tour', 'actividades', 'alimentación'],
      },
      {
        id: 2,
        code: 2,
        name: 'Van',
        units: 1,
        usage: 1,
        minCapacity: '5',
        maxCapacity: '8',
        includeRepresentative: false,
        representativeQty: '0',
        productsItem: ['traslado', 'tour'],
      },
    ],
  },
]);

export const useTransportConfiguration = () => {
  const fetchUnits = async () => {
    loading.value = true;
    try {
    } finally {
      loading.value = false;
    }
  };

  const addGroup = (newGroup: any) => {
    rawGroups.value.push(newGroup);
  };

  const updateGroup = (groupId: string, updatedData: any) => {
    const index = rawGroups.value.findIndex((g) => g.groupId === groupId);
    if (index !== -1) {
      rawGroups.value[index] = { ...rawGroups.value[index], ...updatedData };
    }
  };

  return {
    units,
    loading,
    filterOptions,
    cityOptions,
    segmentationOptions,
    activityOptions,
    clientOptions,
    transportCodeOptions,
    transportUsageOptions,
    configuredCities,
    selectedCityId,
    rawGroups,
    fetchUnits,
    addGroup,
    updateGroup,
  };
};
