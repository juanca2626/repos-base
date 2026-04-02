import { ref, computed, reactive, onMounted, onUnmounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';

export const useGenericContentMarketingComposable = () => {
  const navigationStore = useNavigationStore();
  const { setCompletedItem } = navigationStore;
  const { activeTabKey, getSectionsCodeActive, getSectionsItemActive } =
    storeToRefs(navigationStore);

  const totalFields = 5;

  const isHelpOverlayOpen = ref(false);
  const isItineraryHelpOpen = ref(false);
  const isMenuHelpOpen = ref(false);
  const isSummaryHelpOpen = ref(false);

  const isEditingName = ref(false);
  const isEditingItinerary = ref(false);
  const isEditingSummary = ref(false);
  const isEditingMenu = ref(false);

  const activeCategoryIds = ref<number[]>([]);
  const categoryContents = reactive<Record<number, string>>({});

  const titleModalConfirm = ref('¿Estás seguro de marcar como revisado?');
  const textModalConfirm = ref(
    'Al proceder, marcas como revisado todos los textos, y se lo confirmas a Loading <strong>¿Quieres continuar?</strong>'
  );
  const actionButtonTextModalConfirm = ref('Revisado');
  const showModalConfirm = ref(false);
  const currentSectionBeingReviewed = ref<'itinerary' | 'menu' | 'summary' | null>(null);

  const formState = reactive({
    translations: [],
    nameService: 'Nombre del servicio',
    nameComercial: '',
    itinerary: {
      text: 'Itinerario',
      status: 'PENDING',
    },
    summary: {
      textGeneral:
        'Lorem ipsum dolor sit amet. Cum voluptatem commodi qui error eligendi quo illo libero et error earum eum ipsam unde. Sed ipsa recusandae qui commodi labore ab iure nesciunt At quis recusandae. Sit nisi nulla sed accusamus labore est dolore modi ut nisi incidunt nam incidunt voluptatibus',
      items: [
        {
          id: 1,
          text: 'Recomendación de vuelos',
        },
      ],
      status: 'PENDING',
    },
    menu: {
      text: 'Menu',
      status: 'PENDING',
    },
  });

  const categoriesSummary = ref([
    { id: 1, text: 'Recomendación de vuelos' },
    { id: 2, text: 'Guiado: idioma, categoría' },
    { id: 3, text: 'Condiciones tarifarias' },
    { id: 4, text: 'Nivel de dificultad' },
    { id: 5, text: 'Restricciones de equipaje' },
    { id: 6, text: 'Vestimenta' },
    { id: 7, text: 'Fechas especiales' },
    { id: 8, text: 'Temporada' },
    { id: 9, text: 'Condiciones' },
    { id: 10, text: 'Acomodación' },
    { id: 11, text: 'Alimentación: restricciones' },
  ]);

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? false;
  });

  const activeCategoriesWithContent = computed(() => {
    return activeCategoryIds.value
      .map((id) => {
        const category = categoriesSummary.value.find((c) => c.id === id);
        return category ? { ...category, content: categoryContents[id] || '' } : null;
      })
      .filter(Boolean) as Array<{ id: number; text: string; content: string }>;
  });

  const rules = {
    translations: [{ required: true, message: 'Es requerido al menos un idioma', trigger: 'blur' }],
    nameComercial: [
      { required: true, message: 'El nombre comercial es requerido', trigger: 'blur' },
    ],
  };

  const completedFields = computed(() => {
    let count = 0;

    if (formState.translations.length > 0) {
      count++;
    }

    if (formState.nameComercial != '') {
      count++;
    }

    if (formState.itinerary.text != '') {
      count++;
    }

    if (formState.menu.text != '') {
      count++;
    }

    // si hay categorias activas, contar las categorias activas
    if (activeCategoryIds.value.length > 0) {
      if (activeCategoryIds.value.some((id) => categoryContents[id] != '')) {
        count++;
      }
    }

    return count;
  });

  const translationsOptions = computed(() => {
    return [
      { label: 'Español', value: 'es' },
      { label: 'Inglés', value: 'en' },
      { label: 'Francés', value: 'fr' },
      { label: 'Alemán', value: 'de' },
    ];
  });

  const toggleCategory = (categoryId: number) => {
    const index = activeCategoryIds.value.indexOf(categoryId);
    if (index === -1) {
      activeCategoryIds.value.push(categoryId);
      if (!(categoryId in categoryContents)) {
        categoryContents[categoryId] = '';
      }
    } else {
      activeCategoryIds.value.splice(index, 1);
    }
  };

  const isCategoryActive = (categoryId: number) => activeCategoryIds.value.includes(categoryId);

  const updateCategoryContent = (categoryId: number, content: string) => {
    categoryContents[categoryId] = content;
  };

  const stripHtml = (html: string) => {
    if (!html) return '';
    return html.replace(/<[^>]*>/g, '').trim();
  };

  const toggleHelpOverlay = () => {
    isHelpOverlayOpen.value = !isHelpOverlayOpen.value;
  };

  const openHelpOverlay = () => {
    isHelpOverlayOpen.value = true;
  };

  const closeHelpOverlay = () => {
    isHelpOverlayOpen.value = false;
  };

  const toggleItineraryHelpOverlay = () => {
    isItineraryHelpOpen.value = !isItineraryHelpOpen.value;
  };

  const toggleMenuHelpOverlay = () => {
    isMenuHelpOpen.value = !isMenuHelpOpen.value;
  };

  const toggleSummaryHelpOverlay = () => {
    isSummaryHelpOpen.value = !isSummaryHelpOpen.value;
  };

  const toggleEditName = () => {
    isEditingName.value = !isEditingName.value;
  };

  const toggleEditItinerary = () => {
    isEditingItinerary.value = !isEditingItinerary.value;
  };

  const toggleEditSummary = () => {
    isEditingSummary.value = !isEditingSummary.value;
  };

  const toggleEditMenu = () => {
    isEditingMenu.value = !isEditingMenu.value;
  };

  const markAsReviewedItinerary = () => {
    currentSectionBeingReviewed.value = 'itinerary';
    showModalConfirm.value = true;
    textModalConfirm.value =
      'Al proceder, marcas como revisado todos los textos, y se lo confirmas a Loading <strong>¿Quieres continuar?</strong>';
  };

  const markAsReviewedMenu = () => {
    currentSectionBeingReviewed.value = 'menu';
    showModalConfirm.value = true;
    textModalConfirm.value =
      'Al proceder, marcas como revisado todos los textos, y se lo confirmas a Loading <strong>¿Quieres continuar?</strong>';
  };

  const markAsReviewedSummary = () => {
    currentSectionBeingReviewed.value = 'summary';
    showModalConfirm.value = true;
    textModalConfirm.value =
      'Al proceder, marcas como revisado todos los textos, y se lo confirmas a Loading <strong>¿Quieres continuar?</strong>';
  };

  const handleConfirm = () => {
    if (currentSectionBeingReviewed.value === 'itinerary') {
      formState.itinerary.status = 'REVIEWED';
    } else if (currentSectionBeingReviewed.value === 'menu') {
      formState.menu.status = 'REVIEWED';
    } else if (currentSectionBeingReviewed.value === 'summary') {
      formState.summary.status = 'REVIEWED';
    }

    showModalConfirm.value = false;

    if (currentSectionBeingReviewed.value === null) {
      handleSave();
    }
  };

  const handleCancel = () => {
    showModalConfirm.value = false;
    currentSectionBeingReviewed.value = null;
  };

  const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
      if (isHelpOverlayOpen.value) closeHelpOverlay();
      if (isItineraryHelpOpen.value) isItineraryHelpOpen.value = false;
      if (isMenuHelpOpen.value) isMenuHelpOpen.value = false;
      if (isSummaryHelpOpen.value) isSummaryHelpOpen.value = false;
    }
  };

  const handleSave = () => {
    const tabKey = activeTabKey.value;
    const sectionCode = getSectionsCodeActive.value;
    const item = getSectionsItemActive.value;
    if (tabKey != null && sectionCode != null && item != null) {
      setCompletedItem(tabKey, sectionCode, item.id);
    }
  };

  const handleConfirmSaveModal = () => {
    currentSectionBeingReviewed.value = null;
    const statusPending = [
      formState.itinerary.status,
      formState.menu.status,
      formState.summary.status,
    ].some((status) => status === 'PENDING');

    if (statusPending) {
      showModalConfirm.value = true;
      titleModalConfirm.value = 'No has marcado todos los textos, ¿deseas marcar todos?';
      textModalConfirm.value =
        'Al proceder, marcas como revisado todos los textos, y se lo confirmas a Loading <strong>¿Quieres continuar?</strong>';
      actionButtonTextModalConfirm.value = 'Revisado';
    } else {
      handleSave();
    }
  };

  onMounted(() => {
    window.addEventListener('keydown', handleEscape);
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', handleEscape);
  });

  return {
    totalFields,
    completedFields,
    formState,
    rules,
    translationsOptions,
    categoriesSummary,
    activeCategoryIds,
    categoryContents,
    activeCategoriesWithContent,
    isHelpOverlayOpen,
    isItineraryHelpOpen,
    isMenuHelpOpen,
    isSummaryHelpOpen,
    isEditingContent,
    isEditingName,
    isEditingItinerary,
    isEditingSummary,
    isEditingMenu,
    showModalConfirm,
    titleModalConfirm,
    textModalConfirm,
    actionButtonTextModalConfirm,

    toggleCategory,
    isCategoryActive,
    updateCategoryContent,
    stripHtml,
    toggleHelpOverlay,
    openHelpOverlay,
    closeHelpOverlay,
    toggleItineraryHelpOverlay,
    toggleMenuHelpOverlay,
    toggleSummaryHelpOverlay,
    toggleEditName,
    toggleEditItinerary,
    toggleEditSummary,
    toggleEditMenu,
    markAsReviewedItinerary,
    markAsReviewedMenu,
    markAsReviewedSummary,
    handleConfirm,
    handleCancel,
    handleConfirmSaveModal,
  };
};
