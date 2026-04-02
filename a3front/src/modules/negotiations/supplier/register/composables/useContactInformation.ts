import type { Rule } from 'ant-design-vue/es/form';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';

export function useContactInformation() {
  const { formStateNegotiation } = useSupplierFormStoreFacade();

  const { getConditionalFormRules } = useSupplierFormView();

  const baseRules: Record<string, Rule[]> = {
    phone: [
      { required: true, message: 'El teléfono es obligatorio', trigger: 'change' },
      { pattern: /^\d{9}$/, message: 'El teléfono debe tener 9 dígitos', trigger: 'change' },
    ],
    email: [
      { required: true, message: 'El correo es obligatorio', trigger: 'change' },
      { type: 'email', message: 'Ingresa un correo válido', trigger: 'change' },
    ],
  };

  // Función para obtener las rules del form si estan en el tab negotiation (en otros tabs son de lectura)
  const formRules: Record<string, Rule[]> = getConditionalFormRules('negotiation', baseRules);

  return {
    formRules,
    formStateNegotiation,
  };
}
