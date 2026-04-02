import { computed } from 'vue';
import type { Rule } from 'ant-design-vue/es/form';
import { useLocationStore } from '@/modules/negotiations/supplier/register/store/locationStore';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';
import { parseKeyOperationLocation } from '@/modules/negotiations/supplier/register/helpers/operationLocationHelper';
import { filterOption } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';

export function useOperationLocation() {
  const { getConditionalFormRules } = useSupplierFormView();

  const {
    formStateNegotiation,
    extraValidations,
    resetExtraValidationByKey,
    applySupplierLocationsUpdate,
  } = useSupplierFormStoreFacade();

  const locationStore = useLocationStore();
  const defaultCountryID = 89;

  const locationsByStateCity = computed(() => locationStore.locationsByStateCity);

  const isLoadingLocations = computed(() => locationStore.isLoading);

  // Función para cargar ubicaciones desde la API
  const loadLocationOptions = async () => {
    await locationStore.fetchLocations(defaultCountryID, false);
  };

  const handleChangeLocationStateCity = (index: number) => {
    formStateNegotiation.operationLocations[index].zoneKey = null;
    formStateNegotiation.operationLocations[index].locationOptionsByZone = [];

    const { countryId, stateId, cityId } = parseKeyOperationLocation(
      formStateNegotiation.operationLocations[index].primaryLocationKey ?? '',
      '-'
    );

    if (countryId && stateId && cityId) {
      formStateNegotiation.operationLocations[index].locationOptionsByZone =
        locationStore.getLocationsByZone(countryId, stateId, cityId);
    }

    resetExtraValidationByKey('errorUniqueLocationKey');

    // actualizar ubicacion - actualizar proveedor
    applySupplierLocationsUpdate(index, (location) => {
      location.country_id = countryId;
      location.state_id = stateId;
      location.city_id = cityId;
      location.zone_id = null;
    });
  };

  const handleChangeLocationOptionZone = (index: number) => {
    const zoneKey = Number(formStateNegotiation.operationLocations[index].zoneKey) || null;

    // actualizar zona - actualizar proveedor
    applySupplierLocationsUpdate(index, (location) => {
      location.zone_id = zoneKey;
    });
  };

  // Función para agregar una nueva ubicación
  const addOperationLocation = () => {
    formStateNegotiation.operationLocations.push({
      primaryLocationKey: null,
      zoneKey: null,
      locationOptionsByZone: [],
      supplierBranchOfficeIds: [],
    });
  };

  // Función para eliminar una ubicación
  const removeOperationLocation = (index: number) => {
    // agregar flag para eliminar ubicacion - actualizar proveedor
    applySupplierLocationsUpdate(index, (location) => {
      location.delete = true;
    });

    formStateNegotiation.operationLocations.splice(index, 1);
    resetExtraValidationByKey('errorUniqueLocationKey');
  };

  const baseRules: Record<string, Rule[]> = {
    primaryLocationKey: [
      { required: true, message: 'El campo departamento/ciudad es obligatorio', trigger: 'change' },
    ],
  };

  const validateUniqueOperationLocation = () => {
    const seen = new Map<string, number>();

    for (let index = 0; index < formStateNegotiation.operationLocations.length; index++) {
      const location = formStateNegotiation.operationLocations[index];
      const zoneKey = location.zoneKey ? `-${location.zoneKey}` : '';
      const key = `${location.primaryLocationKey}${zoneKey}`;

      if (seen.has(key)) {
        return { hasDuplicate: true, index };
      }

      seen.set(key, index);
    }

    return { hasDuplicate: false, index: -1 };
  };

  // Función para obtener las rules del form si estan en el tab negotiation (en otros tabs son de lectura)
  const formRules: Record<string, Rule[]> = getConditionalFormRules('negotiation', baseRules);

  return {
    loadLocationOptions,
    locationsByStateCity,
    handleChangeLocationStateCity,
    isLoadingLocations,
    formStateNegotiation,
    filterOption,
    addOperationLocation,
    removeOperationLocation,
    formRules,
    validateUniqueOperationLocation,
    extraValidations,
    handleChangeLocationOptionZone,
  };
}
