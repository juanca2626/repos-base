import { ref } from 'vue';
import hotelsApi from '@/modules/negotiations/hotels/api/hotelsApi';

export interface HotelHyperguestData {
  hotel_id: number;
  hotel_name: string;
}

export interface HotelAuroraData {
  hotel_id: number;
  hotel_name: string;
}

export interface HotelAmbosData {
  hotel_id: number;
  hotel_name: string;
}
export interface CityHotelData {
  id: number;
  city: string;
  total_hotels: number;
  pct_hyperguest: string;
  hyperguest: string;
  pct_aurora: string;
  aurora: string;
  pct_ambos: string;
  ambos: string;
  state_id: number;
  total_hoteles: number;
  hotels_hyperguest: HotelHyperguestData[];
  hotels_aurora: HotelAuroraData[];
  hotels_ambos: HotelAmbosData[];
}

export interface ChartDataItem {
  label: string;
  value: number;
  percentage?: number;
  details: HotelDetail[];
}

interface DashboardDetailsResponse {
  current_page: number;
  data: CityHotelData[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}

// interface DashboardDetailsHotelsResponse {
//   state_id: number;
//   hotels_hyperguest: [];
//   hotels_aurora: [];
//   hotels_ambos: [];
// }

export interface HotelDetail {
  hotel_id: number;
  hotel_name: string;
  channel_type: 'aurora' | 'hyperguest' | 'ambos';
  typeclass?: string;
}

export interface DashboardDetails {
  aurora: HotelDetail[];
  hyperguest: HotelDetail[];
  ambos: HotelDetail[];
}

interface DashboardChartResponse {
  success: string;
  data: Array<{
    aurora: string;
    hyperguest: string;
    ambos: string;
    total_hoteles: number;
    pct_aurora: string;
    pct_hyperguest: string;
    pct_ambos: string;
    details: DashboardDetails;
  }>;
}

export const useQuotasDashboard = () => {
  const tableData = ref<CityHotelData[]>([]);
  const showDrawerForm = ref<boolean>(false);
  const currentPage = ref<number>(1);
  const itemsPerPage = ref<number>(10);
  const totalItems = ref<number>(0);
  const loading = ref<boolean>(false);

  const hotelsChartData = ref<ChartDataItem[]>([]);
  const totalHotels = ref<number>(0);
  const loadingChart1 = ref<boolean>(true);

  const hotelsChartData2 = ref<ChartDataItem[]>([]);
  const totalHotels2 = ref<number>(0);
  const loadingChart2 = ref<boolean>(true);

  const totalData = ref<HotelHyperguestData[]>([]);
  const hyperguestData = ref<HotelHyperguestData[]>([]);
  const auroraData = ref<HotelAuroraData[]>([]);
  const ambosData = ref<HotelAmbosData[]>([]);

  const chartDetails = ref<HotelHyperguestData[]>([]);
  const chartPercentage = ref<string>('');

  const percentageTotal = ref<number>(0);
  const percentageHyperguest = ref<string>('');
  const percentageAurora = ref<string>('');
  const percentageAmbos = ref<string>('');

  const drawerIcon = ref<string>('');
  const drawerTitle = ref<string>('');
  const drawerSubTitle = ref<string>('');

  // Función para cargar datos del gráfico 1 (preferente: 0)
  const loadChart1Data = async () => {
    try {
      loadingChart1.value = true;
      const response1 = await hotelsApi.get<DashboardChartResponse>(
        '/api/allotment/hotels/dashboard',
        {
          params: {
            preferente: 0,
          },
        }
      );

      if (response1.data && response1.data.data && response1.data.data.length > 0) {
        const chartData1 = response1.data.data[0];
        hotelsChartData.value = [
          {
            label: 'Hyperguest',
            value: parseInt(chartData1.hyperguest) || 0,
            percentage: Math.round(parseFloat(chartData1.pct_hyperguest) || 0),
            details: chartData1.details.hyperguest,
          },
          {
            label: 'Aurora',
            value: parseInt(chartData1.aurora) || 0,
            percentage: Math.round(parseFloat(chartData1.pct_aurora) || 0),
            details: chartData1.details.aurora,
          },
          {
            label: 'Ambos',
            value: parseInt(chartData1.ambos) || 0,
            percentage: Math.round(parseFloat(chartData1.pct_ambos) || 0),
            details: chartData1.details.ambos,
          },
        ];
        totalHotels.value = chartData1.total_hoteles || 0;
      }
    } catch (error) {
      console.error('Error loading chart 1 data:', error);
    } finally {
      loadingChart1.value = false;
    }
  };

  // Función para cargar datos del gráfico 2 (preferente: 1)
  const loadChart2Data = async () => {
    try {
      loadingChart2.value = true;
      const response2 = await hotelsApi.get<DashboardChartResponse>(
        '/api/allotment/hotels/dashboard',
        {
          params: {
            preferente: 1,
          },
        }
      );

      if (response2.data && response2.data.data && response2.data.data.length > 0) {
        const chartData2 = response2.data.data[0];
        hotelsChartData2.value = [
          {
            label: 'Hyperguest',
            value: parseInt(chartData2.hyperguest) || 0,
            percentage: Math.round(parseFloat(chartData2.pct_hyperguest) || 0),
            details: chartData2.details.hyperguest,
          },
          {
            label: 'Aurora',
            value: parseInt(chartData2.aurora) || 0,
            percentage: Math.round(parseFloat(chartData2.pct_aurora) || 0),
            details: chartData2.details.aurora,
          },
          {
            label: 'Ambos',
            value: parseInt(chartData2.ambos) || 0,
            percentage: Math.round(parseFloat(chartData2.pct_ambos) || 0),
            details: chartData2.details.ambos,
          },
        ];
        totalHotels2.value = chartData2.total_hoteles || 0;
      }
    } catch (error) {
      console.error('Error loading chart 2 data:', error);
    } finally {
      loadingChart2.value = false;
    }
  };

  // Función para cargar datos de la tabla
  const loadTableData = async () => {
    try {
      loading.value = true;
      const response = await hotelsApi.get<DashboardDetailsResponse>(
        '/api/allotment/hotels/dashboard-details',
        {
          params: {
            page: currentPage.value,
          },
        }
      );

      if (response.data && response.data.data) {
        // Mapear los datos del response a la estructura esperada
        tableData.value = response.data.data.map((item) => ({
          id: item.id,
          state_id: item.state_id,
          total_hoteles: item.total_hoteles,
          city: item.city,
          total_hotels: item.total_hoteles,
          aurora: item.aurora,
          pct_aurora: item.pct_aurora,
          hyperguest: item.hyperguest,
          pct_hyperguest: item.pct_hyperguest,
          ambos: item.ambos,
          pct_ambos: item.pct_ambos,
          hotels_hyperguest: item.hotels_hyperguest,
          hotels_aurora: item.hotels_aurora,
          hotels_ambos: item.hotels_ambos,
        }));

        // Actualizar la paginación
        currentPage.value = response.data.current_page;
        itemsPerPage.value = response.data.per_page;
        totalItems.value = response.data.total;
      }
    } catch (error) {
      console.error('Error loading table data:', error);
      tableData.value = [];
    } finally {
      loading.value = false;
    }
  };

  // Función principal que ejecuta todos los datos en paralelo
  const loadData = async () => {
    try {
      // Ejecutar todas las peticiones en paralelo: gráficos y tabla
      await Promise.all([loadChart1Data(), loadChart2Data(), loadTableData()]);
    } catch (error) {
      console.error('Error loading dashboard data:', error);
    }
  };

  const goToPage = (page: number) => {
    if (page >= 1 && page <= Math.ceil(totalItems.value / itemsPerPage.value)) {
      currentPage.value = page;
      loadTableData();
    }
  };

  const handleShowDrawerForm = async (record: CityHotelData) => {
    showDrawerForm.value = true;
    drawerIcon.value = 'hotel';
    drawerTitle.value = `Hoteles en ${record.city}`;
    drawerSubTitle.value = 'Cantidad total por conexión';
    hyperguestData.value = record.hotels_hyperguest;
    auroraData.value = record.hotels_aurora;
    ambosData.value = record.hotels_ambos;
    percentageHyperguest.value = record.pct_hyperguest;
    percentageAurora.value = record.pct_aurora;
    percentageAmbos.value = record.pct_ambos;

    // try {
    //   loading.value = true;
    //   const response = await hotelsApi.get<DashboardDetailsHotelsResponse>(
    //     `/api/allotment/hotels/dashboard/${record.state_id}/details`,
    //     {
    //       params: {},
    //     }
    //   );

    //   if (response.data) {
    //     showDrawerForm.value = true;
    //     drawerIcon.value = 'hotel';
    //     drawerTitle.value = `Hoteles en ${record.city}`;
    //     drawerSubTitle.value = 'Cantidad total por conexión';
    //     hyperguestData.value = response.data.hotels_hyperguest;
    //     auroraData.value = response.data.hotels_aurora;
    //     ambosData.value = response.data.hotels_ambos;
    //     percentageHyperguest.value = record.pct_hyperguest;
    //     percentageAurora.value = record.pct_aurora;
    //     percentageAmbos.value = record.pct_ambos;
    //   }
    // } catch (error) {
    //   console.error('Error loading table data:', error);
    //   tableData.value = [];
    // } finally {
    //   loading.value = false;
    // }
  };

  const handleChartShowDrawerForm = async (
    title: string,
    subTtitle: string,
    percentage: string,
    details: any[]
  ) => {
    showDrawerForm.value = true;
    drawerIcon.value = `${subTtitle.toLowerCase()}`;
    drawerTitle.value = `${title}`;
    drawerSubTitle.value = `Conexión ${subTtitle}`;
    chartDetails.value = details;
    chartPercentage.value = `${percentage}`;
  };

  return {
    tableData,
    currentPage,
    itemsPerPage,
    totalItems,
    loading,
    hotelsChartData,
    totalHotels,
    loadingChart1,
    hotelsChartData2,
    totalHotels2,
    loadingChart2,
    showDrawerForm,
    totalData,
    hyperguestData,
    auroraData,
    ambosData,
    chartDetails,
    chartPercentage,
    percentageTotal,
    percentageHyperguest,
    percentageAurora,
    percentageAmbos,
    drawerIcon,
    drawerTitle,
    drawerSubTitle,
    loadData,
    goToPage,
    handleShowDrawerForm,
    handleChartShowDrawerForm,
  };
};
