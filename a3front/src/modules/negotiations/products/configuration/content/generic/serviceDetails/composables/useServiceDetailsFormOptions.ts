import { computed, type Ref, type ComputedRef } from 'vue';
import type {
  Profile,
  PointType,
} from '@/modules/negotiations/products/configuration/infrastructure/supportResource/dtos/supportResource.interface';

import { ServiceStatusForm } from '@/modules/negotiations/products/configuration/content/shared/enums/service-status.enum';

interface SelectOption {
  label: string;
  value: string | number | undefined;
}

export const useServiceDetailsFormOptions = (
  serviceSubTypes: Ref<any[]> | ComputedRef<any[]>,
  profiles: Ref<Profile[]> | ComputedRef<Profile[]>,
  pointTypes: Ref<PointType[]> | ComputedRef<PointType[]>
) => {
  const estadoOptions = [
    { label: 'Activo', value: ServiceStatusForm.ACTIVO },
    { label: 'Suspendido', value: ServiceStatusForm.SUSPENDIDO },
    { label: 'Inactivo', value: ServiceStatusForm.INACTIVO },
  ] as const;

  const subtipoOptions = computed(() => {
    return serviceSubTypes.value.map((subType) => ({
      label: subType.name,
      value: subType.code,
    }));
  });

  const perfilOptions = computed(() => {
    return profiles.value.map((profile: Profile) => ({
      label: profile.name,
      value: profile.code,
    }));
  });

  const puntoInicioOptions = computed(() => {
    return pointTypes.value.map((pointType: PointType) => ({
      label: pointType.name,
      value: pointType.code,
    }));
  });

  const puntoFinOptions = computed(() => {
    return pointTypes.value.map((pointType: PointType) => ({
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

  const getLabelsByValues = (
    options: ComputedRef<SelectOption[]> | readonly SelectOption[],
    value: string | string[] | undefined
  ): string => {
    if (value == null) return '';
    const values = Array.isArray(value) ? value : [value];
    const optionsArray = 'value' in options ? options.value : options;
    return (
      values
        .map((v) => optionsArray.find((opt) => opt.value === v)?.label || '')
        .filter(Boolean)
        .join(', ') || ''
    );
  };

  const getSubtipoLabel = (value: any) => {
    return getLabelByValue(subtipoOptions, value);
  };

  const getPerfilLabel = (value: any) => {
    return getLabelByValue(perfilOptions, value);
  };

  const getPuntoInicioLabel = (value: string | string[] | undefined) => {
    return getLabelsByValues(puntoInicioOptions, value);
  };

  const getPuntoFinLabel = (value: string | string[] | undefined) => {
    return getLabelsByValues(puntoFinOptions, value);
  };

  const getEstadoLabel = (value: any) => {
    return getLabelByValue(estadoOptions, value);
  };

  return {
    subtipoOptions,
    perfilOptions,
    puntoInicioOptions,
    puntoFinOptions,
    estadoOptions,
    getSubtipoLabel,
    getPerfilLabel,
    getPuntoInicioLabel,
    getPuntoFinLabel,
    getEstadoLabel,
  };
};
