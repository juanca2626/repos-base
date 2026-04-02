import { reactive, ref } from 'vue';
import { debounce } from 'lodash-es';
import type { FiltersInputsInterface } from '@/modules/negotiations/type-unit-configurator/type-units/interfaces';
import { useTypeUnitFilterStore } from '@/modules/negotiations/type-unit-configurator/type-units/store/typeUnitFilterStore';

export function useTypeUnitFilter() {
  const { setCodeOrName, setStatus } = useTypeUnitFilterStore();

  const showDownloadResult = ref<boolean>(false);

  const formState = reactive<FiltersInputsInterface>({
    codeOrName: null,
    status: null,
  });

  const handleDownloadResult = () => {
    showDownloadResult.value = true;
  };

  const handleCodeOrName = debounce(() => {
    setCodeOrName(formState.codeOrName);
  }, 500);

  const handleStatus = (): void => {
    setStatus(formState.status);
  };

  const cleanFilters = (): void => {
    formState.codeOrName = null;
    formState.status = null;
    setCodeOrName(null);
    setStatus(null);
  };

  return {
    formState,
    handleCodeOrName,
    showDownloadResult,
    cleanFilters,
    handleStatus,
    handleDownloadResult,
  };
}
