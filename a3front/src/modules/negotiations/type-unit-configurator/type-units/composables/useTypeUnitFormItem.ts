import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import type { FormInstance } from 'ant-design-vue';
import type { Rule } from 'ant-design-vue/es/form';
import { useTypeUnitFormStore } from '@/modules/negotiations/type-unit-configurator/type-units/store/typeUnitFormStore';

export const useTypeUnitFormItem = () => {
  const formRefTypeUnit = ref<FormInstance | null>(null);

  const typeUnitFormStore = useTypeUnitFormStore();

  const { typeUnits } = storeToRefs(typeUnitFormStore);

  const { deleteTypeUnit } = typeUnitFormStore;

  const formRules: Record<string, Rule[]> = {
    code: [
      { required: true, message: 'El código es requerido', trigger: 'change' },
      { min: 3, max: 4, message: 'El código debe tener entre 3 y 4 caracteres', trigger: 'change' },
    ],
    name: [
      { required: true, message: 'El nombre es requerido', trigger: 'change' },
      { min: 3, message: 'El nombre debe tener al menos 3 caracteres', trigger: 'change' },
    ],
  };

  const validateFields = async () => {
    return formRefTypeUnit.value?.validate();
  };

  const resetFields = () => {
    formRefTypeUnit.value?.resetFields();
  };

  return {
    typeUnits,
    formRefTypeUnit,
    formRules,
    resetFields,
    validateFields,
    deleteTypeUnit,
  };
};
