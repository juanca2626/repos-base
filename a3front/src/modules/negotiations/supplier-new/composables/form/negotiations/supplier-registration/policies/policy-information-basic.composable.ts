import { handleCompleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import { MEASUREMENT_UNITS } from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/data-init/data';
import { usePolicyFormUtilsComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/information-basic/policy-form-utils.composable';
import { usePolicyResourceComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/information-basic/policy-resource.composable';
import { usePolicyFormStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-form-store-facade.composable';
import { usePolicyManagerComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-manager.composable';
import { usePolicyStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-store-facade.composable';
import { useSupplierClassificationHelper } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/use-supplier-classification-helper.composable';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import {
  segmentationsSupportingLoading,
  specificationLoadingMap,
} from '@/modules/negotiations/supplier-new/constants/supplier-registration/policies/form-policy';
import { BusinessGroupEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/business-groups.enum';
import { MeasurementUnitEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/measurement-unit.enum';
import { SegmentationEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/segmentation.enum';
import {
  getLabelFromOptions,
  hasEventSegmentation,
  isClientSegmentation,
  isSeriesSegmentation,
  listFormatter,
} from '@/modules/negotiations/supplier-new/helpers/supplier-registration/policies/policy-form-helper';
import type {
  PolicySegmentationSpecification,
  SupplierPolicyCloneResponse,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import { useSupplierPoliciesService } from '@/modules/negotiations/supplier-new/service/supplier-policies.service';
import {
  filterOption,
  filterOptionByName,
} from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';
import { notification } from 'ant-design-vue';
import type { FormInstance } from 'ant-design-vue/es/form';
import dayjs from 'dayjs';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import { computed, nextTick, ref, watch } from 'vue';

dayjs.extend(customParseFormat);

export function usePolicyInformationBasicComposable() {
  const formRef = ref<FormInstance | null>(null);
  const segmentationSelectRef = ref<{ focus: () => void } | null>(null);

  const { openRegisteredDetails, showInformationBasic } = usePolicyManagerComposable();

  const { loadSupplierModules } = useSupplierModulesComposable();

  const { isLoading, showForm, loadingButton, setReloadList, startLoading, stopLoading } =
    usePolicyStoreFacade();

  const {
    formState,
    policyId,
    clonePolicyId,
    reloadHolidayCalendars,
    clonedPolicyData,
    setReloadPolicyData,
    setPolicyId,
    setClonePolicyId,
    setReloadHolidayCalendars,
    setPolicyCloneResponse,
    setFormState,
  } = usePolicyFormStoreFacade();

  const {
    selectOptionsLoading,
    businessGroups,
    businessGroupsWithLimits,
    segmentations,
    allMarkets,
    allClients,
    allServiceTypes,
    allSeasons,
    serviceTypes,
    specificationOptionsMap,
    fetchMainResources,
    getHolidayCalendars,
    getSpecificationData,
    fetchMarkets,
    fetchClients,
    fetchServiceTypes,
    fetchSeasons,
  } = usePolicyResourceComposable(formState);

  const {
    isEditMode,
    formRules,
    disableDateFrom,
    disableDateTo,
    getSpecificationTagCount,
    setDefaultPax,
    isSelectedSegmentation,
    isSelectedObjectId,
    sortSpecifications,
    buildRequestData,
    cleanSpecifications,
    addSpecification,
  } = usePolicyFormUtilsComposable(formState);

  const {
    storeSupplierPolicies,
    // updateSupplierPolicies,
    showSupplierPolicyCloneData,
    patchSupplierPolicy,
  } = useSupplierPoliciesService;

  // Supplier classification helper
  const { isAccommodationSupplier, isServiceSupplier } = useSupplierClassificationHelper();

  // Computed para validar si el formulario básico está completo
  const isFormBasicValid = computed(() => {
    const hasBusinessGroup = !!formState.businessGroupId;
    const hasDateFrom = !!formState.dateFrom;
    const hasDateTo = !!formState.dateTo;
    const hasPax = formState.paxMin !== null && formState.paxMax !== null;
    // Segmentación es opcional según requerimiento
    // const hasSegmentations = formState.policySegmentationIds && formState.policySegmentationIds.length > 0;

    const hasSpecifications =
      formState.segmentationSpecifications &&
      formState.segmentationSpecifications.every((spec) => {
        if (isSeriesSegmentation(spec.segmentationId)) {
          return !!spec.inputValue && spec.inputValue.trim().length > 0;
        }
        return spec.objectIds && spec.objectIds.length > 0;
      });

    // Validación de Unidad de Medida si aplica (Hotel o Alojamiento)
    const hasMeasurementUnit = showMeasurementUnitSelector.value
      ? !!formState.measurementUnit
      : true;

    return (
      hasBusinessGroup &&
      hasDateFrom &&
      hasDateTo &&
      hasPax &&
      hasSpecifications &&
      hasMeasurementUnit
    );
  });

  // Opciones de unidad de medida
  const measurementUnits = ref(MEASUREMENT_UNITS);

  // Computed para checkbox "Es Hotel?" - sincronizado con el store
  const isHotel = computed({
    get: () => formState.isHotel ?? false,
    set: (value: boolean) => {
      formState.isHotel = value;
    },
  });

  // Computed para determinar si debe mostrar el selector de unidad de medida
  const showMeasurementUnitSelector = computed(() => {
    // TODO: HOTEL FEATURE - Por el momento se oculta ya que no hay forma de validar si es hotel.
    // Se deja comentado para futura implementación.
    return false;
    // return isHotel.value || isAccommodationSupplier.value;
  });

  // Label dinámico para el campo pax
  const paxLabel = computed(() => {
    if (formState.measurementUnit === MeasurementUnitEnum.ROOMS) {
      return 'Cantidad de habitaciones permitidas';
    }
    return 'Cantidad de personas permitidas';
  });

  // Inicializar measurementUnit por defecto según el tipo de proveedor
  const initializeMeasurementUnit = () => {
    if (!formState.measurementUnit) {
      formState.measurementUnit = isServiceSupplier.value
        ? MeasurementUnitEnum.PERSONS
        : MeasurementUnitEnum.PERSONS; // Para alojamiento, default es Personas también
    }
  };

  const showPolicySegmentation = computed(() => {
    const selectedBG = businessGroups.value.find((bg) => bg.value === formState.businessGroupId);
    const businessGroupId = Number(formState.businessGroupId);
    const businessGroupCode = String(formState.businessGroupId ?? '')
      .trim()
      .toLowerCase();
    const businessGroupLabel = selectedBG?.label?.trim().toLowerCase() ?? '';

    return (
      [BusinessGroupEnum.GENERAL, BusinessGroupEnum.FITS, BusinessGroupEnum.GROUPS].includes(
        businessGroupId
      ) ||
      ['general', 'fits', 'groups', 'grupos'].includes(businessGroupCode) ||
      ['general', 'fits', 'groups', 'grupos'].includes(businessGroupLabel)
    );
  });

  const isSpecificationLoadable = (segmentationId: number | string) => {
    return segmentationsSupportingLoading.includes(segmentationId);
  };

  const isSpecificationSelectLoading = (segmentationId: number | string) => {
    if (!isSpecificationLoadable(segmentationId)) return false;

    const key = specificationLoadingMap[segmentationId];

    return key ? selectOptionsLoading[key] : false;
  };

  const setPolicyName = () => {
    formState.name = generatePolicyName();
  };

  const handleChangeSpecification = () => {
    setPolicyName();
  };

  const getNameFromObjectId = (
    segmentationId: SegmentationEnum | number | string,
    objectId: number | string
  ): string | null => {
    // Para clients, usar allClients que tiene { id, name }
    if (isClientSegmentation(segmentationId as number)) {
      const client = allClients.value.find((c: any) => c.id === objectId || c.code === objectId);
      return client?.name ?? null;
    }

    // Para otras segmentaciones, usar las opciones del select que tienen { label, value }
    const options = getSpecificationData(segmentationId as number | string);
    const option = options.find((opt: any) => opt.value === objectId);
    return option?.label?.trim() ?? null;
  };

  const extractNamesFromSpecification = (
    specification: PolicySegmentationSpecification
  ): string[] => {
    if (isSeriesSegmentation(specification.segmentationId)) {
      const value = specification.inputValue?.trim();
      return value ? [value] : [];
    }

    // Obtener TODOS los nombres de los objectIds seleccionados
    return specification.objectIds
      .map((objectId) => getNameFromObjectId(specification.segmentationId, objectId))
      .filter((name): name is string => Boolean(name));
  };

  const getBusinessGroupName = (): string => {
    return businessGroups.value.find((row) => row.value === formState.businessGroupId)?.label ?? '';
  };

  const generatePolicyName = (): string => {
    const businessGroupName = getBusinessGroupName();

    // Obtener TODOS los nombres de TODAS las especificaciones
    const allSpecificationNames = formState.segmentationSpecifications
      .flatMap(extractNamesFromSpecification)
      .filter((name) => Boolean(name));

    const formattedNames = allSpecificationNames.length
      ? listFormatter.format(allSpecificationNames)
      : '';

    return `Política ${businessGroupName.toLowerCase()}: ${formattedNames}`.trim();
  };

  const getSegmentationNamesSummary = (): string => {
    // Fallback para segmentaciones que pueden no estar en las opciones del backend
    const segmentationFallbackLabels: Record<number, string> = {
      [SegmentationEnum.SEASONS]: 'Temporadas',
    };

    return formState.policySegmentationIds
      .map((segmentationId) => {
        const option = segmentations.value.find((s) => s.value === segmentationId);
        return option?.label || segmentationFallbackLabels[segmentationId] || '';
      })
      .filter(Boolean)
      .join(', ');
  };

  const updateFormStateWithAvailableData = () => {
    // Obtener el businessGroup completo
    const businessGroup = businessGroupsWithLimits.value.find(
      (bg) => bg.id === formState.businessGroupId
    );

    // Obtener el summary de segmentaciones
    const segmentationNamesSummary = getSegmentationNamesSummary();

    // Actualizar el formState con los datos disponibles
    setFormState({
      ...formState,
      businessGroup: businessGroup || null,
      segmentationNamesSummary,
    });
  };

  const handleSaveForm = async () => {
    // Obtener segmentación existente si estamos en modo edición
    let existingSegmentation = null;
    if (isEditMode.value && policyId.value) {
      const existingPolicy = usePolicyStoreFacade().getPolicyById(String(policyId.value));
      existingSegmentation = existingPolicy?.configuration?.segmentation || null;
    }

    const request = buildRequestData(
      allMarkets.value,
      allClients.value,
      allServiceTypes.value,
      isHotel.value,
      existingSegmentation,
      allSeasons.value
    );

    try {
      loadingButton.value = true;

      let data;
      if (isEditMode.value) {
        // PATCH para actualizar información básica de política existente
        data = await patchSupplierPolicy(String(policyId.value), request);
      } else {
        // POST para crear nueva política
        data = await storeSupplierPolicies(request);
      }

      if (data.success) {
        handleCompleteResponse();

        if (!isEditMode.value) {
          loadSupplierModules();
        }

        setReloadList(true);
        setPolicyId(data.data._id);

        // Actualizar formState con los datos disponibles antes de mostrar el summary
        updateFormStateWithAvailableData();

        openRegisteredDetails();

        // Forzar recarga de los datos después de cambiar la vista
        setTimeout(() => {
          setReloadPolicyData(true);
        }, 50);
      }
    } catch (error: any) {
      if (
        error.response?.status === 409 &&
        error.response?.data?.error === 'PolicyOverlapConflict'
      ) {
        handlePolicyOverlapError(error.response.data);
      } else {
        handleError(error);
      }
      // console.log('Error save: ', error.message); // Log eliminado
    } finally {
      loadingButton.value = false;
    }
  };

  const handlePolicyOverlapError = (errorData: any) => {
    const { details } = errorData;
    if (!details) return;

    const { conflictingPolicyName, conflictingSegmentations, existingValidity } = details;

    let description = `Ya existe una política "${conflictingPolicyName || 'Desconocida'}"`;

    if (conflictingSegmentations && conflictingSegmentations.items?.length > 0) {
      const items = conflictingSegmentations.items.join(', ');
      description += ` para ${items}`;
    }

    if (existingValidity) {
      description += ` con vigencia ${existingValidity.from} - ${existingValidity.to}`;
    }

    notification.error({
      message: 'Conflicto de política',
      description: description,
      duration: 8,
    });
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();

      await handleSaveForm();
    } catch (error: any) {
      console.error('❌ [PolicyInfoBasic] Validation error:', error);
    }
  };

  const handleChangeBusinessGroup = () => {
    // Buscar el businessGroup seleccionado para obtener sus personLimit
    const selectedBG = businessGroupsWithLimits.value.find(
      (bg) => bg.id === formState.businessGroupId
    );

    if (selectedBG && selectedBG.personLimit) {
      // Setear los valores de paxMin y paxMax desde personLimit
      formState.paxMin = selectedBG.personLimit.min;
      formState.paxMax = selectedBG.personLimit.max;
    } else {
      setDefaultPax();
    }

    if (!showPolicySegmentation.value) {
      formState.policySegmentationIds = [];
      formState.segmentationSpecifications = [];
      cleanSpecifications();
    }

    setPolicyName();
  };

  const validatePaxValue = (value: number, field: 'paxMin' | 'paxMax') => {
    const minAllowed = 1;

    // Validar que no sea negativo
    if (value < 0) {
      notification.warning({
        message: 'Valor no permitido',
        description: 'No se permiten valores negativos',
        duration: 3,
      });
      formState[field] = minAllowed;
      return;
    }

    // Validar que no sea decimal
    if (!Number.isInteger(value)) {
      notification.warning({
        message: 'Valor no permitido',
        description: 'No se permiten valores decimales',
        duration: 3,
      });
      formState[field] = Math.max(Math.floor(value), minAllowed);
      return;
    }

    // Validar límite mínimo permitido
    if (value < minAllowed) {
      notification.warning({
        message: 'Valor no permitido',
        description: `El valor mínimo permitido es ${minAllowed}`,
        duration: 3,
      });

      if (field === 'paxMax') {
        const baseMin =
          formState.paxMin !== null && formState.paxMin !== undefined
            ? formState.paxMin
            : minAllowed;
        formState.paxMax = baseMin + 1;
        return;
      }

      formState[field] = minAllowed;
      return;
    }

    // Mantener el valor editado y ajustar el otro campo para evitar bloqueos
    if (field === 'paxMin' && formState.paxMax !== null && formState.paxMax !== undefined) {
      if (value >= formState.paxMax) {
        formState.paxMax = value + 1;
      }
      return;
    }

    if (field === 'paxMax') {
      // Desde manda: al editar Hasta, nunca se modifica Desde.
      if (
        formState.paxMin !== null &&
        formState.paxMin !== undefined &&
        value <= formState.paxMin
      ) {
        formState.paxMax = formState.paxMin + 1;
        return;
      }

      formState.paxMax = value;
      return;
    }
  };

  const handleChangePaxMin = () => {
    if (formState.paxMin !== null && formState.paxMin !== undefined) {
      validatePaxValue(formState.paxMin, 'paxMin');
    }
  };

  const handleChangePaxMax = () => {
    if (formState.paxMax !== null && formState.paxMax !== undefined) {
      validatePaxValue(formState.paxMax, 'paxMax');
    }
  };

  const handleChangeDateFrom = () => {
    getHolidayCalendars();
  };

  const handleChangeDateTo = () => {
    getHolidayCalendars();
  };

  const clearDateFieldValidation = (field: 'dateFrom' | 'dateTo') => {
    setTimeout(() => {
      formRef.value?.clearValidate?.(field);
    }, 0);
  };

  const normalizeDateInput = (rawValue: string): string => {
    const digits = rawValue.replace(/\D/g, '');
    if (digits.length === 8) {
      return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
    }
    if (digits.length === 6) {
      return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/20${digits.slice(4, 6)}`;
    }
    return rawValue;
  };

  const syncManualDateInput = (field: 'dateFrom' | 'dateTo', rawValue: string) => {
    if (!rawValue) {
      formState[field] = null;
      if (field === 'dateFrom') handleChangeDateFrom();
      else handleChangeDateTo();
      return;
    }

    const parsedDate = dayjs(normalizeDateInput(rawValue), 'DD/MM/YYYY', true);
    if (!parsedDate.isValid()) return;

    if (
      field === 'dateFrom' &&
      formState.dateTo &&
      parsedDate.isAfter(dayjs(formState.dateTo), 'day')
    )
      return;
    if (
      field === 'dateTo' &&
      formState.dateFrom &&
      parsedDate.isBefore(dayjs(formState.dateFrom), 'day')
    )
      return;

    formState[field] = parsedDate.format('YYYY-MM-DD');
    clearDateFieldValidation(field);
    if (field === 'dateFrom') handleChangeDateFrom();
    else handleChangeDateTo();
  };

  const handleDateInputBlur = (field: 'dateFrom' | 'dateTo', event: FocusEvent) => {
    const target = event.target as HTMLInputElement | null;
    const rawValue = target?.value?.trim() ?? '';

    syncManualDateInput(field, rawValue);
  };

  const handleDateInputKeydown = (field: 'dateFrom' | 'dateTo', event: KeyboardEvent) => {
    if (event.key !== 'Tab' && event.key !== 'Enter') {
      return;
    }

    const target = event.target as HTMLInputElement | null;
    const rawValue = target?.value?.trim() ?? '';

    const digits = rawValue.replace(/\D/g, '');
    if (digits.length !== 6 && digits.length !== 8) return;

    syncManualDateInput(field, rawValue);

    if (field === 'dateFrom' && target) {
      event.preventDefault();
      const wrapper = target.closest('.date-picker-wrapper');
      const nextInput = wrapper?.nextElementSibling?.querySelector(
        'input'
      ) as HTMLInputElement | null;
      nextInput?.focus();
    } else if (field === 'dateTo' && target) {
      event.preventDefault();
      target.blur();
      nextTick(() => {
        segmentationSelectRef.value?.focus();
      });
    }
  };

  const getSegmentationLabel = (segmentationId: number): string => {
    return getLabelFromOptions(segmentations.value, segmentationId);
  };

  const handleChangeSegmentation = () => {
    cleanSpecifications();
    addSpecification();
    sortSpecifications();
    setPolicyName();
  };

  const handleSelectSegmentation = async (value: number | string) => {
    // Mapeo de códigos de segmentación a IDs numéricos
    const segmentationCodeToId: Record<string, number> = {
      markets: SegmentationEnum.MARKETS,
      clients: SegmentationEnum.CLIENTS,
      series: SegmentationEnum.SERIES,
      events: SegmentationEnum.EVENTS,
      'service-types': SegmentationEnum.SERVICE_TYPES,
      serviceTypes: SegmentationEnum.SERVICE_TYPES,
      seasons: SegmentationEnum.SEASONS,
    };

    // Normalizar el valor: convertir string a número si es necesario
    let normalizedValue: number;
    if (typeof value === 'string') {
      normalizedValue = segmentationCodeToId[value] || Number(value) || (value as any);
    } else {
      normalizedValue = value;
    }

    if (normalizedValue === SegmentationEnum.EVENTS) {
      getHolidayCalendars();
    } else if (normalizedValue === SegmentationEnum.MARKETS) {
      fetchMarkets();
    } else if (normalizedValue === SegmentationEnum.CLIENTS) {
      fetchClients();
    } else if (normalizedValue === SegmentationEnum.SERVICE_TYPES) {
      await fetchServiceTypes();
      await nextTick();
    } else if (normalizedValue === SegmentationEnum.SEASONS) {
      await fetchSeasons();
      await nextTick();
    }
  };

  const setDataForClone = async (data: SupplierPolicyCloneResponse) => {
    formState.businessGroupId = data.business_group_id;
    formState.name = data.name;
    formState.dateFrom = data.date_from;
    formState.dateTo = data.date_to;
    formState.paxMin = data.pax_min;
    formState.paxMax = data.pax_max;
    formState.policySegmentationIds = data.policy_segmentations.map((row) => row.segmentation_id);

    formState.segmentationSpecifications = data.policy_segmentations.map((item) => {
      return {
        segmentationId: item.segmentation_id,
        objectIds: item.object_ids,
        inputValue: item.specification_name ?? undefined,
      };
    });

    if (hasEventSegmentation(formState.policySegmentationIds)) {
      setReloadHolidayCalendars(true);
    }
  };

  const loadDataForClone = async () => {
    try {
      startLoading();
      const { data } = await showSupplierPolicyCloneData(clonePolicyId.value!);

      setDataForClone(data);
      setClonePolicyId(null);
      setPolicyCloneResponse(data);
    } catch (error: any) {
      handleError(error);
      // console.log('Error load data for clone', error); // Log eliminado
    } finally {
      stopLoading();
    }
  };

  watch(reloadHolidayCalendars, (value) => {
    if (value) {
      getHolidayCalendars();
      setReloadHolidayCalendars(false);
    }
  });

  watch(clonePolicyId, (value) => {
    if (!value) return;
    loadDataForClone();
  });

  // Watcher para pre-llenar formulario cuando hay datos clonados
  watch(
    () => clonedPolicyData.value,
    async (data) => {
      if (!data) return;
      await loadDataFromClonedPolicy(data);
    },
    { immediate: true }
  );

  /**
   * Pre-llena el formulario con datos de una política clonada
   */
  const loadDataFromClonedPolicy = async (data: any) => {
    // Asegurar que los recursos están cargados
    if (businessGroups.value.length === 0) {
      await fetchMainResources();
    }

    // Pre-llenar información básica
    formState.name = data.name;
    formState.businessGroupId = data.businessGroupId;
    formState.dateFrom = data.dateFrom;
    formState.dateTo = data.dateTo;
    formState.paxMin = data.paxMin;
    formState.paxMax = data.paxMax;
    formState.measurementUnit = data.measurementUnit;
    formState.isHotel = data.isHotel || false;
    formState.policySegmentationIds = data.policySegmentationIds || [];
    formState.segmentationSpecifications = data.segmentationSpecifications || [];

    // Cargar opciones para las segmentaciones seleccionadas
    for (const segId of formState.policySegmentationIds) {
      await handleSelectSegmentation(segId);
    }

    // Limpiar los datos clonados después de usarlos
    // No limpiar aquí para que las reglas también puedan acceder a ellos
    // clearClonedPolicyData();
  };

  // Solo cargar recursos cuando se muestra el formulario de información básica
  let resourcesLoaded = false;
  watch(showInformationBasic, async (value) => {
    if (value && !resourcesLoaded) {
      resourcesLoaded = true;
      await fetchMainResources();
      initializeMeasurementUnit();
    }
  });

  const loadPolicyData = async () => {
    if (!policyId.value || !isEditMode.value) return;

    const policy = usePolicyStoreFacade().getPolicyById(String(policyId.value));

    if (policy) {
      const config = policy.configuration || {};
      const validity = config.validity || {};
      const quantity = config.quantityRange || {};
      const seg = config.segmentation || {};

      formState.name = policy.name;
      formState.dateFrom = validity.from;
      formState.dateTo = validity.to;
      formState.paxMin = quantity.min;
      formState.paxMax = quantity.max;
      formState.measurementUnit = config.measureUnit;
      formState.businessGroupId = config.appliesTo;

      // Mapear segmentaciones
      const segmentationIds: number[] = [];
      const specifications: any[] = [];

      if (seg.markets?.length > 0) {
        segmentationIds.push(SegmentationEnum.MARKETS);
        specifications.push({
          segmentationId: SegmentationEnum.MARKETS,
          objectIds: seg.markets.map((m: any) => m.code || m.id),
        });
        // Cargar opciones de mercados para el select
        fetchMarkets();
      }

      if (seg.clients?.length > 0) {
        segmentationIds.push(SegmentationEnum.CLIENTS);
        specifications.push({
          segmentationId: SegmentationEnum.CLIENTS,
          objectIds: seg.clients.map((c: any) => c.code || c.id),
        });
        // Cargar opciones de clientes para el select
        fetchClients();
      }

      if (seg.series?.length > 0) {
        segmentationIds.push(SegmentationEnum.SERIES);
        seg.series.forEach((s: string) => {
          specifications.push({
            segmentationId: SegmentationEnum.SERIES,
            objectIds: [],
            inputValue: s,
          });
        });
      }

      if (seg.seasons?.length > 0) {
        segmentationIds.push(SegmentationEnum.SEASONS);
        specifications.push({
          segmentationId: SegmentationEnum.SEASONS,
          objectIds: seg.seasons.map((s: any) => s.id || s.code),
        });
        // Cargar opciones de temporadas para el select
        fetchSeasons();
      }

      if (seg.serviceTypes?.length > 0) {
        segmentationIds.push(SegmentationEnum.SERVICE_TYPES);
        specifications.push({
          segmentationId: SegmentationEnum.SERVICE_TYPES,
          objectIds: seg.serviceTypes.map((s: any) => s.id || s.code),
        });
        // Cargar opciones de tipos de servicios para el select
        fetchServiceTypes();
      }

      formState.policySegmentationIds = segmentationIds;
      formState.segmentationSpecifications = specifications;

      updateFormStateWithAvailableData();
    }
  };

  watch(
    () => policyId.value,
    (newVal) => {
      if (newVal && isEditMode.value) {
        loadPolicyData();
      }
    },
    { immediate: true }
  );

  return {
    isLoading,
    showForm,
    formState,
    formRef,
    formRules,
    businessGroups,
    segmentations,
    serviceTypes,
    specificationOptionsMap,
    loadingButton,
    handleSave,
    handleChangeBusinessGroup,
    handleChangePaxMin,
    handleChangePaxMax,
    handleChangeDateFrom,
    handleChangeDateTo,
    handleDateInputBlur,
    handleDateInputKeydown,
    segmentationSelectRef,
    filterOptionByName,
    disableDateFrom,
    disableDateTo,
    isSelectedSegmentation,
    isSelectedObjectId,
    handleChangeSegmentation,
    getSegmentationLabel,
    isSeriesSegmentation,
    getSpecificationData,
    filterOption,
    isSpecificationSelectLoading,
    handleChangeSpecification,
    handleSelectSegmentation,
    getSpecificationTagCount,
    isFormBasicValid,
    // Measurement unit properties
    measurementUnits,
    showMeasurementUnitSelector,
    paxLabel,
    isAccommodationSupplier,
    isHotel,
    showPolicySegmentation,
  };
}
