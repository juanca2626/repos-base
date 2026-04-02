<template>
  <a-layout class="completed-layout">
    <a-result :status="status" :title="computedTitle" :sub-title="computedSubtitle" />
  </a-layout>
</template>

<script setup>
  import { computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const props = defineProps({
    title: {
      type: String,
      default: '',
    },
    subtitle: {
      type: String,
      default: '',
    },
    status: {
      type: String,
      default: 'error',
      validator: (value) =>
        ['success', 'error', 'info', 'warning', '403', '404', '500'].includes(value),
    },
  });

  const computedTitle = computed(() =>
    props.title === undefined ? t('quote.error.500.title') : props.title
  );
  const computedSubtitle = computed(() =>
    props.subtitle === undefined ? t('quote.error.500.subtitle') : props.subtitle
  );
</script>

<style scoped lang="scss">
  .completed-layout {
    background-color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
  }

  .a-result {
    width: 100%;
  }
</style>
