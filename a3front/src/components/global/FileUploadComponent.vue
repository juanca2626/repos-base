<template>
  <template v-if="editable">
    <a-popover placement="leftBottom" trigger="click" v-if="multiple">
      <template #content>
        <div class="p-3">
          <div>
            <div v-if="files.length > 0" class="files mb-3">
              <div class="file-item" v-for="(file, index) in files" :key="index">
                <span>{{ file.name }}</span>
                <span class="delete-file" @click="handleClickDeleteFile(index)">
                  {{ t('global.button.delete') }}
                </span>
              </div>
            </div>
            <div class="dropzone" v-bind="getRootProps()">
              <div
                class="border"
                :class="{
                  isDragActive,
                }"
              >
                <input v-bind="getInputProps()" />
                <p v-if="isDragActive">{{ t('global.label.drop_the_files_here') }} ...</p>
                <p v-else>{{ t('global.label.drag_and_drop_files_here') }}</p>
              </div>
            </div>
            <a-row align="middle" justify="center" class="mt-3" v-if="!props.multiple">
              <a-col>
                <a-button
                  key="submit"
                  type="primary"
                  default
                  size="large"
                  :loading="uploadsStore.isLoading || uploadsStore.isLoadingChunk"
                  v-if="files.length > 0 && error == '' && error_form == ''"
                  @click="sendFilesToS3"
                  >{{ t('global.label.upload_files') }}</a-button
                >
              </a-col>
            </a-row>
          </div>
        </div>
      </template>
      <template #title>
        <div class="px-3 pt-3">
          <span v-if="title == '' || title == null">Subir documentos a comunicación</span>
          <span v-else>{{ title }}</span>
        </div>
      </template>
      <a-button type="primary" default size="large">
        <font-awesome-icon :icon="['fas', 'upload']" />
        <b v-if="show_quantity && multiple"> ({{ files.length }})</b>
      </a-button>
    </a-popover>
    <template v-else>
      <div v-if="files.length > 0" class="files mb-3">
        <div class="file-item" v-for="(file, index) in files" :key="index">
          <span>{{ file.name }}</span>
          <span class="delete-file" @click="handleClickDeleteFile(index)">
            {{ t('global.button.delete') }}
          </span>
        </div>
      </div>
      <template v-else>
        <input
          type="file"
          :name="`file-${index}`"
          :id="`file-${index}`"
          class="inputfile inputfile-2"
          v-on:change="handleFileUploadDocument($event)"
        />
        <label :for="`file-${index}`">
          <span class="iborrainputfile"
            ><font-awesome-icon :icon="['fas', 'upload']" /> {{ t('global.label.select_file') }}
          </span>
        </label>

        <div class="file-item" v-if="props.link != '' && props.link != null">
          <span>
            <img :src="link" alt="" style="max-height: 100px; object-fit: cover" />
          </span>
          <span class="delete-file" @click="handleClickDeleteFile(0, true)">
            <a-tooltip>
              <template #title>{{ t('global.button.delete') }}</template>
              <font-awesome-icon :icon="['far', 'trash-can']" />
            </a-tooltip>
          </span>
        </div>
      </template>
    </template>
  </template>
  <template v-if="multiple">
    <div class="file-item" v-for="(link, index) in links" :key="`file-${index}`">
      <a-row type="flex" justify="space-between" align="middle" class="w-100">
        <a-col>
          <a :href="link" data-fancybox>
            <span>{{ link }}</span>
          </a>
        </a-col>
        <a-col>
          <a :href="link" target="_blank">
            <link-outlined />
          </a>
        </a-col>
      </a-row>
    </div>
  </template>
  <template v-else>
    <div class="d-block" v-if="link != '' && link != null">
      <a :href="link" data-fancybox>
        <img :src="link" alt="" style="max-height: 100px; object-fit: cover" />
      </a>
    </div>
  </template>
</template>

