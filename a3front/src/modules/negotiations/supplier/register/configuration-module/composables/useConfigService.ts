import { computed, ref, watch } from 'vue';
import TransportUnitComponent from '@/modules/negotiations/supplier/register/configuration-module/components/units/TransportUnitComponent.vue';
import NegotiationPoliciesTableComponent from '@/modules/negotiations/supplier/register/configuration-module/components/NegotiationPoliciesTableComponent.vue';
import NegotiationSupplierTributaryInformationComponent from '@/modules/negotiations/supplier/register/configuration-module/components/NegotiationSupplierTributaryInformationComponent.vue';
import ServiceComponent from '@/modules/negotiations/supplier/register/configuration-module/components/services/ServiceComponent.vue';
import ContactComponent from '@/modules/negotiations/supplier/register/configuration-module/components/contacts/ContactComponent.vue';
import TechnicalSheetComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/TechnicalSheetComponent.vue';
import { useDetailsSection } from '@/modules/negotiations/supplier/register/configuration-module/composables/useDetailsSection';
import { SupplierSubClassifications } from '@/modules/negotiations/constants';
import type {
  TabConfig,
  TabOrderMapping,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { TabKeyEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/tab-key.enum';
import type {
  TabOptions,
  TabOrder,
  TabsOrderMapping,
} from '@/modules/negotiations/supplier/register/configuration-module/types';

export function useConfigService() {
  const { configSubClassification } = useDetailsSection();

  const loading = ref(false);

  const allTabs: TabConfig[] = [
    {
      key: TabKeyEnum.SERVICE,
      tab: 'Servicios',
      component: ServiceComponent,
    },
    {
      key: TabKeyEnum.CONTACTS,
      tab: 'Contactos',
      component: ContactComponent,
    },
    {
      key: TabKeyEnum.POLICIES,
      tab: 'Políticas',
      component: NegotiationPoliciesTableComponent,
    },
    {
      key: TabKeyEnum.SUPPLIER_TRIBUTARY_INFORMATION,
      tab: 'Información SUNAT',
      component: NegotiationSupplierTributaryInformationComponent,
    },
    {
      key: TabKeyEnum.UNIT_LIST,
      tab: 'Listado de unidades',
      component: TransportUnitComponent,
    },
    {
      key: TabKeyEnum.TECHNICAL_SHEET,
      tab: 'Ficha técnica',
      component: TechnicalSheetComponent,
    },
  ];

  const tabsOrderMapping: TabsOrderMapping = {
    [SupplierSubClassifications.MUSEUMS]: {
      [TabKeyEnum.SERVICE]: 1,
      [TabKeyEnum.CONTACTS]: 2,
      [TabKeyEnum.POLICIES]: 3,
      [TabKeyEnum.SUPPLIER_TRIBUTARY_INFORMATION]: 4,
    },
    [SupplierSubClassifications.TOURIST_TRANSPORT]: {
      [TabKeyEnum.UNIT_LIST]: 1,
      [TabKeyEnum.SERVICE]: 2,
      [TabKeyEnum.CONTACTS]: 3,
      [TabKeyEnum.TECHNICAL_SHEET]: 4,
      [TabKeyEnum.SUPPLIER_TRIBUTARY_INFORMATION]: 5,
    },
    [SupplierSubClassifications.OVERFLIGHT]: {
      [TabKeyEnum.SERVICE]: 1,
      [TabKeyEnum.CONTACTS]: 2,
      [TabKeyEnum.POLICIES]: 3,
      [TabKeyEnum.TECHNICAL_SHEET]: 4,
      [TabKeyEnum.SUPPLIER_TRIBUTARY_INFORMATION]: 5,
    },
  };

  const findTabConfig = (tabKey: TabKeyEnum): TabConfig | undefined => {
    return allTabs.find((tab) => tab.key === tabKey);
  };

  const getKeysFromTabs = (tabOrderMapping: TabOrder): TabKeyEnum[] => {
    return Object.keys(tabOrderMapping) as TabKeyEnum[];
  };

  const mapTabsWithOrder = (
    tabOrderMapping: TabOrder,
    keysFromTabs: TabKeyEnum[]
  ): TabOrderMapping[] => {
    return keysFromTabs
      .map((tabKey) => {
        const tab = findTabConfig(tabKey);

        if (!tab) return null;

        return {
          ...tab,
          order: tabOrderMapping[tabKey],
        };
      })
      .filter((tab): tab is TabOrderMapping => tab !== null);
  };

  const getSortedTabsBySubclassification = (
    subclassification: SupplierSubClassifications
  ): TabOrderMapping[] => {
    const tabOrderMapping = tabsOrderMapping[subclassification] as TabOrder;

    if (!tabOrderMapping) {
      return [];
    }

    const keysFromTabs = getKeysFromTabs(tabOrderMapping);

    const tabsWithOrder = mapTabsWithOrder(tabOrderMapping, keysFromTabs);

    // ordena según valor de order
    return tabsWithOrder.sort((a, b) => a.order - b.order);
  };

  const tabOptions: TabOptions = {
    [SupplierSubClassifications.TOURIST_TRANSPORT]: getSortedTabsBySubclassification(
      SupplierSubClassifications.TOURIST_TRANSPORT
    ),
    [SupplierSubClassifications.MUSEUMS]: getSortedTabsBySubclassification(
      SupplierSubClassifications.MUSEUMS
    ),
    [SupplierSubClassifications.OVERFLIGHT]: getSortedTabsBySubclassification(
      SupplierSubClassifications.OVERFLIGHT
    ),
    [SupplierSubClassifications.CRUISES]: getSortedTabsBySubclassification(
      SupplierSubClassifications.CRUISES
    ),
  };

  const tabs = computed(() => {
    const subClassification = configSubClassification.value;
    if (!subClassification) {
      return [];
    }
    return tabOptions[subClassification as SupplierSubClassifications] || [];
  });

  watch(
    configSubClassification,
    (newSubClassification) => {
      if (newSubClassification !== null) {
        loading.value = true;
        setTimeout(() => {
          loading.value = false;
        }, 500);
      }
    },
    { immediate: true }
  );

  return {
    tabs,
    loading,
  };
}
