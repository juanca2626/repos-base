<script lang="ts" setup>
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const { quoteNew } = useQuote();
</script>

<template>
  <div class="text">
    <input
      v-model="quoteNew.name"
      name="title-title"
      type="text"
      autocomplete="off"
      :placeholder="t('quote.label.new_name_of_quote')"
    />
    &nbsp;
  </div>
</template>

<style lang="scss" scoped></style>
