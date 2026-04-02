import { computed, onMounted, reactive, ref, watch } from 'vue';
import { filterOption } from '@/modules/negotiations/supplier/register//helpers/supplierFormHelper';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/supplier/interfaces/supplier-form.interface';
import type {
  FileUploadData,
  TransportDocumentReviewFormProps,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { useProcessReview } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useProcessReview';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
import type {
  TransportDocumentFormTypeMap,
  TransportDocumentResponseType,
} from '@/modules/negotiations/supplier/register/configuration-module/types';
import { useDocumentFormUtils } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useDocumentFormUtils';

export const useTransportDocumentReviewForm = (
  emit: DrawerEmitTypeInterface,
  props: TransportDocumentReviewFormProps<TypeTechnicalSheetEnum>
) => {
  const isLoadingForm = ref<boolean>(false);

  type FormState = TransportDocumentFormTypeMap[typeof props.typeTechnicalSheet];
  type TransportDocumentResponse = TransportDocumentResponseType<typeof props.typeTechnicalSheet>;

  const initFormState: FormState = {
    id: null,
    expirationDate: null,
    observations: null,
    status: null,
  };

  const formState = reactive<FormState>({ ...initFormState });

  const handleClose = (): void => {
    resetForm();
    initData();
    emit('update:showDrawerForm', false);
  };

  const {
    reviewEntity,
    isLoading: isLoadingUtil,
    handleDownload,
    handleShow,
  } = useDocumentFormUtils(props.typeTechnicalSheet);

  const {
    formRefObservation,
    commonFormRules: formRules,
    handleRejected,
    handleApproved,
    resetFields,
  } = useProcessReview({
    reviewEntity,
    formState,
    isLoading: isLoadingForm,
    handleClose,
  });

  const initDocumentFileData: FileUploadData = {
    isFileUploaded: true, // al revisar el documento ya está cargado
    fileSize: 0,
    filename: undefined,
    uploadInterval: null,
  };

  const documentFileData = reactive<FileUploadData>({ ...initDocumentFileData });

  const initForm = (): void => {
    Object.assign(formState, { ...initFormState });
  };

  const initDocumentFile = (): void => {
    Object.assign(documentFileData, { ...initDocumentFileData });
  };

  const initData = (): void => {
    initForm();
    initDocumentFile();
  };

  const resetForm = () => {
    resetFields();
  };

  const handleShowDocument = async () => {
    await handleShow(props.documentId ?? '', setDataToForm);
  };

  const setDataToForm = (data: TransportDocumentResponse) => {
    formState.id = data.id;
    formState.expirationDate = data.expiration_date;
    documentFileData.fileSize = data.document.size;
    documentFileData.filename = data.document.filename;
    documentFileData.documentId = data.id;
  };

  const handleDownloadDocument = async () => {
    await handleDownload(documentFileData.filename ?? '', formState.id);
  };

  const isLoading = computed(() => {
    return isLoadingForm.value || isLoadingUtil.value;
  });

  watch(
    () => props.showDrawerForm,
    (value) => {
      if (value) {
        handleShowDocument();
      }
    }
  );

  onMounted(async () => {
    initData();
  });

  return {
    formRefObservation,
    formState,
    isLoading,
    formRules,
    documentFileData,
    filterOption,
    handleClose,
    handleRejected,
    handleApproved,
    handleDownloadDocument,
  };
};
