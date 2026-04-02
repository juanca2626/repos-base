<template>
  <div class="files-toggler-start-day">
    <files-edit-field-static hideContent>
      <template #label>{{ t('files.label.start_date') }}</template>
      <template #content>
        <span><slot /></span>
        <template v-if="editable">
          <span
            @click="toggleIsEditing"
            style="margin-left: 11px; line-height: 21px; vertical-align: middle; cursor: pointer"
          >
            <font-awesome-icon :icon="['far', 'pen-to-square']" />
          </span>
        </template>
      </template>
    </files-edit-field-static>
  </div>

  <a-modal v-model:visible="showAlert" :width="400">
    <template #title>{{ t('files.label.modify_date') }}</template>
    <a-alert class="text-danger p-3" type="error" show-icon>
      <template #description>
        <span class="text-500">{{ t('files.label.alert_modify_date') }}</span>
      </template>
      <template #icon><font-awesome-icon :icon="['fas', 'circle-exclamation']" /></template>
    </a-alert>
    <p class="text-center mt-3 mx-2 font-490 mb-0">
      {{ t('files.message.change_date') }} <b>{{ t('files.button.quote') }}</b>
    </p>
    <template #footer>
      <div class="text-center">
        <a-button
          type="default"
          default
          @click="handleCancel"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          {{ t('global.button.cancel') }}
        </a-button>
        <a-button
          type="primary"
          primary
          :loading="loading"
          @click="handleOk"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          <span :class="loading ? 'ms-2' : ''">
            {{ t('global.label.goto') }}
            <span class="text-lowercase">{{ t('files.button.quote') }}</span>
          </span>
        </a-button>
      </div>
    </template>
  </a-modal>

  <a-modal v-model:visible="showNotify" :title="t('files.label.modify_date')" :width="400">
    <a-alert
      class="text-danger"
      type="error"
      show-icon
      description="Se encontró una cotización abierta en el tablero."
    >
      <template #icon><exclamation-circle-outlined /></template>
    </a-alert>
    <template #footer>
      <div class="text-center">
        <a-button
          type="primary"
          ghost
          :loading="loading"
          @click="handleA2"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          <span :class="loading ? 'ms-2' : ''">
            {{ t('global.label.goto') }} {{ t('global.label.board') }}
          </span>
        </a-button>
        <a-button
          type="primary"
          default
          :loading="loading"
          @click="handleA2Force"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          <span :class="loading ? 'ms-2' : ''">
            {{ t('files.button.replace') }}
          </span>
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<script setup>
  import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
  import { ref, watch } from 'vue';
  import Cookies from 'js-cookie';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import { useInputsMontadosStore, useFilesStore } from '@store/files';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const isEditing = ref(false);
  const showAlert = ref(false);
  const showNotify = ref(false);

  localStorage.removeItem('a3_file_id');

  const inputsMontadosStore = useInputsMontadosStore();
  const filesStore = useFilesStore();

  const toggleIsEditing = () => {
    isEditing.value = !isEditing.value;
    inputsMontadosStore.currentInput = 'start-day';
    showAlert.value = true;
  };

  const handleOk = async () => {
    await filesStore.verifyQuote();
    showAlert.value = false;

    if (filesStore.getFlagBoard) {
      showNotify.value = true;
    } else {
      await filesStore.sendQuote(filesStore.getFile.id);

      if (filesStore.getFlagSendBoard) {
        localStorage.setItem('a3_file_id', filesStore.getFile.id);
        handleA2();
      }
    }
  };

  const handleCancel = () => {
    showAlert.value = false;
    showNotify.value = false;
  };

  const handleA2 = () => {
    Cookies.set('a3_client_code', filesStore.getFile.clientCode, { domain: window.DOMAIN });
    Cookies.set('a3_client_id', filesStore.getFile.clientId, { domain: window.DOMAIN });

    localStorage.setItem('a3_file_id', filesStore.getFile.id);
    window.location.href = window.url_app + 'quotes';
  };

  const handleA2Force = async () => {
    await filesStore.sendQuote(filesStore.getFile.id, { force: true });
    if (!filesStore.getError) {
      handleA2();
    }
  };

  defineProps({
    data: {
      type: String,
      default: () => '',
    },
    editable: {
      type: Boolean,
      default: () => true,
    },
  });

  watch(
    () => inputsMontadosStore.currentInput,
    (value) => {
      if (value !== 'start-day') isEditing.value = false;
    }
  );
</script>

<style lang="scss">
  .files-toggler-start-day {
    position: relative;
  }
  .start-day-datepicker {
    position: relative;

    .save-btn {
      cursor: pointer;
      position: absolute;
      top: -0;
      right: -30px;
      pointer-events: all;
      color: #3d3d3d;
    }

    .save-btn:hover {
      color: #eb5757;
    }

    & :deep(.ant-select-selector) {
      margin-left: 30px;

      height: 45px;
      line-height: 45px;
    }
    & :deep(.ant-select-selection-placeholder) {
      line-height: 45px;
      text-align: left;
    }
    & :deep(.ant-select-selection-item) {
      line-height: 45px;
      text-align: left;
    }
  }

  .ant-modal-header {
    background-color: transparent !important;
    text-align: center;
    border: 0;
  }

  .ant-modal-body {
    padding: 1rem !important;
    font-size: 17px !important;
  }

  .ant-modal-footer {
    border: 0;
    text-align: center;
    padding: 0 0 1rem 0 !important;
  }

  .ant-modal-title {
    color: #000;
    font-size: 22px;
    font-weight: 640;
    margin: 1rem;
    margin-bottom: 0;
  }

  .ant-modal-close svg {
    fill: #eb5757;
  }

  .text-center {
    text-align: center;
  }

  .font-490 {
    font-weight: 490;
  }
</style>
