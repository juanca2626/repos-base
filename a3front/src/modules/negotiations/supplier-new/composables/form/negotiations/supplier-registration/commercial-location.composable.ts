import type { Rule } from 'ant-design-vue/es/form';
import slugify from 'slugify';
import { storeToRefs } from 'pinia';
import { computed, onMounted, reactive, ref, watch } from 'vue';

import {
  handleCompleteResponse,
  handleErrorResponse,
} from '@/modules/negotiations/api/responseApi';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierGlobalStore } from '@/modules/negotiations/supplier-new/store/supplier-global.store';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useCommercialLocationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/commercial-location.store';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import { useSupplierNegotiationByItem } from '@/modules/negotiations/supplier-new/composables/form/supplier-negotiation-by-item.composable';
import type PlaceOperationComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/locations/place-operation-component.vue';
import { useSupplierClassificationStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/supplier-classification-store-facade.composable';
import { useSupplierCompleteData } from '@/modules/negotiations/supplier-new/composables/form/negotiations/use-supplier-complete-data.composable';
import { PLACE_OPERATION_CLASSIFICATIONS } from '@/modules/negotiations/supplier-new/constants/supplier-registration/place-operation-classifications.constant';

export type LocationFormState = {
  commercial_locations: string | undefined;
  commercial_locations_name?: string | undefined;
  commercial_address: string | undefined;
  fiscal_address: string | undefined;
  geolocation: { lat: number; lng: number } | null;
  code: string | undefined;
  authorized_management: boolean;
  zone_id: string | number | undefined;
};

export function useCommercialLocationComposable() {
  const {
    supplierId,
    isEditMode,
    countryId,
    showFormComponent,
    showModalCode,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getDisabledComponent,
    getSavedFormComponent,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    handleDisabledSpecific,
    handleSavedFormSpecific,
    openNextSection,
    markItemComplete,
  } = useSupplierGlobalComposable();

  const { supplierClassificationId } = useSupplierClassificationStoreFacade();

  const { setSelectedCountryName } = useSupplierGlobalStore();

  const { loadSupplierModules } = useSupplierModulesComposable();

  const { updateOrCreateSupplierLocations } = useSupplierService;

  const { showMapSection } = useSupplierNegotiationByItem();

  // Query unificada centralizada - compartida con todos los composables
  const { completeData, refetch: refetchCompleteData } = useSupplierCompleteData();

  const formRefPlaceOperation = ref<InstanceType<typeof PlaceOperationComponent> | null>(null);

  const requiresPlaceOperation = computed(() => {
    return (
      supplierClassificationId.value != null &&
      PLACE_OPERATION_CLASSIFICATIONS.includes(supplierClassificationId.value)
    );
  });

  const commercialLocationStore = useCommercialLocationStore();

  const { formState, formRef, locations, locationsLoaded, zones, zonesLoaded, zonesDisabled } =
    storeToRefs(commercialLocationStore);

  const { loadLocations } = commercialLocationStore;

  // Loading flags (UI)
  const isLoading = ref(false);
  const isLoadingButton = ref(false);
  const isInitialLoading = ref(false); // ✅ CAMBIADO: Iniciar en false, no mostrar loading innecesario

  // Estado inicial
  const initFormState: LocationFormState = {
    commercial_locations: undefined,
    commercial_address: undefined,
    fiscal_address: undefined,
    geolocation: null, // Para nuevos registros, no establecer coordenadas por defecto
    code: undefined,
    authorized_management: false,
    zone_id: undefined,
  };

  const originalFormState = reactive<LocationFormState>({ ...initFormState });

  const formRules: Record<string, Rule[]> = {
    commercial_locations: [
      { required: true, message: 'Por favor, seleccione la locación.', trigger: 'change' },
    ],
    commercial_address: [
      {
        message: 'Por favor, ingrese la dirección comercial principal.',
        trigger: 'change',
      },
    ],
    fiscal_address: [
      {
        message: 'Por favor, ingrese la dirección fiscal.',
        trigger: 'change',
      },
    ],
  };

  const getGeolocationItem = () => {
    // Ocultar coordenadas si es aerolínea (supplierClassificationId = 1)
    if (supplierClassificationId.value === 1) return [];

    if (!formState.value.geolocation) return [];

    return [
      {
        key: 'geolocation',
        label: 'Latitud / Longitud:',
        format: (value: { lat: number; lng: number } | null) => {
          if (!value || (value.lat === 0 && value.lng === 0)) return '';
          return `${value.lat.toFixed(6)} / ${value.lng.toFixed(6)}`;
        },
      },
    ];
  };

  const normalizeLocationId = (id: string) => {
    if (!id) return id;
    const parts = id.split('-');
    if (parts.length === 4 && parts[3] !== '0') {
      parts[3] = '0'; // 👈 quitamos zone_id para buscar el label correcto
    }
    return parts.join('-');
  };

  const getListItem = computed(() => {
    const fields = [
      {
        key: 'commercial_locations_name',
        label: 'País / Departamento / Ciudad / Distrito:',
      },
      {
        key: 'zone_id',
        label: 'Zona turística:',
        format: (value: any) => {
          if (!value) return '';
          // Buscar en supplier_info.zone primero
          if (completeData.value?.data?.data?.supplier_info?.zone?.name) {
            return completeData.value.data.data.supplier_info.zone.name;
          }
          // Fallback: buscar en zones
          const zone: any = zones.value.find((z: any) => z.zone_id == value) || {};
          return zone.name || '';
        },
      },
      {
        key: 'commercial_address',
        label: 'Dirección comercial:',
      },
      {
        key: 'fiscal_address',
        label: 'Dirección fiscal:',
      },
      ...getGeolocationItem(),
    ];

    const result = fields.map(({ key, label, format }) => {
      const rawValue = (formState.value as any)[key];
      const formattedValue = format ? format(rawValue) : rawValue;

      return {
        title: label,
        value: formattedValue,
      };
    });

    return result;
  });

  const getIsFormValid = computed(() => {
    const requiredFields = ['commercial_locations'];
    return requiredFields.every((key) => {
      const value = (formState.value as any)[key];
      return value !== undefined && value !== null && value !== '';
    });
  });

  const hasSignificantData = computed(() => {
    return !!(
      formState.value.commercial_locations ||
      formState.value.commercial_address ||
      formState.value.fiscal_address ||
      formState.value.zone_id
    );
  });

  const spinTip = computed(() => (isLoadingButton.value ? 'Guardando...' : 'Cargando...'));
  const spinning = computed(
    () => isLoading.value || isLoadingButton.value || isInitialLoading.value
  );

  const buildPlaceOperationRequest = () => {
    if (!requiresPlaceOperation.value) {
      return {};
    }

    return {
      place_operations: formRefPlaceOperation.value?.getRequestPayload() ?? [],
    };
  };

  const getRequestData = () => {
    const [countryId, stateId, cityId] = formState.value.commercial_locations
      ? normalizeLocationId(formState.value.commercial_locations)
          .split('-')
          .map(Number)
          .map((v: any) => (v === 0 ? null : v))
      : [null, null, null];

    if (!showMapSection.value) {
      formState.value.geolocation = undefined;
    }

    return {
      country_id: countryId,
      state_id: stateId,
      city_id: cityId,
      zone_id: formState.value.zone_id,
      commercial_address: formState.value.commercial_address,
      fiscal_address: formState.value.fiscal_address,
      geolocation: formState.value.geolocation,
      ...buildPlaceOperationRequest(),
    };
  };

  const filterOption = (search: string, option: any) => {
    return slugify(option.name, { lower: true, replacement: '-', trim: true }).includes(
      slugify(search, { lower: true, replacement: '-', trim: true })
    );
  };

  const resetFormState = () => {
    Object.assign(formState.value, { ...initFormState });
    if (formRef.value) formRef.value.resetFields();
    Object.assign(originalFormState, { ...initFormState });
    zones.value = [];
    zonesDisabled.value = true;
    zonesLoaded.value = false;
    formRefPlaceOperation.value?.resetFields();
    setSelectedCountryName(null);
  };

  const loadLocationsData = async (id?: number) => {
    const countryToLoad = id ?? countryId.value;
    if (!countryToLoad) return;

    try {
      const { locations: locs } = await loadLocations(countryToLoad, 1);
      locations.value = locs.map((loc: any) => ({
        id: loc.id.toString(),
        name: loc.name,
      }));
      locationsLoaded.value = true; // Marcar como cargado
    } catch (error) {
      console.log(error);
      locations.value = [];
    }
  };

  // ✅ FUNCIÓN ELIMINADA: initializeFormData ya no se necesita
  // El watch de completeData se encarga de hidratar el formulario directamente
  // desde la query unificada, sin depender de supplier.value

  const handleClose = () => {
    const isValid = getIsFormValid.value;
    const isSaved = getSavedFormComponent.value(FormComponentEnum.COMMERCIAL_LOCATION);
    Object.assign(formState.value, { ...originalFormState });
    if (isValid && isSaved) {
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, true);
      return;
    }
    handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
  };

  const handleSaveForm = async () => {
    const request = getRequestData();

    try {
      isLoadingButton.value = true;

      const response = await updateOrCreateSupplierLocations(supplierId.value, request);
      if (response && response.success) {
        handleCompleteResponse(response);
        await loadSupplierModules();
        await refetchCompleteData();

        Object.assign(originalFormState, { ...formState.value });
        handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, true);
        handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, true);
        markItemComplete('location');
        openNextSection(FormComponentEnum.CONTACT_INFORMATION);
      } else {
        handleErrorResponse();
      }
    } catch (error: any) {
      console.log(error);
      handleErrorResponse();
    } finally {
      isLoadingButton.value = false;
    }
  };

  const handleSave = async () => {
    try {
      await formRefPlaceOperation.value?.validateFields();
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error: any) {
      console.log(error.message);
    }
  };

  const handleShowForm = () => {
    handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
  };

  const handleLocationChanged = (locationData: { lat: number; lng: number; address: string }) => {
    if (!formState.value.geolocation) {
      formState.value.geolocation = { lat: 0, lng: 0 };
    }
    formState.value.geolocation.lat = locationData.lat;
    formState.value.geolocation.lng = locationData.lng;
    formState.value.commercial_address = locationData.address;
  };

  const handlePlaceChanged = (place: any) => {
    if (place && place.geometry && place.geometry.location) {
      const lat = place.geometry.location.lat();
      const lng = place.geometry.location.lng();
      if (!formState.value.geolocation) {
        formState.value.geolocation = { lat: 0, lng: 0 };
      }
      formState.value.geolocation.lat = lat;
      formState.value.geolocation.lng = lng;
      if (place.formatted_address) {
        formState.value.commercial_address = place.formatted_address;
      }
    }
  };

  const setCommercialLocations = async () => {
    // Las zones están en resources.zones_locations.zones (es un objeto con {locations: [], zones: []})
    const newZones = completeData.value?.data?.data?.resources?.zones_locations?.zones;

    if (newZones && Array.isArray(newZones) && newZones.length > 0) {
      if (JSON.stringify(newZones) !== JSON.stringify(zones.value)) {
        zones.value = newZones;
        zonesDisabled.value = false;
      }
    } else {
      zones.value = [];
      zonesDisabled.value = true;
    }
  };

  const extractAndSaveCountryName = (locationId: string | undefined) => {
    if (!locationId || !locations.value.length) {
      setSelectedCountryName(null);
      return;
    }

    const normalizedId = normalizeLocationId(locationId);
    const location = locations.value.find(
      (loc: { id: string; name?: string }) => loc.id === normalizedId
    );

    if (location?.name) {
      // Extraer solo el país (primera parte antes del separador)
      // Maneja tanto " / " como ", " como separadores
      let countryName = location.name;

      if (countryName.includes(' / ')) {
        countryName = countryName.split(' / ')[0];
      } else if (countryName.includes(', ')) {
        countryName = countryName.split(', ')[0];
      }

      setSelectedCountryName(countryName.trim());
    } else {
      setSelectedCountryName(null);
    }
  };

  // Función para convertir nombres de países a códigos ISO de Google Maps
  const getCountryCodeFromName = (countryName: string): string[] => {
    const countryMapping: Record<string, string> = {
      Perú: 'PE',
      Peru: 'PE',
      Brasil: 'BR',
      Brazil: 'BR',
      Chile: 'CL',
      Argentina: 'AR',
      Colombia: 'CO',
      Ecuador: 'EC',
      Bolivia: 'BO',
      Uruguay: 'UY',
      Paraguay: 'PY',
      Venezuela: 'VE',
      México: 'MX',
      Mexico: 'MX',
      'Estados Unidos': 'US',
      'United States': 'US',
      España: 'ES',
      Spain: 'ES',
      Francia: 'FR',
      France: 'FR',
      'Reino Unido': 'GB',
      'United Kingdom': 'GB',
      Alemania: 'DE',
      Germany: 'DE',
      Italia: 'IT',
      Italy: 'IT',
      Portugal: 'PT',
      Japón: 'JP',
      Japan: 'JP',
      China: 'CN',
      India: 'IN',
      Canadá: 'CA',
      Canada: 'CA',
      Australia: 'AU',
    };

    const countryCode = countryMapping[countryName];
    return countryCode ? [countryCode] : [];
  };

  // Computed para las restricciones de búsqueda basadas en la ubicación seleccionada
  const searchRestrictions = computed(() => {
    if (!formState.value.commercial_locations) {
      return {
        countryRestriction: [],
        searchBounds: null,
      };
    }

    const normalizedId = normalizeLocationId(formState.value.commercial_locations);
    const location = locations.value.find(
      (loc: { id: string; name?: string }) => loc.id === normalizedId
    );

    if (!location?.name) {
      return {
        countryRestriction: [],
        searchBounds: null,
      };
    }

    // Extraer el nombre del país
    let countryName = location.name;
    if (countryName.includes(' / ')) {
      countryName = countryName.split(' / ')[0];
    } else if (countryName.includes(', ')) {
      countryName = countryName.split(', ')[0];
    }

    const countryRestriction = getCountryCodeFromName(countryName.trim());

    return {
      countryRestriction,
      searchBounds: null, // Podríamos agregar bounds específicos por región si es necesario
    };
  });

  // Función para obtener las restricciones de búsqueda basadas en la ubicación seleccionada
  const getSearchRestrictions = () => {
    return searchRestrictions.value;
  };

  onMounted(async () => {
    if (isEditMode.value) {
      isInitialLoading.value = false;
    } else {
      resetFormState();
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
      handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
      isInitialLoading.value = false;
    }
  });

  watch(supplierId, async (newId, oldId) => {
    if (newId && newId !== oldId && !isEditMode.value) {
      resetFormState();
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, true);
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
      handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
    }
  });

  watch(countryId, async (newCountryId) => {
    if (newCountryId) {
      await loadLocationsData(newCountryId);
    }
  });

  watch(isEditMode, (newIsEditMode) => {
    if (!newIsEditMode) {
      resetFormState();
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, true);
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
      handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
    }
  });

  watch(
    () => formState.value.commercial_locations,
    async (newLocations, oldLocations) => {
      if (newLocations == undefined) {
        formState.value.zone_id = undefined;
        zonesDisabled.value = true;
        zones.value = [];
        setSelectedCountryName(null);
        return;
      }

      if (newLocations !== oldLocations && newLocations) {
        zonesLoaded.value = false;
        formState.value.zone_id = undefined;

        await setCommercialLocations();

        // Guardar el nombre del país en el estado global
        extractAndSaveCountryName(newLocations);
      }
    }
  );

  // Watch completeData para cargar datos cuando estén disponibles
  watch(
    () => completeData.value,
    async (newData) => {
      if (!supplierId.value || !newData) {
        return;
      }

      const supplierInfo = newData.data?.data?.supplier_info;

      // Cargar locations si el país está disponible en supplierInfo
      if (supplierInfo?.country?.id && !locationsLoaded.value) {
        await loadLocationsData(supplierInfo.country.id);
      }

      // ✅ CARGAR LOCATIONS desde completeData (si ya vienen en la respuesta)
      const locationsSource =
        newData.data?.data?.resources?.zones_locations?.locations ||
        newData.data?.data?.resources?.locations;

      if (locationsSource && locationsSource.length > 0) {
        locations.value = locationsSource.map((loc: any) => ({
          id: typeof loc.id === 'string' ? loc.id : loc.id.toString(),
          name: loc.name,
        }));
        locationsLoaded.value = true;
      } else if (!locationsLoaded.value && countryId.value) {
        await loadLocationsData(countryId.value);
      }

      // ✅ CARGAR ZONES desde completeData
      const zonesSource = newData.data?.data?.resources?.zones_locations?.zones;

      if (zonesSource && Array.isArray(zonesSource) && zonesSource.length > 0) {
        zones.value = zonesSource;
        zonesLoaded.value = true;
      }

      if (supplierInfo) {
        const hasSignificantData = !!(
          (supplierInfo.commercial_address && supplierInfo.commercial_address.trim()) ||
          (supplierInfo.fiscal_address && supplierInfo.fiscal_address.trim()) ||
          supplierInfo.geolocation ||
          supplierInfo.zone_id
        );

        if (hasSignificantData) {
          formState.value.commercial_address = supplierInfo.commercial_address || undefined;
          formState.value.fiscal_address = supplierInfo.fiscal_address || undefined;
          formState.value.geolocation = supplierInfo.geolocation || null;
          formState.value.zone_id = supplierInfo.zone_id || undefined;
          formState.value.code = supplierInfo.code || undefined;
          formState.value.authorized_management = supplierInfo.authorized_management || false;

          const rawLocations = supplierInfo.commercial_locations;
          formState.value.commercial_locations =
            rawLocations && rawLocations !== '0-0-0-0'
              ? normalizeLocationId(rawLocations)
              : undefined;

          const locationParts = [
            supplierInfo.country?.name,
            supplierInfo.state?.name,
            supplierInfo.city?.name,
          ].filter(Boolean);
          formState.value.commercial_locations_name = locationParts.join(' / ') || undefined;

          await setCommercialLocations();
          extractAndSaveCountryName(formState.value.commercial_locations);

          originalFormState.commercial_locations = formState.value.commercial_locations;
          originalFormState.commercial_address = formState.value.commercial_address;
          originalFormState.fiscal_address = formState.value.fiscal_address;
          originalFormState.geolocation = formState.value.geolocation
            ? { lat: formState.value.geolocation.lat, lng: formState.value.geolocation.lng }
            : null;
          originalFormState.zone_id = formState.value.zone_id;
          originalFormState.code = formState.value.code;
          originalFormState.authorized_management = formState.value.authorized_management || false;

          handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, true);
          handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, true);
          handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, true);
        } else {
          resetFormState();
          handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
          handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
          handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_LOCATION, false);
        }
      }

      isInitialLoading.value = false;
    },
    { immediate: true, deep: true }
  );

  return {
    formState,
    formRef,
    formRules,
    locations,
    showFormComponent,
    showModalCode,
    zones,
    zonesDisabled,
    zonesLoaded,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getDisabledComponent,
    getListItem,
    getIsFormValid,
    spinning,
    spinTip,
    isLoading,
    isLoadingButton,
    isInitialLoading,
    isEditMode,
    hasSignificantData,
    formRefPlaceOperation,
    requiresPlaceOperation,
    handleClose,
    handleSave,
    handleShowForm,
    handleDisabledSpecific,
    handleLocationChanged,
    handlePlaceChanged,
    filterOption,
    resetFormState,
    getSearchRestrictions,
    searchRestrictions,
  };
}
