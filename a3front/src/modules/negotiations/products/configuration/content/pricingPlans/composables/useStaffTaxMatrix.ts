import { computed, watch } from 'vue';
import { buildStaffTaxDefinitions } from '@/modules/negotiations/products/configuration/content/pricingPlans/engine/staff/buildStaffTaxDefinitions';
import { syncStaffTaxesMap } from '@/modules/negotiations/products/configuration/content/pricingPlans/engine/staff/syncStaffTaxesMap';
import type {
  StaffState,
  StaffTaxDefinition,
  StaffTaxesMap,
} from '@/modules/negotiations/products/configuration/content/pricingPlans/types/staff.types';

interface Props {
  model: StaffState;
}

export const useStaffTaxMatrix = ({ model }: Props) => {
  const taxDefinitions = computed<StaffTaxDefinition[]>(() => {
    return buildStaffTaxDefinitions(model.taxes);
  });

  const hasVisibleTaxes = computed(() => {
    return taxDefinitions.value.length > 0;
  });

  const syncMatrix = () => {
    model.staff.staffTaxes = syncStaffTaxesMap({
      selectedStaff: model.staff.selectedStaff,
      currentStaffTaxes: model.staff.staffTaxes as StaffTaxesMap,
      definitions: taxDefinitions.value,
    });
  };

  const hasAnyTaxSelected = (staffId: string) => {
    const staffTaxes = model.staff.staffTaxes[staffId] ?? {};

    return taxDefinitions.value.some((definition) => Boolean(staffTaxes[definition.key]));
  };

  const isTaxSelected = (staffId: string, taxKey: string) => {
    return Boolean(model.staff.staffTaxes[staffId]?.[taxKey]);
  };

  const setTaxSelected = (staffId: string, taxKey: string, checked: boolean) => {
    if (!model.staff.staffTaxes[staffId]) {
      model.staff.staffTaxes[staffId] = {};
    }

    model.staff.staffTaxes[staffId][taxKey] = checked;
  };

  watch(
    () => ({
      selectedStaff: model.staff.selectedStaff,
      affectedByIGV: model.taxes.affectedByIGV,
      servicePercentage: model.taxes.servicePercentage,
      additionalPercentage: model.taxes.additionalPercentage,
      additionalPercentages: model.taxes.additionalPercentages.map((item) => ({
        id: item.id,
        name: item.name,
        percentage: item.percentage,
      })),
    }),
    () => {
      syncMatrix();
    },
    {
      deep: true,
      immediate: true,
    }
  );

  return {
    taxDefinitions,
    hasVisibleTaxes,
    syncMatrix,
    hasAnyTaxSelected,
    isTaxSelected,
    setTaxSelected,
  };
};
