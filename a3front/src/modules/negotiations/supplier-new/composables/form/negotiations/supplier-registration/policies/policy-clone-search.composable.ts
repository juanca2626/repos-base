import { computed, nextTick, reactive, ref, watch } from 'vue';
import { useSupplierPoliciesService } from '@/modules/negotiations/supplier-new/service/supplier-policies.service';
import type {
  PolicyCloneSearchForm,
  PolicyCloneSearchModalEmit,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import {
  filterOption,
  mapItemsToOptions,
} from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';
import { isFilled } from '@/modules/negotiations/suppliers/helpers/field-validation-helper';
import { handleErrorResponse } from '@/modules/negotiations/api/responseApi';

import { usePolicyFormStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-form-store-facade.composable';
import { FormModeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/form-mode.enum';
import { SegmentationEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/segmentation.enum';
import type { ClonedPolicyData } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/policies/supplier-policy-form.store';

export function usePolicyCloneSearchComposable(
  emit: PolicyCloneSearchModalEmit,
  getShowModal?: () => boolean
) {
  const suppliers = ref<SelectOption[]>([]);
  const supplierPolicies = ref<SelectOption[]>([]);
  const isCloning = ref<boolean>(false);

  // Almacena las políticas completas del proveedor seleccionado (incluyendo reglas si están disponibles)
  const loadedPoliciesData = ref<any[]>([]);

  const initialFormData: PolicyCloneSearchForm = {
    supplierId: null,
    supplierPolicyId: null,
  };

  const formState = reactive<PolicyCloneSearchForm>({ ...initialFormData });

  // Servicios
  const { showSupplierPolicyListOptions } = useSupplierPoliciesService;
  const { fetchSupplierListOptions } = useSupplierResourceService;

  // Loading states
  const isLoadingSuppliers = ref<boolean>(false);
  const isLoadingPolicies = ref<boolean>(false);
  const isLoading = computed(() => isLoadingSuppliers.value || isLoadingPolicies.value);

  // const { supplierId } = useSupplierGlobalComposable();
  const { setPolicyId, setFormMode, setClonedPolicyData, resetFormState } =
    usePolicyFormStoreFacade();

  const isFormValid = computed(() => {
    const requiredFields = ['supplierId', 'supplierPolicyId'];
    return requiredFields.every((key) => {
      const value = (formState as any)[key];
      return isFilled(value);
    });
  });

  const isDisabled = computed(() => {
    return isLoading.value || !isFormValid.value || isCloning.value;
  });

  const resetFormStateLocal = () => {
    Object.assign(formState, {
      ...initialFormData,
    });
  };

  const resetSupplierPolicies = () => {
    supplierPolicies.value = [];
    loadedPoliciesData.value = [];
  };

  const resetData = () => {
    resetFormStateLocal();
    resetSupplierPolicies();
  };

  /**
   * Carga las políticas de un proveedor (con sus datos completos)
   * Usa el endpoint /suppliers/:supplierId/policies que devuelve la data para copiar
   */
  const loadPoliciesForSupplier = async (selectedSupplierId: string) => {
    isLoadingPolicies.value = true;
    supplierPolicies.value = [];
    loadedPoliciesData.value = [];

    try {
      console.log('[PolicyCloneSearch] Loading policies for supplier:', selectedSupplierId);
      const response = await showSupplierPolicyListOptions(selectedSupplierId);
      console.log('[PolicyCloneSearch] Response:', response);

      if (response?.success && response?.data && response.data.length > 0) {
        // Guardar la data completa de las políticas
        loadedPoliciesData.value = response.data;

        // Mapear para el select (id y nombre)
        const mappedData = response.data.map((item: any) => ({
          id: item._id || item.id,
          name: item.name || 'Sin nombre',
        }));
        supplierPolicies.value = mapItemsToOptions(mappedData);
        console.log('[PolicyCloneSearch] Loaded policies:', supplierPolicies.value.length);
      }
    } catch (error: any) {
      console.error('[PolicyCloneSearch] Error loading policies:', error);
    } finally {
      isLoadingPolicies.value = false;
    }
  };

  const handleChangeSupplier = () => {
    formState.supplierPolicyId = null;
    resetSupplierPolicies();

    // Cargar políticas del proveedor seleccionado
    if (formState.supplierId) {
      loadPoliciesForSupplier(formState.supplierId);
    }
  };

  const loadData = async () => {
    isLoadingSuppliers.value = true;

    try {
      const response = await fetchSupplierListOptions();

      if (response.success) {
        const mappedData = response.data.map((item: any) => ({
          id: item._id,
          name: item.name,
        }));
        suppliers.value = mapItemsToOptions(mappedData);
      }
    } finally {
      isLoadingSuppliers.value = false;
    }
  };

  const handleCancel = () => {
    resetData();
    emit('update:showModal', false);
  };

  /**
   * Transforma la data de la política al formato ClonedPolicyData
   * Soporta tanto el formato del endpoint de listado como otros formatos
   */
  const transformPolicyToClonedData = (policy: any): ClonedPolicyData => {
    // Intentar extraer datos de diferentes estructuras posibles
    const config = policy.configuration || {};
    const validity = config.validity || {};
    const quantity = config.quantityRange || {};
    const seg = config.segmentation || {};

    // Mapear segmentaciones si existen
    const segmentationIds: number[] = [];
    const specifications: any[] = [];

    // Intentar desde policy_segmentations (formato anterior)
    if (policy.policy_segmentations?.length > 0) {
      policy.policy_segmentations.forEach((ps: any) => {
        segmentationIds.push(ps.segmentation_id);
        specifications.push({
          segmentationId: ps.segmentation_id,
          objectIds: ps.object_ids || [],
          inputValue: ps.specification_name || undefined,
        });
      });
    }
    // Intentar desde configuration.segmentation (nuevo formato)
    else if (seg) {
      if (seg.markets?.length > 0) {
        segmentationIds.push(SegmentationEnum.MARKETS);
        specifications.push({
          segmentationId: SegmentationEnum.MARKETS,
          objectIds: seg.markets.map((m: any) => m.code || m.id),
        });
      }
      if (seg.clients?.length > 0) {
        segmentationIds.push(SegmentationEnum.CLIENTS);
        specifications.push({
          segmentationId: SegmentationEnum.CLIENTS,
          objectIds: seg.clients.map((c: any) => c.code || c.id),
        });
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
      }
      if (seg.serviceTypes?.length > 0) {
        segmentationIds.push(SegmentationEnum.SERVICE_TYPES);
        specifications.push({
          segmentationId: SegmentationEnum.SERVICE_TYPES,
          objectIds: seg.serviceTypes.map((s: any) => s.id || s.code),
        });
      }
    }

    return {
      name: policy.name ? `${policy.name} (copia)` : 'Política (copia)',
      businessGroupId: policy.business_group_id || config.appliesTo || null,
      dateFrom: policy.date_from || validity.from || null,
      dateTo: policy.date_to || validity.to || null,
      paxMin: policy.pax_min || quantity.min || null,
      paxMax: policy.pax_max || quantity.max || null,
      measurementUnit: policy.measure_unit || config.measureUnit || null,
      isHotel: policy.is_hotel || config.isHotel || false,
      policySegmentationIds: segmentationIds,
      segmentationSpecifications: specifications,
      rules: policy.rules || {},
      configuration: config,
    };
  };

  /**
   * Copia la política seleccionada usando los datos ya cargados
   * NO llama al backend - usa los datos de loadedPoliciesData
   */
  const handleClone = async () => {
    if (!formState.supplierPolicyId) return;

    try {
      isCloning.value = true;
      console.log('[PolicyCloneSearch] Copying policy:', formState.supplierPolicyId);

      // Buscar la política en los datos YA CARGADOS (NO llamar al backend)
      const policy = loadedPoliciesData.value.find(
        (p: any) => (p._id || p.id) === formState.supplierPolicyId
      );

      if (!policy) {
        handleErrorResponse('No se encontró la política en los datos cargados.');
        return;
      }

      console.log('[PolicyCloneSearch] Found policy data:', policy);

      // Transformar la data al formato ClonedPolicyData
      const clonedData = transformPolicyToClonedData(policy);
      console.log('[PolicyCloneSearch] Transformed data:', clonedData);

      // Limpiar el policyId para que sea modo creación (no edición)
      setPolicyId(null);

      // Resetear el formState del store PRIMERO
      resetFormState();

      // Esperar a que Vue actualice
      await nextTick();

      // Establecer modo CLONE
      setFormMode(FormModeEnum.CLONE);

      // Guardar la data clonada en el store
      setClonedPolicyData(clonedData);

      // Cerrar el drawer
      handleCancel();
    } catch (error: any) {
      console.error('[PolicyCloneSearch] Error copying policy:', error);
      handleErrorResponse('Ocurrió un error al copiar la política.');
    } finally {
      isCloning.value = false;
    }
  };

  // Función para inicializar el modal
  const initializeModal = async () => {
    // Si solo hay un proveedor, preseleccionarlo automáticamente
    if (suppliers.value.length === 1 && !formState.supplierId) {
      formState.supplierId = String(suppliers.value[0].value);
    }

    // Si hay un proveedor seleccionado, cargar sus políticas
    if (formState.supplierId && supplierPolicies.value.length === 0) {
      await loadPoliciesForSupplier(formState.supplierId);
    }
  };

  // Watcher para cuando se abre el modal
  if (getShowModal) {
    watch(
      () => getShowModal(),
      async (isOpen) => {
        if (isOpen) {
          // Asegurar que los proveedores estén cargados
          if (suppliers.value.length === 0) {
            await loadData();
          }

          await initializeModal();
        } else {
          // Limpiar cuando se cierra el modal
          resetData();
        }
      },
      { immediate: true }
    );
  }

  return {
    isLoading,
    isCloning,
    formState,
    suppliers,
    supplierPolicies,
    isDisabled,
    handleCancel,
    handleClone,
    filterOption,
    handleChangeSupplier,
  };
}
