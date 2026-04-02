<script lang="ts" setup>
  import CheckBoxComponent from '@/quotes/components/global/CheckBoxComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import useQuoteLanguages from '@/quotes/composables/useQuoteLanguages';
  import { computed, onMounted, reactive } from 'vue';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  // const logoWidth = ref<string>('1');
  const {
    quoteCategories,
    selectedCategory,
    quoteLanguageId,
    quote,
    downloadSkeletonUse,
    currentReportQuote,
  } = useQuote();
  const { languages } = useQuoteLanguages();

  //const nameServicio = ref<string>(quote.value.name);

  // interface Props {
  //   selected?: boolean;
  // }

  interface State {
    selectedIdLanguage: string;
    addPorta: boolean;
  }

  // interface Portada {
  //   imagePortada: string;
  //   urlPortada: string;
  // }

  defineProps({
    selected: true,
  });

  const languageSelectIso = (idSelect) => {
    if (idSelect) {
      const result = languages.value.find((elem) => elem.id == idSelect);
      return result.iso;
    }
  };

  const state: State = reactive({
    selectedIdLanguage: languageSelectIso(quoteLanguageId.value),
    addPorta: true,
  });

  const selectedLanguage = (event) => {
    state.selectedIdLanguage = languageSelectIso(event.target.value);
    downloadSkeletonUse.value.selectedIdLanguage = state.selectedIdLanguage;
  };

  const dataSkeleton = () => {
    let nameServicioItem = getValueInput('nameServicio');

    if (!state.selectedIdLanguage) {
      state.selectedIdLanguage = 'es';
    }

    console.log(state.selectedIdLanguage);

    downloadSkeletonUse.value.selectedIdLanguage = state.selectedIdLanguage;
    downloadSkeletonUse.value.addPorta = state.addPorta;
    downloadSkeletonUse.value.nameService = nameServicioItem;
  };

  const getValueInput = (id) => {
    let inputValue = document.getElementById(id).value;
    return inputValue;
  };

  const includeCover = (value: boolean) => {
    state.addPorta = value;
    downloadSkeletonUse.value.addPorta = state.addPorta;
  };

  const nameServicio = computed(() => getServiceName());

  const getServiceName = () => {
    if (quote.value.name) {
      return quote.value.name;
    } else if (currentReportQuote.value) {
      return currentReportQuote.value.name;
    }
  };

  onMounted(() => {
    dataSkeleton();
  });
</script>
<template>
  <h3 class="title p-0">{{ t('quote.label.download_day_day') }}</h3>
  <div class="body">
    <div class="left">
      <div class="input">
        <label>{{ t('quote.label.quote_name') }}</label>
        <input type="text" id="nameServicio" :value="nameServicio" />
      </div>
      <div class="input">
        <label>{{ t('quote.label.language') }}</label>
        <select id="langSelectItem" @change="selectedLanguage">
          <option
            v-for="language of languages"
            :value="language.id"
            :selected="quoteLanguageId === language.id"
          >
            {{ language.label }}
          </option>
        </select>
      </div>
      <div class="input">
        <label>{{ t('quote.label.category') }}</label>

        <select :name="selectedCategory">
          <option
            v-for="quoteCategory of quoteCategories"
            v-if="quoteCategories"
            :value="quoteCategory.type_class_id"
            :selected="selectedCategory === quoteCategory.type_class_id"
          >
            {{ quoteCategory.type_class.translations[0].value }}
          </option>

          <option
            v-for="quoteCategory of currentReportQuote.categories"
            v-if="currentReportQuote.categories"
            :value="quoteCategory.type_class_id"
            :selected="selectedCategory === quoteCategory.type_class_id"
          >
            {{ quoteCategory.type_class.translations[0].value }}
          </option>
        </select>
      </div>
      <CheckBoxComponent
        :label="t('quote.label.include_header')"
        modelValue="state.addPorta"
        :class="selected"
        @checked="includeCover"
      />
    </div>
  </div>
</template>

<style lang="scss">
  .centerImg {
    margin: 0 auto;
  }

  .modal-Skeletondetalle .modal-inner {
    max-width: 416px;
    padding: 20px 30px;

    .title {
      padding: 10px 38px 0;
    }

    .body {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 24px;
      align-self: stretch;
      overflow: hidden;

      .left,
      .right {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 24px;
        flex: 1 0 0;

        .input {
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          gap: 6px;
          align-self: stretch;

          label {
            color: #575757;
            font-size: 14px;
            font-style: normal;
            font-weight: 500;
            line-height: 21px;
            letter-spacing: 0.21px;
          }

          input,
          select {
            border-radius: 4px;
            border: 1px solid #c4c4c4;
            background: #ffffff;
            display: flex;
            height: 45px;
            padding: 4px 10px;
            align-items: center;
            gap: 5px;
            align-self: stretch;
          }
        }
      }
    }

    .modal-footer {
      margin-top: 40px;
    }
  }
</style>
