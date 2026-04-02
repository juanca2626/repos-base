<template>
  <QuillEditor
    ref="editorRef"
    :content="modelValue"
    content-type="html"
    :toolbar="toolbarOptions"
    :style="style"
    @text-change="handleTextChange"
    @paste="handlePaste"
    @update:content="handleContentUpdate"
  />
</template>

<script setup>
  import { ref, watch } from 'vue';
  import { QuillEditor } from '@vueup/vue-quill';
  import '@vueup/vue-quill/dist/vue-quill.snow.css';
  import DOMPurify from 'dompurify';

  const props = defineProps({
    modelValue: {
      type: String,
      default: '',
    },
    style: {
      type: Object,
      default: () => ({ height: '200px', 'margin-bottom': '20px' }),
    },
    placeholder: {
      type: String,
      default: '',
    },
  });

  const emit = defineEmits(['update:modelValue', 'image-upload-start', 'image-upload-complete']);

  const editorRef = ref(null);
  const allFiles = ref(0);
  const allFilesCompleted = ref(0);
  const isProcessing = ref(false);

  // Configuración de la toolbar
  const toolbarOptions = [
    [{ font: [] }],
    [{ size: ['small', false, 'large', 'huge'] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ color: [] }, { background: [] }],
    [{ align: [] }],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ indent: '-1' }, { indent: '+1' }],
    [{ header: [1, 2, 3, 4, 5, 6, false] }],
    ['blockquote', 'code-block'],
    [{ script: 'sub' }, { script: 'super' }],
    ['clean'],
    ['link', 'image'],
  ];

  // Handler para actualizar el contenido
  const handleContentUpdate = (content) => {
    emit('update:modelValue', content);
  };

  // Convertir base64 a Blob
  const b64toBlob = (b64Data, contentType = '', sliceSize = 512) => {
    const byteCharacters = atob(b64Data);
    const byteArrays = [];

    for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
      const slice = byteCharacters.slice(offset, offset + sliceSize);
      const byteNumbers = new Array(slice.length);

      for (let i = 0; i < slice.length; i++) {
        byteNumbers[i] = slice.charCodeAt(i);
      }

      const byteArray = new Uint8Array(byteNumbers);
      byteArrays.push(byteArray);
    }

    return new Blob(byteArrays, { type: contentType });
  };

  // Subir archivo a Cloudinary
  const uploadFile = async (content64) => {
    return new Promise((resolve, reject) => {
      allFiles.value += 1;
      emit('image-upload-start');

      const contentType = content64.split(';base64')[0].replace('data:', '');
      const base64Data = content64.replace(`data:${contentType};base64,`, '');
      const file = b64toBlob(base64Data, contentType);

      const url = 'https://api.cloudinary.com/v1_1/dt4nv0isx/upload';
      const xhr = new XMLHttpRequest();

      xhr.open('POST', url, true);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          allFilesCompleted.value += 1;

          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            resolve(response.secure_url);
          } else {
            reject(new Error('Upload failed'));
          }

          if (allFiles.value === allFilesCompleted.value) {
            emit('image-upload-complete');
          }
        }
      };

      const formData = new FormData();
      formData.append('upload_preset', 'y4szi2vz');
      formData.append('folder', 'Customer-Service-Files');
      formData.append('tags', 'browser_upload,customer-service-file');
      formData.append('file', file);

      xhr.send(formData);
    });
  };

  // Procesar imágenes en el contenido
  const processImagesInContent = async (content) => {
    if (!content) return content;

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = content;
    const images = tempDiv.querySelectorAll('img');

    let processedContent = content;
    let uploadPromises = [];

    for (let i = 0; i < images.length; i++) {
      const img = images[i];
      const src = img.getAttribute('src');
      const alt = img.getAttribute('alt');

      // Si la imagen es base64 y no está renderizada
      if (src && src.startsWith('data:') && alt !== 'rendered') {
        const imageIndex = allFiles.value;
        const placeholderId = `image_placeholder_${imageIndex}`;

        // Reemplazar con placeholder temporal
        processedContent = processedContent.replace(
          img.outerHTML,
          `<div id="${placeholderId}"><em>Subiendo imagen ${imageIndex + 1}...</em></div>`
        );

        // Subir imagen
        uploadPromises.push(
          uploadFile(src, imageIndex)
            .then((imageUrl) => {
              // Reemplazar placeholder con imagen real
              processedContent = processedContent.replace(
                `<div id="${placeholderId}"><em>Subiendo imagen ${imageIndex + 1}...</em></div>`,
                `<img src="${imageUrl}" alt="rendered" style="max-width: 100%; height: auto;" />`
              );
            })
            .catch((error) => {
              console.error('Error uploading image:', error);
              // Mantener el placeholder de error
              processedContent = processedContent.replace(
                `<div id="${placeholderId}"><em>Subiendo imagen ${imageIndex + 1}...</em></div>`,
                `<div style="color: red;">Error subiendo imagen</div>`
              );
            })
        );
      }
    }

    // Esperar a que todas las imágenes se suban
    if (uploadPromises.length > 0) {
      await Promise.all(uploadPromises);
    }

    return processedContent;
  };

  // Manejar cambios en el texto
  const handleTextChange = async () => {
    if (isProcessing.value) return;

    isProcessing.value = true;

    try {
      const quill = editorRef.value.getQuill();
      const currentContent = quill.root.innerHTML;
      const processedContent = await processImagesInContent(currentContent);

      if (processedContent !== currentContent) {
        quill.root.innerHTML = processedContent;
        emit('update:modelValue', processedContent);
      }
    } catch (error) {
      console.error('Error processing content:', error);
    } finally {
      isProcessing.value = false;
    }
  };

  // Manejar paste de imágenes
  const handlePaste = async (event) => {
    const items = event.clipboardData?.items;

    if (!items) return;

    for (let item of items) {
      if (item.type.indexOf('image') !== -1) {
        event.preventDefault();

        const file = item.getAsFile();
        const reader = new FileReader();

        reader.onload = async (e) => {
          const base64 = e.target.result;

          // Insertar placeholder temporal
          const quill = editorRef.value.getQuill();
          const range = quill.getSelection();
          const imageIndex = allFiles.value;
          // const placeholderId = `paste_placeholder_${imageIndex}`;

          quill.insertText(range.index, `[Subiendo imagen ${imageIndex + 1}...]`);

          // Subir imagen
          try {
            const imageUrl = await uploadFile(base64, imageIndex);

            // Reemplazar placeholder con imagen real
            const content = quill.root.innerHTML;
            const newContent = content.replace(
              `[Subiendo imagen ${imageIndex + 1}...]`,
              `<img src="${imageUrl}" alt="rendered" style="max-width: 100%; height: auto;" />`
            );

            quill.root.innerHTML = newContent;
            emit('update:modelValue', newContent);
          } catch (error) {
            console.error('Error uploading pasted image:', error);
            // Reemplazar con mensaje de error
            const content = quill.root.innerHTML;
            const newContent = content.replace(
              `[Subiendo imagen ${imageIndex + 1}...]`,
              `[Error subiendo imagen]`
            );
            quill.root.innerHTML = newContent;
            emit('update:modelValue', newContent);
          }
        };

        reader.readAsDataURL(file);
        break;
      }
    }
  };

  // Sanitizar contenido al emitir
  watch(
    () => props.modelValue,
    (newValue) => {
      if (newValue) {
        const sanitized = DOMPurify.sanitize(newValue);
        if (sanitized !== newValue) {
          emit('update:modelValue', sanitized);
        }
      }
    }
  );

  defineExpose({
    processImagesInContent,
    getAllFiles: () => ({ total: allFiles.value, completed: allFilesCompleted.value }),
  });
</script>
