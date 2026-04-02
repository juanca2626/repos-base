<template>
  <a-row type="flex" justify="space-between" align="middle" style="gap: 5px; flex-flow: nowrap">
    <template
      v-if="
        record.rq_total > 0 ||
        record.flag_editable ||
        record.confirmation_code == '' ||
        record.confirmation_code == null
      "
    >
      <a-col flex="auto">
        <a-input
          size="small"
          :placeholder="t('files.column.code')"
          v-model:value="record.alt_confirmation_code"
          :disabled="record.blocked"
        ></a-input>
      </a-col>
      <template
        v-if="
          record.flag_editable || record.confirmation_code == '' || record.confirmation_code == null
        "
      >
        <a-col>
          <span
            v-if="!record.blocked"
            style="font-size: 15px"
            @click="saveRecord('composition', record)"
            class="cursor-pointer"
          >
            <a-tooltip>
              <template #title>{{ t('global.button.save') }}</template>
              <font-awesome-icon :icon="['far', 'circle-check']" />
            </a-tooltip>
          </span>
        </a-col>
      </template>
    </template>
    <template v-else>
      <template v-if="!record.flag_editable">
        <a-col>
          <span @click="record.flag_editable = true" class="cursor-pointer text-danger">
            <a-tooltip>
              <template #title>{{ t('global.label.edit') }}</template>
              <font-awesome-icon :icon="['far', 'pen-to-square']" />
              {{ record.confirmation_code || ' - ' }}
            </a-tooltip>
          </span>
        </a-col>
      </template>
      <a-col v-else>
        <span
          v-if="!record.blocked"
          style="font-size: 15px"
          @click="saveRecordOK('composition', record)"
          class="cursor-pointer"
        >
          <a-tooltip>
            <template #title>{{ t('global.button.save') }}</template>
            <font-awesome-icon :icon="['far', 'circle-check']" />
          </a-tooltip>
        </span>
      </a-col>
    </template>
  </a-row>
</template>

<script setup>
  import { debounce } from 'lodash-es';
  import { notification } from 'ant-design-vue';
  import { useFilesStore } from '@/stores/files';
  // import { formatDate, truncateString } from '@/utils/files.js';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();
  const emit = defineEmits(['handleOnLoadReport']);

  defineProps({
    record: {
      type: String,
      default: () => {},
    },
    type: {
      type: String,
      default: () => 'hotel',
    },
  });

  const saveRecord = debounce(async (type, record) => {
    if (
      typeof record.alt_confirmation_code == 'undefined' ||
      record.alt_confirmation_code === null ||
      record.alt_confirmation_code === ''
    ) {
      return false;
    }

    localStorage.setItem('confirmation_code', record.alt_confirmation_code);
    record.blocked = true;
    record.confirmation_code = localStorage.getItem('confirmation_code');

    await filesStore.saveConfirmationCode({
      type,
      id: record.id,
      confirmation_code: record.confirmation_code,
      itinerary_id: record.file_itinerary_id,
      file_number: filesStore.getFile.fileNumber,
    });
    record.blocked = false;
    record.isLoading = true;

    if (filesStore.getError != '') {
      notification['error']({
        message: `Ocurrió un Error`,
        description: filesStore.getError,
        duration: 5,
      });
    } else {
      record.flag_editable = false;
      emit('handleOnLoadReport', true);
    }
  }, 350);

  const saveRecordOK = debounce(async (type, record) => {
    if (record.confirmation_code != null && record.confirmation_code != '') {
      record.blocked = true;
      await filesStore.saveConfirmationCode({
        type,
        id: record.id,
        confirmation_code: record.confirmation_code,
        itinerary_id: record.file_itinerary_id,
        file_number: filesStore.getFile.fileNumber,
      });
      record.blocked = false;
      record.isLoading = true;

      if (filesStore.getError != '') {
        notification['error']({
          message: `Ocurrió un Error`,
          description: filesStore.getError,
          duration: 5,
        });
      } else {
        record.flag_editable = false;
      }
    } else {
      notification['error']({
        message: `Datos incompletos`,
        description: 'Por favor, ingrese un código de confirmación para continuar.',
        duration: 5,
      });
    }
  }, 350);
</script>
