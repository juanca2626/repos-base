import type { Ref } from 'vue';
import { reactive, ref, watch } from 'vue';
import type { Rule } from 'ant-design-vue/es/form';
import type { FormInstance } from 'ant-design-vue';
import { useTransportConfiguratorStore } from '@/modules/negotiations/transport-configurator/store/TransportConfiguratorStore';
import type { Location } from '@/modules/negotiations/transport-configurator/interfaces/unit-transport-configurator.interface';

export const useUnitTransportConfigurator = (unitId: number) => {
  const store = useTransportConfiguratorStore();

  const form = reactive({
    name: null,
    code: null,
    status: null,
    is_trunk: null,
    locations: [],
  });

  const locationOptions = ref<Location[]>(store.locationOptions);
  const formRef: Ref<FormInstance | null> = ref(null);

  const rules: Record<string, Rule[]> = {
    code: [{ required: true, message: 'Código es requerido', min: 3, trigger: ['blur', 'change'] }],
    name: [{ required: true, message: 'Nombre es requerido', min: 3, trigger: ['blur', 'change'] }],
    locations: [
      {
        required: true,
        message: 'Seleccione al menos una sede',
        type: 'array',
        min: 1,
        trigger: ['blur', 'change'],
      },
    ],
  };

  const onDeleteUnit = () => {
    store.deleteUnit(unitId);
  };

  watch(
    () => form.locations,
    (newVal) => {
      console.log('Actualización de form.locations:', newVal);
    },
    { deep: true }
  );

  return {
    form,
    rules,
    locationOptions,
    onDeleteUnit,
    formRef,
  };
};
