<script lang="ts" setup>
  import { toRef } from 'vue';
  import { useI18n } from 'vue-i18n';

  const props = defineProps<{ itinerary?: string }>();
  const { t } = useI18n();
  const itinerary = toRef(props, 'itinerary');
</script>

<template>
  <div v-if="itinerary" class="itinerary-wrapper">
    <div class="detail-header">
      <div class="title p-0">
        <svg
          fill="none"
          height="18"
          viewBox="0 0 18 19"
          width="18"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M14.25 16.1094L9 12.3594L3.75 16.1094V4.10938C3.75 3.71155 3.90804 3.33002 4.18934 3.04871C4.47064 2.76741 4.85218 2.60938 5.25 2.60938H12.75C13.1478 2.60938 13.5294 2.76741 13.8107 3.04871C14.092 3.33002 14.25 3.71155 14.25 4.10938V16.1094Z"
            stroke="#EB5757"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
          />
        </svg>
        <span>{{ t('quote.label.itinerary') }}</span>
      </div>
    </div>

    <div class="detail-body">
      <div class="html-content" v-html="itinerary"></div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .itinerary-wrapper {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }

  .detail-header {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #f0f0f0; // Línea sutil decorativa
    padding-bottom: 4px;

    .title {
      display: flex;
      align-items: center;
      gap: 8px;

      span {
        color: #212529;
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
        // Quité el underline porque puede confundirse con un link
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
    }
  }

  .detail-body {
    .html-content {
      background-color: transparent;
      font-size: 13px !important;
      line-height: 1.6;
      text-align: justify;

      > * {
        background-color: transparent;
        font-size: 13px !important;
        line-height: 1.6;
        text-align: justify;
      }
    }
  }
</style>
