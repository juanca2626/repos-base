import type { FormInstance, Rule } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { reactive, ref, watch } from 'vue';
import type {
  DownloadResultForm,
  DownloadResultProps,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import type { ModalEmitTypeInterface } from '@/modules/negotiations/supplier/interfaces';
import { EXCEL_FORMAT_DOWNLOAD } from '@/modules/negotiations/supplier/register/configuration-module/constants/file-download-config.';
import { useTechnicalSheetStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useTechnicalSheetStore';

export const useDownloadResult = (emit: ModalEmitTypeInterface, props: DownloadResultProps) => {
  const isLoading = ref<boolean>(false);
  const visibleAlert = ref<boolean>(true);
  const formRefDownloadResult = ref<FormInstance | null>(null);

  const initFormState: DownloadResultForm = {
    filename: '',
    format: 'excel',
    extension: 'xlsx',
    supplierBranchOfficeIds: [],
  };

  const technicalSheetStore = useTechnicalSheetStore();

  const { isTransportVehicleActive } = storeToRefs(technicalSheetStore);

  const formState = reactive<DownloadResultForm>(structuredClone(initFormState));

  const availableFileFormats = [{ ...EXCEL_FORMAT_DOWNLOAD }];

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
    visibleAlert.value = true;
  };

  const handleCancel = () => {
    resetForm();
    initData();
    emit('update:showModal', false);
  };

  const handleDownload = async () => {
    isLoading.value = true;

    try {
      await formRefDownloadResult.value?.validate();
      await props.onDownload(formState);
      handleCancel();
    } catch (error) {
      console.error('Ocurrió un error al descargar el archivo:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleCloseAlert = () => {
    visibleAlert.value = false;
  };

  watch(
    () => props.showModal,
    (newVal) => {
      if (newVal) {
        formState.filename = props.initialFilename ?? '';
      }
    }
  );

  return {
    isLoading,
    formState,
    availableFileFormats,
    visibleAlert,
    formRefDownloadResult,
    formRules,
    isTransportVehicleActive,
    handleDownload,
    handleCancel,
    handleCloseAlert,
  };
};
