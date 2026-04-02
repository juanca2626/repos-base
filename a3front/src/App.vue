<template>
  <a-config-provider
    :theme="{
      token: {
        colorPrimary: '#bd0d12',
        colorInfo: '#bd0d12',
        colorInfoText: '#bd0d12',
        colorPrimaryText: '#bd0d12',
        colorLink: '#bd0d12',
      },
      components: {
        Breadcrumb: {
          fontSize: 12,
        },
      },
    }"
    :get-popup-container="globalGetPopupContainer"
  >
    <RouterView />
  </a-config-provider>
</template>
<style lang="scss">
  @import '@/scss/app.scss';
</style>

<script setup>
  import { merge } from 'lodash';
  import { onMounted, ref, watch } from 'vue';
  import { useRoute } from 'vue-router';
  import { useLanguagesStore } from '@/stores/global';
  import { getUrlAuroraFront } from '@/utils/auth';
  import axios from 'axios';
  import { useI18n } from 'vue-i18n';
  import { globalGetPopupContainer } from '@/utils/antdConfig';

  const { getLocaleMessage, locale, mergeLocaleMessage } = useI18n({
    useScope: 'global',
  });

  const languagesStore = useLanguagesStore();
  const route = useRoute();

  const loading = ref(true);
  const currentLang = ref('');

  watch(
    () => route.query.lang,
    (lang) => {
      if (lang) {
        languagesStore.setCurrentLanguage(lang);
        locale.value = lang;
      }
    }
  );

  onMounted(async () => {
    await languagesStore.fetch();
    getLanguagesJSON();

    // Aurora Widget - Test
    const script = document.createElement('script');
    script.src = 'https://lima-tours.reechai.com/widget.js';
    document.head.appendChild(script);
  });

  watch(locale, () => {
    getLanguagesFiles();
  });

  const getLanguagesJSON = () => {
    const global = import.meta.glob('./lang/**/global.json');
    const files = import.meta.glob('./lang/**/files.json');
    const quotes = import.meta.glob('./lang/**/files.json');

    let iso = '';
    for (const path in global) {
      global[path]().then((mod) => {
        iso = path.substring(7, 9);
        const messages = {
          global: JSON.parse(JSON.stringify(mod)),
        };
        addTranslations(iso, messages);
      });
    }

    for (const path in files) {
      files[path]().then((mod) => {
        iso = path.substring(7, 9);
        const messages = {
          files: JSON.parse(JSON.stringify(mod)),
        };
        addTranslations(iso, messages);
      });
    }

    for (const path in quotes) {
      quotes[path]().then((mod) => {
        iso = path.substring(7, 9);
        const messages = {
          quote: JSON.parse(JSON.stringify(mod)),
        };
        addTranslations(iso, messages);

        const messages_ = {
          quotes: JSON.parse(JSON.stringify(mod)),
        };
        addTranslations(iso, messages_);
      });
    }

    getLanguagesFiles();
  };

  const addTranslations = (iso, messages) => {
    let currentMessages = getLocaleMessage(iso);
    const mensajesCombinados = merge({}, currentMessages, messages);
    currentMessages = { ...mensajesCombinados };
    mergeLocaleMessage(iso, currentMessages);
  };

  const getLanguagesFiles = async () => {
    currentLang.value = languagesStore.getLanguage;
    locale.value = languagesStore.getLanguage;

    const base = getUrlAuroraFront();

    // Helper seguro para evitar romper el montaje si falla la red/DNS
    const safeFetch = async (url) => {
      try {
        if (!url) return null;
        const res = await axios.get(url);
        return res?.data ?? null;
      } catch (e) {
        console.warn('[i18n] Remote translations unavailable:', url, e?.message || e);
        return null;
      }
    };

    const [globalData, filesData, quotesData] = await Promise.all([
      safeFetch(base && `${base}translation/${languagesStore.getLanguage}/slug/global`),
      safeFetch(base && `${base}translation/${languagesStore.getLanguage}/slug/files`),
      safeFetch(base && `${base}translation/${languagesStore.getLanguage}/slug/quote`),
    ]);

    if (globalData) {
      addTranslations(languagesStore.getLanguage, { global: globalData });
    }
    if (filesData) {
      addTranslations(languagesStore.getLanguage, { files: filesData });
    }
    if (quotesData) {
      addTranslations(languagesStore.getLanguage, { quote: quotesData });
      addTranslations(languagesStore.getLanguage, { quotes: quotesData });
    }

    // Siempre liberamos el loading aunque no haya datos remotos
    setTimeout(() => {
      loading.value = false;
    }, 10);
  };
</script>
