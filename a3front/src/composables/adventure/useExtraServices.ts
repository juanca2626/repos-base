import { useExtraServicesStore } from '@/stores/adventure';
import { storeToRefs } from 'pinia';

export function useExtraServices() {
  const extraServicesStore = useExtraServicesStore();

  const { isLoading, error, pagination, getExtraServices, extraService, filters, types } =
    storeToRefs(extraServicesStore);

  const {
    fetchAll,
    findService,
    save,
    update,
    changeStatus,
    saveTemplateService,
    remove,
    saveDepartureService,
  } = extraServicesStore;

  return {
    isLoading,
    error,
    extraService,
    pagination,
    filters,
    types,
    extraServices: getExtraServices,
    fetchExtraServices: fetchAll,
    fetchService: findService,
    saveExtraService: save,
    updateExtraService: update,
    updateStatus: changeStatus,
    saveTemplateExtraService: saveTemplateService,
    deleteExtraService: remove,
    saveDepartureExtraService: saveDepartureService,
  };
}
