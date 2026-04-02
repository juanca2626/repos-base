import { ref } from 'vue';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import { usePolicyManagerComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-manager.composable';
import { usePolicyFormStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-form-store-facade.composable';
import { FormModeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/form-mode.enum';
import { MEASUREMENT_UNITS } from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/data-init/data';
import { findLabel } from '@/modules/negotiations/supplier-new/helpers/supplier-registration/policies/policy-rule-helper';

dayjs.extend(utc);

export function usePolicyInformationBasicSummaryComposable() {
  const { openInformationBasic } = usePolicyManagerComposable();

  const { formState, setFormMode } = usePolicyFormStoreFacade();

  const measurementUnits = ref(MEASUREMENT_UNITS);

  const handleEdit = () => {
    openInformationBasic();
    setFormMode(FormModeEnum.EDIT);
  };

  const formatDate = (date: string | null) => {
    if (!date) return '';
    // Usar UTC para evitar que la zona horaria local cambie el día
    return dayjs.utc(date).format('DD/MM/YYYY');
  };

  const getMeasurementUnitLabel = () => {
    if (!formState.measurementUnit) return '-';
    return findLabel(measurementUnits, formState.measurementUnit);
  };

  const getSanitizedPolicyName = () => {
    return formState.name ? formState.name.replace(/:\s*$/, '').trim() : '';
  };

  const shouldShowSegmentation = () => {
    const sanitizedName = getSanitizedPolicyName();
    return sanitizedName.toLowerCase() !== 'política general';
  };

  return {
    formState,
    handleEdit,
    formatDate,
    getMeasurementUnitLabel,
    getSanitizedPolicyName,
    shouldShowSegmentation,
  };
}
