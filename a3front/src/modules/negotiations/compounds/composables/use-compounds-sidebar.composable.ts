import { onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useCompoundsNavigationStore } from '@/modules/negotiations/compounds/store/compounds-navigation.store';
import { fetchCompoundsSidebarUseCase } from '@/modules/negotiations/compounds/application/sidebar/fetchSidebar.useCase';

export const useCompoundsSidebar = () => {
  const navigationStore = useCompoundsNavigationStore();

  const { sections, isSidebarCollapsed, totalProgress, activeSectionCode } =
    storeToRefs(navigationStore);

  const { setSections, setTotalProgress, setActiveSection, toggleSidebar } = navigationStore;

  const loadSidebar = async () => {
    try {
      // compoundId vacío por ahora — el mock no lo necesita
      const model = await fetchCompoundsSidebarUseCase({ compoundId: '' });

      setSections(model.sections);
      setTotalProgress(model.totalProgress);

      // Activar la primera sección por defecto
      if (model.sections.length > 0) {
        setActiveSection(model.sections[0].code);
      }
    } catch (error) {
      console.error('Error loading compounds sidebar:', error);
    }
  };

  const handleSelectSection = (code: string) => {
    setActiveSection(code);
  };

  onMounted(() => {
    loadSidebar();
  });

  return {
    sections,
    isSidebarCollapsed,
    totalProgress,
    activeSectionCode,
    toggleSidebar,
    handleSelectSection,
  };
};
