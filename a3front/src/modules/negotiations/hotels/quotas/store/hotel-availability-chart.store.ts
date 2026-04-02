import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { HotelAvailabilityPeriodData } from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';

export const useHotelAvailabilityChartStore = defineStore('hotelAvailabilityChartStore', () => {
  const chartData = ref<(number | null)[][]>([]);
  const chartXLabels = ref<string[]>([]);
  const chartColors = ref<string[]>([]);
  const chartLabels = ref<string[]>([]); // Nombres de las categorías
  const rawChartData = ref<HotelAvailabilityPeriodData[]>([]);
  const chartType = ref<'month' | 'day'>('month');
  const visibleLines = ref<Record<number, boolean>>({}); // Control de visibilidad por índice de línea

  const setChartData = (data: (number | null)[][]) => {
    chartData.value = data;
    // Inicializar todas las líneas como visibles por defecto
    if (data.length > 0 && Object.keys(visibleLines.value).length === 0) {
      data.forEach((_, index) => {
        visibleLines.value[index] = true;
      });
    }
  };

  const setChartXLabels = (labels: string[]) => {
    chartXLabels.value = labels;
  };

  const setChartColors = (colors: string[]) => {
    chartColors.value = colors;
  };

  const setChartLabels = (labels: string[]) => {
    chartLabels.value = labels;
  };

  const setRawChartData = (data: HotelAvailabilityPeriodData[]) => {
    rawChartData.value = data;
  };

  const setChartType = (type: 'month' | 'day') => {
    chartType.value = type;
  };

  const toggleLineVisibility = (lineIndex: number) => {
    visibleLines.value[lineIndex] = !visibleLines.value[lineIndex];
  };

  const isLineVisible = (lineIndex: number): boolean => {
    return visibleLines.value[lineIndex] !== false; // Por defecto true si no está definido
  };

  const clearChartData = () => {
    chartData.value = [];
    chartXLabels.value = [];
    chartColors.value = [];
    chartLabels.value = [];
    rawChartData.value = [];
    visibleLines.value = {};
  };

  return {
    chartData,
    chartXLabels,
    chartColors,
    chartLabels,
    rawChartData,
    chartType,
    visibleLines,
    setChartData,
    setChartXLabels,
    setChartColors,
    setChartLabels,
    setRawChartData,
    setChartType,
    toggleLineVisibility,
    isLineVisible,
    clearChartData,
  };
});
