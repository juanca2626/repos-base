<template>
  <div v-if="!upload_files.file_url_link">
    <input
      type="file"
      :name="'file-' + index"
      :id="'file-' + index"
      class="inputfile inputfile-2"
      v-on:change="handleFileUploadDocument($event)"
    />
    <label :for="'file-' + index">
      <span class="iborrainputfile"
        ><icon-upload class="icon" /> {{ t('global.label.select_file') }}
      </span>
    </label>
  </div>
  <div v-else>
    <a
      :href="upload_files.file_url_link"
      v-if="['jpg', 'png', 'jpeg'].includes(upload_files.file_url_ext)"
      target="_blank"
    >
      <img :src="upload_files.file_url_link" width="150" height="150" />
    </a>
    <a :href="upload_files.file_url_link" target="_blank" v-else>
      <img src="../../images/document.jpg" width="150" height="150" />
    </a>

    <input type="button" :value="t('quote.label.delete')" @click="delete_upload()" />
  </div>
</template>

<script setup lang="ts">
  import { ref, onMounted } from 'vue';
  import IconUpload from '@/components/icons/IconUpload.vue';
  import { useUploadsStore } from '@/stores/global';
  import useLoader from '@/quotes/composables/useLoader';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n();

  const { showIsLoading, closeIsLoading } = useLoader();
  const useUpload = useUploadsStore();

  const props = defineProps({
    index: {
      type: Number,
      default: () => 0,
    },
    folder: {
      type: String,
      default: () => '',
    },
    document: {
      type: String,
      default: () => '',
    },
  });

  interface Emits {
    (e: 'onResponseFiles'): void;
  }

  const emits = defineEmits<Emits>();

  const loading = ref(false);
  const upload_files = ref({
    name: '',
    error: '',
    chunks: '',
    parts: '',
    file_url: '',
    file_url_link: '',
    file_url_ext: '',
  });

  const handleFileUploadDocument = async ($event: Event) => {
    const target = $event.target as HTMLInputElement;
    if (target && target.files) {
      showIsLoading();
      loading.value = true;
      upload_files.value.name = target.files[0].name;
      await encodeFile64Chunk(target.files[0]);
      await sendFilesToS3();
      loading.value = false;
      closeIsLoading();
    }
  };

  const encodeFile64Chunk = async (file) => {
    const chunkSize = 4 * 1024 * 1024; // 4 MB
    await chunkFile(file, chunkSize);
  };

  const chunkFile = async (file, chunkSize) => {
    await encodeFile64(file, chunkSize);
  };

  const encodeFile64 = async (file, chunkSize) => {
    const reader = new FileReader();
    const filePromises = new Promise((resolve, reject) => {
      reader.onloadend = async () => {
        try {
          let content = reader.result;
          let regexChunk = new RegExp(`.{1,${chunkSize}}`, 'g');
          let chunks = content.match(regexChunk);
          upload_files.value.chunks = chunks;
          upload_files.value.parts = chunks.length;

          for (let i = 1; i <= chunks.length; i++) {
            const chunk = chunks[i - 1];

            let response = await useUpload.sendChunkToLambda({
              filename: upload_files.value.name,
              part: i,
              base64_chunk: chunk,
            });

            upload_files.value.file_url = response.link;
          }
          resolve([]);
        } catch (err) {
          reject(err);
        }
      };
      reader.onerror = (error) => {
        reject(error);
      };
      reader.readAsDataURL(file);
    });

    await filePromises.then();
  };

  const sendFilesToS3 = async () => {
    if (upload_files.value.error == '') {
      let params = {
        nroref: '359911',
        folder: props.folder,
        filename: upload_files.value.name,
        file_url: upload_files.value.file_url,
        parts: upload_files.value.parts,
        base64: '',
      };

      let response = await useUpload.sendToS3([params]);
      upload_files.value.file_url_link = response[0].link;
      upload_files.value.file_url_ext = getFileExtension(response[0].link);
      emits('onResponseFiles', response[0].link);
    }
  };

  onMounted(() => {
    upload_files.value.file_url_link = props.document;
    upload_files.value.file_url_ext = getFileExtension(props.document);
  });

  const delete_upload = async () => {
    upload_files.value.file_url_link = '';
    emits('onResponseFiles', '');
  };

  const getFileExtension = function (filename) {
    if (filename) {
      return filename.slice(((filename.lastIndexOf('.') - 1) >>> 0) + 2);
    }
    return '';
  };
</script>

<style lang="scss" scoped>
  .container-input {
    text-align: center;
    background: #282828;
    border-top: 5px solid #c39f77;
    padding: 50px 0;
    border-radius: 6px;
    width: 50%;
    margin: 0 auto;
    margin-bottom: 20px;
  }

  .inputfile {
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    z-index: -1;
  }

  .inputfile + label {
    max-width: 80%;
    font-size: 1.25rem;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
    display: inline-block;
    overflow: hidden;
    padding: 0.625rem 1.25rem;
  }

  .inputfile + label svg {
    width: 1em;
    height: 1em;
    vertical-align: middle;
    fill: currentColor;
    margin-top: -0.25em;
    margin-right: 0.25em;
  }

  .inputfile + label svg.icon {
    margin: 0;
    width: 1.3em;
    height: 1.3em;
    display: inline-block;
    fill: none;
  }

  .iborrainputfile {
    font-size: 16px;
    font-weight: normal;
    font-family: 'Lato';
  }

  /* style 1 */

  .inputfile-1 + label {
    color: #fff;
    background-color: #c39f77;
  }

  .inputfile-1:focus + label,
  .inputfile-1.has-focus + label,
  .inputfile-1 + label:hover {
    background-color: #9f8465;
  }

  .inputfile-2 + label {
    color: #c39f77;
    border: 2px solid currentColor;
  }

  .inputfile-2:focus + label,
  .inputfile-2.has-focus + label,
  .inputfile-2 + label:hover {
    color: #9f8465;
  }
</style>
