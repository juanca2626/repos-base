import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { supplierClassificationStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/supplierClassification.store';
import { storeToRefs } from 'pinia';

export const useSupplierScrollableTab = () => {
  const route = useRoute();
  const supplierStore = supplierClassificationStore();
  const block = ref(true);
  const { data, isLoading, activeKey } = storeToRefs(supplierStore);

  const tabsWrapper = ref<HTMLElement | null>(null);
  const scrollPosition = ref(0);
  const maxScroll = ref(0);
  const scrollAmount = 100;

  const scrollLeft = () => {
    scrollPosition.value = Math.max(0, scrollPosition.value - scrollAmount);
  };

  const scrollRight = () => {
    scrollPosition.value = Math.min(maxScroll.value, scrollPosition.value + scrollAmount);
  };

  const handleTabClick = (key: number) => {
    supplierStore.setActiveKey(key);
  };

  const updateMaxScroll = () => {
    if (tabsWrapper.value) {
      const wrapperWidth = tabsWrapper.value.clientWidth;
      const listWidth = tabsWrapper.value.scrollWidth;
      maxScroll.value = Math.max(0, listWidth - wrapperWidth);
    }
  };

  onMounted(() => {
    const id = route.params.id as string;
    supplierStore.fetchData(id);
    updateMaxScroll();
    window.addEventListener('resize', updateMaxScroll);
  });

  return {
    data,
    isLoading,
    activeKey,
    tabsWrapper,
    scrollPosition,
    maxScroll,
    block,
    scrollLeft,
    scrollRight,
    handleTabClick,
  };
};
