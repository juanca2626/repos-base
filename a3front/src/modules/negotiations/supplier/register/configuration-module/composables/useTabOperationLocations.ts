import { onMounted, ref, nextTick, onUnmounted } from 'vue';
import type {
  OperationLocationData,
  OperationLocationProps,
  OperationLocationEmit,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export const useTabOperationLocations = (
  props: OperationLocationProps,
  emit: OperationLocationEmit
) => {
  const { data } = props;
  const isLoading = ref<boolean>(false);
  const activeKey = ref<string | null>(null);

  const tabsWrapper = ref<HTMLElement | null>(null);
  const scrollPosition = ref(0);
  const maxScroll = ref(0);

  const updateMaxScroll = () => {
    if (tabsWrapper.value) {
      const wrapperWidth = tabsWrapper.value.clientWidth;
      const listWidth = tabsWrapper.value.scrollWidth;

      maxScroll.value = Math.max(0, listWidth - wrapperWidth);
    }
  };

  const scrollToItem = (item: OperationLocationData) => {
    if (tabsWrapper.value) {
      const items = tabsWrapper.value.querySelectorAll('.tab-item') as NodeListOf<HTMLElement>;
      const itemIndex = data.findIndex((dataItem) => dataItem.ids === item.ids);

      if (itemIndex !== -1 && items[itemIndex]) {
        const itemOffset = items[itemIndex].offsetLeft;

        const extraOffset = getExtraOffset(items.length, itemIndex);

        scrollPosition.value = Math.min(itemOffset, maxScroll.value) + extraOffset;
      }
    }
  };

  const getExtraOffset = (itemsLength: number, itemIndex: number): number => {
    // controlar margin-left de tabs-list
    if (itemIndex === itemsLength - 1 && maxScroll.value > 0) return 60;

    if (itemIndex === 0) return 0;

    return 6;
  };

  const handleTabClick = (item: OperationLocationData) => {
    activeKey.value = item.ids;
    scrollToItem(item);
    emit('handleTabClick', item);
  };

  onMounted(async () => {
    window.addEventListener('resize', updateMaxScroll);

    if (data.length > 0) {
      await nextTick();
      updateMaxScroll();
      handleTabClick(data[0]);
    }
  });

  onUnmounted(() => {
    window.removeEventListener('resize', updateMaxScroll);
  });

  return {
    data,
    isLoading,
    activeKey,
    tabsWrapper,
    scrollPosition,
    handleTabClick,
  };
};
