import { computed, onUnmounted, ref, watch } from 'vue';
import {
  handleCompleteResponse,
  handleErrorResponse,
} from '@/modules/negotiations/api/responseApi';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierPoliciesService } from '@/modules/negotiations/supplier-new/service/supplier-policies.service';
import { usePolicyStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-store-facade.composable';
import { usePolicyManagerComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-manager.composable';
import { useSupplierViewComposable } from '@/modules/negotiations/supplier-new/composables/supplier-view.composable';
import { usePolicyFormStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-form-store-facade.composable';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { SideBarModulesEnum } from '@/modules/negotiations/supplier-new/enums/side-bar-modules.enum';
import { SubPanelCollapseEnum } from '@/modules/negotiations/supplier-new/enums/sub-panel-collapse.enum';
import { FormModeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/form-mode.enum';
import { useProgressiveFormFlowComposable } from './progressive-form-flow.composable';
import { useDeletePolicyMutation } from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-policies.query';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc);

export function useSupplierPoliciesComposable() {
  const { openPolicyManager } = useSupplierViewComposable();
  const { openRegisteredDetails, openInformationBasic } = usePolicyManagerComposable();

  const {
    supplierId,
    isEditMode,
    getShowFormComponent,
    getLoadingFormComponent,
    getIsEditFormComponent,
    handleIsEditFormSpecific,
    handleShowFormSpecific,
    handleSetActiveItem,
    markItemComplete,
  } = useSupplierGlobalComposable();

  const { isServicesComplete } = useProgressiveFormFlowComposable();

  // Delete mutation usando Vue Query
  const deletePolicyMutation = useDeletePolicyMutation({
    onSuccess: () => {
      handleCompleteResponse(undefined, 'Política eliminada correctamente.');
      fetchPoliciesListData();
    },
    onError: () => {
      handleErrorResponse();
    },
  });

  // Estado para el modal de eliminación
  const isDeleteConfirmModalOpen = ref<boolean>(false);
  const policyIdToDelete = ref<string | null>(null);
  const isDeleting = computed(() => deletePolicyMutation.isPending.value);
  const isCloning = ref<boolean>(false);

  const { setPolicyId, setFormMode } = usePolicyFormStoreFacade();

  const sorterPax = (a: any, b: any) => {
    if (a.pax_min !== b.pax_min) {
      return a.pax_min - b.pax_min;
    }
    return a.pax_max - b.pax_max;
  };

  const columns = ref<any>([
    {
      title: 'Nombre de política',
      dataIndex: 'name',
      width: '22%',
      forSummary: true,
    },
    {
      title: 'Segmentación',
      dataIndex: 'segment',
      width: '12%',
      forSummary: true,
    },
    {
      title: 'Vigencia',
      dataIndex: 'validity',
      width: '15%',
      forSummary: true,
    },
    {
      title: 'Cantidad',
      dataIndex: 'pax',
      width: '10%',
      sorter: sorterPax,
      forSummary: true,
    },
    {
      title: 'Acciones',
      dataIndex: 'actions',
      width: '15%',
      align: 'center',
    },
  ]);

  const { sourceData, isLoading, showForm, reloadList, setReloadList, startLoading, stopLoading } =
    usePolicyStoreFacade();

  const { getPoliciesBySupplier, updateSupplierPoliciesStatus } = useSupplierPoliciesService;

  const fetchPoliciesListData = async () => {
    if (!supplierId.value) {
      console.warn('No supplierId available to fetch policies');
      return null;
    }

    try {
      startLoading();

      const response = await getPoliciesBySupplier(supplierId.value);

      // La API puede retornar un objeto único o un array
      // Si es un objeto único con success, extraemos data
      let policies: any[] = [];
      if (response.success && response.data) {
        // Si data es un array, usarlo directamente; si es objeto único, convertir a array
        policies = Array.isArray(response.data) ? response.data : [response.data];
      } else if (Array.isArray(response)) {
        policies = response;
      }

      setSourceData(policies);
    } catch (error) {
      console.error('Error al cargar las listas de politicas:', error);
      return null;
    } finally {
      stopLoading();
    }
  };

  /**
   * Obtiene el texto de segmentación de una política
   * Basado en configuration.appliesTo y configuration.segmentation
   */
  const getSegmentationText = (policy: any): string => {
    const config = policy.configuration;
    if (!config) return '-';

    const segmentation = config.segmentation;

    // Construir texto basado en segmentación activa
    const segments: string[] = [];

    if (segmentation?.markets?.length > 0) {
      segments.push('Mercado');
    }
    if (segmentation?.clients?.length > 0) {
      segments.push('Cliente');
    }
    if (segmentation?.series?.length > 0) {
      segments.push('Serie');
    }
    if (segmentation?.seasons?.length > 0) {
      segments.push('Temporada');
    }
    if (segmentation?.serviceTypes?.length > 0) {
      segments.push('Tipo de servicio');
    }
    if (segmentation?.parties?.length > 0) {
      segments.push('Partida');
    }

    // Si hay segmentaciones, mostrarlas; si no, mostrar '-'
    return segments.length > 0 ? segments.join(' / ') : '-';
  };

  /**
   * Transforma los datos de la API al formato esperado por la tabla
   */
  const transformPolicyToTableRow = (policy: any) => {
    const config = policy.configuration || {};
    const quantityRange = config.quantityRange || {};
    const validity = config.validity || {};

    return {
      id: policy._id,
      name: policy.name ? policy.name.replace(/:\s*$/, '') : '-',
      segment: getSegmentationText(policy),
      pax_min: quantityRange.min || 0,
      pax_max: quantityRange.max || 0,
      date_from: validity.from || null,
      date_to: validity.to || null,
      status: policy.active,
      // Guardar la data original para usar en reglas
      rules: policy.rules || {},
      configuration: policy.configuration || {},
      // Campos adicionales para compatibilidad
      cancellations: policy.rules?.cancellation || [],
      reconfirmations: policy.rules?.reconfirmation || [],
      released: policy.rules?.released || [],
      _originalData: policy,
    };
  };

  const setSourceData = (policies: any[]) => {
    sourceData.value = [];

    policies.forEach((policy) => {
      const transformedRow = transformPolicyToTableRow(policy);
      sourceData.value.push(transformedRow);
    });

    // Si hay al menos una política guardada, marcar la sección como completa
    if (policies.length > 0) {
      markItemComplete('policies');
    }
  };

  const handleUpdateSupplierPoliciesStatus = async (id: number, status: boolean): Promise<void> => {
    const { success, data } = await updateSupplierPoliciesStatus(id, status);

    if (success) {
      await fetchPoliciesListData();
      handleCompleteResponse(data, 'Política actualizada satisfactoriamente.');
    } else {
      handleErrorResponse();
    }
  };

  const getRowClassName = (record: any, index: number) => {
    return record && index % 2 === 1 ? 'custom-table-row-striped' : '';
  };

  const isEditConfirmModalOpen = ref<boolean>(false);
  const policyIdToEdit = ref<string | null>(null);

  const proceedToEditPolicy = (id: string) => {
    // Actualizar el ítem activo del sidebar
    handleSetActiveItem('supplier', 'supplier-negotiations', 'policies');
    openPolicyManager();
    openRegisteredDetails();
    setFormMode(FormModeEnum.EDIT);
    setPolicyId(id);
  };

  const handleEditPolicy = (id: string) => {
    policyIdToEdit.value = id;
    isEditConfirmModalOpen.value = true;
  };

  const handleConfirmEdit = () => {
    if (policyIdToEdit.value !== null) {
      proceedToEditPolicy(policyIdToEdit.value);
      isEditConfirmModalOpen.value = false;
      policyIdToEdit.value = null;
    }
  };

  const handleCancelEdit = () => {
    isEditConfirmModalOpen.value = false;
    policyIdToEdit.value = null;
  };

  const activePanelItemPolicy = () => {
    handleSetActiveItem(
      SideBarModulesEnum.SUPPLIER,
      SubPanelCollapseEnum.SUPPLIER_NEGOTIATIONS,
      FormComponentEnum.MODULE_POLICIES
    );

    if (sourceData.value.length === 0) {
      handleAddPolicy();
    }
  };

  const openPolicyForm = () => {
    openPolicyManager();
    openInformationBasic();
  };

  const handleAddPolicy = () => {
    // Actualizar el ítem activo del sidebar
    handleSetActiveItem('supplier', 'supplier-negotiations', 'policies');
    openPolicyForm();
    setFormMode(FormModeEnum.CREATE);
  };

  const handleDestroy = (id: string) => {
    policyIdToDelete.value = id;
    isDeleteConfirmModalOpen.value = true;
  };

  const handleConfirmDelete = () => {
    if (policyIdToDelete.value !== null) {
      deletePolicyMutation.mutate(policyIdToDelete.value, {
        onSettled: () => {
          isDeleteConfirmModalOpen.value = false;
          policyIdToDelete.value = null;
        },
      });
    }
  };

  const handleCancelDelete = () => {
    isDeleteConfirmModalOpen.value = false;
    policyIdToDelete.value = null;
  };

  const isCloneConfirmModalOpen = ref<boolean>(false);
  const policyIdToClone = ref<string | null>(null);

  /**
   * Transforma una política del store al formato ClonedPolicyData
   */
  const transformPolicyToClonedData = (policy: any) => {
    const config = policy.configuration || {};
    const validity = config.validity || {};
    const quantity = config.quantityRange || {};
    const seg = config.segmentation || {};

    // Importar el enum de segmentación
    const SegmentationEnum = {
      MARKETS: 1,
      CLIENTS: 2,
      SERIES: 3,
      EVENTS: 4,
      SERVICE_TYPES: 5,
      SEASONS: 6,
    };

    // Mapear segmentaciones
    const segmentationIds: number[] = [];
    const specifications: any[] = [];

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

    return {
      name: policy.name ? `${policy.name} (copia)` : 'Política (copia)',
      businessGroupId: config.appliesTo || null,
      dateFrom: validity.from || null,
      dateTo: validity.to || null,
      paxMin: quantity.min || null,
      paxMax: quantity.max || null,
      measurementUnit: config.measureUnit || null,
      isHotel: config.isHotel || false,
      policySegmentationIds: segmentationIds,
      segmentationSpecifications: specifications,
      rules: policy.rules || {},
      configuration: config,
    };
  };

  /**
   * Nuevo flujo de clone: copia datos internamente y redirige a información básica
   */
  const proceedToClonePolicy = async (id: string) => {
    isCloning.value = true;
    try {
      // Obtener la política del store local
      const policy = sourceData.value.find((p: any) => p.id === id);

      if (!policy) {
        handleErrorResponse('No se pudo encontrar la política seleccionada.');
        isCloneConfirmModalOpen.value = false;
        policyIdToClone.value = null;
        return;
      }

      // Usar _originalData si está disponible para tener toda la información
      const policyData = policy._originalData || policy;

      // Transformar la data de la política al formato ClonedPolicyData
      const clonedData = transformPolicyToClonedData(policyData);

      // Importar las funciones del store
      const { setClonedPolicyData, resetFormState } = usePolicyFormStoreFacade();

      // Guardar la data clonada en el store
      setClonedPolicyData(clonedData);

      // Limpiar el policyId para que sea modo creación (no edición)
      setPolicyId(null);

      // Resetear el formState del store para evitar datos residuales
      resetFormState();

      // Cerrar el modal
      isCloneConfirmModalOpen.value = false;
      policyIdToClone.value = null;

      // Establecer modo CLONE para que los formularios sepan que deben pre-llenar
      setFormMode(FormModeEnum.CLONE);

      // Abrir el policy manager y mostrar información básica
      handleSetActiveItem('supplier', 'supplier-negotiations', 'policies');
      openPolicyManager();
      openInformationBasic();
    } catch (error) {
      console.error('Error al clonar política:', error);
      handleErrorResponse('Ocurrió un error al copiar la política.');
      isCloneConfirmModalOpen.value = false;
      policyIdToClone.value = null;
    } finally {
      isCloning.value = false;
    }
  };

  const handleClone = (id: string) => {
    policyIdToClone.value = id;
    isCloneConfirmModalOpen.value = true;
  };

  const handleConfirmClone = async () => {
    if (policyIdToClone.value !== null) {
      await proceedToClonePolicy(policyIdToClone.value);
    }
  };

  const handleCancelClone = () => {
    isCloneConfirmModalOpen.value = false;
    policyIdToClone.value = null;
  };

  watch(
    () => reloadList.value,
    (value) => {
      if (value) {
        fetchPoliciesListData();
        setReloadList(false);
      }
    }
  );

  const handleShowForm = () => {
    // Actualizar el ítem activo del sidebar
    handleSetActiveItem('supplier', 'supplier-negotiations', 'policies');
    handleIsEditFormSpecific(FormComponentEnum.MODULE_POLICIES, false);
    activePanelItemPolicy();
  };

  const columnsForSummary = computed(() => {
    return columns.value.filter((row: any) => row.forSummary);
  });

  const getSummaryCancellationDescription = (row: any) => {
    return row.cancellationDescriptions.join(', ');
  };

  const resetData = () => {
    sourceData.value = [];
    handleShowFormSpecific(FormComponentEnum.MODULE_POLICIES, false);
    handleIsEditFormSpecific(FormComponentEnum.MODULE_POLICIES, false);
  };

  onUnmounted(() => {
    resetData();
  });

  const formatDate = (date: string) => {
    if (!date) return '';
    // Usar UTC para evitar que la zona horaria local cambie el día
    return dayjs.utc(date).format('DD/MM/YYYY');
  };

  watch(
    () => supplierId.value,
    async (newSupplierId) => {
      if (!newSupplierId) return;

      await fetchPoliciesListData();

      if (sourceData.value.length > 0) {
        handleShowFormSpecific(FormComponentEnum.MODULE_POLICIES, true);
        handleIsEditFormSpecific(FormComponentEnum.MODULE_POLICIES, true);
      }
    },
    { immediate: true }
  );

  return {
    columns,
    sourceData,
    isLoading,
    showForm,
    isEditMode,

    getShowFormComponent,
    getLoadingFormComponent,
    getIsEditFormComponent,
    columnsForSummary,

    handleUpdateSupplierPoliciesStatus,
    getRowClassName,
    handleEditPolicy,
    handleAddPolicy,
    handleDestroy,
    handleShowForm,
    getSummaryCancellationDescription,
    activePanelItemPolicy,
    handleClone,
    formatDate,

    isEditConfirmModalOpen,
    handleConfirmEdit,
    handleCancelEdit,

    isCloneConfirmModalOpen,
    handleConfirmClone,
    handleCancelClone,
    isCloning,

    // Delete modal
    isDeleteConfirmModalOpen,
    handleConfirmDelete,
    handleCancelDelete,
    isDeleting,

    isServicesCompleteTemp: isServicesComplete,
  };
}
