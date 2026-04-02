import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import type { SupplierContactForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

export const useContactInformationStore = defineStore('contactInformation', () => {
  // const initialContactForm: ContactForm = {
  //   id: null,
  //   typeContactId: null,
  //   typeContactName: null,
  //   stateId: null,
  //   stateName: null,
  //   fullName: null,
  //   phone: null,
  //   email: null,
  //   isEditMode: true,
  // };

  const initialFormData: SupplierContactForm = {
    web: null,
    phone: null,
    email: null,
    countryPhoneCode: null,
    statePhoneCode: null,
    contacts: [
      {
        id: null,
        typeContactId: null,
        typeContactName: null,
        stateId: null,
        stateName: null,
        fullName: '',
        phone: '',
        email: '',
        isEditMode: true, // 👈 editable desde el inicio
      },
    ],
  };

  const formState = reactive<SupplierContactForm>(structuredClone(initialFormData));

  const formRef = ref<FormInstance | null>(null);

  const resetFormState = (withEmptyContact = false) => {
    Object.assign(formState, structuredClone(initialFormData));

    if (withEmptyContact) {
      formState.contacts.push({
        id: null,
        typeContactId: null,
        typeContactName: null,
        stateId: null,
        stateName: null,
        fullName: '',
        phone: '',
        email: '',
        isEditMode: true,
      });
    }
  };

  const addContact = () => {
    formState.contacts.push({
      id: null,
      typeContactId: null,
      typeContactName: null,
      stateId: null,
      stateName: null,
      fullName: null,
      phone: null,
      email: null,
      isEditMode: true,
    });
  };

  const editContact = (index: number) => {
    formState.contacts[index].isEditMode = true;
  };

  const removeContact = (index: number) => {
    formState.contacts.splice(index, 1);
  };

  const $reset = () => {
    formState.web = null;
    formState.phone = null;
    formState.email = null;
    formState.countryPhoneCode = null;
    formState.statePhoneCode = null;
    formState.contacts = [];
    // Reset any other form state...
  };

  return {
    initialFormData,
    formState,
    formRef,
    resetFormState,
    addContact,
    editContact,
    removeContact,
    $reset,
  };
});
