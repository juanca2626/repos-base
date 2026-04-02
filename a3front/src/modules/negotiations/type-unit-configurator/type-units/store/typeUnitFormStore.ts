import { defineStore } from 'pinia';
import { ref } from 'vue';
import type {
  TypeUnit,
  TypeUnitForm,
} from '@/modules/negotiations/type-unit-configurator/type-units/interfaces';

export const useTypeUnitFormStore = defineStore('typeUnitFormStore', () => {
  const showDrawerForm = ref<boolean>(false);

  const initTypeUnitForm: TypeUnitForm = {
    id: null,
    name: '',
    code: '',
    status: true,
    isTrunk: false,
  };

  const typeUnits = ref<TypeUnitForm[]>([]);

  const isEditMode = ref<boolean>(false);

  const setShowDrawerForm = (value: boolean): void => {
    showDrawerForm.value = value;
  };

  const resetTypeUnits = (): void => {
    typeUnits.value = [
      {
        ...initTypeUnitForm,
      },
    ];
  };

  const setTypeUnitFromEdit = (item: TypeUnit): void => {
    typeUnits.value = [
      {
        id: item.id,
        name: item.typeUnitName,
        code: item.code,
        status: item.status,
        isTrunk: item.isTrunk,
      },
    ];
  };

  const setIsEditMode = (value: boolean): void => {
    isEditMode.value = value;
  };

  const addTypeUnit = () => {
    typeUnits.value.push({
      ...initTypeUnitForm,
    });
  };

  const deleteTypeUnit = (index: number) => {
    typeUnits.value.splice(index, 1);
  };

  return {
    showDrawerForm,
    typeUnits,
    isEditMode,
    resetTypeUnits,
    addTypeUnit,
    setShowDrawerForm,
    deleteTypeUnit,
    setIsEditMode,
    setTypeUnitFromEdit,
  };
});
