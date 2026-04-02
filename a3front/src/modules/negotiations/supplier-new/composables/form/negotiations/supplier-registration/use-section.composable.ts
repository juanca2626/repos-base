import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { defineStore } from 'pinia';
import { useSupplierGlobalStore } from '@/modules/negotiations/supplier-new/store/supplier-global.store';

// 🔹 Store temporal definido en el mismo archivo
const useSectionsTempStore = defineStore('sectionsTemp', {
  state: () => ({
    isServicesCompleteTemp: false,
  }),
  actions: {
    setIsServicesCompleteTemp(value: boolean) {
      this.isServicesCompleteTemp = value;
    },
  },
});

export function useSectionsComposable() {
  const supplierGlobalStore = useSupplierGlobalStore();
  const { panelSideBar } = storeToRefs(supplierGlobalStore);

  // usamos el store temporal
  const sectionsTempStore = useSectionsTempStore();
  const { isServicesCompleteTemp } = storeToRefs(sectionsTempStore);

  const sections = computed(() => {
    if (!Array.isArray(panelSideBar.value)) return [];
    return panelSideBar.value.flatMap((panel: any) =>
      (panel.subPanels || []).flatMap((subPanel: any) =>
        (subPanel.items || []).map((item: any) => ({
          key: item.key,
          title: item.title,
          isComplete: item.isComplete,
        }))
      )
    );
  });

  const isClassificationComplete = computed(() =>
    sections.value.some((s) => s.key === 'classification' && s.isComplete)
  );

  const isGeneralInformationComplete = computed(() =>
    sections.value.some((s) => s.key === 'general_information' && s.isComplete)
  );

  const isLocationComplete = computed(() =>
    sections.value.some((s) => s.key === 'location' && s.isComplete)
  );

  const isContactsComplete = computed(() =>
    sections.value.some((s) => s.key === 'contacts' && s.isComplete)
  );

  const isCommercialInformationComplete = computed(() =>
    sections.value.some((s) => s.key === 'commercial_information' && s.isComplete)
  );

  const isServicesComplete = computed(() =>
    sections.value.some((s) => s.key === 'services' && s.isComplete)
  );

  return {
    sections,
    isClassificationComplete,
    isGeneralInformationComplete,
    isLocationComplete,
    isContactsComplete,
    isCommercialInformationComplete,
    isServicesComplete,

    // estado temporal global (Pinia en el mismo archivo)
    isServicesCompleteTemp,
    setIsServicesCompleteTemp: sectionsTempStore.setIsServicesCompleteTemp,
  };
}
