import { ref, onMounted, nextTick } from 'vue';
import type { Rule } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { useSupplierFormViewStore } from '@/modules/negotiations/supplier/register/store/supplierFormViewStore';
import { EditableNegotiationSectionEnum } from '@/modules/negotiations/supplier/register/enums/editable-negotiation-section.enum';
import { EditableTreasurySectionEnum } from '@/modules/negotiations/supplier/register/enums/editable-treasury-section.enum';

export function useSupplierFormView() {
  const activeKey = ref('1'); // Tab principal

  const marginRightInfoCollaborators = ref(0);

  const editableNegotiationSections = Object.values(EditableNegotiationSectionEnum);

  const editableTreasurySections = Object.values(EditableTreasurySectionEnum);

  const supplierFormViewStore = useSupplierFormViewStore();

  const { collapseSectionKeys, activeCollaboratorTab } = storeToRefs(supplierFormViewStore);

  const { registrationNotifyModals, setCollapseSectionKeys } = supplierFormViewStore;

  const handleChangeCollaboratorTab = () => {
    setCollapseSectionKeys(activeCollaboratorTab.value);
  };

  const setMarginRightInfoCollaborators = () => {
    const basePaddingNavList = 42;
    const tabsNavList = document.querySelector('.ant-tabs-nav-list');
    const tabsNavListWidth = tabsNavList?.getBoundingClientRect().width ?? 0;

    const infoCollaborators = document.querySelector('.info-collaborators');
    const infoCollaboratorsWidth = infoCollaborators?.getBoundingClientRect().width ?? 0;

    marginRightInfoCollaborators.value =
      tabsNavListWidth - infoCollaboratorsWidth - basePaddingNavList;
  };

  const getConditionalFormRules = (tabApplyRules: string, formRules: Record<string, Rule[]>) => {
    return activeCollaboratorTab.value === tabApplyRules ? formRules : {};
  };

  const isEditableSection = (section: string, editableSections: string[]): boolean => {
    return editableSections.includes(section);
  };

  onMounted(() => {
    nextTick(() => {
      setMarginRightInfoCollaborators();
    });
  });

  return {
    marginRightInfoCollaborators,
    activeCollaboratorTab,
    activeKey,
    collapseSectionKeys,
    registrationNotifyModals,
    editableNegotiationSections,
    editableTreasurySections,
    handleChangeCollaboratorTab,
    getConditionalFormRules,
    isEditableSection,
  };
}
