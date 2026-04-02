import type { FormInstance, Rule } from 'ant-design-vue/es/form';
import { computed, onMounted, ref, watch } from 'vue';
// import { useSupportResourceService } from '@/modules/negotiations/supplier-new/service/support-resources.service';
import type {
  LocationResponse,
  PlaceOperationForm,
  PlaceOperationFormProps,
  PlaceOperationResponse,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration/locations';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import { filterOption } from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';
import {
  joinKeyOperationLocation,
  parseKeyOperationLocation,
} from '@/modules/negotiations/suppliers/helpers/operation-location-helper';
import { useNotificationsComposable } from '@/modules/negotiations/suppliers/composables/notifications.composable';
import { useSupplierGlobalStoreFacade } from '@/modules/negotiations/supplier-new/composables/supplier-global-store-facade.composable';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierPlaceOperationsService } from '@/modules/negotiations/supplier-new/service/supplier-place-operations.service';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';

export function usePlaceOperationComposable(props: PlaceOperationFormProps) {
  const { showNotificationError } = useNotificationsComposable();
  // const { fetchCountryLocations } = useSupportResourceService;
  const { fetchLocations } = useSupplierResourceService;

  const { indexSupplierPlaceOperations } = useSupplierPlaceOperationsService;

  const { supplierId } = useSupplierGlobalStoreFacade();
  const { countryId } = useSupplierGlobalComposable();

  // const countryLocations = ref<CountryLocation[]>([]);
  const allLocations = ref<LocationResponse[]>([]);

  const stateCityLocations = ref<SelectOption[]>([]);
  const isLoading = ref<boolean>(false);
  const formRefPlaceOperation = ref<FormInstance | null>(null);

  const formPlaceOperations = ref<PlaceOperationForm[]>([]);

  const formRules: Record<string, Rule[]> = {
    primaryLocationKey: [
      { required: true, message: 'El campo departamento/ciudad es obligatorio', trigger: 'change' },
    ],
  };

  const getRequestPayload = () => {
    return formPlaceOperations.value.map((row) => {
      const { countryId, stateId, cityId } = parseKeyOperationLocation(
        row.primaryLocationKey ?? '',
        '-'
      );

      return {
        id: row.id,
        country_id: countryId,
        state_id: stateId,
        city_id: cityId,
        zone_id: row.zoneLocationKey ? Number(row.zoneLocationKey) : null,
      };
    });
  };

  const resetFormPlaceOperations = () => {
    formPlaceOperations.value = [];
  };

  const handleAddPlaceOperation = () => {
    formPlaceOperations.value.push({
      id: null,
      primaryLocationKey: null,
      zoneLocationKey: null,
      zoneLocations: [],
    });
  };

  const handleDeletePlaceOperation = (index: number) => {
    formPlaceOperations.value.splice(index, 1);
  };

  const getStateCityLabel = (location: LocationResponse): string => {
    const parts = location.name.split(',').map((p) => p.trim());

    const second = parts.length > 1 ? parts[1] : null;
    const third = parts.length > 2 ? parts[2] : null;

    return [second, third].filter(Boolean).join(', ');
  };

  const mapStateCityLocations = (allLocations: LocationResponse[]): SelectOption[] => {
    return allLocations
      .filter((row) => row.zone_id == null)
      .map((location: LocationResponse) => {
        return {
          // label: `${location.country_name}, ${location.location_name}`,
          label: getStateCityLabel(location),
          value: joinKeyOperationLocation(
            '-',
            location.country_id,
            location.state_id,
            location.city_id || undefined
          ),
        };
      });
  };

  const getZoneLabel = (location: LocationResponse): string => {
    return location.name.split(',').pop()?.trim() ?? '';
    // return location.location_name.split(',').pop()?.trim() ?? '';
  };

  const getZoneLocations = (countryId: number, stateId: number, cityId: number): SelectOption[] => {
    return allLocations.value
      .filter((row) => {
        return (
          row.country_id === countryId &&
          row.state_id === stateId &&
          row.city_id === cityId &&
          row.zone_id !== null
        );
      })
      .map((location: LocationResponse) => ({
        label: getZoneLabel(location),
        value: location.zone_id?.toString() ?? '',
      }));
  };

  const selectedCountryId = computed(() => {
    return !props.selectedLocation
      ? countryId.value
      : parseKeyOperationLocation(props.selectedLocation, '-').countryId;
  });

  const loadLocations = async () => {
    if (!selectedCountryId.value) return;

    try {
      isLoading.value = true;
      const data = await fetchLocations(selectedCountryId.value);

      if (data.success) {
        allLocations.value = data.data.locations;
        stateCityLocations.value = mapStateCityLocations(allLocations.value);
      }
    } catch (error: any) {
      console.log('Error load locations: ', error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleChangeStateCityLocation = (index: number) => {
    formPlaceOperations.value[index].zoneLocationKey = null;
    formPlaceOperations.value[index].zoneLocations = [];

    const { countryId, stateId, cityId } = parseKeyOperationLocation(
      formPlaceOperations.value[index].primaryLocationKey ?? '',
      '-'
    );

    if (countryId && stateId && cityId) {
      formPlaceOperations.value[index].zoneLocations = getZoneLocations(countryId, stateId, cityId);
    }
  };

  const validateUniquePlaceOperations = () => {
    const checkedKeys = new Map<string, number>();

    for (let index = 0; index < formPlaceOperations.value.length; index++) {
      const location = formPlaceOperations.value[index];

      if (location.primaryLocationKey) {
        const zoneLocationKey = location.zoneLocationKey ? `-${location.zoneLocationKey}` : '';
        const key = `${location.primaryLocationKey}${zoneLocationKey}`;

        if (checkedKeys.has(key)) {
          return { hasDuplicate: true, index };
        }

        checkedKeys.set(key, index);
      }
    }

    return { hasDuplicate: false, index: -1 };
  };

  const validateFields = async () => {
    const validateUnique = validateUniquePlaceOperations();

    if (validateUnique.hasDuplicate) {
      const message = 'Hay lugares de operación duplicados, verifique.';
      showNotificationError(message);
      throw new Error(message);
    }

    await formRefPlaceOperation.value?.validate();
  };

  const resetFields = () => {
    formRefPlaceOperation.value?.resetFields();
  };

  const fetchPlaceOperationsList = async () => {
    if (!supplierId.value) return;

    try {
      isLoading.value = true;

      const response = await indexSupplierPlaceOperations(supplierId.value);

      if (response.success && response.data.length > 0) {
        mapFormData(response.data);
      } else {
        initializeForm();
      }
    } catch (error: any) {
      console.log('Error load place operations list: ', error);
    } finally {
      isLoading.value = false;
    }
  };

  const mapFormData = (data: PlaceOperationResponse[]) => {
    formPlaceOperations.value = [];

    data.forEach((row) => {
      const { country_id: countryId, state_id: stateId, city_id: cityId } = row;

      const zoneLocations =
        countryId && stateId && cityId ? getZoneLocations(countryId, stateId, cityId) : [];

      formPlaceOperations.value.push({
        id: row.id,
        primaryLocationKey: joinKeyOperationLocation('-', countryId, stateId, cityId ?? undefined),
        zoneLocationKey: row.zone_id?.toString() ?? null,
        zoneLocations,
      });
    });
  };

  const initializeForm = () => {
    resetFormPlaceOperations();
    handleAddPlaceOperation();
  };

  watch(selectedCountryId, async () => {
    await loadLocations();
    initializeForm();
  });

  onMounted(async () => {
    if (supplierId.value && selectedCountryId.value) {
      await loadLocations();
      await fetchPlaceOperationsList();
    } else {
      initializeForm();
    }
  });

  return {
    formPlaceOperations,
    formRules,
    formRefPlaceOperation,
    stateCityLocations,
    isLoading,
    handleAddPlaceOperation,
    handleDeletePlaceOperation,
    filterOption,
    handleChangeStateCityLocation,
    validateFields,
    resetFields,
    getRequestPayload,
    fetchPlaceOperationsList,
  };
}
