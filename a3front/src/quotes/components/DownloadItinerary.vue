<script lang="ts" setup>
  import CheckBoxComponent from '@/quotes/components/global/CheckBoxComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import useQuoteLanguages from '@/quotes/composables/useQuoteLanguages';
  import { computed, onMounted, reactive, ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useLanguagesStore } from '@/stores/global';

  const languageStore = useLanguagesStore();

  const { t } = useI18n();
  const logoWidth = ref<string>('1');
  const { languages } = useQuoteLanguages();
  const {
    quoteCategories,
    selectedCategory,
    setComboPortada,
    quote,
    downloadItinerary,
    currentReportQuote,
  } = useQuote();

  //const nameServicio = ref<string>(quote.value.name);

  const nameServicio = computed(() => getServiceName());

  const getServiceName = () => {
    if (quote.value.name) {
      return quote.value.name;
    } else if (currentReportQuote.value) {
      return currentReportQuote.value.name;
    }
  };

  interface State {
    selectedIdLanguage: string;
    addPorta: boolean;
  }

  interface Portada {
    imagePortada: string;
    urlPortada: string;
  }

  const languageSelectIso = (idSelect) => {
    if (idSelect) {
      const result = languages.value.find((elem) => elem.id == idSelect);
      return result.value;
    }
  };

  const languageSelectId = (idSelect) => {
    if (idSelect) {
      const result = languages.value.find((elem) => elem.value == idSelect);
      return result.id;
    }
  };

  const state: State = reactive({
    selectedIdLanguage: languageSelectId(languageStore.currentLanguage),
    addPorta: true,
  });

  const portada: Portada = reactive({
    imagePortada: '',
    urlPortada: '',
  });

  const selectedLanguage = (event) => {
    state.selectedIdLanguage = languageSelectIso(event.target.value);
    downloadItinerary.value.selectedIdLanguage = state.selectedIdLanguage;
  };

  const imgFrontPage = async () => {
    let nameServicioItem = getValueInput('nameServicio');
    let destinosPortada = getValueInput('destinosPortada');
    // const result = languages.value.find(({ id }) => id === quoteLanguageId.value);
    const resultImg = await setComboPortada(
      destinosPortada,
      languageSelectIso(state.selectedIdLanguage),
      logoWidth.value,
      nameServicioItem
    );
    portada.imagePortada = '';
    portada.urlPortada = '';
    portada.imagePortada = resultImg.baseUrl + resultImg.data.image + '.jpg';
    portada.urlPortada = resultImg.data.image + '.jpg';

    downloadItinerary.value.selectedIdLanguage = languageSelectIso(state.selectedIdLanguage);
    downloadItinerary.value.typePortada = logoWidth.value;
    downloadItinerary.value.typePortadanameServicioItem =
      logoWidth.value === 3 ? nameServicioItem : '';
    downloadItinerary.value.urlPortada = state.addPorta ? resultImg.data.image + '.jpg' : '';
    downloadItinerary.value.addPorta = state.addPorta;
    downloadItinerary.value.nameServicioItem = nameServicioItem;
    downloadItinerary.value.destinosPortada = state.addPorta ? destinosPortada : '';
  };

  const getValueInput = (id) => {
    let inputValue = document.getElementById(id).value;
    return inputValue;
  };

  const includeCover = (value: boolean) => {
    state.addPorta = value;
    downloadItinerary.value.addPorta = state.addPorta;
  };

  onMounted(() => {
    imgFrontPage();
  });
</script>
<template>
  <h3 class="title p-0">{{ t('quote.label.download_itinerary') }}</h3>
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
            :selected="state.selectedIdLanguage === language.id"
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
        @checked="includeCover"
      />
    </div>
    <div class="right" v-if="state.addPorta">
      <div class="input">
        <label>{{ t('quote.label.cover_image') }}</label>
        <select id="destinosPortada" @change="imgFrontPage">
          <option value="amazonas">AMAZONAS</option>
          <option value="arequipa">AREQUIPA</option>
          <option value="arequipa-catedral">AREQUIPA CATEGRAL</option>
          <option value="argentina">ARGENTINA</option>
          <option value="aventura">AVENTURA</option>
          <option value="ballestas">BALLESTAS</option>
          <option value="bolivia">BOLIVIA</option>
          <option value="brasil">BRASIL</option>
          <option value="camino-inca">CAMINO INCA</option>
          <option value="chile">CHILE</option>
          <option value="colca">COLCA</option>
          <option value="comunidad-local">COMUNIDAD LOCAL</option>
          <option value="cusco">CUSCO</option>
          <option value="cusco-iglesia">CUSCO IGLESIA</option>
          <option value="familia1">FAMILIA1</option>
          <option value="familia2">FAMILIA2</option>
          <option value="familia3">FAMILIA3</option>
          <option value="familia4">FAMILIA4</option>
          <option value="kuelap">KUELAP</option>
          <option value="lima1">LIMA1</option>
          <option value="lima2">LIMA2</option>
          <option value="lima3">LIMA3</option>
          <option value="lujo">LUJO</option>
          <option value="machupicchu">MACHUPICCHU</option>
          <option value="mapi">MAPI</option>
          <option value="maras">MARAS</option>
          <option value="moray">MORAY</option>
          <option value="mpi2">MPI2</option>
          <option value="nasca">NASCA</option>
          <option value="portada">PORTADA</option>
          <option value="puerto-maldonado">PUERTO MALDONADO</option>
          <option value="puno">PUNO</option>
          <option value="trujillo">TRUJILLO</option>
          <option value="valle">VALLE</option>
          <option value="vinicunca">VINICUNCA</option>
        </select>
      </div>
      <p class="text-inclu">{{ t('quote.label.include_logo_client') }}</p>
      <a-radio-group v-model:value="logoWidth" name="logoWidth" @change="imgFrontPage">
        <a-radio value="3">{{ t('quote.label.none') }}</a-radio>
        <a-radio value="1">{{ t('quote.label.yes') }}</a-radio>
        <a-radio value="2">{{ t('quote.label.no') }}</a-radio>
      </a-radio-group>
      <img class="centerImg" height="195" :src="portada.imagePortada" width="140" />
    </div>
  </div>
</template>

<style lang="scss">
  .text-inclu {
    margin-bottom: -16px;
    color: #575757;
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
    line-height: 21px;
    letter-spacing: 0.21px;
  }

  .centerImg {
    margin: 0 auto;
  }

  .modal-itinerariodetalle .modal-inner {
    max-width: 690px;
    padding: 20px 30px;

    .body {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 24px;
      align-self: stretch;

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
