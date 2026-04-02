import { ref, computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import type {
  Inclusion,
  Requirement,
  SelectOption,
} from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';
import { useTrainContentFormAction } from './useTrainContentFormAction';
import { useTrainContentDataLoader } from './useTrainContentDataLoader';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import { sendTrainTextForReview } from '../services/trainContent.service';
interface UseTrainContentFormProps {
  currentKey: string;
  currentCode: string;
}

export const useTrainContentForm = (props?: UseTrainContentFormProps) => {
  const route = useRoute();
  const trainStore = useTrainConfigurationStore();
  const navigationStore = useNavigationStore();
  const { getSectionsItemActive } = storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const supportResourcesStore = useSupportResourcesStore();
  const { inclusions: supplierInclusions, requirements: supplierRequirements } =
    storeToRefs(supportResourcesStore);

  const textRemark = ref('');
  const remarkStatus = ref<string>('PENDING');
  const isSendingReview = ref(false);

  const textRemarkModel = computed({
    get: () => textRemark.value,
    set: (v: string) => {
      textRemark.value = v;
    },
  });

  const handleSendForReview = async () => {
    if (!textRemark.value) return;

    const productSupplierId = route.params.id as string;
    const serviceDetailsId = trainStore.getServiceDetailId(
      props?.currentKey || '',
      props?.currentCode || ''
    );
    if (!productSupplierId || !serviceDetailsId) return;

    isSendingReview.value = true;
    try {
      await sendTrainTextForReview(productSupplierId, serviceDetailsId, {
        textTypeCode: 'REMARK',
        html: textRemark.value,
      });
      remarkStatus.value = 'SENT_FOR_REVIEW';
    } finally {
      isSendingReview.value = false;
    }
  };
  const totalItems = 3;

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? false;
  });

  const showEditModal = ref(false);

  const isFormValid = computed(() => {
    return completedFields.value === totalItems;
  });

  const handleEditMode = () => {
    showEditModal.value = true;
  };

  const handleConfirmEdit = () => {
    if (getSectionsItemActive.value) {
      getSectionsItemActive.value.editing = true;
    }
    showEditModal.value = false;
    loadContentOnEditMode();
  };

  const handleCancelEdit = () => {
    showEditModal.value = false;
  };

  const getInclusionLabel = (code: string | null): string => {
    if (code == null) return '';
    return inclusionOptions.value.find((o) => o.value === code)?.label ?? '';
  };

  const getRequirementLabel = (code: string | null): string => {
    if (code == null) return '';
    return requirementOptions.value.find((o) => o.value === code)?.label ?? '';
  };

  const getRemarkExcerpt = (maxLength = 120): string => {
    const html = textRemark.value || '';
    if (!html) return '';
    const withoutNbsp = html.replace(/&nbsp;/gi, ' ').replace(/&#160;/g, ' ');
    const plain = withoutNbsp
      .replace(/<[^>]*>/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
    return plain.length <= maxLength ? plain : `${plain.slice(0, maxLength)}...`;
  };

  const completedFields = computed(() => {
    let count = 0;
    if (textRemark.value) count++;
    if (inclusions.value.some((inclusion) => inclusion.description !== null)) count++;
    if (requirements.value.some((requirement) => requirement.description !== null)) count++;
    return count;
  });

  const inclusions = ref<Inclusion[]>([
    {
      id: null,
      description: null,
      incluye: false,
      visibleCliente: false,
      editMode: false,
    },
  ]);

  const requirements = ref<Requirement[]>([
    {
      id: null,
      description: null,
      visibleCliente: false,
      editMode: false,
    },
  ]);

  const inclusionOptions = computed<SelectOption[]>(() => {
    const inclusionsList = supplierInclusions.value;
    if (!inclusionsList || inclusionsList.length === 0) {
      return [];
    }
    return inclusionsList.map((inclusion) => ({
      label: inclusion.name,
      value: inclusion.code,
    }));
  });

  const requirementOptions = computed<SelectOption[]>(() => {
    const requirementsList = supplierRequirements.value;
    if (!requirementsList || requirementsList.length === 0) {
      return [];
    }
    return requirementsList.map((requirement) => ({
      label: requirement.name,
      value: requirement.code,
    }));
  });

  const inclusionsWithoutId = computed(() => {
    return inclusions.value.filter((inclusion) => inclusion.id === null);
  });

  const addInclusion = () => {
    inclusions.value.push({
      id: null,
      description: null,
      incluye: false,
      visibleCliente: false,
      editMode: false,
    });
  };

  const removeInclusion = (index: number) => {
    if (inclusions.value.length > 1) {
      inclusions.value.splice(index, 1);
    }
  };

  const addRequirement = () => {
    requirements.value.push({
      id: null,
      description: null,
      visibleCliente: false,
      editMode: false,
    });
  };

  const removeRequirement = (index: number) => {
    if (requirements.value.length > 1) {
      requirements.value.splice(index, 1);
    }
  };

  const { loadContentData } = useTrainContentDataLoader(textRemark, inclusions, requirements);

  const loadContentOnEditMode = () => {
    const key = props?.currentKey || '';
    const code = props?.currentCode || '';
    loadContentData(key, code);
  };

  // Acciones de guardado
  const { isLoadingButton, handleSaveAndAdvance } = useTrainContentFormAction(
    textRemark,
    inclusions,
    requirements,
    {
      currentKey: props?.currentKey || '',
      currentCode: props?.currentCode || '',
      currentItemId: getSectionsItemActive.value?.id || '',
    },
    setCompletedItem
  );

  const handleSave = async () => {
    try {
      await handleSaveAndAdvance();
    } catch (error) {
      console.error('Error al guardar el contenido:', error);
    }
  };

  onMounted(() => {
    loadContentOnEditMode();
  });

  return {
    isFormValid,
    totalItems,
    completedFields,
    textRemark,
    textRemarkModel,
    inclusions,
    inclusionsWithoutId,
    requirements,
    inclusionOptions,
    requirementOptions,
    isLoadingButton,
    isEditingContent,
    showEditModal,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,
    getInclusionLabel,
    getRequirementLabel,
    getRemarkExcerpt,
    loadContentOnEditMode,

    addRequirement,
    removeRequirement,
    addInclusion,
    removeInclusion,
    handleSave,
    handleSendForReview,
    isSendingReview,
    remarkStatus,
  };
};
