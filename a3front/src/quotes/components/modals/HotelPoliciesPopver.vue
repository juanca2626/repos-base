<script setup lang="ts">
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { computed } from 'vue';
  import type { Hotel } from '@/quotes/interfaces';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  // const { quote } = useQuote();
  interface Props {
    hotel: Hotel;
  }

  const props = defineProps<Props>();

  const checkIn = computed(() => props.hotel.checkIn ?? '');
  const checkOut = computed(() => props.hotel.checkOut ?? '');
  const allowsChild = computed(() => props.hotel.political_children.child.allows_child ?? false);
  const maxAgeChild = computed(() => props.hotel.political_children.child.max_age_child ?? 0);
  const minAgeChild = computed(() => props.hotel.political_children.child.min_age_child ?? 0);
</script>

<template>
  <a-popover :title="t('quote.label.hotel_policy_details')" placement="rightTop">
    <template #content>
      <div class="hotel-policies">
        <div class="hotel-policies-general">
          <h4>{{ t('quote.label.general_policy') }}</h4>
          <p>
            {{ t('quote.label.check_in') }}: {{ checkIn }} | {{ t('quote.label.check_out') }} :
            {{ checkOut }}
          </p>

          <div v-if="allowsChild">
            <h4>{{ t('quote.label.children_policy') }}</h4>
            <p>
              {{ t('quote.label.child') }} {{ minAgeChild }} {{ t('quote.label.years') }}
              {{ t('quote.label.to') }} {{ maxAgeChild }} {{ t('quote.label.years') }}
              {{ t('quote.label.free') }}
            </p>
          </div>

          <div v-if="false">
            <h4>{{ t('quote.label.no_show') }}</h4>
            <p>100% {{ t('quote.label.fee') }} + 18% {{ t('quote.label.tax') }}</p>
          </div>

          <div v-if="false">
            <h4>{{ t('quote.label.day_use') }}</h4>
            <p>{{ t('quote.label.contact_specialist') }}</p>
          </div>
        </div>

        <div class="hotel-policies-cancelation" v-if="false">
          <h4>{{ t('quote.label.cancellation_policy') }}</h4>
          <p>{{ t('quote.label.cancellation_policy_description') }}</p>
        </div>
      </div>
    </template>
    <font-awesome-icon icon="info-circle" :style="{ color: '#55A3FF', fontSize: '16px' }" />
  </a-popover>
</template>

<style scoped lang="scss">
  .hotel-policies {
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
