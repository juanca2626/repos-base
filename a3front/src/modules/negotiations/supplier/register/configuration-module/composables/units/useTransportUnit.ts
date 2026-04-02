import { onMounted } from 'vue';
import { useTechnicalSheetLocations } from '@//modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/useTechnicalSheetLocations';

export function useTransportUnit() {
  const { isLoading, locationData, selectedLocation, handleTabClick, fetchLocationData } =
    useTechnicalSheetLocations();

  onMounted(async () => {
    await fetchLocationData();
  });

  return {
    isLoading,
    locationData,
    selectedLocation,
    handleTabClick,
  };
}
