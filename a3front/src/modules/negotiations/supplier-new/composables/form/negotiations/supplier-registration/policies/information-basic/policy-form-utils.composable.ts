import dayjs, { Dayjs } from 'dayjs';
import type { Rule } from 'ant-design-vue/es/form';
import { SegmentationEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/segmentation.enum';
import type { SupplierPolicyForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import {
  isFitsBusinessGroup,
  isGroupsBusinessGroup,
  isSeriesSegmentation,
} from '@/modules/negotiations/supplier-new/helpers/supplier-registration/policies/policy-form-helper';
import { usePolicyFormStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-form-store-facade.composable';
import { computed } from 'vue';
import { isEditFormMode } from '@/modules/negotiations/supplier-new/helpers/supplier-registration/policies/form-mode-helper';
import { useSupplierGlobalStoreFacade } from '@/modules/negotiations/supplier-new/composables/supplier-global-store-facade.composable';

export function usePolicyFormUtilsComposable(formState: SupplierPolicyForm) {
  const { supplierId } = useSupplierGlobalStoreFacade();

  const { formMode } = usePolicyFormStoreFacade();

  const isEditMode = computed(() => isEditFormMode(formMode.value));

  const formRules: Record<string, Rule[]> = {
    name: [{ required: true, message: 'Ingresa nombre completo', trigger: 'change' }],
    dateFrom: [{ required: true, message: 'Selecciona la fecha de inicio', trigger: 'change' }],
    dateTo: [{ required: true, message: 'Selecciona la fecha de fin', trigger: 'change' }],
    paxMin: [
      { required: true, message: 'Ingresa el número mínimo de personas', trigger: 'change' },
    ],
    paxMax: [
      { required: true, message: 'Ingresa el número máximo de personas', trigger: 'change' },
    ],
    businessGroupId: [
      { required: true, message: 'Seleccionar el campo aplica para', trigger: 'change' },
    ],
    // policySegmentationIds: [
    //   { required: true, message: 'Selecciona la segmentación de política', trigger: 'change' },
    // ],
    measurementUnit: [
      { required: true, message: 'Selecciona la unidad de medida', trigger: 'change' },
    ],
    objectIds: [{ required: true, message: 'Selecciona la especificación', trigger: 'change' }],
    inputValue: [
      { required: true, message: 'Ingresa el valor de la especificación', trigger: 'change' },
    ],
  };

  const disableDateFrom = (current: Dayjs) => {
    if (!formState.dateTo) return false;

    return current && current.isAfter(dayjs(formState.dateTo), 'day');
  };

  const disableDateTo = (current: Dayjs) => {
    if (!formState.dateFrom) return false;

    return current && current.isBefore(dayjs(formState.dateFrom), 'day');
  };

  const getSpecificationTagCount = (segmentationId: number | string) => {
    const data: Record<string | number, number> = {
      [SegmentationEnum.MARKETS]: 2,
      [SegmentationEnum.CLIENTS]: 1,
      [SegmentationEnum.EVENTS]: 2,
      [SegmentationEnum.SERVICE_TYPES]: 2,
      [SegmentationEnum.SEASONS]: 2,
      markets: 2,
      clients: 1,
      events: 2,
      serviceTypes: 2,
      'service-types': 2,
      seasons: 2,
    };

    return data[segmentationId] || 0;
  };

  const setDefaultPax = () => {
    if (isFitsBusinessGroup(formState.businessGroupId)) {
      formState.paxMin = 1;
      formState.paxMax = 15;
    } else if (isGroupsBusinessGroup(formState.businessGroupId)) {
      formState.paxMin = 1;
      formState.paxMax = 99;
    }
  };

  const isSelectedSegmentation = (value: number): boolean => {
    return formState.policySegmentationIds.includes(value);
  };

  const isSelectedObjectId = (value: number, index: number): boolean => {
    return formState.segmentationSpecifications[index].objectIds.includes(value);
  };

  const sortSpecifications = () => {
    formState.segmentationSpecifications.sort((a, b) => a.segmentationId - b.segmentationId);
  };

  const buildRequestData = (
    allMarkets: any[] = [],
    allClients: any[] = [],
    allServiceTypes: any[] = [],
    isHotel: boolean = false,
    existingSegmentation: any = null,
    allSeasons: any[] = []
  ) => {
    // Obtener markets seleccionados con code y name
    const selectedMarkets = formState.segmentationSpecifications
      .filter(
        (spec) =>
          spec.segmentationId === SegmentationEnum.MARKETS ||
          (spec.segmentationId as any) === 'markets'
      )
      .flatMap((spec) =>
        spec.objectIds.map((id) => {
          const market = allMarkets.find((m) => m.code === id || m.id === id);
          return market ? { code: market.code, name: market.name } : null;
        })
      )
      .filter(Boolean);

    // Obtener clients seleccionados con code y name
    const selectedClients = formState.segmentationSpecifications
      .filter(
        (spec) =>
          spec.segmentationId === SegmentationEnum.CLIENTS ||
          (spec.segmentationId as any) === 'clients'
      )
      .flatMap((spec) =>
        spec.objectIds.map((id) => {
          const client = allClients.find((c) => c.code === id || c.id === id);
          return client ? { code: client.code, name: client.name } : null;
        })
      )
      .filter(Boolean);

    // Obtener series (nombre como string)
    const selectedSeries = formState.segmentationSpecifications
      .filter((spec) => isSeriesSegmentation(spec.segmentationId))
      .map((spec) => spec.inputValue || '')
      .filter(Boolean);

    // Obtener serviceTypes seleccionados con code y name
    const selectedServiceTypes = formState.segmentationSpecifications
      .filter(
        (spec) =>
          spec.segmentationId === SegmentationEnum.SERVICE_TYPES ||
          (spec.segmentationId as any) === 'serviceTypes' ||
          (spec.segmentationId as any) === 'service-types'
      )
      .flatMap((spec) =>
        spec.objectIds.map((id) => {
          const serviceType = allServiceTypes.find((st) => st.code === id || st.id === id);
          return serviceType ? { code: serviceType.code, name: serviceType.name } : null;
        })
      )
      .filter(Boolean);

    // Obtener seasons seleccionados con code y name
    const selectedSeasons = formState.segmentationSpecifications
      .filter(
        (spec) =>
          spec.segmentationId === SegmentationEnum.SEASONS ||
          (spec.segmentationId as any) === 'seasons'
      )
      .flatMap((spec) =>
        spec.objectIds.map((id) => {
          const season = allSeasons.find((s) => s.code === id || s.id === id);
          return season ? { code: season.code, name: season.name } : null;
        })
      )
      .filter(Boolean);

    // Construir segmentación basada en las segmentaciones seleccionadas en el multiselect
    const isMarketSelected =
      formState.policySegmentationIds.includes(SegmentationEnum.MARKETS) ||
      formState.policySegmentationIds.includes('markets' as any);
    const isClientSelected =
      formState.policySegmentationIds.includes(SegmentationEnum.CLIENTS) ||
      formState.policySegmentationIds.includes('clients' as any);
    const isSeriesSelected = formState.policySegmentationIds.some((id) => isSeriesSegmentation(id));
    const isServiceTypesSelected =
      formState.policySegmentationIds.includes(SegmentationEnum.SERVICE_TYPES) ||
      formState.policySegmentationIds.includes('serviceTypes' as any) ||
      formState.policySegmentationIds.includes('service-types' as any);
    const isSeasonsSelected =
      formState.policySegmentationIds.includes(SegmentationEnum.SEASONS) ||
      formState.policySegmentationIds.includes('seasons' as any);

    const segmentation = {
      markets: isMarketSelected
        ? selectedMarkets.length > 0
          ? selectedMarkets
          : existingSegmentation?.markets || []
        : [],
      clients: isClientSelected
        ? selectedClients.length > 0
          ? selectedClients
          : existingSegmentation?.clients || []
        : [],
      series: isSeriesSelected
        ? selectedSeries.length > 0
          ? selectedSeries
          : existingSegmentation?.series || []
        : [],
      parties: existingSegmentation?.parties || [],
      seasons: isSeasonsSelected
        ? selectedSeasons.length > 0
          ? selectedSeasons
          : existingSegmentation?.seasons || []
        : [],
      serviceTypes: isServiceTypesSelected
        ? selectedServiceTypes.length > 0
          ? selectedServiceTypes
          : existingSegmentation?.serviceTypes || []
        : [],
    };

    return {
      // supplier_id: '691d1e84d5b2554126c7df51',
      supplier_id: supplierId.value,
      name: formState.name,
      configuration: {
        measureUnit: isHotel ? formState.measurementUnit : null,
        appliesTo: formState.businessGroupId,
        quantityRange: {
          min: formState.paxMin,
          max: formState.paxMax,
        },
        validity: {
          from: formState.dateFrom,
          to: formState.dateTo,
        },
        segmentation,
      },
    };
  };

  const cleanSpecifications = () => {
    formState.segmentationSpecifications = formState.segmentationSpecifications.filter((item) => {
      return item.segmentationId && formState.policySegmentationIds.includes(item.segmentationId);
    });
  };

  const addSpecification = () => {
    formState.policySegmentationIds.forEach((id) => {
      const exists = formState.segmentationSpecifications.some(
        (item) => item.segmentationId === id
      );

      if (!exists) {
        formState.segmentationSpecifications.push({
          segmentationId: id,
          objectIds: [],
        });
      }
    });
  };

  return {
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
  };
}
