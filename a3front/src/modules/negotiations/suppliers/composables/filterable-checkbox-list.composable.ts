import { ref, computed, watch } from 'vue';
import type { FilterableCheckboxListParams } from '@/modules/negotiations/suppliers/interfaces';
import { sleep } from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';

export const useFilterableCheckboxList = ({
  options,
  selectedOptionsModel,
  searchModel,
  chunkSize = 5,
  emit,
}: FilterableCheckboxListParams) => {
  const visibleCount = ref<number>(chunkSize);

  const filteredOptions = computed(() => {
    const query = searchModel.value.trim();

    return !query
      ? options.value
      : options.value.filter((item) => item.label.toLowerCase().includes(query));
  });

  const visibleOptions = computed(() => {
    return filteredOptions.value.slice(0, visibleCount.value);
  });

  const canShowMore = computed(() => visibleCount.value < filteredOptions.value.length);

  const showMore = async () => {
    emit('loading', true);

    visibleCount.value = Math.min(visibleCount.value + chunkSize, filteredOptions.value.length);

    await sleep(300);

    emit('loading', false);
  };

  const isVisible = (item: string) => visibleOptions.value.some((option) => option.value === item);

  const visibleSelected = computed(() => {
    return selectedOptionsModel.value.filter((item) => {
      return isVisible(item);
    });
  });

  const handleCheckboxGroup = (values: string[]) => {
    const hidden = selectedOptionsModel.value.filter((item) => {
      return !isVisible(item);
    });

    selectedOptionsModel.value = [...new Set([...hidden, ...values])];
  };

  watch(filteredOptions, () => {
    visibleCount.value = chunkSize;
  });

  return {
    visibleOptions,
    canShowMore,
    filteredOptions,
    visibleSelected,
    showMore,
    handleCheckboxGroup,
  };
};
