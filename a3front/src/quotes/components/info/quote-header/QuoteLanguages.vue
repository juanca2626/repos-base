<script setup lang="ts">
  import BoxComponent from '@/quotes/components/info/BoxComponent.vue';
  import QuoteLanguagesForm from '@/quotes/components/info/quote-header/QuoteLanguagesForm.vue';
  import { usePopup } from '@/quotes/composables/usePopup';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useLanguagesStore } from '@/stores/global';
  import { computed } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  // const loading = ref(true);
  const { quoteLanguageId, processing } = useQuote();
  const languagesStore = useLanguagesStore();
  const { showForm, toggleForm } = usePopup();

  const language = computed(() => {
    console.log(languagesStore.getAllLanguages, quoteLanguageId.value);

    if (quoteLanguageId.value) {
      const result = languagesStore.getAllLanguages.find(({ id }) => id === quoteLanguageId.value);
      return result?.label;
    } else {
      return 'Seleccione';
    }
  });

  const changeLanguages = (
    args: [
      {
        label: string;
        value: number;
      },
    ]
  ) => {
    showForm.value = false;
    console.log(args);
    quoteLanguageId.value = args[0].id;
    //console.log(args[0])
    language.value = args[0];
  };
</script>

<template>
  <BoxComponent
    class="languageBox"
    :title="t('quote.label.language')"
    :disabled="processing"
    @edit="toggleForm()"
  >
    <template #text>{{ language }} <br /></template>
    <template #form>
      <quote-languages-form
        :languages="languagesStore.getAllLanguages"
        :show="showForm"
        @selected="changeLanguages"
      />
    </template>
  </BoxComponent>
</template>

<style scoped lang="scss"></style>
