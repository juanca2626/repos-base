import { computed, type Ref, type ComputedRef } from 'vue';
import type {
  Profile,
  PointType,
} from '@/modules/negotiations/products/general/interfaces/resources';
import { ServiceStatusForm } from '@/modules/negotiations/products/configuration/content/shared/enums/service-status.enum';

interface SelectOption {
  label: string;
  value: string | number | undefined;
}

export const useServiceDetailsFormOptions = (
  supplierProfiles: Ref<Profile[]> | ComputedRef<Profile[]>,
  supplierPointTypes: Ref<PointType[]> | ComputedRef<PointType[]>
) => {
  const estadoOptions = [
    { label: 'Activo', value: ServiceStatusForm.ACTIVO },
    { label: 'Suspendido', value: ServiceStatusForm.SUSPENDIDO },
    { label: 'Inactivo', value: ServiceStatusForm.INACTIVO },
  ] as const;

  const perfilOptions = computed(() => {
    return supplierProfiles.value.map((profile: Profile) => ({
      label: profile.name,
      value: profile.code,
    }));
  });

  const puntoInicioOptions = computed(() => {
    return supplierPointTypes.value.map((pointType: PointType) => ({
      label: pointType.name,
      value: pointType.code,
    }));
  });

  const puntoFinOptions = computed(() => {
    return supplierPointTypes.value.map((pointType: PointType) => ({
      label: pointType.name,
      value: pointType.code,
    }));
  });

  const getLabelByValue = (
    options: ComputedRef<SelectOption[]> | readonly SelectOption[],
    value: any
  ): string => {
    const optionsArray = 'value' in options ? options.value : options;
    return optionsArray.find((opt) => opt.value === value)?.label || '';
  };

  const getPerfilLabel = (value: any) => {
    return getLabelByValue(perfilOptions, value);
  };

  const getPuntoInicioLabel = (value: any) => {
    return getLabelByValue(puntoInicioOptions, value);
  };

  const getPuntoFinLabel = (value: any) => {
    return getLabelByValue(puntoFinOptions, value);
  };

  const getEstadoLabel = (value: any) => {
    return getLabelByValue(estadoOptions, value);
  };

  return {
    perfilOptions,
    puntoInicioOptions,
    puntoFinOptions,
    estadoOptions,
    getPerfilLabel,
    getPuntoInicioLabel,
    getPuntoFinLabel,
    getEstadoLabel,
  };
};
