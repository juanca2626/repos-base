import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import type { CollaboratorType } from '@/modules/negotiations/supplier/types';

export const useSupplierFormViewStore = defineStore('supplierFormViewStore', () => {
  // Tab de colaboradores
  const activeCollaboratorTab = ref('negotiation');

  const collapseKeysNegotiations = [
    'observations',
    'general-information',
    'commercial-location',
    'operation-location',
    'contact-information',
  ];

  const collapseKeysTreasury = ['payment-condition'];

  const collapseKeysAccounting = ['tax-condition'];

  const collapseSectionKeys = ref<string[]>(collapseKeysNegotiations);

  const registrationNotifyModals = reactive({
    negotiation: false,
    treasury: false,
    accounting: false,
  });

  const setCollapseSectionKeys = (collaborator: string) => {
    const sectionCollaboratorMap = {
      treasury: collapseKeysTreasury,
      accounting: collapseKeysAccounting,
      negotiation: collapseKeysNegotiations,
    };

    collapseSectionKeys.value =
      sectionCollaboratorMap[collaborator as CollaboratorType] || collapseKeysNegotiations;
  };

  return {
    collapseSectionKeys,
    setCollapseSectionKeys,
    activeCollaboratorTab,
    registrationNotifyModals,
  };
});
