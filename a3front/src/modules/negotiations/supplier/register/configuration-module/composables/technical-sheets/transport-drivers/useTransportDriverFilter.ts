import { reactive, ref } from 'vue';
import { debounce } from 'lodash-es';
import { driverDocumentStatusOptions } from '@/modules/negotiations/supplier/register/configuration-module/constants/driver-document-status';
import { useTransportDriverFiltersStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useTransportDriverFiltersStore';
import type { DriverFiltersInputsInterface } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export const useTransportDriverFilter = () => {
  const isLoading = ref<boolean>(false);

  const formState = reactive<DriverFiltersInputsInterface>({
    name: '',
    surnames: '',
    documentStatus: null,
  });

  const { setName, setSurnames, setDocumentStatus, resetFilters } =
    useTransportDriverFiltersStore();

  const handleName = debounce(() => {
    setName(formState.name);
  }, 500);

  const handleSurnames = debounce(() => {
    setSurnames(formState.surnames);
  }, 500);

  const handleDocumentStatus = () => {
    setDocumentStatus(formState.documentStatus);
  };

  const cleanFilters = () => {
    formState.name = '';
    formState.surnames = '';
    formState.documentStatus = null;
    resetFilters();
  };

  return {
    formState,
    driverDocumentStatusOptions,
    isLoading,
    handleName,
    handleSurnames,
    cleanFilters,
    handleDocumentStatus,
  };
};
