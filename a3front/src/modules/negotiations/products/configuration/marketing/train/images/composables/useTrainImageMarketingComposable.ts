import { ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import type { UploadFile } from 'ant-design-vue';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';

const MAX_FILE_SIZE = 300 * 1024; // 300KB
const ACCEPTED_TYPES = ['image/jpeg', 'image/png'];

export const useTrainImageMarketingComposable = () => {
  const navigationStore = useNavigationStore();
  const { activeTabKey, getSectionsCodeActive, getSectionsItemActive } =
    storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const fileList = ref<UploadFile[]>([]);
  const imageUrl = ref('');
  const imageUrls = ref<string[]>([]);

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? true;
  });

  const previewImages = [
    {
      src: 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=400&h=280&fit=crop',
      name: 'Nombre del archivo.jpg',
    },
    {
      src: 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=400&h=280&fit=crop',
      name: 'Nombre del archivo.jpg',
    },
    {
      src: 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=400&h=280&fit=crop',
      name: 'Nombre del archivo.jpg',
    },
  ];

  const beforeUpload = (file: File) => {
    const isImage = ACCEPTED_TYPES.includes(file.type);
    const isLt300K = file.size < MAX_FILE_SIZE;

    if (!isImage) {
      console.warn('Solo se permiten archivos JPG o PNG');
      return false;
    }
    if (!isLt300K) {
      console.warn('El archivo no debe superar 300KB');
      return false;
    }

    return true;
  };

  const customRequest = ({ onSuccess }: { onSuccess?: () => void }) => {
    onSuccess?.();
  };

  const addAnotherUrl = () => {
    if (imageUrl.value.trim()) {
      imageUrls.value = [...imageUrls.value, imageUrl.value.trim()];
      imageUrl.value = '';
    }
  };

  const hasChanges = computed(
    () => fileList.value.length > 0 || imageUrls.value.length > 0 || !!imageUrl.value
  );

  const handleSave = () => {
    console.log('handleSave');
    const tabKey = activeTabKey.value;
    const sectionCode = getSectionsCodeActive.value;
    const itemId = getSectionsItemActive.value?.id;
    if (tabKey != null && sectionCode != null && itemId != null) {
      setCompletedItem(tabKey, sectionCode, itemId);
    }
  };

  return {
    fileList,
    imageUrl,
    imageUrls,
    hasChanges,
    isEditingContent,
    previewImages,

    beforeUpload,
    customRequest,
    addAnotherUrl,
    handleSave,
  };
};
