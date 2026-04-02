import { computed } from 'vue';
import { usePolicyStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-store-facade.composable';
import { useSupplierViewComposable } from '@/modules/negotiations/supplier-new/composables/supplier-view.composable';
import { PolicyViewTypeEnum } from '@/modules/negotiations/supplier-new/enums/policy-view-type.enum';
import { usePolicyFormStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-form-store-facade.composable';
import { FormModeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/form-mode.enum';
import { isEditFormMode } from '@/modules/negotiations/supplier-new/helpers/supplier-registration/policies/form-mode-helper';

export function usePolicyManagerComposable() {
  const { closePolicyManager } = useSupplierViewComposable();

  const { formMode, resetFormState, setFormMode, setPolicyId } = usePolicyFormStoreFacade();

  const { policyViewType, setPolicyViewType } = usePolicyStoreFacade();

  const showInformationBasic = computed(
    () => policyViewType.value === PolicyViewTypeEnum.INFORMATION_BASIC
  );

  const showRegisteredDetails = computed(
    () => policyViewType.value === PolicyViewTypeEnum.REGISTERED_DETAILS
  );

  const openInformationBasic = () => {
    setPolicyViewType(PolicyViewTypeEnum.INFORMATION_BASIC);
  };

  const openRegisteredDetails = () => {
    setPolicyViewType(PolicyViewTypeEnum.REGISTERED_DETAILS);
  };

  const formTitle = computed(() => {
    const data = {
      [FormModeEnum.CREATE]: 'Agregar nueva política',
      [FormModeEnum.EDIT]: 'Editar política',
      [FormModeEnum.CLONE]: 'Copiar política',
    };

    return formMode.value ? data[formMode.value] : '';
  });

  const showCloneButton = computed(() => {
    return !isEditFormMode(formMode.value) && showInformationBasic.value;
  });

  const exitPolicyManager = () => {
    resetFormState();
    setFormMode(null);
    setPolicyId(null);
    closePolicyManager();
  };

  const backToPolicies = () => {
    exitPolicyManager();
  };

  return {
    showInformationBasic,
    showRegisteredDetails,
    formTitle,
    backToPolicies,
    exitPolicyManager,
    openRegisteredDetails,
    openInformationBasic,
    showCloneButton,
  };
}
