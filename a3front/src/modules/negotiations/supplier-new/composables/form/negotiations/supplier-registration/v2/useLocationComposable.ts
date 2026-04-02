import { ref, reactive, computed, watch } from 'vue';
import type { Rule, FormInstance } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import {
  useSupplierCompleteDataRegistrationQuery,
  extractLocationsForRegistration,
  getSupplierEditQueryKey,
  SUPPLIER_EDIT_KEYS,
} from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-complete.query';
import { useZoneLocationsQuery } from '@/modules/negotiations/supplier-new/composables/queries/use-zone-locations.query';
import { useSelectedSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/selected-supplier-classification.store';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import { handleCompleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import quotesApi from '@/quotes/api/quotesApi';
import { extractCityNameFromLabel } from '@/modules/negotiations/supplier-new/data/cityPhonePrefixes';
import { PLACE_OPERATION_CLASSIFICATIONS } from '@/modules/negotiations/supplier-new/constants/supplier-registration/place-operation-classifications.constant';
// Types
interface Geolocation {
  lat: number;
  lng: number;
}

interface LocationFormState {
  commercial_locations?: number | string;
  zone_id?: number;
  commercial_address: string;
  fiscal_address: string;
  geolocation: Geolocation;
}

interface LocationReadData {
  location: string;
  zone: string;
  commercialAddress: string;
  fiscalAddress: string;
}

interface SearchRestrictions {
  countryRestriction: string[]; // Array vacío [] significa sin restricción
  searchBounds: any; // Google Maps LatLngBounds | undefined
  countryName?: string | null; // Hint por nombre de país si no hay ISO
}

export function useLocationComposable(showMapSection = true) {
  // Composables y servicios (deben llamarse en el nivel superior)
  const {
    supplierId,
    markItemComplete,
    isEditMode: isGlobalEditMode,
    countryId,
    cityId,
    cityName,
    handleSetActiveItem,
  } = useSupplierGlobalComposable();
  const { loadSupplierModules } = useSupplierModulesComposable();
  const { updateOrCreateSupplierLocations } = useSupplierService;
  const queryClient = useQueryClient();

  const refetchSupplierData = async () => {
    if (supplierId.value) {
      await queryClient.invalidateQueries({ queryKey: getSupplierEditQueryKey(supplierId.value) });
    }
  };

  // Query para passengers-countries (datos muy estáticos — caché 15 min)
  const { data: passengersCountriesData } = useQuery({
    queryKey: ['passengers-countries', 'es'],
    queryFn: async () => {
      const { data } = await quotesApi.get('/api/passengers-countries?lang=es');
      return Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
    },
    staleTime: 15 * 60 * 1000,
    refetchOnMount: false,
    refetchOnWindowFocus: false,
  });

  // Mapeo dinámico de country_id a ISO (derivado del query cacheado)
  const countryIdToIso = computed<Record<number, string>>(() => {
    const countries = passengersCountriesData.value || [];
    const mapping: Record<number, string> = {};
    countries.forEach((country: any) => {
      mapping[country.id] = country.iso;
    });
    return mapping;
  });

  // Store de clasificación (para obtener supplier_classification_id)
  const supplierClassificationStore = useSelectedSupplierClassificationStore();
  const { selectedClassificationId: supplierClassificationId } = storeToRefs(
    supplierClassificationStore
  );

  // (Se eliminó carga local de locations; se usan las del query)

  // Estado del modo edición local
  // En modo registro: true (mostrar formulario)
  // En modo edición: false inicialmente (mostrar lectura)
  const isEditMode = ref(!isGlobalEditMode.value);
  const isSaving = ref(false);

  // Form ref
  const formRef = ref<FormInstance>();
  const placeOperationRef = ref<any>(null);

  // Form state
  const formState = reactive<LocationFormState>({
    commercial_locations: undefined,
    zone_id: undefined,
    commercial_address: '',
    fiscal_address: '',
    geolocation: {
      lat: -12.046374, // Lima, Perú por defecto
      lng: -77.042793,
    },
  });

  // Query para modo REGISTRO (obtener solo recursos)
  const { data: registrationData, isLoading: isLoadingRegistrationData } =
    useSupplierCompleteDataRegistrationQuery(
      {
        keys: ['zones_locations'],
      },
      {
        enabled: computed(() => !isGlobalEditMode.value), // Solo en modo registro
      }
    );

  // Modo EDICIÓN: useQuery con el MISMO queryKey compartido. Sin HTTP extra, con reactividad.
  const { data: supplierData, isLoading: isLoadingSupplierData } = useQuery({
    queryKey: computed(() =>
      supplierId.value
        ? getSupplierEditQueryKey(supplierId.value)
        : (['supplier', 'edit-complete', '__disabled__'] as const)
    ),
    queryFn: async () =>
      useSupplierService.showSupplierCompleteData(supplierId.value!, {
        keys: [...SUPPLIER_EDIT_KEYS],
      }),
    enabled: computed(() => !!supplierId.value && isGlobalEditMode.value),
    staleTime: 5 * 60 * 1000,
    refetchOnMount: false,
    refetchOnWindowFocus: false,
  });

  // Parámetros reactivos para el query de zonas (derivados de formState.commercial_locations)
  const zoneQueryCountryId = computed<number | null>(() => {
    if (!formState.commercial_locations) return null;
    const parts = formState.commercial_locations.toString().split('-').map(Number);
    return parts[0] || null;
  });

  const zoneQueryStateId = computed<number | null>(() => {
    if (!formState.commercial_locations) return null;
    const parts = formState.commercial_locations.toString().split('-').map(Number);
    return parts[1] === 0 ? null : parts[1];
  });

  const zoneQueryCityId = computed<number | null>(() => {
    if (!formState.commercial_locations) return null;
    const parts = formState.commercial_locations.toString().split('-').map(Number);
    return parts[2] === 0 ? null : parts[2];
  });

  // Query compartido para zonesLocations — Vue Query deduplica si los parámetros coinciden
  const { data: zoneLocationsQueryData } = useZoneLocationsQuery(
    zoneQueryCountryId,
    zoneQueryStateId,
    zoneQueryCityId
  );

  // Extraer datos según el modo
  const locationData = computed(() => {
    if (isGlobalEditMode.value && supplierData.value) {
      // Modo edición: usar la misma función que registro
      return extractLocationsForRegistration(supplierData.value);
    } else if (registrationData.value) {
      // Modo registro: extraer desde registrationData
      return extractLocationsForRegistration(registrationData.value);
    }
    return { locations: [], zoneLocations: [] };
  });

  // Cargar datos del supplier al formulario cuando están disponibles (solo en modo edición)
  watch(
    () => supplierData.value,
    (data) => {
      if (data && isGlobalEditMode.value) {
        const supplierInfo = data?.data?.data?.supplier_info;
        const generalInfo = data?.data?.data?.general_info;

        if (supplierInfo && generalInfo) {
          // NO cargar locations filtradas por país - usar las del query que incluyen todos los países

          // SOLUCIÓN: El commercial_locations puede venir con formato "country-state-city-zone"
          // pero las locations del endpoint tienen "country-state-city-0"
          // Necesitamos normalizar el valor para que coincida con las locations disponibles
          let normalizedCommercialLocation = supplierInfo.commercial_locations;

          if (normalizedCommercialLocation && typeof normalizedCommercialLocation === 'string') {
            const parts = normalizedCommercialLocation.split('-');
            // Si tiene 4 partes (country-state-city-zone), reconstruir sin el zone_id (reemplazar con 0)
            if (parts.length === 4) {
              normalizedCommercialLocation = `${parts[0]}-${parts[1]}-${parts[2]}-0`;
            }
          }

          // Usar el valor normalizado para el select de ubicación
          formState.commercial_locations = normalizedCommercialLocation || undefined;
          formState.zone_id = supplierInfo.zone_id || undefined;
          // Leer direcciones desde supplier_info, con fallback a general_info
          formState.commercial_address =
            supplierInfo.commercial_address || (generalInfo as any).commercial_address || '';
          formState.fiscal_address =
            supplierInfo.fiscal_address || (generalInfo as any).fiscal_address || '';

          if (supplierInfo.geolocation) {
            formState.geolocation = supplierInfo.geolocation;
          }

          // Actualizar país y ciudad global (las zonas se cargan reactivamente vía useZoneLocationsQuery)
          if (normalizedCommercialLocation) {
            const parts = normalizedCommercialLocation.toString().split('-').map(Number);
            countryId.value = parts[0] || null;
            cityId.value = supplierInfo.city?.id ?? parts[2] ?? null;
            cityName.value = supplierInfo.city?.name ?? null;
          }

          // Si no hay datos reales, mantener en modo edición para que el usuario pueda ingresar info
          const hasData = !!(
            supplierInfo.commercial_address ||
            (generalInfo as any).commercial_address ||
            supplierInfo.fiscal_address ||
            (generalInfo as any).fiscal_address ||
            supplierInfo.commercial_locations
          );
          isEditMode.value = !hasData;
        }
      }
    },
    { immediate: true }
  );

  // Extraer locations
  const locationsData = computed(() => {
    // SIEMPRE usar las locations del query (zones_locations) que incluyen TODOS los países
    // NO usar localLocations que están filtradas por un solo país
    return locationData.value.locations.map((location: any) => {
      // Construir el id en formato "country_id-state_id-city_id-zone_id"
      const countryId = location.country_id || 0;
      const stateId = location.state_id || 0;
      const cityId = location.city_id || 0;
      const zoneId = location.zone_id || 0;
      const constructedId = `${countryId}-${stateId}-${cityId}-${zoneId}`;

      return {
        id: location.id || constructedId,
        name: location.name,
      };
    });
  });

  // Extraer zones desde zoneLocations (query compartido cacheado; fallback al query inicial)
  const zonesData = computed(() => {
    const fromQuery = zoneLocationsQueryData.value?.data?.zonesLocations;
    if (fromQuery && fromQuery.length > 0) return fromQuery;
    return locationData.value.zoneLocations || [];
  });
  // Zones disabled cuando no hay ubicación seleccionada o no hay zonas disponibles
  const zonesDisabled = computed(() => {
    // Deshabilitar si no hay ubicación seleccionada
    if (!formState.commercial_locations) {
      return true;
    }
    // Deshabilitar si no hay zonas disponibles
    return zonesData.value.length === 0;
  });

  // Determinar si es proveedor tipo STAFF
  const isStaff = computed(() => supplierClassificationId.value === 'STA');

  // Determinar si requiere place operation (basado en la clasificación del proveedor)
  const requiresPlaceOperation = computed(() => {
    return (
      supplierClassificationId.value != null &&
      PLACE_OPERATION_CLASSIFICATIONS.includes(supplierClassificationId.value)
    );
  });

  // Computed para extraer el country_id de commercial_locations
  const selectedCountryId = computed(() => {
    if (!formState.commercial_locations) return null;
    const parts = formState.commercial_locations.toString().split('-').map(Number);
    return parts[0] || null;
  });

  // Computed para obtener el código ISO del país seleccionado
  const selectedCountryIso = computed(() => {
    if (!selectedCountryId.value) return null;
    const iso = countryIdToIso.value[selectedCountryId.value] || null;
    // Google Places acepta códigos ISO-3166 alfa-2, en minúsculas es lo más seguro
    return typeof iso === 'string' ? iso.toLowerCase() : iso;
  });

  // Mapa id -> nombre de país a partir del recurso de locations (país puro tiene state_id=0, city_id=0)
  const countryIdToName = computed<Record<number, string>>(() => {
    const map: Record<number, string> = {};
    const list = (locationData.value as any)?.locations || [];
    for (const loc of list) {
      const cId = loc?.country_id || 0;
      const sId = loc?.state_id || 0;
      const ciId = loc?.city_id || 0;
      if (cId && sId === 0 && ciId === 0 && typeof loc?.name === 'string') {
        map[cId] = loc.name;
      }
    }
    return map;
  });

  const selectedCountryName = computed(() => {
    if (!selectedCountryId.value) return null;
    const fromMap = countryIdToName.value[selectedCountryId.value];
    if (fromMap) return fromMap;
    // Fallback: derivar del nombre de la opción seleccionada (primer segmento antes de coma)
    const current = (locationsData.value as any[]).find(
      (l: any) => l.id === formState.commercial_locations
    );
    if (current?.name && typeof current.name === 'string') {
      const idx = current.name.indexOf(',');
      return idx > 0 ? current.name.substring(0, idx).trim() : current.name.trim();
    }
    return null;
  });

  // Restricciones de búsqueda para el mapa (dinámico según el país seleccionado)
  const searchRestrictions = computed<SearchRestrictions>(() => {
    const iso = selectedCountryIso.value;
    const cname = selectedCountryName.value;
    if (!iso) {
      return { countryRestriction: [], searchBounds: undefined, countryName: cname || null };
    }
    return { countryRestriction: [iso], searchBounds: undefined, countryName: cname || null };
  });

  // Loading para mostrar overlay (durante carga inicial Y al guardar)
  const isLoading = computed(() => {
    return isSaving.value || isLoadingRegistrationData.value || isLoadingSupplierData.value;
  });

  // Data para modo lectura
  const readData = computed<LocationReadData>(() => {
    let locationName = '-';

    // Si estamos en modo edición, construir el nombre desde supplier_info
    if (isGlobalEditMode.value && supplierData.value) {
      const supplierInfo = supplierData.value?.data?.data?.supplier_info;
      if (supplierInfo) {
        const parts: string[] = [];
        if (supplierInfo.country?.name) parts.push(supplierInfo.country.name);
        if (supplierInfo.state?.name) parts.push(supplierInfo.state.name);
        if (supplierInfo.city?.name) parts.push(supplierInfo.city.name);
        locationName = parts.length > 0 ? parts.join(' / ') : '-';
      }
    } else {
      // En modo registro, buscar en locationsData
      locationName =
        locationsData.value.find((loc: any) => loc.id === formState.commercial_locations)?.name ||
        '-';
    }

    const zoneName = zonesData.value.find((z: any) => z.zone_id === formState.zone_id)?.name || '-';

    return {
      location: locationName,
      zone: zoneName,
      commercialAddress: formState.commercial_address || '-',
      fiscalAddress: formState.fiscal_address || '-',
    };
  });

  // Reglas de validación
  const rules = computed<Record<string, Rule[]>>(() => ({
    commercial_locations: [
      {
        required: true,
        message: 'Por favor seleccione una ubicación',
      },
    ],
    commercial_address: [
      {
        required: !isStaff.value,
        message: 'Por favor ingrese la dirección comercial',
      },
    ],
    fiscal_address: [
      {
        required: true,
        message: 'Por favor ingrese la dirección fiscal',
      },
    ],
  }));

  // Métodos
  const handleEditMode = () => {
    isEditMode.value = true;
    // Actualizar el ítem activo del sidebar
    handleSetActiveItem('supplier', 'supplier-negotiations', 'location');
  };

  const handleCancel = () => {
    isEditMode.value = false;
    // Reset form state si es necesario
  };

  const handleSaveForm = async () => {
    if (!supplierId.value) {
      console.error('No se encontró el ID del proveedor');
      return false;
    }

    try {
      isSaving.value = true;

      // Parsear commercial_locations para extraer country_id, state_id, city_id
      // El formato es: "country_id-state_id-city_id" (ej: "89-0-0" o "89-15-123")
      let countryId = null;
      let stateId = null;
      let cityId = null;

      if (formState.commercial_locations) {
        const parts = formState.commercial_locations.toString().split('-').map(Number);
        countryId = parts[0] || null;
        stateId = parts[1] === 0 ? null : parts[1];
        cityId = parts[2] === 0 ? null : parts[2];
      }

      // Preparar request con la estructura esperada por el backend
      const request = {
        country_id: countryId,
        state_id: stateId,
        city_id: cityId,
        zone_id: formState.zone_id || null,
        commercial_address: formState.commercial_address,
        fiscal_address: formState.fiscal_address,
        geolocation: showMapSection ? formState.geolocation : undefined,
      };

      // Si requiere lugares de operación, agregar al request
      if (requiresPlaceOperation.value && placeOperationRef.value) {
        const placeOperations = placeOperationRef.value.getRequestPayload();
        if (placeOperations && placeOperations.length > 0) {
          (request as any).place_operations = placeOperations;
        }
      }

      // Llamar al servicio de backend
      const response = await updateOrCreateSupplierLocations(supplierId.value, request);

      if (response?.success) {
        handleCompleteResponse(response);

        // Recargar módulos del supplier
        await loadSupplierModules();

        // Refrescar los datos del supplier para actualizar el modo lectura con la nueva ubicación
        await refetchSupplierData();

        // Marcar item como completo en el sidebar
        markItemComplete('location');

        // Cambiar a modo lectura
        isEditMode.value = false;
        return true;
      } else {
        console.error('Error al guardar la ubicación');
        return false;
      }
    } catch (error) {
      handleError(error as Error);
      return false;
    } finally {
      isSaving.value = false;
    }
  };

  const handleSave = async () => {
    try {
      // 1. Validar formulario principal
      await formRef.value?.validate();

      // 2. Si requiere lugares de operación, validarlos
      if (requiresPlaceOperation.value && placeOperationRef.value) {
        await placeOperationRef.value.validateFields();

        // Validar que haya al menos un lugar de operación
        const payload = placeOperationRef.value.getRequestPayload();
        if (!payload || payload.length === 0) {
          throw new Error('Debe agregar al menos un lugar de operación');
        }
      }

      // 3. Guardar
      await handleSaveForm();
    } catch (error) {
      console.error('Validation failed:', error);
      // Re-lanzar el error para que se muestre la notificación
      if (
        error instanceof Error &&
        error.message === 'Debe agregar al menos un lugar de operación'
      ) {
        throw error;
      }
    }
  };

  const handleLocationChanged = (location: Geolocation) => {
    formState.geolocation = location;
  };

  const handlePlaceChanged = (place: any) => {
    if (!place.geometry) return;

    const lat = place.geometry.location.lat();
    const lng = place.geometry.location.lng();

    formState.geolocation = { lat, lng };
    formState.commercial_address = place.formatted_address || '';
  };

  const filterOption = (input: string, option: any) => {
    const name = option.name || option.label || '';

    // Función para normalizar texto (remover acentos y convertir a minúsculas)
    const normalize = (text: string) => {
      return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
    };

    return normalize(name).indexOf(normalize(input)) >= 0;
  };

  // Watch: Limpiar solo zone_id cuando cambia commercial_locations.
  // Las direcciones digitadas se conservan aunque cambie País / Ciudad.
  // Las zonas se recargan reactivamente vía useZoneLocationsQuery cuando cambian los computed params
  watch(
    () => formState.commercial_locations,
    (newValue, oldValue) => {
      if (newValue !== oldValue && oldValue !== undefined && isEditMode.value) {
        formState.zone_id = undefined;
      }
      if (!newValue) {
        formState.zone_id = undefined;
        // Actualizar país global cuando se limpia la ubicación
        countryId.value = null;
        cityId.value = null;
        cityName.value = null;
      } else {
        // Actualizar país global según la nueva ubicación seleccionada
        const parts = newValue.toString().split('-').map(Number);
        countryId.value = parts[0] || null;
        cityId.value = parts[2] || null;
        // Extraer nombre de ciudad del label de la opción seleccionada
        const selectedOption = locationsData.value.find((l: any) => l.id === newValue);
        cityName.value = extractCityNameFromLabel(selectedOption?.name ?? '');
      }
    }
  );

  // Watch: Resetear formState cuando cambia el supplierId (navegación entre proveedores)
  watch(
    () => supplierId.value,
    (newId, oldId) => {
      if (oldId !== undefined && newId !== oldId && newId !== undefined) {
        formState.commercial_locations = undefined;
        formState.zone_id = undefined;
        formState.commercial_address = '';
        formState.fiscal_address = '';
        formState.geolocation = { lat: -12.046374, lng: -77.042793 };
        isEditMode.value = false;
        countryId.value = null;
        cityId.value = null;
        cityName.value = null;
      }
    }
  );

  // Computed: validar si el formulario es válido (todos los campos requeridos completos)
  const isFormValid = computed(() => {
    // Campos requeridos
    const hasLocation = !!formState.commercial_locations;
    const hasCommercialAddress = isStaff.value || !!formState.commercial_address?.trim();
    const hasFiscalAddress = !!formState.fiscal_address?.trim();

    return hasLocation && hasCommercialAddress && hasFiscalAddress;
  });

  return {
    // Estado
    formState,
    formRef,
    placeOperationRef,
    isEditMode,
    isLoading, // Para mostrar overlay bloqueante solo al guardar
    showMapSection,

    // Datos computados
    locations: locationsData,
    zones: zonesData,
    zonesDisabled,
    requiresPlaceOperation,
    searchRestrictions,
    readData,
    isFormValid,
    isStaff,

    // Reglas de validación
    rules,

    // Métodos
    handleEditMode,
    handleCancel,
    handleSave,
    handleLocationChanged,
    handlePlaceChanged,
    filterOption,
  };
}
