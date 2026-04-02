import { ref, reactive, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';

export const useGenericTranslationsMarketingComposable = () => {
  const navigationStore = useNavigationStore();
  const { getSectionsItemActive, activeTabKey, getSectionsCodeActive } =
    storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const showAiTranslationAlert = ref(true);
  const activeLanguageTab = ref('es');

  const itineraryOriginal =
    'Un transporte con un representante lo recogerá desde su hotel en Urubamba para ser trasladado a la estación de Ollantaytambo. Luego, viajará una hora y media en el tren Expedition con vistas a paisajes andinos espectaculares desde la estación de Ollantaytambo hasta Aguas Calientes. Posteriormente, realizará un recorrido en autobús de 25 minutos hasta Machu Picchu, una impresionante hazaña de ingeniería y arquitectura que se cree que sirvió como santuario y retiro para el inca Pachacútec. Designado como Patrimonio de la Humanidad por la UNESCO y una de las Nuevas Siete Maravillas del Mundo moderno, Machu Picchu, que significa "Montaña Vieja", cautiva con su esplendor histórico y su majestuosidad natural.';

  const menuOriginal =
    '1. Desayuno continental\nIncluye café, jugo, pan y mermelada.\n\n2. Almuerzo tipo picnic\nSándwich, fruta y agua mineral.\n\n3. Cena en restaurante local\nPlato típico de la región con bebida.';

  const summaryOriginal =
    '1. Recomendación de vuelos\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n\n2. Nivel de dificultad\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.';

  const formState = reactive({
    nameComercialOriginal: 'Tour en ruedas por la playa',
    translations: [
      {
        key: 'es',
        language_id: 1,
        translation_name_comercial: 'Tour en ruedas por la playa',
        qualification_name_comercial_id: 4,
        translation_itinerary:
          'Un transporte con un representante lo recogerá desde su hotel en Urubamba para ser trasladado a la estación de Ollantaytambo. Luego, viajará una hora y media en el tren Expedition con vistas a paisajes andinos espectaculares desde la estación de Ollantaytambo hasta Aguas Calientes. Posteriormente, realizará un recorrido en autobús de 25 minutos hasta Machu Picchu, una impresionante hazaña de ingeniería y arquitectura que se cree que sirvió como santuario y retiro para el inca Pachacútec. Designado como Patrimonio de la Humanidad por la UNESCO y una de las Nuevas Siete Maravillas del Mundo moderno, Machu Picchu, que significa "Montaña Vieja", cautiva con su esplendor histórico y su majestuosidad natural.',
        qualification_itinerary_id: 4,
        translation_itinerary_correct:
          'Un transporte con un representante lo recogerá desde su hotel en Urubamba para ser trasladado a la estación de Ollantaytambo. Luego, viajará una hora y media en el tren Expedition con vistas a paisajes andinos espectaculares desde la estación de Ollantaytambo hasta Aguas Calientes. Posteriormente, realizará un recorrido en autobús de 25 minutos hasta Machu Picchu, una impresionante hazaña de ingeniería y arquitectura que se cree que sirvió como santuario y retiro para el inca Pachacútec. Designado como Patrimonio de la Humanidad por la UNESCO y una de las Nuevas Siete Maravillas del Mundo moderno, Machu Picchu, que significa "Montaña Vieja", cautiva con su esplendor histórico y su majestuosidad natural.',
        menu: 'Menu',
        translation_menu: 'Menu traducido',
        qualification_menu_id: 4,
        translation_menu_correct: 'Menu traducido',
        summary: 'Summary',
        translation_summary: 'Summary traducido',
        qualification_summary_id: 3,
        translation_summary_correct: 'Summary traducido',
      },
      {
        key: 'en',
        language_id: 2,
        translation_name_comercial: 'Beach Wheel Tour',
        qualification_name_comercial_id: 4,
        translation_itinerary:
          'A transport with a representative will pick you up from your hotel in Urubamba to be transferred to the Ollantaytambo station. Then, you will travel an hour and a half on the Expedition train with spectacular Andean landscape views from Ollantaytambo station to Aguas Calientes. Subsequently, you will take a 25-minute bus ride to Machu Picchu, an impressive feat of engineering and architecture believed to have served as a sanctuary and retreat for the Inca Pachacútec. Designated as a UNESCO World Heritage Site and one of the New Seven Wonders of the Modern World, Machu Picchu, meaning "Old Mountain", captivates with its historical splendor and natural majesty.',
        qualification_itinerary_id: 2,
        translation_itinerary_correct:
          'A transport with a representative will pick you up from your hotel in Urubamba to be transferred to the Ollantaytambo station. Then, you will travel an hour and a half on the Expedition train with spectacular Andean landscape views from Ollantaytambo station to Aguas Calientes. Subsequently, you will take a 25-minute bus ride to Machu Picchu, an impressive feat of engineering and architecture believed to have served as a sanctuary and retreat for the Inca Pachacútec. Designated as a UNESCO World Heritage Site and one of the New Seven Wonders of the Modern World, Machu Picchu, meaning "Old Mountain", captivates with its historical splendor and natural majesty.',
        menu: 'Menu',
        translation_menu: 'Translated menu',
        qualification_menu_id: 3,
        translation_menu_correct: 'Menu traducido',
        summary: 'Summary',
        translation_summary: 'Translated summary',
        qualification_summary_id: 4,
        translation_summary_correct: 'Summary traducido',
      },
      {
        key: 'fr',
        language_id: 3,
        translation_name_comercial: 'Tour en roues sur la plage',
        qualification_name_comercial_id: 3,
        translation_itinerary: 'Itinéraire traduit',
        qualification_itinerary_id: 3,
        translation_itinerary_correct: 'Itinéraire traduit',
        menu: 'Menu',
        translation_menu: 'Menu traduit',
        qualification_menu_id: 3,
        translation_menu_correct: 'Menu traduit',
        summary: 'Résumé',
        translation_summary: 'Résumé traduit',
        qualification_summary_id: 3,
        translation_summary_correct: 'Résumé traduit',
      },
      {
        key: 'de',
        language_id: 4,
        translation_name_comercial: 'Strand-Radtour',
        qualification_name_comercial_id: 4,
        translation_itinerary: 'Übersetzte Reiseroute',
        qualification_itinerary_id: 4,
        translation_itinerary_correct: 'Übersetzte Reiseroute',
        menu: 'Menü',
        translation_menu: 'Übersetztes Menü',
        qualification_menu_id: 4,
        translation_menu_correct: 'Übersetztes Menü',
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

  const qualificationItineraryOptions = qualificationNameComercialOptions;
  const qualificationMenuOptions = qualificationNameComercialOptions;
  const qualificationSummaryOptions = qualificationNameComercialOptions;

  const languagesOptions = [
    { key: 'es', label: 'Español' },
    { key: 'en', label: 'Inglés' },
    { key: 'fr', label: 'Francés' },
    { key: 'de', label: 'Alemán' },
  ];

  const totalFields = 5;
  const completedFields = 0;

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

  const updateQualificationItinerary = (langKey: string, value: number) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.qualification_itinerary_id = value;
  };

  const updateQualificationMenu = (langKey: string, value: number) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.qualification_menu_id = value;
  };

  const updateQualificationSummary = (langKey: string, value: number) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.qualification_summary_id = value;
  };

  const updateTranslationItineraryCorrect = (langKey: string, value: string) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.translation_itinerary_correct = value;
  };

  const updateTranslationMenuCorrect = (langKey: string, value: string) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.translation_menu_correct = value;
  };

  const updateTranslationSummaryCorrect = (langKey: string, value: string) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.translation_summary_correct = value;
  };

  const updateTranslationNameComercial = (langKey: string, value: string) => {
    const t = formState.translations.find((t) => t.key === langKey);
    if (t) t.translation_name_comercial = value;
  };
  // =============================================================================
  // ACTIONS
  // =============================================================================

  const closeAiTranslationAlert = () => {
    showAiTranslationAlert.value = false;
  };

  const sendFeedback = () => {
    console.log('sendFeedback');
    const tabKey = activeTabKey.value;
    const sectionCode = getSectionsCodeActive.value;
    const item = getSectionsItemActive.value;
    if (tabKey != null && sectionCode != null && item != null) {
      setCompletedItem(tabKey, sectionCode, item.id);
    }
  };

  return {
    // Variables
    showAiTranslationAlert,
    activeLanguageTab,
    formState,
    itineraryOriginal,
    menuOriginal,
    summaryOriginal,
    qualificationNameComercialOptions,
    qualificationItineraryOptions,
    qualificationMenuOptions,
    qualificationSummaryOptions,
    languagesOptions,
    totalFields,
    completedFields,
    // Computeds
    isEditingContent,
    // Getters
    getTranslationByLang,
    // Setters
    updateQualificationNameComercial,
    updateQualificationItinerary,
    updateQualificationMenu,
    updateQualificationSummary,
    updateTranslationItineraryCorrect,
    updateTranslationMenuCorrect,
    updateTranslationSummaryCorrect,
    updateTranslationNameComercial,
    // Actions
    closeAiTranslationAlert,
    sendFeedback,
  };
};
