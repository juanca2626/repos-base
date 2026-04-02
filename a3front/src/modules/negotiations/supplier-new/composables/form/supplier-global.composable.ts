import { storeToRefs } from 'pinia';
import { computed, h, onBeforeMount, onUnmounted, nextTick, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useQueryClient } from '@tanstack/vue-query';
import { useSupplierGlobalStore } from '@/modules/negotiations/supplier-new/store/supplier-global.store';
import IconUserPending from '@/modules/negotiations/supplier-new/icons/icon-user-pending.vue';
import IconUserComplete from '@/modules/negotiations/supplier-new/icons/icon-user-complete.vue';
import { SideBarModulesEnum } from '@/modules/negotiations/supplier-new/enums/side-bar-modules.enum';
import { SubPanelCollapseEnum } from '@/modules/negotiations/supplier-new/enums/sub-panel-collapse.enum';
import { useRouteIdComposable } from '@/modules/negotiations/supplier-new/composables/route-id.composable';
import { size } from 'lodash';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import {
  extractPanelModules,
  extractModuleOptions,
} from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-modules.query';
import { getSupplierEditQueryKey } from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-complete.query';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/supplier-classification.store';

// 🔹 Map global para trackear qué rutas ya se inicializaron
const loadedRoutes = new Map<string, Promise<void>>();

