import { computed, reactive, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';

export const useTrainTranslationsMarketingComposable = () => {
  const navigationStore = useNavigationStore();
  const { getSectionsItemActive, activeTabKey, getSectionsCodeActive } =
    storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const completedFields = ref(0);
  const totalFields = ref(3);

  const showAiTranslationAlert = ref(true);
  const activeLanguageTab = ref('es');

  const summaryOriginal =
    '1. Recomendación de vuelos\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n\n2. Nivel de dificultad\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.';

  const formState = reactive({
    nameComercialOriginal: 'Tren Expedition a Machu Picchu',
    translations: [
      {
        key: 'es',
        language_id: 1,
        translation_name_comercial: 'Tren Expedition a Machu Picchu',
        qualification_name_comercial_id: 4,
        summary: 'Summary',
        translation_summary: 'Summary traducido',
        qualification_summary_id: 3,
        translation_summary_correct: 'Summary traducido',
      },
      {
        key: 'en',
        language_id: 2,
        translation_name_comercial: 'Expedition Train to Machu Picchu',
        qualification_name_comercial_id: 4,
        summary: 'Summary',
        translation_summary: 'Translated summary',
        qualification_summary_id: 4,
        translation_summary_correct: 'Summary traducido',
      },
      {
        key: 'fr',
        language_id: 3,
        translation_name_comercial: 'Train Expedition vers Machu Picchu',
        qualification_name_comercial_id: 3,
        summary: 'Résumé',
        translation_summary: 'Résumé traduit',
        qualification_summary_id: 3,
        translation_summary_correct: 'Résumé traduit',
      },
      {
        key: 'de',
        language_id: 4,
        translation_name_comercial: 'Expedition-Zug nach Machu Picchu',
        qualification_name_comercial_id: 4,
        summary: 'Zusammenfassung',
        translation_summary: 'Übersetzte Zusammenfassung',
        qualification_summary_id: 4,
        translation_summary_correct: 'Übersetzte Zusammenfassung',
      },
    ],
  });

  const qualificationNameComercialOptions = [
    { id: 1, name: 'La traducción no tiene sentido' },
    { id: 2, name: 'Difícil de entender' },
    { id: 3, name: 'Se entiende el mensaje general' },
    { id: 4, name: 'Traducción precisa y natural' },
  ];

  const qualificationSummaryOptions = qualificationNameComercialOptions;

  const languagesOptions = [
    { key: 'es', label: 'Español' },
    { key: 'en', label: 'Inglés' },
    { key: 'fr', label: 'Francés' },
    { key: 'de', label: 'Alemán' },
  ];

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? false;
  });

  const getTranslationByLang = (langKey: string) => {
    return formState.translations.find((t) => t.key === langKey);
  };

  const updateQualificationNameComercial = (langKey: string, value: number) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.qualification_name_comercial_id = value;
  };

  const updateQualificationSummary = (langKey: string, value: number) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.qualification_summary_id = value;
  };

  const updateTranslationSummaryCorrect = (langKey: string, value: string) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.translation_summary_correct = value;
  };

  const updateTranslationNameComercial = (langKey: string, value: string) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.translation_name_comercial = value;
  };

  const closeAiTranslationAlert = () => {
    showAiTranslationAlert.value = false;
  };

  const sendFeedback = () => {
    const tabKey = activeTabKey.value;
    const sectionCode = getSectionsCodeActive.value;
    const item = getSectionsItemActive.value;
    if (tabKey != null && sectionCode != null && item != null) {
      setCompletedItem(tabKey, sectionCode, item.id);
    }
  };

  return {
    formState,
    completedFields,
    totalFields,
    isEditingContent,
    showAiTranslationAlert,
    activeLanguageTab,
    languagesOptions,
    summaryOriginal,
    qualificationNameComercialOptions,
    qualificationSummaryOptions,
    getTranslationByLang,
    updateQualificationNameComercial,
    updateQualificationSummary,
    updateTranslationSummaryCorrect,
    updateTranslationNameComercial,
    closeAiTranslationAlert,
    sendFeedback,
  };
};
