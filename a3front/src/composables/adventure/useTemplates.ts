import { useTemplatesStore } from '@/stores/adventure';
import { storeToRefs } from 'pinia';

export function useTemplates() {
  const templatesStore = useTemplatesStore();

  const {
    isLoading,
    error,
    types,
    durations,
    filters,
    filtersCash,
    pagination,
    getTemplates,
    getServices,
    getCash,
    getExtraCash,
    getBreakpoints,
    template,
    templateClone,
    newService,
    serviceTypes,
  } = storeToRefs(templatesStore);

  const {
    fetchAll,
    fetchOne,
    fetchServices,
    fetchServicesGrouped,
    fetchCashService,
    fetchBreakpoints,
    save,
    update,
    remove,
    clone,
    saveService,
    updateService,
    deleteService,
    updateProviders,
    saveBreakpoint,
  } = templatesStore;

  return {
    isLoading,
    error,
    template,
    templateClone,
    filters,
    filtersCash,
    pagination,
    types,
    breakpoints: getBreakpoints,
    serviceTypes,
    durations,
    service: newService,
    templates: getTemplates,
    services: getServices,
    cash: getCash,
    extraCash: getExtraCash,
    fetchTemplates: fetchAll,
    fetchTemplate: fetchOne,
    fetchTemplateServicesGrouped: fetchServicesGrouped,
    fetchTemplateServices: fetchServices,
    fetchTemplateCashService: fetchCashService,
    fetchTemplateBreakpoints: fetchBreakpoints,
    saveTemplate: save,
    updateTemplate: update,
    deleteTemplate: remove,
    saveTemplateClone: clone,
    saveTemplateBreakpoint: saveBreakpoint,
    saveService,
    updateService,
    deleteService,
    updateProviders,
  };
}
