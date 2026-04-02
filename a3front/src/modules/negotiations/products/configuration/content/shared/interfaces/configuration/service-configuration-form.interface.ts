import type { Ref } from 'vue';
import type { FormState } from '@/modules/negotiations/products/configuration/interfaces/shared-service.interface';

export interface UseServiceConfigurationFormProps {
  currentKey: string;
  currentCode: string;
}

export interface UseServiceConfigurationFormParams {
  formState: Ref<FormState>;
  measurementUnitOptions: Ref<Array<{ label: string; value: any }>>;
  props: UseServiceConfigurationFormProps;
}
