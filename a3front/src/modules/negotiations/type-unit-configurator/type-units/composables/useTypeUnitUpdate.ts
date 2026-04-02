import { type Ref } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { TypeUnitForm } from '@/modules/negotiations/type-unit-configurator/type-units/interfaces';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';

export function useTypeUnitUpdate(isLoading: Ref<boolean>) {
  const update = async (form: TypeUnitForm): Promise<boolean> => {
    isLoading.value = true;

    try {
      const { data } = await supportApi.put(`units/${form.id}`, {
        code: form.code,
        name: form.name,
        status: form.status,
        is_trunk: form.isTrunk,
      });

      handleSuccessResponse(data);

      return data.success;
    } catch (error: any) {
      console.error('Error update type unit:', error);
      handleError(error);
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    update,
  };
}
