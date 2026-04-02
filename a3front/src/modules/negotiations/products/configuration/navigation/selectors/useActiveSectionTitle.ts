import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import {
  findActiveSectionWithSourceItem,
  findSectionWithActiveItem,
  findActiveSection,
  formatSectionTitle,
  sectionHasItems,
} from '../utils';

export const useActiveSectionTitle = () => {
  const navigationStore = useNavigationStore();
  const { sections, activeTabKey } = storeToRefs(navigationStore);

  const title = computed(() => {
    const key = activeTabKey.value;
    if (key == null) return null;

    const sectionsList = sections.value;

    const activeSectionWithSourceItem = findActiveSectionWithSourceItem(sectionsList, key);

    if (activeSectionWithSourceItem) {
      if (!sectionHasItems(activeSectionWithSourceItem)) return '';
      return activeSectionWithSourceItem.title ?? '';
    }

    const sectionWithActiveItem = findSectionWithActiveItem(sectionsList, key);
    if (!sectionWithActiveItem) return '';

    const activeSection = sectionWithActiveItem ?? findActiveSection(sectionsList, key);
    if (!activeSection) return '';

    if (!sectionHasItems(activeSection)) return '';

    return formatSectionTitle(activeSection);
  });

  return {
    title,
  };
};
