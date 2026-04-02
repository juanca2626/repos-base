import { defineStore } from 'pinia';
import { ref, reactive, computed } from 'vue';
import dayjs, { Dayjs } from 'dayjs';
import { notification } from 'ant-design-vue';
import { mapToSelectOptions } from '@operations/shared/utils/formUtils';
import {
  type Headquarter,
  type ProviderContractType,
  type ProviderProfileType,
  type HeadquartersResponse,
  type ProviderContractTypesResponse,
  type ProviderProfileTypesResponse,
  // type BlockingReasonsResponse,
} from '../interfaces';
import { useBlockCalendarStore } from './blockCalendar.store';
import {
  fetchBlockingReasons,
  fetchHeadquarters,
  fetchProviderContractTypes,
  fetchProviderProfileTypes,
} from '../api/blackoutCalendarApi';

interface FormState {
  contractProvider: ProviderContractType;
  profileProvider: ProviderProfileType;
  headquarter: Headquarter;
  searchTerm: string;
  monthYear: Dayjs;
}

export const useFiltersFormStore = defineStore('filtersForm', () => {
  const blockCalendarStore = useBlockCalendarStore();

  // 🔹 Estado principal
  const headquarters = ref<HeadquartersResponse['data']>([]);
  const providerContractTypes = ref<ProviderContractTypesResponse['data']>([]);
  const providerProfileTypes = ref<ProviderProfileTypesResponse['data']>([]);
  // const blockingReasons = ref<BlockingReasonsResponse['data']>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  // 🔹 Estado inicial del formulario
  const formState: FormState = reactive({
    contractProvider: {} as ProviderContractType,
    profileProvider: {} as ProviderProfileType,
    headquarter: {} as Headquarter,
    searchTerm: '',
    monthYear: dayjs().startOf('month'),
  });

  // 🔹 Generar opciones de select genéricamente
  // const mapToSelectOptions = <T extends { iso: string; description: string }>(
  //   items: T[],
  //   allLabel: string
  // ): SelectProps['options'] => [
  //   { value: 'ALL', label: allLabel },
  //   ...items.map(({ iso, description }) => ({ value: iso, label: description })),
  // ];

  const providerContractTypesOptions = computed(() =>
    mapToSelectOptions(providerContractTypes.value, 'Todos (Planta & Freelance)')
  );
  const providerProfileTypesOptions = computed(() =>
    mapToSelectOptions(providerProfileTypes.value, 'Todos los perfiles')
  );
  const headquartersOptions = computed(() =>
    headquarters.value.map(({ code, description }) => ({ value: code, label: description }))
  );

  // 🔹 Limpiar filtros y resetear valores
  const cleanFilters = () => {
    Object.assign(formState, {
      contractProvider: {} as ProviderContractType,
      profileProvider: {} as ProviderProfileType,
      headquarter: {} as Headquarter,
      searchTerm: '',
    });
    onSubmit();
  };

  // 🔹 Enviar datos del formulario
  const onSubmit = async () => {
    const { monthYear } = formState;
    const month = monthYear.format('MM');
    const year = monthYear.format('YYYY');

    await Promise.all([
      blockCalendarStore.getMonthInfo(+month, +year),
      blockCalendarStore.getLocksByMonth(formState),
    ]);
  };

  // 🔹 Cargar datos de filtros
  const loadFiltersData = async () => {
    isLoading.value = true;
    error.value = null;

    try {
      const [
        contractTypesResponse,
        profileTypesResponse,
        headquartersResponse,
        blockingReasonsResponse,
      ] = await Promise.all([
        fetchProviderContractTypes(),
        fetchProviderProfileTypes(),
        fetchHeadquarters(),
        fetchBlockingReasons(),
      ]);

      console.log('🚀 ~ loadFiltersData ~ contractTypesResponse:', contractTypesResponse);
      console.log('🚀 ~ loadFiltersData ~ profileTypesResponse:', profileTypesResponse);
      console.log('🚀 ~ loadFiltersData ~ headquartersResponse:', headquartersResponse);

      providerContractTypes.value = contractTypesResponse.data;
      providerProfileTypes.value = profileTypesResponse.data;
      headquarters.value = headquartersResponse.data;
      blockCalendarStore.blockingReasons.value = blockingReasonsResponse.data;
    } catch (err) {
      console.error(err);
      error.value = 'Error al cargar los datos de los filtros.';
      notification.error({
        message: 'Error',
        description: 'Ocurrió un error al cargar los datos iniciales.',
      });
    } finally {
      isLoading.value = false;
    }
  };

  return {
    // State
    formState,
    isLoading,
    error,
    // Getters
    headquartersOptions,
    providerContractTypesOptions,
    providerProfileTypesOptions,
    // Actions
    loadFiltersData,
    cleanFilters,
    onSubmit,
  };
});
