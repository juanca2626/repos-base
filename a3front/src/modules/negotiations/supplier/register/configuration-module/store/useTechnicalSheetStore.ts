import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';

export const useTechnicalSheetStore = defineStore('technicalSheetStore', () => {
  const isLoading = ref<boolean>(false);

  const typeTechnicalSheet = ref<TypeTechnicalSheetEnum>(TypeTechnicalSheetEnum.TRANSPORT_VEHICLE);

  const registeredVehiclesCount = ref<number>(0);
  const registeredDriversCount = ref<number>(0);

  const setIsLoading = (value: boolean) => {
    isLoading.value = value;
  };

  const setRegisteredVehiclesCount = (value: number) => {
    registeredVehiclesCount.value = value;
  };

  const setRegisteredDriversCount = (value: number) => {
    registeredDriversCount.value = value;
  };

  const setTypeTechnicalSheet = (value: TypeTechnicalSheetEnum) => {
    typeTechnicalSheet.value = value;
  };

  const isTransportVehicleActive = computed(
    () => typeTechnicalSheet.value === TypeTechnicalSheetEnum.TRANSPORT_VEHICLE
  );

  const toggleTypeTechnicalSheet = () => {
    const { VEHICLE_DRIVER, TRANSPORT_VEHICLE } = TypeTechnicalSheetEnum;

    setTypeTechnicalSheet(
      typeTechnicalSheet.value === VEHICLE_DRIVER ? TRANSPORT_VEHICLE : VEHICLE_DRIVER
    );
  };

  return {
    isLoading,
    typeTechnicalSheet,
    isTransportVehicleActive,
    registeredVehiclesCount,
    registeredDriversCount,
    setIsLoading,
    setTypeTechnicalSheet,
    toggleTypeTechnicalSheet,
    setRegisteredVehiclesCount,
    setRegisteredDriversCount,
  };
});
