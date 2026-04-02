<template>
  <div class="quotes-info-buttons">
    <template v-if="page === 'details'">
      <div class="check">
        <icon-confirmed :width="24" :height="24" />
      </div>
      <div class="check-text">{{ t('quote.label.text_ok') }}</div>
      <div class="warning">
        <icon-on-request />
      </div>
      <div class="warning-text">RQ</div>
    </template>
    <div class="rectangle" style="border: 2px dashed #eb5757"></div>
    <div class="rectangle-text">{{ t('quote.label.optional_service') }}</div>
  </div>
</template>
<script lang="ts" setup>
  import IconConfirmed from '@/quotes/components/icons/IconConfirmed.vue';
  import IconOnRequest from '@/quotes/components/icons/IconOnRequest.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();
  const { page } = useQuote();
</script>

<style lang="scss" scoped>
  .quotes-info-buttons {
    display: flex;
    height: 27px;
    flex-shrink: 0;
    /*width: 352px; */
    align-items: center;
    margin-top: 10px;
    padding-bottom: 50px;
    /*justify-content: space-between;*/

    .check {
      width: 24px;
      height: 24px;
      flex-shrink: 0;
    }

    .check-text {
      width: 25px;
      height: 25px;
      flex-shrink: 0;
      font-size: 18px;
      font-style: normal;
      font-weight: 400;
      line-height: 30px;
      letter-spacing: -0.27px;
      margin-left: 5px;
      margin-right: 20px;
    }

    .warning {
      width: 24px;
      height: 24px;
      flex-shrink: 0;
    }

    .warning-text {
      width: 29px;
      height: 25px;
      flex-shrink: 0;
      font-size: 18px;
      font-style: normal;
      font-weight: 400;
      line-height: 30px; /* 166.667% */
      letter-spacing: -0.27px;
      margin-left: 5px;
      margin-right: 20px;
    }

    .rectangle {
      width: 21px;
      height: 21px;
      flex-shrink: 0;
      background: #fcecec;
    }

    .rectangle-text {
      margin-left: 5px;
      width: 152px;
      height: 25px;
      flex-shrink: 0;
      font-size: 18px;
      font-style: normal;
      font-weight: 400;
      font-weight: 400;
      color: #000;
      line-height: 30px;
      letter-spacing: -0.27px;
    }
  }
</style>
