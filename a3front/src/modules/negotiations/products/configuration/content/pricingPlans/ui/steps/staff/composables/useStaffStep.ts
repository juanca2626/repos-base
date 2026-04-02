import { computed } from 'vue';
import { ServiceTypeEnum } from '@/modules/negotiations/products/configuration/enums/ServiceType.enum';
import type {
  StaffState,
  StaffOption,
} from '@/modules/negotiations/products/configuration/content/pricingPlans/types/staff.types';
interface Props {
  model: StaffState;
  serviceType: ServiceTypeEnum;
}

export const useStaffStep = (props: Props) => {
  const staffOptions = computed<StaffOption[]>(() => [
    { value: 'guide', label: 'Guía' },
    { value: 'tour_leader', label: 'Tour Leader' },
    { value: 'driver', label: 'Chofer' },
    { value: 'guide_representative', label: 'Guía o representante' },
    { value: 'scort_tour_conductor', label: 'Scort o Tour Conductor' },
  ]);

  const staffLabelMap = computed<Record<string, string>>(() => {
    return staffOptions.value.reduce<Record<string, string>>((acc, item) => {
      acc[item.value] = item.label;
      return acc;
    }, {});
  });

  const isPackageService = computed(() => {
    return props.serviceType === ServiceTypeEnum.PACKAGE;
  });

  const getStaffLabel = (staffId: string) => {
    return staffLabelMap.value[staffId] || staffId;
  };

  return {
    staffOptions,
    isPackageService,
    staffLabelMap,
    getStaffLabel,
  };
};
