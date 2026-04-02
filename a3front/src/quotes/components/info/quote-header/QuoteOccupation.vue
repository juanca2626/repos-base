<script setup lang="ts">
  import BoxComponent from '@/quotes/components/info/BoxComponent.vue';
  import QuoteOccupationAssignator from '@/quotes/components/info/quote-header/QuoteOccupationAssignator.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { usePopup } from '@/quotes/composables/usePopup';
  import { computed, watchEffect } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useOccupationStore } from '@/quotes/store/occupation.store';

  const { t } = useI18n();
  const { showForm, toggleForm } = usePopup();
  const { accommodation, processing } = useQuote();
  const storeOccupation = useOccupationStore();

  const single = computed(() => accommodation.value.single);
  const double = computed(() => accommodation.value.double);
  const triple = computed(() => accommodation.value.triple);

  const singleF = computed(() => accommodation.value.single.toString().padStart(2, '0'));
  const doubleF = computed(() => accommodation.value.double.toString().padStart(2, '0'));
  const tripleF = computed(() => accommodation.value.triple.toString().padStart(2, '0'));

  watchEffect(() => {
    if (storeOccupation.show == true) {
      toggleForm();
    }

    if (accommodation.value.single + accommodation.value.double + accommodation.value.triple > 0) {
      storeOccupation.closeWindowOccupation();
    }
  });
</script>

<template>
  <BoxComponent
    :showEdit="true"
    :title="t('quote.label.rooms')"
    :disabled="processing"
    @edit="toggleForm()"
    class="titlerooms"
  >
    <template #text>
      <div class="item">
        <span v-if="single > 0">{{ singleF }} SGL</span>
        <span v-if="double > 0">{{ doubleF }} DBL</span>
        <span v-if="triple > 0">{{ tripleF }} TPL</span>
      </div>
    </template>
    <template #form></template>
  </BoxComponent>

  <ModalComponent
    :modalActive="showForm"
    class="modal-passengers modal-assignator"
    @close="showForm = false"
  >
    <template #body>
      <div class="container">
        <div class="title">{{ t('quote.label.assign_accommodation') }}</div>
        <div class="body">
          <quote-occupation-assignator @close="showForm = false" v-if="showForm" />
        </div>
      </div>
    </template>
  </ModalComponent>
</template>

<style lang="scss">
  .titlerooms .text {
    text-transform: capitalize !important;
  }

  .item {
    span {
      margin-right: 5px;
    }
  }

  .modal-assignator {
    .container {
      flex-direction: column;
      gap: 0px !important;

      & > .title {
        margin-bottom: 30px !important;
        margin-top: 10px;
      }

      .body {
        width: 100%;
        max-height: 60vh;
        overflow: auto !important;

        .title {
          margin-top: 10px !important;
        }
      }
    }
  }

  :deep(.rooms-form) {
    width: 330px !important;

    .box {
      .amountN {
        width: 90px;
      }
    }
  }
</style>
