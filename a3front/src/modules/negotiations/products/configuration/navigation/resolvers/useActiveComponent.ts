import { marketingComponentMap } from '@/modules/negotiations/products/configuration/marketing/componentMap';
import { loadingComponentMap } from '@/modules/negotiations/products/configuration/content/componentMap';
import { computed, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useNavigationStore } from '../../stores/useNavegationStore';
import { useConfigurationStore } from '../../stores/useConfigurationStore';

export const useActiveComponent = () => {
  const navigationStore = useNavigationStore();
  const { sections, role, currentKey, currentCode } = storeToRefs(navigationStore);

  const configurationStore = useConfigurationStore();
  const { productSupplierType } = storeToRefs(configurationStore);

  const activeSectionItem = computed(() => {
    const activeItem = sections.value
      .flatMap((section) => section.items ?? [])
      .find((item) => item.active);

    if (activeItem) return activeItem;

    const activeSection = sections.value.find(
      (section) => section.active && section.source === 'item'
    );

    return activeSection ? { ...activeSection, id: activeSection.code } : null;
  });

  const activeComponent = computed(() => {
    if (!activeSectionItem.value) return null;

    if (role.value === 'MARKETING') {
      return marketingComponentMap[productSupplierType.value][activeSectionItem.value.id];
    }

    return loadingComponentMap[productSupplierType.value][activeSectionItem.value.id];
  });

  watch(activeSectionItem, () => {
    if (
      activeSectionItem.value?.id === 'content' &&
      activeSectionItem.value.active &&
      role.value === 'LOADING'
    ) {
      configurationStore.loadServiceContent(role.value, currentKey.value, currentCode.value);
    }
  });

  return {
    activeComponent,
    currentKey,
    currentCode,
  };
};
