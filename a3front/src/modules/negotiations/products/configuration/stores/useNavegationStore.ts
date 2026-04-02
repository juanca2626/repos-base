import { ref, reactive, computed, watch } from 'vue';
import { defineStore } from 'pinia';
import { storeToRefs } from 'pinia';
import type { ApiNavigationItem, ApiItem } from '../navigation/types';
import type { TabItem } from '../interfaces/tab.interface';
import { workflowByRole } from '../workflows';
import { applyWorkflow } from '../workflows/applyWorkflow';
import type { Role } from '../types/index';
import { useConfigurationStore } from './useConfigurationStore';
import { fetchSidebarUseCase } from '../application/sidebar/fetchSidebar.useCase';
import { getUserRole } from '@/utils/auth';

export const useNavigationStore = defineStore('navigationStore', () => {
  const configurationStore = useConfigurationStore();

  const totalProgress = ref(0);
  const isLoading = ref<boolean>(false);
  const tabs = reactive<TabItem[]>([]);
  const sections = reactive<ApiNavigationItem[]>([]);
  const role = computed<Role>(() => {
    const userRole = getUserRole();
    return userRole === 'mk' ? 'MARKETING' : 'LOADING';
  });
  const isSidebarCollapsed = ref<boolean>(false);
  const temporaryActiveCode = ref<string | null>(null);

  const activeTabKey = computed(() => tabs.find((t) => t.isActive)?.key ?? null);
  const workflow = computed(() => workflowByRole[role.value]);

  const getSectionsKeyActive = computed(() => {
    const key = activeTabKey.value;
    if (key == null) return [];
    const sectionFiltered = sections.filter((s) => s.key === key);
    return sectionFiltered || [];
  });

  const getSectionsActiveTypeItem = computed(() => {
    const key = activeTabKey.value;
    if (key == null) return null;
    return sections.find((s) => s.active && s.source === 'item' && s.key === key);
  });

  const getSectionsCodeByTabKeyActive = computed(() => {
    const key = activeTabKey.value;
    if (key == null) return [];
    return sections.filter((s) => s.key === key && s.source === 'section');
  });

  const getSectionsCodeActive = computed(() => {
    const key = activeTabKey.value;
    const code = temporaryActiveCode.value;
    if (key == null) return null;

    const activeSectionItem = sections.find(
      (section) => section.active === true && section.source === 'item' && section.key === key
    );

    if (activeSectionItem) {
      return activeSectionItem.code;
    }

    const activeSection = sections.find(
      (section) =>
        Array.isArray(section.items) &&
        section.items.some((item) => item.active === true) &&
        section.key === key
    );

    return code ?? activeSection?.code ?? null;
  });

  const getSectionsItemActive = computed(() => {
    const key = activeTabKey.value;
    if (key == null) return null;
    return sections.flatMap((section) => section.items ?? []).find((item) => item.active) ?? null;
  });

  const currentKey = computed(() => tabs.find((tab) => tab.isActive)?.key ?? null);
  const currentCode = computed(() => getSectionsCodeActive.value);

  const setActiveTabKey = async (tabKey: string) => {
    tabs.forEach((t) => {
      t.isActive = t.key === tabKey;
    });
    temporaryActiveCode.value = null;
    applyWorkflow(sections, workflow.value, getSectionsCodeActive.value ?? '', tabKey);
  };

  const clearAllItemsActiveState = (): void => {
    sections.forEach((s) => {
      // se desactivara la section que no tiene items
      if (s.items?.length === 0) {
        s.active = false;
      }

      s.items?.forEach((item: ApiItem) => {
        item.active = false;
      });
    });
  };

  const activateItemInSection = (
    sectionKey: string | null,
    sectionCode: string | null,
    itemId: string | null
  ): void => {
    if (sectionKey == null || sectionCode == null || itemId == null) return;
    const section = sections.find((s) => s.key === sectionKey && s.code === sectionCode);
    if (!section?.items) return;
    section.items.forEach((item: ApiItem) => {
      item.active = item.id === itemId;
    });
  };

  const setActiveSectionItem = async (
    sectionKey: string,
    sectionCode: string,
    itemId: string
  ): Promise<void> => {
    temporaryActiveCode.value = sectionCode;
    await clearAllItemsActiveState();
    await activateItemInSection(sectionKey, sectionCode, itemId);
  };

  const toggleSection = async (sectionKey: ApiNavigationItem) => {
    await clearAllItemsActiveState();
    const section = sections.find((s) => s.key === sectionKey.key && s.code === sectionKey.code);
    if (section) {
      section.active = !section.active;
    }
  };

  const setCompletedItem = (
    sectionKey: string,
    sectionCode: string,
    itemId: string | null
  ): void => {
    const section = sections.find((s) => s.key === sectionKey && s.code === sectionCode);

    if (section && section.source === 'item' && itemId == null) {
      section.completed = true;
      section.active = false;
      section.editing = false;

      loadSidebar();
    }

    if (!section?.items) return;

    const item = section.items.find((i) => i.id === itemId);
    if (item) {
      item.completed = true;
      item.active = false;
      item.editing = false;
    }
    loadSidebar();
  };

  const loadSidebar = async (): Promise<void> => {
    try {
      isLoading.value = true;
      const roleStore = role.value;
      const configurationStore = useConfigurationStore();
      const { productSupplierId, productSupplierType } = storeToRefs(configurationStore);

      const data = await fetchSidebarUseCase({
        role: roleStore,
        serviceType: productSupplierType.value,
        productSupplierId: productSupplierId.value ?? '',
        codeOrKey: activeTabKey.value ?? '',
      });

      sections.splice(0, sections.length, ...data.sections);

      totalProgress.value = data.totalProgress;

      await applyWorkflow(
        sections,
        workflow.value,
        getSectionsCodeActive.value ?? '',
        activeTabKey.value ?? ''
      );

      isSidebarCollapsed.value = false;
    } catch (error) {
      console.error(error);
    } finally {
      isLoading.value = false;
    }
  };

  const clearData = () => {
    sections.splice(0, sections.length);
    tabs.splice(0, tabs.length);
    totalProgress.value = 0;
    isSidebarCollapsed.value = false;
    isLoading.value = false;
    temporaryActiveCode.value = null;
  };

  watch(
    () => configurationStore.items,
    (items) => {
      if (items.length === 0) return;

      const tabActive = activeTabKey.value;
      const defaultActiveKey = items[0]?.key ?? null;
      const activeKey = tabActive ?? defaultActiveKey;

      tabs.splice(
        0,
        tabs.length,
        ...items.map((item, index) => ({
          key: item.key,
          name: item.name,
          isActive:
            activeKey === null
              ? index === 0
                ? true
                : false
              : item.key === activeKey
                ? true
                : false,
        }))
      );

      if (tabActive !== null) {
        loadSidebar();
      }
    },
    { immediate: true }
  );

  watch(
    () => activeTabKey.value,
    (newTabKey) => {
      if (newTabKey) {
        loadSidebar();
      }
    }
  );

  return {
    totalProgress,
    sections,
    tabs,
    isSidebarCollapsed,
    isLoading,
    role,

    // getters
    activeTabKey,
    getSectionsKeyActive,
    getSectionsCodeByTabKeyActive,
    getSectionsCodeActive,
    getSectionsItemActive,
    getSectionsActiveTypeItem,
    currentKey,
    currentCode,
    // setters
    setActiveTabKey,
    setActiveSectionItem,
    setCompletedItem,
    // acciones
    toggleSection,
    loadSidebar,
    clearData,
  };
});
