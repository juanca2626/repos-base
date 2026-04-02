import { computed, reactive } from 'vue';
import type {
  UploadPhotoProps,
  UploadPhotoEmits,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export const useUploadPhoto = (props: UploadPhotoProps, emit: UploadPhotoEmits) => {
  const formPreview = reactive({
    visible: false,
    image: '',
    title: '',
  });

  const getBase64 = (file: File) => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () => resolve(reader.result);
      reader.onerror = (error) => reject(error);
    });
  };

  const handleCancelPreview = () => {
    formPreview.visible = false;
    formPreview.title = '';
  };

  const handlePreview = async (file: any) => {
    if (!file.url && !file.preview) {
      file.preview = (await getBase64(file.originFileObj)) as string;
    }
    formPreview.image = file.url || file.preview;
    formPreview.visible = true;
    formPreview.title = file.name || file.url.substring(file.url.lastIndexOf('/') + 1);
  };

  const handleUpload = (fileData: any) => {
    if (props.onUpload) {
      props.onUpload(fileData, props.imageKey);
    }
  };

  const handleRemove = () => {
    if (props.onRemove) {
      props.onRemove(props.imageKey);
    }
  };

  const localFileList = computed({
    get: () => props.fileList,
    set: (newFileList) => emit('update:fileList', newFileList),
  });

  return {
    formPreview,
    localFileList,
    getBase64,
    handleCancelPreview,
    handlePreview,
    handleUpload,
    handleRemove,
  };
};
