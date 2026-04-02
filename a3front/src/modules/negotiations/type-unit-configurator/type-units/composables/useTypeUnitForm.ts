import { onMounted, ref } from 'vue';
import { emit as emitBus, on } from '@/modules/negotiations/api/eventBus';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleError } from '@/modules/negotiations/api/responseApi';
import { storeToRefs } from 'pinia';
import type {
  TypeUnit,
  TypeUnitForm,
} from '@/modules/negotiations/type-unit-configurator/type-units/interfaces';
import type { TypeUnitFormItemRef } from '@/modules/negotiations/type-unit-configurator/type-units/types';
import { useTypeUnitFormStore } from '@/modules/negotiations/type-unit-configurator/type-units/store/typeUnitFormStore';
import { useNotifications } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useNotifications';
import { useTypeUnitUpdate } from '@/modules/negotiations/type-unit-configurator/type-units/composables/useTypeUnitUpdate';

export const useTypeUnitForm = () => {
  const resource = 'units';
  const isLoading = ref<boolean>(false);
  const formRefsTypeUnit = ref<TypeUnitFormItemRef[]>([]);

  const typeUnitFormStore = useTypeUnitFormStore();

  const { typeUnits, isEditMode, showDrawerForm } = storeToRefs(typeUnitFormStore);

  const { resetTypeUnits, addTypeUnit, setIsEditMode, setTypeUnitFromEdit, setShowDrawerForm } =
    typeUnitFormStore;

  const { showNotificationError, showNotificationSuccess } = useNotifications();

  const { update } = useTypeUnitUpdate(isLoading);

  const initData = (): void => {
    resetTypeUnits();
    setIsEditMode(false);
  };

  const resetForm = () => {
    formRefsTypeUnit.value.map((form: TypeUnitFormItemRef) => form?.resetFields());
  };

  const validateAllForms = async () => {
    const validations = formRefsTypeUnit.value.map((form: TypeUnitFormItemRef) =>
      form?.validateFields()
    );

    const results = await Promise.all(validations);

    return results.every((valid) => valid);
  };

  const buildRequest = (typeUnit: TypeUnitForm) => {
    return {
      code: typeUnit.code,
      name: typeUnit.name,
      status: typeUnit.status,
      is_trunk: typeUnit.isTrunk,
    };
  };

  const handleClose = (): void => {
    resetForm();
    initData();
    setShowDrawerForm(false);
  };

  const processResponses = (errors: any[], successMessages: string[]) => {
    if (errors.length === 0) {
      showNotificationSuccess('Todas las unidades se guardaron correctamente.');
    } else if (successMessages.length === 0) {
      showNotificationError('Todas las unidades no se pudieron guardar.');

      errors.forEach((error) => handleError(error));
    } else {
      showNotificationError('Algunas unidades no se pudieron guardar.');

      successMessages.forEach((message) => showNotificationSuccess(message));

      errors.forEach((error) => handleError(error));
    }
  };

  const completeSaveForm = () => {
    emitBus('reloadTypeUnitList');
    resetForm();
    handleClose();
  };

  const storeForm = async () => {
    isLoading.value = true;

    const successMessages: string[] = [];
    const errors = [];

    for (const typeUnit of typeUnits.value) {
      try {
        const request = buildRequest(typeUnit);
        const { data } = await supportApi.post(resource, request);

        if (data.success) {
          successMessages.push(
            `Unidad ${typeUnit.code} - ${typeUnit.name} guardada correctamente.`
          );
        }
      } catch (error: any) {
        errors.push(error);
      }
    }

    processResponses(errors, successMessages);

    if (successMessages.length > 0) {
      completeSaveForm();
    }

    isLoading.value = false;
  };

  const updateForm = async () => {
    const success = await update(typeUnits.value[0]);

    if (success) {
      completeSaveForm();
    }
  };

  const handleSubmit = async () => {
    try {
      const isValid = await validateAllForms();

      if (isValid) {
        if (isEditMode.value) {
          await updateForm();
        } else {
          await storeForm();
        }
      }
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  on('editTypeUnit', (item: TypeUnit) => {
    setShowDrawerForm(true);
    setDataToFormUpdate(item);
  });

  const setDataToFormUpdate = async (item: TypeUnit) => {
    setIsEditMode(true);
    setTypeUnitFromEdit(item);
  };

  onMounted(() => {
    initData();
  });

  return {
    showDrawerForm,
    formRefsTypeUnit,
    typeUnits,
    isLoading,
    isEditMode,
    handleClose,
    handleSubmit,
    addTypeUnit,
  };
};
