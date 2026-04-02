<template>
  <popover-hover-and-click placement-click="bottomLeft" placement-hover="topLeft">
    <svg
      xmlns="http://www.w3.org/2000/svg"
      style="color: #2e2b9e"
      width="24"
      height="24"
      fill="none"
      stroke="currentColor"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
      class="feather feather-info"
    >
      <circle cx="12" cy="12" r="10" />
      <path d="M12 16v-4M12 8h.01" />
    </svg>
    <template #content-hover>{{ t('files.label.log') }}</template>
    <template #content-click>
      <div class="file-info-content-click">
        <div class="file-info-content-click">
          <div class="file-info-content-click-title">{{ t('files.label.information') }}</div>

          <div class="file-info-content-click-subtitle">{{ t('files.label.date_open') }}:</div>
          <div class="file-info-content-click-description">
            {{ formatDate(data.createdAt) || '-' }}
          </div>

          <div class="file-info-content-click-subtitle">
            {{ t('files.label.executive_open') }} :
          </div>
          <div class="file-info-content-click-description">
            ({{ data.executiveCode || '-' }}) {{ data.executiveName || '-' }}
          </div>

          <div class="file-info-content-click-subtitle">{{ t('files.label.deadline') }}:</div>
          <div class="file-info-content-click-description">
            {{ formatDate(data.deadline) || '-' }}
          </div>
          <div class="file-info-content-click-subtitle">{{ t('files.label.order') }}:</div>
          <div class="file-info-content-click-description">
            {{ data.reservationId || '-' }}
          </div>
        </div>
      </div>
    </template>
    <template #content-buttons>&nbsp;</template>
  </popover-hover-and-click>
</template>

<script setup>
  import PopoverHoverAndClick from '@/components/files/reusables/PopoverHoverAndClick.vue';
  import { formatDate } from '@/utils/files.js';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });
</script>

<style scoped lang="scss">
  .file-info-content-click {
    font-family: var(--files-font-basic);
    width: 275px;
    height: 220px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;
    color: var(--files-black-2);

    &-title {
      font-weight: 700;
      text-align: center;
      text-transform: uppercase;
    }
    &-subtitle {
      font-weight: 600;
    }
    &-description {
      font-weight: 400;
    }
  }
</style>
