<template>
  <div class="d-block text-center" style="margin: 0 auto">
    <template v-if="type === 'hotel'">
      <span @click="showModalLogs(record, type)" class="cursor-pointer me-1">
        <a-tooltip>
          <template #title>{{ t('global.label.view_emails_sent') }}</template>
          <font-awesome-icon :icon="['far', 'envelope']" />
        </a-tooltip>
      </span>
    </template>
    <template v-if="type === 'service'">
      <template v-if="parseInt(record.send_notification) === 1">
        <span @click="showModalLogs(record, type)" class="cursor-pointer me-2">
          <a-tooltip>
            <template #title>{{ t('global.label.view_emails_sent') }}</template>
            <font-awesome-icon :icon="['far', 'envelope']" />
          </a-tooltip>
        </span>
        <a-tag class="tag-bg-dark-gray" size="small">{{ t('global.label.sent') }}</a-tag>
      </template>
      <template v-else>
        <a-button default type="primary" @click="showModalLogs(record, type)" size="small">
          <small class="text-uppercase">{{ t('global.label.send') }}</small>
        </a-button>
      </template>
    </template>
  </div>
</template>

<script setup>
  const emit = defineEmits(['handleModalLogs', 'handleOnLoadReport', 'handleOnSetLoading']);

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  defineProps({
    record: {
      type: String,
      default: () => {},
    },
    type: {
      type: String,
      default: 'service',
    },
  });

  const showModalLogs = async (record, type = 'service') => {
    emit('handleModalLogs', { record: record, type: type });
  };
</script>
