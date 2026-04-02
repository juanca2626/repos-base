import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';

export const useNegotiationContactsStore = defineStore('negotiationContactsStore', () => {
  const initialFormStateContact: any = {
    firstname: '',
    type_contact_id: undefined,
    department_id: undefined,
    supplier_branch_office_id: undefined,
    phone: '',
    email: '',
  };

  const formStateContact = reactive<any>(structuredClone(initialFormStateContact));
  const formRules = reactive({
    firstname: [{ required: true, message: 'Ingresa nombre y apellidos', trigger: 'blur' }],
    type_contact_id: [{ required: true, message: 'Selecciona un tipo', trigger: 'change' }],
    department_id: [{ required: true, message: 'Selecciona un cargo', trigger: 'change' }],
    supplier_branch_office_id: [
      { required: false, message: 'Selecciona una ciudad', trigger: 'change' },
    ],
    phone: [{ required: true, message: 'Ingresa un teléfono', trigger: 'blur' }],
    email: [
      { required: true, message: 'Ingresa tu correo electrónico.', trigger: 'blur' },
      { type: 'email', message: 'Ingresa un correo electrónico válido.', trigger: 'change' },
      {
        whitespace: true,
        message: 'El correo electrónico no puede estar vacío.',
        trigger: 'change',
      },
    ],
  });

  const resetFormStateNegotiation = () => {
    Object.assign(formStateContact, structuredClone(initialFormStateContact));
  };

  const showModal = ref<boolean>(false);
  const setShowModal = (value: boolean) => {
    showModal.value = value;
  };

  const isEditModal = ref<boolean>(false);
  const setIsEditModal = (value: boolean) => {
    isEditModal.value = value;
  };

  return {
    formStateContact,
    formRules,
    showModal,
    setShowModal,
    resetFormStateNegotiation,
    isEditModal,
    setIsEditModal,
  };
});
