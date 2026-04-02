<script setup lang="ts">
  import { computed } from 'vue';
  import type { RoomRate } from '@/quotes/interfaces';
  import IconAlert from '@/quotes/components/icons/IconAlert.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  // const { quote } = useQuote();

  interface Props {
    ratePlan: RoomRate;
  }

  const props = defineProps<Props>();

  const noShow = computed(() => props.ratePlan.no_show ?? '');
  const dayUse = computed(() => props.ratePlan.day_use ?? '');
  const cancellation = computed(() => props.ratePlan.political?.cancellation.name ?? '');
</script>

<template>
  <a-popover :title="t('quote.label.tax_policy_details')" placement="rightTop">
    <template #content>
      <div class="hotel-rate-plan-policies">
        <div class="hotel-rate-plan-policies-general">
          <div v-if="noShow">
            <h4>{{ t('quote.label.no_show') }}</h4>
            <p>{{ noShow }}</p>
          </div>

          <div v-if="dayUse">
            <h4>{{ t('quote.label.day_use') }}</h4>
            <p>{{ dayUse }}</p>
          </div>
        </div>

        <div class="hotel-rate-plan-policies-cancelation" v-if="cancellation">
          <h4>{{ t('quote.label.cancellation_policy') }}</h4>
          <p>
            {{ cancellation }}
          </p>
        </div>
      </div>
    </template>

    <a-tooltip placement="top">
      <template #title>
        <span> {{ t('quote.label.information') }}</span>
      </template>
      <icon-alert :height="22" :width="22" />
    </a-tooltip>
  </a-popover>
</template>

<style scoped lang="scss">
  .hotel-rate-plan-policies {
    font-family: Montserrat;
    font-size: 0.75rem;
    font-style: normal;
    font-weight: 400;
    line-height: 1.188rem; /* 158.333% */
    letter-spacing: 0.011rem;
    max-width: 240px;

    h4 {
      font-size: 0.75rem;
      font-weight: 700;
      margin-bottom: 0;
    }

    &-general,
    &-cancelation {
      padding: 10px;
    }

    &-cancelation {
      background-color: #fff2f2;
    }
  }
</style>
