import { defineStore } from 'pinia';
import { ref } from 'vue';
import hotelsApi from '@/modules/negotiations/hotels/api/hotelsApi';
import { useHotelAvailabilityLoadingStore } from './hotel-availability-loading.store';
import type {
  CityHotelData,
  ChartDataItem,
  FilterOption,
  TypeClassResponse,
} from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';

export const useQuotasStore = defineStore('quotasStore', () => {
  const isLoading = ref<boolean>(false);
  const dashboardData = ref<CityHotelData[]>([]);
  const hotelsChartData = ref<ChartDataItem[]>([
    { label: 'Hyperguest', value: 930, percentage: 63 },
    { label: 'Aurora', value: 310, percentage: 21 },
    { label: 'Ambos', value: 232, percentage: 16 },
  ]);
  const totalHotels = ref<number>(1550);
  const hotelsChartData2 = ref<ChartDataItem[]>([
    { label: 'Hyperguest', value: 35, percentage: 35 },
    { label: 'Aurora', value: 10, percentage: 20 },
    { label: 'Ambos', value: 5, percentage: 10 },
  ]);
  const totalHotels2 = ref<number>(50);

  // Variables para categorías internas
  let categoriesLoadingPromise: Promise<FilterOption[]> | null = null;
  let sharedCategories: FilterOption[] = [];
  let categoriesLoaded = false;
  const internalCategories = ref<FilterOption[]>([]);
  const isLoadingCategories = ref<boolean>(true);

  const setLoading = (loading: boolean) => {
    isLoading.value = loading;
  };

  const setDashboardData = (data: CityHotelData[]) => {
    dashboardData.value = data;
  };

  const setHotelsChartData = (data: ChartDataItem[]) => {
    hotelsChartData.value = data;
  };

  const setTotalHotels = (total: number) => {
    totalHotels.value = total;
  };

  const setHotelsChartData2 = (data: ChartDataItem[]) => {
    hotelsChartData2.value = data;
  };

  const setTotalHotels2 = (total: number) => {
    totalHotels2.value = total;
  };

  // Función para obtener las categorías internas desde el API
  const fetchInternalCategories = async (lang: string = 'es') => {
    const loadingStore = useHotelAvailabilityLoadingStore();

    // Si ya hay una petición en curso, esperar a que termine y sincronizar
    if (categoriesLoadingPromise) {
      const result = await categoriesLoadingPromise;
      internalCategories.value = result;
      isLoadingCategories.value = false;
      return;
    }

    // Si ya se cargaron las categorías, sincronizar y retornar
    if (categoriesLoaded && sharedCategories.length > 0) {
      internalCategories.value = sharedCategories;
      isLoadingCategories.value = false;
      return;
    }

    isLoadingCategories.value = true;
    loadingStore.startRequest('categories');

    // Crear la promesa compartida
    categoriesLoadingPromise = (async (): Promise<FilterOption[]> => {
      try {
        const response = await hotelsApi.get<{ success: boolean; data: TypeClassResponse[] }>(
          '/api/typeclass/allotment',
          {
            params: { lang },
          }
        );

        // Transformar la respuesta al formato FilterOption
        const categoriesData = response.data.data || response.data;
        let categoriesArray: TypeClassResponse[] = [];

        if (Array.isArray(categoriesData)) {
          categoriesArray = categoriesData;
        } else if (categoriesData && typeof categoriesData === 'object') {
          categoriesArray = Object.values(categoriesData);
        }

        const mappedCategories = categoriesArray.map((item: TypeClassResponse) => ({
          label:
            item.translations && item.translations.length > 0
              ? item.translations[0].value
              : item.code,
          code: item.id.toString(),
        }));

        // Almacenar en variable compartida
        sharedCategories = mappedCategories;
        categoriesLoaded = true;
        return mappedCategories;
      } catch (error) {
        console.error('Error fetching internal categories:', error);
        // Mantener valores por defecto en caso de error
        const defaultCategories: FilterOption[] = [];
        sharedCategories = defaultCategories;
        categoriesLoaded = false;
        return defaultCategories;
      } finally {
        isLoadingCategories.value = false;
        loadingStore.endRequest('categories');
        categoriesLoadingPromise = null;
      }
    })();

    const result = await categoriesLoadingPromise;
    internalCategories.value = result;
  };

  return {
    isLoading,
    dashboardData,
    hotelsChartData,
    totalHotels,
    hotelsChartData2,
    totalHotels2,
    setLoading,
    setDashboardData,
    setHotelsChartData,
    setTotalHotels,
    setHotelsChartData2,
    setTotalHotels2,
    // Categorías internas
    internalCategories,
    isLoadingCategories,
    fetchInternalCategories,
  };
});
