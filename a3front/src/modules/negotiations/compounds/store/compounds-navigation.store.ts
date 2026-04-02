import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { CompoundSidebarNode } from '@/modules/negotiations/compounds/domain/sidebar/sidebar.types';

export const useCompoundsNavigationStore = defineStore('compounds-navigation', () => {
  const sections = ref<CompoundSidebarNode[]>([]);
  const isSidebarCollapsed = ref(true); // inicia colapsado
  const totalProgress = ref(0);

  const activeSectionCode = computed(() => {
    return sections.value.find((s) => s.active)?.code ?? null;
  });

  function setSections(newSections: CompoundSidebarNode[]) {
    sections.value = newSections;
  }

  function setTotalProgress(value: number) {
    totalProgress.value = value;
  }

  function setActiveSection(code: string) {
    sections.value = sections.value.map((section) => ({
      ...section,
      active: section.code === code,
    }));
  }

  function toggleSidebar() {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
  }

  return {
    sections,
    isSidebarCollapsed,
    totalProgress,
    activeSectionCode,
    setSections,
    setTotalProgress,
    setActiveSection,
    toggleSidebar,
  };
});
