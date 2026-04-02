import { onMounted, ref, watch, nextTick, computed, onUnmounted } from 'vue';
import { useOperationLocationsTab } from '@/modules/negotiations/supplier/store/operation-locations-tab.store';
import { storeToRefs } from 'pinia';
import { useSupplierClassificationStore } from '@/modules/negotiations/supplier/store/supplier-classification.store';
import { useSearchTouristTransportFiltersStore } from '@/modules/negotiations/supplier/store/search-tourist-transport-filters.store';

interface LocationInterface {
  ids: string;
  country_id: number | null;
  state_id: number | null;
  city_id: number | null;
  zone_id: number | null;
  display_name: string;
}

export const useTouristTransportTabCities = () => {
  const operationLocationsTabStore = useOperationLocationsTab();
  const block = ref(true);
  const { data, isLoading, activeKey } = storeToRefs(operationLocationsTabStore);

  const tabsWrapper = ref<HTMLElement | null>(null);
  const scrollPosition = ref(0);
  const maxScroll = ref(0);
  const scrollAmount = 200;
  const supplierClassificationStore = useSupplierClassificationStore();
  const searchFiltersStore = useSearchTouristTransportFiltersStore();

  const updateVisibleItems = () => {
    if (tabsWrapper.value) {
      const wrapperWidth = tabsWrapper.value.clientWidth;
      const items = tabsWrapper.value.querySelectorAll('.tab-item');
      let accumulatedWidth = 0;

      items.forEach((item) => {
        if (accumulatedWidth < wrapperWidth) {
          accumulatedWidth += item.clientWidth;
        }
      });
    }
  };

  const updateMaxScroll = () => {
    if (tabsWrapper.value) {
      const wrapperWidth = tabsWrapper.value.clientWidth;
      const listWidth = tabsWrapper.value.scrollWidth;
      maxScroll.value = Math.max(0, listWidth - wrapperWidth);
      updateVisibleItems();
    }
  };

  const scrollLeft = () => {
    scrollPosition.value = Math.max(0, scrollPosition.value - scrollAmount);
    updateVisibleItems();
  };

  const scrollRight = () => {
    scrollPosition.value = Math.min(maxScroll.value, scrollPosition.value + scrollAmount);
    updateVisibleItems();
  };

  const handleTabClick = (item: LocationInterface) => {
    searchFiltersStore.setOperationLocation({
      country_id: item.country_id,
      state_id: item.state_id,
      city_id: item.city_id,
      zone_id: item.zone_id,
    });
    activeKey.value = item.ids;
    scrollToItem(item);
  };

  const scrollToItem = (item: LocationInterface) => {
    if (tabsWrapper.value) {
      const items = tabsWrapper.value.querySelectorAll('.tab-item');
      const itemIndex = data.value.findIndex((dataItem) => dataItem.ids === item.ids);
      if (itemIndex !== -1 && items[itemIndex]) {
        const itemOffset = items[itemIndex].offsetLeft;
        scrollPosition.value = Math.min(itemOffset, maxScroll.value);
        updateVisibleItems();
      }
    }
  };

  watch(
    () => data.value,
    async (newValue) => {
      if (newValue.length > 0) {
        await nextTick();
        updateMaxScroll();

        searchFiltersStore.setOperationLocation({
          country_id: newValue[0].country_id,
          state_id: newValue[0].state_id,
          city_id: newValue[0].city_id,
          zone_id: newValue[0].zone_id,
        });
      }
    }
  );

  onMounted(() => {
    operationLocationsTabStore.fetchData(
      supplierClassificationStore.supplier_sub_classification_id
    );

    window.addEventListener('resize', updateMaxScroll);

    onUnmounted(() => {
      window.removeEventListener('resize', updateMaxScroll);
    });
  });

  const canScrollRight = computed(() => {
    return scrollPosition.value < maxScroll.value;
  });

  return {
    data,
    isLoading,
    activeKey,
    tabsWrapper,
    scrollPosition,
    block,
    scrollLeft,
    scrollRight,
    handleTabClick,
    canScrollRight,
  };
};
