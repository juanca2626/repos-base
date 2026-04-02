import type { Ref } from 'vue';
import { nanoid } from 'nanoid';
import type { groupHoliday } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/pricingCommon.types';
interface UseHolidayCategoriesProps {
  holidays: Ref<groupHoliday[]>;
  uiState: {
    selectedItemId: string | null;
  };
  formState?: any;
}

export function useHolidayCategories({ holidays, uiState, formState }: UseHolidayCategoriesProps) {
  const getCategoryId = (category: any) => String(category?.id ?? category?.uuid ?? '');

  const getHolidaySource = (): groupHoliday[] => {
    if (formState !== undefined) {
      if (!Array.isArray(formState.selectedHolidays)) {
        formState.selectedHolidays = [];
      }

      return formState.selectedHolidays;
    }

    return holidays.value;
  };

  const selectCategory = (categoryId: string) => {
    uiState.selectedItemId = null;

    if (formState !== undefined) {
      formState.selectedCategoryId = categoryId;
    }
  };

  const selectItem = (itemId: string) => {
    uiState.selectedItemId = itemId;
  };

  const addCategory = () => {
    const source = getHolidaySource();
    const customCount = source.filter((c) => c.key === 'CUSTOM').length;

    const newCategory: groupHoliday = {
      uuid: nanoid(),
      key: 'CUSTOM',
      label: `Personalizado ${customCount + 1}`,
      icon: 'tag',
      color: '#9333EA',
      priority: source.length + 1,
      isActive: true,
      dates: [],
    };

    source.push(newCategory);

    if (formState !== undefined) {
      formState.selectedCategoryId = newCategory.uuid;
    }
  };

  const cloneCategory = (categoryId: string) => {
    const source = getHolidaySource();
    const base = source.find((c) => getCategoryId(c) === String(categoryId));

    if (!base) return;

    const clone: groupHoliday = {
      ...structuredClone(base),
      uuid: nanoid(),
      label: `${base.label}_copy`,
      dates: base.dates.map((date: any) => ({
        ...date,
      })),
    };

    source.push(clone);
  };

  const moveItemToCategory = (itemId: string | number, toCategoryId: string | number) => {
    const source = getHolidaySource();
    const matchItem = (d: any) => {
      return String(d?.externalId) === String(itemId);
    };

    const fromCategory = source.find((c) => c.dates.some((d: any) => matchItem(d)));

    const toCategory = source.find((c) => getCategoryId(c) === String(toCategoryId));

    if (!fromCategory || !toCategory) return;

    const index = fromCategory.dates.findIndex((d: any) => matchItem(d));

    if (index === -1) return;

    const [item] = fromCategory.dates.splice(index, 1);

    item.moveInfo = {
      ...item.moveInfo,
      currentGroupKey: toCategory.key,
    };

    toCategory.dates.push(item);
  };

  const toggleCategoryCheck = (categoryId: string) => {
    const source = getHolidaySource();
    const category = source.find((c) => getCategoryId(c) === String(categoryId));

    if (!category) return;

    category.isActive = !category.isActive;

    category.dates.forEach((date: any) => {
      date.isActive = category.isActive;
    });
  };

  const updateCategoryCheckState = (categoryId: string) => {
    const source = getHolidaySource();
    const category = source.find((c) => getCategoryId(c) === String(categoryId));

    if (!category) return;

    category.isActive = category.dates.every((date: any) => date.isActive);
  };

  return {
    selectCategory,
    selectItem,
    addCategory,
    cloneCategory,
    moveItemToCategory,
    toggleCategoryCheck,
    updateCategoryCheckState,
  };
}
