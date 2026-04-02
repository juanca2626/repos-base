import type { Rule } from 'ant-design-vue/es/form';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';

export function usePaymentCondition() {
  const { formStateTreasury } = useSupplierFormStoreFacade();

  const { getConditionalFormRules } = useSupplierFormView();

  const baseRules: Record<string, Rule[]> = {
    creditDays: [
      { required: true, message: 'El campo días de crédito es obligatorio', trigger: 'change' },
    ],
    startDateSunat: [
      { required: true, message: 'La fecha de inicio SUNAT es obligatoria', trigger: 'change' },
    ],
    creditDaysSunat: [
      {
        required: true,
        message: 'El campo días de crédito SUNAT es obligatorio',
        trigger: 'change',
      },
    ],
  };

  const formRules: Record<string, Rule[]> = getConditionalFormRules('treasury', baseRules);

  return {
    formStateTreasury,
    formRules,
  };
}