export function useSupplierGlobalComposable() {
  const supplierGlobalStore = useSupplierGlobalStore();
  const queryClient = useQueryClient();
  const { id, idRef } = useRouteIdComposable();

  const {
    supplierId,
    supplier,
    supplierPaymentId,
    supplierPayment,
    supplierTaxConditionId,
    supplierTaxCondition,
    supplierTributaryInformationId,
    supplierBankInformationId,
    supplierBankInformation,
    countryId,
    cityId,
    cityName,
    isEditModeState,
    showSubForm,
    activeCollapseKey,
    activeSideBarCollapseKey,
    activeSubSideBarCollapseKey,
    activeSideBarOptionKey,
    stepperCurrent,
    stepperOption,
    showFormComponent,
    panelSideBar,
    optionsForm,
    showModalCode,
    subClassificationSupplierId,
    classifications,
    isSupplierLoaded,
    isPanelSideBarLoaded,
    menuPanelKey,
    menuSubPanelKey,
    menuItemKey,
    sidebarScrollRequestKey,
    sidebarScrollRequestNonce,
  } = storeToRefs(supplierGlobalStore);

  // NO crear instancias de queries aquí, usar refetch directo cuando se necesite
  // Esto evita crear múltiples instancias del query en cada componente

  const updateFormComponentState = (key: string, partialState: Record<string, any>) => {
    if (!showFormComponent.value[key]) {
      showFormComponent.value[key] = {};
    }
    showFormComponent.value[key] = {
      ...showFormComponent.value[key],
      ...partialState,
    };
  };

  const handleShowFormSpecific = (key: string, show = false) =>
    updateFormComponentState(key, { showForm: show });

  const handleIsEditFormSpecific = (key: string, state = false) =>
    updateFormComponentState(key, { isEditForm: state });

  const handleLoadingFormSpecific = (key: string, state = false) =>
    updateFormComponentState(key, { loading: state });

  const handleLoadingButtonSpecific = (key: string, state = false) =>
    updateFormComponentState(key, { loadingButton: state });

  const handleDisabledSpecific = (key: string, state = false) =>
    updateFormComponentState(key, { disabled: state });

  const handleSavedFormSpecific = (key: string, state = false) =>
    updateFormComponentState(key, { saved: state });

  const handleProgressFormSpecific = (key: string, completed: boolean, percent: number) =>
    updateFormComponentState(key, { completed, percent });

  const markFormComponentComplete = (key: FormComponentEnum) => {
    handleSavedFormSpecific(key, true);
    handleIsEditFormSpecific(key, true);
    handleShowFormSpecific(key, true);
  };

  const markItemComplete = (itemKey: string) => {
    supplierGlobalStore.markItemComplete(itemKey);
  };

  const handleSetActiveItem = (panelKey: string, subPanelKey = '', itemKey = '', state = false) => {
    if (!panelSideBar.value || !Array.isArray(panelSideBar.value)) return;

    panelSideBar.value.forEach((panel) => {
      if (panel.subPanels && Array.isArray(panel.subPanels)) {
        panel.subPanels.forEach((subPanel) => {
          if (subPanel.items && Array.isArray(subPanel.items)) {
            subPanel.items.forEach((item) => {
              item.isActive =
                panel.key === panelKey && subPanel.key === subPanelKey
                  ? item.key === itemKey
                  : state;
            });
          }
        });
      }
    });
  };

  const handleChangeCollapseKey = (key = 0) => {
    const selectedStep = stepperOption.value?.[key];

    if (!selectedStep || selectedStep.disabled) {
      return;
    }

    const steps = ['negotiations', 'treasury', 'accounting', 'supplier-management'];
    const sideBar = [
      SubPanelCollapseEnum.SUPPLIER_NEGOTIATIONS,
      SubPanelCollapseEnum.SUPPLIER_TREASURY,
      SubPanelCollapseEnum.SUPPLIER_ACCOUNTING,
      undefined,
    ];

    if (!steps[key] || !sideBar[key]) {
      return;
    }

    stepperCurrent.value = key;
    activeCollapseKey.value = [steps[key]];
    activeSubSideBarCollapseKey.value = [sideBar[key]];
  };

  const handleChangeActiveSideBarCollapseKey = (
    panelKey: string,
    subPanelKey?: string,
    itemKey?: string
  ) => {
    const isSameActiveItem =
      menuPanelKey.value === panelKey &&
      menuSubPanelKey.value === (subPanelKey || null) &&
      menuItemKey.value === (itemKey || null);

    if (isSameActiveItem) {
      // Mismo ítem: solo re-disparar el scroll sin cambiar estado
      if (itemKey !== undefined) {
        sidebarScrollRequestKey.value = itemKey || null;
        sidebarScrollRequestNonce.value += 1;
      }
      return;
    }

    activeSideBarCollapseKey.value = [panelKey];
    menuPanelKey.value = panelKey;
    menuSubPanelKey.value = subPanelKey || null;
    menuItemKey.value = itemKey || null;
    if (itemKey !== undefined) {
      activeSideBarOptionKey.value =
        panelKey === SideBarModulesEnum.SUPPLIER
          ? itemKey || FormComponentEnum.CLASSIFICATION_SUPPLIER
          : itemKey || FormComponentEnum.MODULE_SERVICES;

      sidebarScrollRequestKey.value = itemKey || null;
      sidebarScrollRequestNonce.value += 1;
    }
    handleSetActiveItem(panelKey, subPanelKey, itemKey);
  };

  const handleChangeGroupSideBarCollapseKey = (panelKey: string, subPanelKey = '') => {
    const mapKey = panelKey === SideBarModulesEnum.SUPPLIER ? 'SUPPLIER' : 'MODULES';
    const sideBarMap = {
      [`${mapKey}_NEGOTIATIONS`]: 'negotiations',
      [`${mapKey}_TREASURY`]: 'treasury',
      [`${mapKey}_ACCOUNTING`]: 'accounting',
    } as const;
    const stepperIndexMap = {
      [`${mapKey}_NEGOTIATIONS`]: 0,
      [`${mapKey}_TREASURY`]: 1,
      [`${mapKey}_ACCOUNTING`]: 2,
    } as const;
    if (subPanelKey in sideBarMap) {
      stepperCurrent.value = stepperIndexMap[subPanelKey as keyof typeof stepperIndexMap];
      activeCollapseKey.value = [sideBarMap[subPanelKey as keyof typeof sideBarMap]];
      activeSubSideBarCollapseKey.value = [subPanelKey];
    }
  };

  const handleShowModalCode = (state: boolean) => (showModalCode.value = state);

  const getShowFormComponent = computed(
    () => (value: string) => showFormComponent.value[value]?.showForm || false
  );
  const getIsEditFormComponent = computed(
    () => (value: string) => showFormComponent.value[value]?.isEditForm || false
  );
  const getLoadingFormComponent = computed(
    () => (value: string) => showFormComponent.value[value]?.loading || false
  );
  const getLoadingButtonComponent = computed(
    () => (value: string) => showFormComponent.value[value]?.loadingButton || false
  );
  const getDisabledComponent = computed(
    () => (value: string) => showFormComponent.value[value]?.disabled || false
  );
  const getSavedFormComponent = computed(
    () => (value: string) => showFormComponent.value[value]?.saved || false
  );
  const getCompletedFormComponent = computed(
    () => (values: string[]) => values.every((v) => showFormComponent.value[v]?.completed || false)
  );
  const getPercentFormComponent = computed(
    () => (values: string[]) =>
      values.reduce((acc, v) => acc + (showFormComponent.value[v]?.percent || 0), 0)
  );
  const getRenderFormComponent = computed(
    () => (value: string, allow: string[], defaultRender: string) =>
      showFormComponent.value[allow.includes(value) ? value : defaultRender]?.render || null
  );
  const getProgressCountFormComponent = computed(() => (key: any, type: any) => {
    const module = isSupplierFormActive.value ? 'supplier' : 'modules';
    return optionsForm.value[module]?.[key]?.[`${module}-${type}`];
  });

  const isSupplierFormActive = computed(() =>
    activeSideBarCollapseKey.value?.includes(SideBarModulesEnum.SUPPLIER)
  );
  const isEditMode = computed(() => !!isEditModeState.value);
  const hasClassifications = computed(
    () => Array.isArray(classifications.value) && classifications.value.length > 0
  );
  const hasSingleClassification = computed(
    () => Array.isArray(classifications.value) && size(classifications.value) === 1
  );
  const openNextSection = (component: FormComponentEnum) => handleShowFormSpecific(component, true);

  const isClassificationVisible = computed(() => {
    return true;
  });

  const isGeneralInformationVisible = computed(() => {
    if (isEditMode.value) return true;

    if (!panelSideBar.value || !Array.isArray(panelSideBar.value)) {
      return false;
    }

    let isComplete = false;
    panelSideBar.value.forEach((panel) => {
      panel.subPanels?.forEach((subPanel: any) => {
        subPanel.items?.forEach((item: any) => {
          if (item.key === 'classification' && item.isComplete) {
            isComplete = true;
          }
        });
      });
    });

    return isComplete;
  });

  const isLocationVisible = computed(() => {
    if (isEditMode.value) return true;

    if (!panelSideBar.value || !Array.isArray(panelSideBar.value)) {
      return false;
    }

    let isComplete = false;
    panelSideBar.value.forEach((panel) => {
      panel.subPanels?.forEach((subPanel: any) => {
        subPanel.items?.forEach((item: any) => {
          if (item.key === 'general_information' && item.isComplete) {
            isComplete = true;
          }
        });
      });
    });

    return isComplete;
  });

  const isContactsVisible = computed(() => {
    if (isEditMode.value) return true;

    if (!panelSideBar.value || !Array.isArray(panelSideBar.value)) {
      return false;
    }

    let isComplete = false;
    panelSideBar.value.forEach((panel) => {
      panel.subPanels?.forEach((subPanel: any) => {
        subPanel.items?.forEach((item: any) => {
          if (item.key === 'location' && item.isComplete) {
            isComplete = true;
          }
        });
      });
    });

    return isComplete;
  });

  const isCommercialInformationVisible = computed(() => {
    if (isEditMode.value) return true;

    if (!panelSideBar.value || !Array.isArray(panelSideBar.value)) {
      return false;
    }

    let isComplete = false;
    panelSideBar.value.forEach((panel) => {
      panel.subPanels?.forEach((subPanel: any) => {
        subPanel.items?.forEach((item: any) => {
          if (item.key === 'contacts' && item.isComplete) {
            isComplete = true;
          }
        });
      });
    });

    return isComplete;
  });

  const isServicesVisible = computed(() => {
    if (isEditMode.value) return true;

    if (!panelSideBar.value || !Array.isArray(panelSideBar.value)) {
      return false;
    }

    let isComplete = false;
    panelSideBar.value.forEach((panel) => {
      panel.subPanels?.forEach((subPanel: any) => {
        subPanel.items?.forEach((item: any) => {
          if (item.key === 'commercial_information' && item.isComplete) {
            isComplete = true;
          }
        });
      });
    });

    return isComplete;
  });

  const isPoliciesVisible = computed(() => {
    if (isEditMode.value) return true;

    // Fallback: verificar si services fue guardado en showFormComponent
    if (showFormComponent.value[FormComponentEnum.MODULE_SERVICES]?.saved) {
      return true;
    }

    if (!panelSideBar.value || !Array.isArray(panelSideBar.value)) {
      return false;
    }

    let isComplete = false;
    panelSideBar.value.forEach((panel) => {
      panel.subPanels?.forEach((subPanel: any) => {
        subPanel.items?.forEach((item: any) => {
          if (item.key === 'services' && item.isComplete) {
            isComplete = true;
          }
        });
      });
    });

    return isComplete;
  });

  const initializeFormComponents = () => {
    if (!showFormComponent.value[FormComponentEnum.CLASSIFICATION_SUPPLIER]) {
      showFormComponent.value[FormComponentEnum.CLASSIFICATION_SUPPLIER] = {
        showForm: true,
        isEditForm: false,
        saved: false,
        loading: false,
        loadingButton: false,
        disabled: false,
        completed: false,
        percent: 0,
      };
    }
  };

  const resetAllComponents = () => {
    isSupplierLoaded.value = false;
    isPanelSideBarLoaded.value = false;
    isEditModeState.value = false;
    showSubForm.value = false;

    supplier.value = {};
    supplierId.value = undefined;
    classifications.value = [];
    subClassificationSupplierId.value = undefined;

    supplierPaymentId.value = undefined;
    supplierPayment.value = {};
    supplierTaxConditionId.value = undefined;
    supplierTaxCondition.value = {};
    supplierTributaryInformationId.value = undefined;
    supplierBankInformationId.value = undefined;
    supplierBankInformation.value = {};

    showFormComponent.value = {};

    if (!activeCollapseKey.value?.length) {
      activeCollapseKey.value = ['negotiations'];
    }
    if (!activeSideBarCollapseKey.value?.length) {
      activeSideBarCollapseKey.value = [SubPanelCollapseEnum.SUPPLIER_NEGOTIATIONS];
    }

    // activeSideBarOptionKey ya no tiene fallback a 'services' - se establece solo al editar una sección

    stepperCurrent.value = 0;

    initializeFormComponents();

    menuPanelKey.value = 'supplier';
    menuSubPanelKey.value = '"supplier-negotiations"';
    menuItemKey.value = 'classification';

    if (panelSideBar.value?.length) {
      panelSideBar.value = panelSideBar.value.map((panel) => ({
        ...panel,
        subPanels:
          panel.subPanels?.map((subPanel) => ({
            ...subPanel,
            items:
              subPanel.items?.map((item) => ({
                ...item,
                isActive: false,
                isComplete: false,
              })) || [],
          })) || [],
      }));
    }

    // Resetear el store de clasificación para evitar que datos de un proveedor
    // editado previamente aparezcan al crear un nuevo proveedor
    useSupplierClassificationStore().resetStore();
  };

  const updateStepperOptions = () => {
    const module = isSupplierFormActive.value ? 'supplier' : 'modules';
    const response = optionsForm.value[module];

    if (response && stepperOption.value?.length) {
      stepperOption.value = stepperOption.value.map((item) => ({
        ...item,
        icon:
          response.progress?.[`${module}-${item.key}`] == 100
            ? h(IconUserComplete)
            : h(IconUserPending),
        // Mantener descripción por defecto si el backend no provee una
        description: response.status?.[`${module}-${item.key}`] || item.description,
      }));
    }
  };

  const loadSupplierData = async () => {
    try {
      supplierId.value = id;

      if (!subClassificationSupplierId.value) {
        subClassificationSupplierId.value = 1;
      }

      await nextTick();

      // Hacer la llamada directa al servicio en lugar de usar el query
      const response = await useSupplierService.showModules({
        supplier_id: id,
        supplier_sub_classification_id: subClassificationSupplierId.value,
      });

      if (response) {
        panelSideBar.value = extractPanelModules(response, panelSideBar.value);
        optionsForm.value = extractModuleOptions(response);
      }

      await nextTick();
      updateStepperOptions();
    } catch (error) {
      console.error('Error:', error);
    }
  };

  const loadEmptyModules = async () => {
    try {
      // Hacer la llamada directa al servicio en lugar de usar el query
      const response = await useSupplierService.showModules({
        supplier_id: null,
        supplier_sub_classification_id: null,
      });

      if (response) {
        const panels = extractPanelModules(response);

        panelSideBar.value = panels.map((panel) => ({
          ...panel,
          progress: 0,
          subPanels:
            panel.subPanels?.map((subPanel: any) => ({
              ...subPanel,
              complete: 0,
              items:
                subPanel.items?.map((item: any) => ({
                  ...item,
                  isActive: item.key === 'classification',
                  isComplete: false,
                })) || [],
            })) || [],
        }));

        optionsForm.value = extractModuleOptions(response);

        // En modo registro, activar inicialmente la sección de clasificación
        activeSideBarOptionKey.value = 'classification';
      }
    } catch (error) {
      throw error;
    }
  };

  const route = useRoute();

  onBeforeMount(async () => {
    const isFormRoute = route.path.includes('/register') || route.path.includes('/edit');

    if (!isFormRoute) {
      return;
    }

    // 🔹 Crear key única para esta ruta
    const routeKey = route.fullPath;

    // 🔹 Si ya existe una carga en progreso para esta ruta, esperarla y salir
    if (loadedRoutes.has(routeKey)) {
      await loadedRoutes.get(routeKey);
      return;
    }

    // 🔹 Crear promesa de carga y guardarla
    const loadingPromise = (async () => {
      try {
        initializeFormComponents();

        if (id) {
          supplierId.value = id;
          isEditModeState.value = true;
          await loadSupplierData();
          isSupplierLoaded.value = true;
        } else if (!isPanelSideBarLoaded.value) {
          await loadEmptyModules();
          isPanelSideBarLoaded.value = true;
        }
      } catch (error) {
        console.error(error);
        isSupplierLoaded.value = false;
        isPanelSideBarLoaded.value = false;
        loadedRoutes.delete(routeKey);
      }
    })();

    loadedRoutes.set(routeKey, loadingPromise);
    await loadingPromise;
  });

  onUnmounted(() => {
    const isFormRoute = route.path.includes('/register') || route.path.includes('/edit');
    if (isFormRoute) {
      loadedRoutes.delete(route.fullPath);
    }
  });

  watch(
    () => optionsForm.value,
    (newValue) => {
      try {
        if (!newValue) return;
        updateStepperOptions();
      } catch (error) {
        console.error(error);
      }
    },
    { deep: true, immediate: true }
  );

  watch(
    idRef,
    async (newId, oldId) => {
      // 🔹 Limpiar el Map cuando cambia la ruta
      loadedRoutes.clear();

      if (oldId !== undefined && newId === undefined) {
        if (panelSideBar.value?.length) {
          panelSideBar.value = panelSideBar.value.map((panel) => ({
            ...panel,
            progress: 0,
            subPanels:
              panel.subPanels?.map((subPanel) => ({
                ...subPanel,
                complete: 0,
                items:
                  subPanel.items?.map((item) => ({
                    ...item,
                    isActive: item.key === 'classification',
                    isComplete: false,
                  })) || [],
              })) || [],
          }));
        }

        isEditModeState.value = false;
        supplierId.value = undefined;
        isSupplierLoaded.value = false;
        isPanelSideBarLoaded.value = false;

        showFormComponent.value = {};

        supplier.value = {};
        supplierPayment.value = {};
        supplierTaxCondition.value = {};
        supplierBankInformation.value = {};
        classifications.value = [];
        subClassificationSupplierId.value = undefined;

        // Resetear el store de clasificación para evitar que datos de un proveedor
        // editado previamente aparezcan al crear un nuevo proveedor
        useSupplierClassificationStore().resetStore();

        await loadEmptyModules();
        isPanelSideBarLoaded.value = true;

        initializeFormComponents();
      } else if (newId !== undefined && oldId === undefined) {
        supplierId.value = newId;
        isEditModeState.value = true;
        await loadSupplierData();
        isSupplierLoaded.value = true;
      } else if (newId !== undefined && oldId !== undefined && newId !== oldId) {
        // Remove cache for BOTH old and new supplier IDs.
        // Removing the old prevents bleed-through; removing the new forces a fresh
        // HTTP fetch even if this ID was visited before (staleTime would otherwise
        // serve the cached response instead of the latest server data).
        queryClient.removeQueries({ queryKey: getSupplierEditQueryKey(oldId) });
        queryClient.removeQueries({ queryKey: getSupplierEditQueryKey(newId) });

        // Reset Pinia state from previous supplier
        isSupplierLoaded.value = false;
        showFormComponent.value = {};
        supplier.value = {};
        supplierPayment.value = {};
        supplierPaymentId.value = undefined;
        supplierTaxCondition.value = {};
        supplierTaxConditionId.value = undefined;
        supplierTributaryInformationId.value = undefined;
        supplierBankInformation.value = {};
        supplierBankInformationId.value = undefined;
        classifications.value = [];
        subClassificationSupplierId.value = undefined;
        useSupplierClassificationStore().resetStore();

        supplierId.value = newId;
        isEditModeState.value = true;
        await loadSupplierData();
        isSupplierLoaded.value = true;
      }
    },
    { immediate: false }
  );

  return {
    supplierId,
    supplier,
    supplierPaymentId,
    supplierPayment,
    supplierTaxConditionId,
    supplierTaxCondition,
    supplierTributaryInformationId,
    supplierBankInformationId,
    supplierBankInformation,
    countryId,
    cityId,
    cityName,
    subClassificationSupplierId,
    showSubForm,
    classifications,
    activeCollapseKey,
    activeSideBarCollapseKey,
    activeSubSideBarCollapseKey,
    activeSideBarOptionKey,
    stepperOption,
    stepperCurrent,
    showFormComponent,
    showModalCode,
    panelSideBar,
    optionsForm,
    isEditMode,
    isLoadingModuleGeneral: computed(() => supplierGlobalStore.isLoadingModuleGeneral),
    sidebarScrollRequestKey,
    sidebarScrollRequestNonce,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getDisabledComponent,
    getSavedFormComponent,
    getCompletedFormComponent,
    getPercentFormComponent,
    getRenderFormComponent,
    getProgressCountFormComponent,
    isSupplierFormActive,
    hasClassifications,
    hasSingleClassification,
    isClassificationVisible,
    isGeneralInformationVisible,
    isLocationVisible,
    isContactsVisible,
    isCommercialInformationVisible,
    isServicesVisible,
    isPoliciesVisible,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    handleLoadingFormSpecific,
    handleLoadingButtonSpecific,
    handleDisabledSpecific,
    handleSavedFormSpecific,
    handleChangeCollapseKey,
    handleShowModalCode,
    handleProgressFormSpecific,
    handleChangeActiveSideBarCollapseKey,
    handleChangeGroupSideBarCollapseKey,
    openNextSection,
    markFormComponentComplete,
    markItemComplete,
    resetAllComponents,
    handleSetActiveItem,
  };
}
