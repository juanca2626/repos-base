import { computed } from 'vue';
import type { Rule } from 'ant-design-vue/es/form';
import { useLocationStore } from '@/modules/negotiations/supplier/register/store/locationStore';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';
import { filterOption } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';

export function useCommercialLocation() {
  const { formStateNegotiation } = useSupplierFormStoreFacade();

  const { getConditionalFormRules } = useSupplierFormView();

  const locationStore = useLocationStore();

  const locationCommercialOptions = computed(() => locationStore.locationOptions);
  const isLoadingLocations = computed(() => locationStore.isLoading);

  const baseRules: Record<string, Rule[]> = {
    commercialAddress: [
      { required: true, message: 'La dirección comercial es obligatoria', trigger: 'change' },
    ],
    comercialLocation: [
      { required: true, message: 'La ubicación es obligatoria', trigger: 'change' },
    ],
  };

  // Función para obtener las rules del form si estan en el tab negotiation (en otros tabs son de lectura)
  const formRules: Record<string, Rule[]> = getConditionalFormRules('negotiation', baseRules);

  return {
    locationCommercialOptions,
    isLoadingLocations,
    formStateNegotiation,
    filterOption,
    formRules,
  };
}