<script setup>
  import { ref } from 'vue';
  import { useDropzone } from 'vue3-dropzone';
  import { useUploadsStore } from '@/stores/global';
  import { useFilesStore } from '@/stores/files';
  import { useI18n } from 'vue-i18n';
  import { LinkOutlined } from '@ant-design/icons-vue';

  const { t } = useI18n({
    useScope: 'global',
  });

  const props = defineProps({
    title: {
      type: String,
      default: () => '',
    },
    folder: {
      type: String,
      default: () => '',
    },
    multiple: {
      type: Boolean,
      default: () => true,
    },
    index: {
      type: Number,
      default: () => 0,
    },
    editable: {
      type: Boolean,
      default: () => true,
    },
    links: {
      type: Array,
      default: () => [],
    },
    link: {
      type: String,
      default: () => '',
    },
  });

  const files = ref([]);
  const upload_files = ref([]);

  const uploadsStore = useUploadsStore();
  const filesStore = useFilesStore();

  const error = ref('');
  const loading = ref(false);
  const error_form = ref('');
  const message = ref('');
  const show_quantity = ref(false);

  const emit = defineEmits(['onResponseFiles']);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
  });

  function onDrop(acceptFiles) {
    files.value.push(...acceptFiles);
    onChangeFileUpload();
  }

  function handleClickDeleteFile(index, single) {
    if (single) {
      if (props.link != '' && props.link != null) {
        uploadsStore.removeFile(props.link);
        emit('onResponseFiles', []);
      }
    } else {
      files.value.splice(index, 1);
    }
  }

  const handleFileUploadDocument = ($event) => {
    files.value = [];
    const target = $event.target;
    if (target && target.files) {
      files.value.push(target.files[0]);
    }
    onChangeFileUpload();
  };

  const onChangeFileUpload = () => {
    upload_files.value = [];

    for (let i = 0; i < files.value.length; i++) {
      let file = files.value[i];

      //if(parseInt(file.size / 1024 / 1024) < parseInt(size_limit.value)) // 25MB
      //{
      upload_files.value.push({
        name: file.name,
        error: '',
      });

      setTimeout(() => {
        encodeFile64Chunk(file, i);
      }, 10);
      //}
    }
  };

  const encodeFile64 = async (file, index, chunkSize) => {
    const reader = new FileReader();

    reader.onloadend = async () => {
      let content = reader.result;
      let regexChunk = new RegExp(`.{1,${chunkSize}}`, 'g');
      let chunks = content.match(regexChunk);
      upload_files.value[index].chunks = chunks;
      upload_files.value[index].parts = chunks.length;

      for (let i = 1; i <= chunks.length; i++) {
        const chunk = chunks[i - 1];

        let response = await uploadsStore.sendChunkToLambda({
          filename: upload_files.value[index].name,
          part: i,
          base64_chunk: chunk,
        });

        upload_files.value[index].file_url = response.link;

        if (!props.multiple.value) {
          if (i == chunks.length && error.value == '' && error_form.value == '') {
            sendFilesToS3();
          }
        }
      }
    };

    reader.readAsDataURL(file);
  };

  const chunkFile = async (file, index, chunkSize) => {
    await encodeFile64(file, index, chunkSize);
  };

  const encodeFile64Chunk = async (file, index) => {
    const chunkSize = 4 * 1024 * 1024; // 4 MB
    await chunkFile(file, index, chunkSize);
  };

  const sendFilesToS3 = async () => {
    error.value = '';
    message.value = '';
    error_form.value = '';
    loading.value = true;
    uploadsStore.initedAsync();
    let params = [];

    upload_files.value.forEach(async (item) => {
      if (item.error == '') {
        params.push({
          nroref: filesStore.getFile.fileNumber ?? '-',
          folder: props.folder,
          filename: item.name,
          file_url: item.file_url,
          parts: item.parts,
          base64: '',
        });
      }
    });

    let response = await uploadsStore.sendToS3(params);
    uploadsStore.finished();
    emit('onResponseFiles', response);
    show_quantity.value = true;
  };
</script>

<style lang="scss" scoped>
  .dropzone,
  .files {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    box-shadow:
      rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
      rgba(60, 64, 67, 0.15) 0px 1px 3px 1px;
    font-size: 12px;
    line-height: 1.5;
  }

  .border {
    border: 2px dashed #ccc;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    transition: all 0.3s ease;
    background: #fff;

    &.isDragActive {
      border: 2px dashed #ffb300;
      background: rgb(255 167 18 / 20%);
    }
  }

  .file-item {
    gap: 7px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgb(255 167 18 / 20%);
    padding: 7px;
    padding-left: 15px;
    margin-top: 10px;

    &:first-child {
      margin-top: 0;
    }

    .delete-file {
      background: red;
      color: #fff;
      padding: 5px 10px;
      border-radius: 8px;
      cursor: pointer;
    }
  }

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
    max-width: 100%;
    width: 100%;
    border-radius: 6px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
    display: inline-block;
    overflow: hidden;
    text-align: center;
    padding: 0.5rem 1rem;
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
    color: #eb5757;
    border: 2px solid currentColor;
  }

  .inputfile-2:focus + label,
  .inputfile-2.has-focus + label,
  .inputfile-2 + label:hover {
    color: #eb5757;
  }
</style>
