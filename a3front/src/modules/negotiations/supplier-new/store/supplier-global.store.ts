import { defineStore } from 'pinia';
import { h, ref } from 'vue';
import IconUserPending from '@/modules/negotiations/supplier-new/icons/icon-user-pending.vue';

import { SupplierViewTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-view-type.enum';

// 🔹 Definición de tipos
export type SidebarItem = {
  key: string;
  title?: string;
  isActive?: boolean;
  isComplete?: boolean;
};

export type SidebarSubPanel = {
  key: string;
  title?: string;
  items: SidebarItem[];
};

export type SidebarPanel = {
  key: string;
  title?: string;
  subPanels: SidebarSubPanel[];
};

export const useSupplierGlobalStore = defineStore('supplierGlobalStore', () => {
  const supplierId = ref<number | undefined>(undefined);
  const supplier = ref<any>({});
  const isEditModeState = ref<boolean>(false);
  const supplierPaymentId = ref<number | undefined>(undefined);
  const subClassificationSupplierId = ref<number | undefined>(undefined);
  const supplierPayment = ref<any>({});
  const supplierTaxConditionId = ref<number | undefined>(undefined);
  const supplierTaxCondition = ref<any>({});
  const supplierBankInformationId = ref<number | undefined>(undefined);
  const supplierBankInformation = ref<any>({});
  const countryId = ref<number | null>(null);
  const selectedCountryName = ref<string | null>(null);
  const cityId = ref<number | null>(null);
  const cityName = ref<string | null>(null);
  const showSubForm = ref<boolean>(false);
  const supplierTributaryInformationId = ref<number | undefined>(undefined);
  const showModalCode = ref<boolean>(false);
  const classifications = ref<any>([]);
  const activeCollapseKey = ref<any>(['negotiations']);
  const activeSideBarCollapseKey = ref<any>(['supplier']);
  const activeSideBarOptionKey = ref<string>('');
  const activeSubSideBarCollapseKey = ref(['supplier-negotiations']);
  const stepperCurrent = ref<any>(0);

  // controlar vistas en supplier form base
  const supplierViewType = ref<SupplierViewTypeEnum>(SupplierViewTypeEnum.SUPPLIER_FORM);

  const stepperOption = ref<Array<any>>([
    {
      key: 'negotiations',
      title: 'Negociaciones',
      description: 'En progreso',
      icon: h(IconUserPending),
      disabled: false,
    },
    {
      key: 'treasury',
      title: 'Tesorería',
      description: 'Por completar',
      icon: h(IconUserPending),
      disabled: true,
    },
    {
      key: 'accounting',
      title: 'Contabilidad',
      description: 'Por completar',
      icon: h(IconUserPending),
      disabled: true,
    },
    {
      key: 'supplier-management',
      title: 'Gestión de proveedores',
      description: 'Por completar',
      icon: h(IconUserPending),
      disabled: true,
    },
  ]);

  const showFormComponent = ref<any>({
    classificationSupplier: {
      showForm: true,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: false,
      readOnly: false,
      saved: false,
    },
    generalInformation: {
      showForm: false,
      isEditForm: false,
      disabled: false,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    commercialLocation: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: true,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    contactInformation: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: true,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    commercialInformation: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: true,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    services: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: true,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    policies: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: true,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    placeOperation: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: true,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    basicInformation: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: true,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    sunatInformation: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: true,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    contact: {
      completed: false,
      percent: 0,
    },
    moduleSunatInformation: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: false,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    moduleTreasuryBankInformation: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: false,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    moduleTreasuryBeneficiaries: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: true,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    moduleTreasuryAccountContable: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: false,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
    moduleAccountingSunatInformation: {
      showForm: false,
      isEditForm: false,
      loading: false,
      loadingButton: false,
      disabled: false,
      readOnly: false,
      saved: false,
      completed: false,
      percent: 0,
    },
  });

  // 🔹 Ahora con tipo explícito
  const panelSideBar = ref<SidebarPanel[]>([]);
  const optionsForm = ref<any>([]);
  const isSupplierLoaded = ref<boolean>(false);
  const isPanelSideBarLoaded = ref<boolean>(false);
  const isLoadingModuleGeneral = ref<boolean>(false);
  const loadingModuleCounter = ref<number>(0);
  const menuPanelKey = ref<string>('supplier');
  const menuSubPanelKey = ref<string | null>('"supplier-negotiations"');
  const menuItemKey = ref<string | null>('classification');
  const sidebarScrollRequestKey = ref<string | null>(null);
  const sidebarScrollRequestNonce = ref<number>(0);

  // 🔹 Flag para controlar inicialización única
  const isInitializing = ref<boolean>(false);
  const isInitialized = ref<boolean>(false);

  const isLoadingFormGeneral = ref<boolean>(false);
  const loadingFormCounter = ref<number>(0);

  const setIsLoadingFormGeneral = (value: boolean) => {
    if (value) {
      loadingFormCounter.value++;
    } else {
      loadingFormCounter.value = Math.max(0, loadingFormCounter.value - 1);
    }
    isLoadingFormGeneral.value = loadingFormCounter.value > 0;
  };

  const setIsLoadingModuleGeneral = (value: boolean) => {
    if (value) {
      loadingModuleCounter.value++;
    } else {
      loadingModuleCounter.value = Math.max(0, loadingModuleCounter.value - 1);
    }
    isLoadingModuleGeneral.value = loadingModuleCounter.value > 0;
  };

  const setSupplierViewType = (value: SupplierViewTypeEnum) => {
    supplierViewType.value = value;
  };

  const setSelectedCountryName = (countryName: string | null) => {
    selectedCountryName.value = countryName;
  };

  /**
   * Resetea completamente el estado del formulario
   */
  const resetFormState = () => {
    // Resetear panelSideBar
    if (panelSideBar.value && Array.isArray(panelSideBar.value)) {
      panelSideBar.value = panelSideBar.value.map((panel) => ({
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
    }

    // Resetear estados
    isEditModeState.value = false;
    supplierId.value = undefined;
    isSupplierLoaded.value = false;
    isPanelSideBarLoaded.value = false;
    isInitializing.value = false;
    isInitialized.value = false;
    showFormComponent.value = {};
    supplier.value = {};
    supplierPayment.value = {};
    supplierTaxCondition.value = {};
    supplierBankInformation.value = {};
    classifications.value = [];
    subClassificationSupplierId.value = undefined;
  };

  /**
   * Marca un item como completo en el panelSideBar
   */
  const markItemComplete = (itemKey: string) => {
    if (!panelSideBar.value || !Array.isArray(panelSideBar.value)) {
      return;
    }

    // Crear deep clone
    panelSideBar.value = panelSideBar.value.map((panel) => {
      const newPanel: any = { ...panel };

      if (newPanel.subPanels && Array.isArray(newPanel.subPanels)) {
        newPanel.subPanels = newPanel.subPanels.map((subPanel: any) => {
          const newSubPanel = { ...subPanel };

          if (newSubPanel.items && Array.isArray(newSubPanel.items)) {
            newSubPanel.items = newSubPanel.items.map((item: any) => {
              const newItem = { ...item };
              if (newItem.key === itemKey) {
                newItem.isComplete = true;
              }
              return newItem;
            });

            // Calcular totales
            const totalItems = newSubPanel.items.length;
            const completedItems = newSubPanel.items.filter((item: any) => item.isComplete).length;
            newSubPanel.complete = completedItems;
            newSubPanel.total = totalItems;
          }

          return newSubPanel;
        });

        // Calcular progreso
        const totalSubPanelItems = newPanel.subPanels.reduce(
          (sum: number, sp: any) => sum + (sp.total || 0),
          0
        );
        const completedSubPanelItems = newPanel.subPanels.reduce(
          (sum: number, sp: any) => sum + (sp.complete || 0),
          0
        );

        if (totalSubPanelItems > 0) {
          newPanel.progress = Math.round((completedSubPanelItems / totalSubPanelItems) * 100);
        } else {
          newPanel.progress = 0;
        }
      }

      return newPanel;
    });
  };

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
    selectedCountryName,
    subClassificationSupplierId,
    isEditModeState,
    showSubForm,
    activeCollapseKey,
    activeSideBarCollapseKey,
    activeSubSideBarCollapseKey,
    activeSideBarOptionKey,
    stepperCurrent,
    stepperOption,
    showFormComponent,
    showModalCode,
    panelSideBar,
    optionsForm,
    classifications,
    isSupplierLoaded,
    isPanelSideBarLoaded,
    isInitializing,
    isInitialized,
    menuPanelKey,
    menuSubPanelKey,
    menuItemKey,
    sidebarScrollRequestKey,
    sidebarScrollRequestNonce,
    isLoadingModuleGeneral,
    isLoadingFormGeneral,
    supplierViewType,
    setIsLoadingFormGeneral,
    setIsLoadingModuleGeneral,
    setSupplierViewType,
    setSelectedCountryName,
    resetFormState,
    markItemComplete,
  };
});
