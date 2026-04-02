import type { FormInstance, Rule } from 'ant-design-vue/es/form';
import dayjs from 'dayjs';
import { computed, reactive, ref, watch } from 'vue';
import type {
  DownloadResultForm,
  DownloadResultProps,
} from '@/modules/negotiations/type-unit-configurator/type-units/interfaces';
import type { ModalEmitTypeInterface } from '@/modules/negotiations/supplier/interfaces';
import { useTypeUnitConfiguratorStore } from '@/modules/negotiations/type-unit-configurator/store/typeUnitConfiguratorStore';
import { storeToRefs } from 'pinia';
import { buildPeriodDateRange } from '@/modules/negotiations/type-unit-configurator/helpers/periodHelper';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { useNotifications } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useNotifications';

export const useDownloadResult = (emit: ModalEmitTypeInterface, props: DownloadResultProps) => {
  const isLoading = ref<boolean>(false);
  const visibleAlert = ref<boolean>(true);
  const formRefDownloadResult = ref<FormInstance | null>(null);
  const currentYear = dayjs().year();

  const initFormState: DownloadResultForm = {
    filename: 'Reporte_configuracion_transporte',
    periodYear: currentYear,
  };

  const formState = reactive<DownloadResultForm>(structuredClone(initFormState));

  const { showNotificationSuccess } = useNotifications();

  const typeUnitConfiguratorStore = useTypeUnitConfiguratorStore();

  const { periodYears, isLoading: isLoadingPeriodYears } = storeToRefs(typeUnitConfiguratorStore);
  const { loadPeriodYearsIfEmpty } = typeUnitConfiguratorStore;

  const formRules: Record<string, Rule[]> = {
    filename: [
      { required: true, message: 'Debe ingresar el nombre del archivo', trigger: 'change' },
    ],
  };

  const initForm = () => {
    Object.assign(formState, structuredClone(initFormState));
  };

  const resetForm = () => {
    formRefDownloadResult.value?.resetFields();
  };

  const initData = () => {
    initForm();
  };

  const handleCancel = () => {
    resetForm();
    initData();
    emit('update:showModal', false);
  };

  const download = async () => {
    const { dateFrom, dateTo } = buildPeriodDateRange(formState.periodYear);

    const response = await supportApi.get('unit-settings/detail-report', {
      responseType: 'blob',
      params: {
        dateFrom,
        dateTo,
      },
    });

    const fileBlob = new Blob([response.data]);
    const link = document.createElement('a');
    link.href = URL.createObjectURL(fileBlob);
    link.download = `${formState.filename}.xlsx`;
    link.click();

    showNotificationSuccess('Archivo descargado exitosamente.');
  };

  const handleDownload = async () => {
    isLoading.value = true;

    try {
      await formRefDownloadResult.value?.validate();
      await download();
      handleCancel();
    } catch (error) {
      console.error('Ocurrió un error al descargar el archivo:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const isLoadingMain = computed(() => isLoading.value || isLoadingPeriodYears.value);

  watch(
    () => props.showModal,
    async (newVal) => {
      if (newVal) {
        await loadPeriodYearsIfEmpty();
      }
    }
  );

  return {
    isLoadingMain,
    formState,
    visibleAlert,
    formRefDownloadResult,
    formRules,
    periodYears,
    handleDownload,
    handleCancel,
  };
};
