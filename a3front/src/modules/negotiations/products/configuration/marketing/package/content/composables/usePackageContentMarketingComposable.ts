import { ref, computed, reactive } from 'vue';
import { storeToRefs } from 'pinia';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';

export const usePackageContentMarketingComposable = () => {
  const navigationStore = useNavigationStore();
  const { getSectionsItemActive, activeTabKey, getSectionsCodeActive } =
    storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;
  const totalFields = 5;
  const isHelpOverlayOpen = ref(false);
  const isItineraryHelpOpen = ref(false);
  const isMenuHelpOpen = ref(false);
  const isSummaryHelpOpen = ref(false);
  const activeCategoryIds = ref<number[]>([]);
  const categoryContents = reactive<Record<number, string>>({});
  const titleModalConfirm = ref('¿Estás seguro de guardar los datos?');
  const textModalConfirm = ref(
    'Al proceder, se guardarán los datos y se lo confirmas a Loading <strong>¿Quieres continuar?</strong>'
  );
  const actionButtonTextModalConfirm = ref('Guardar');
  const showModalConfirm = ref(false);
  const isEditingName = ref(false);
  const isEditingItinerary = ref(false);
  const isEditingSummary = ref(false);
  const isEditingMenu = ref(false);

  const rules = {
    translations: [{ required: true, message: 'Es requerido al menos un idioma', trigger: 'blur' }],
    nameComercial: [
      { required: true, message: 'El nombre comercial es requerido', trigger: 'blur' },
    ],
  };

  const formState = reactive({
    translations: ['es', 'en'],
    nameService: 'Nombre del servicio',
    nameComercial: 'Nombre comercial',
    itinerary: {
      text: 'Itinerario',
      status: 'PENDING',
      days: [
        {
          dayNumber: 1,
          text: 'Itinerario 1',
          status: 'PENDING',
        },
        {
          dayNumber: 2,
          text: 'Itinerario 2',
          status: 'PENDING',
        },
      ],
    },
    menu: {
      text: 'Menu',
      status: 'PENDING',
    },
    summary: {
      textGeneral:
        'Lorem ipsum dolor sit amet. Cum voluptatem commodi qui error eligendi quo illo libero et error earum eum ipsam unde. Sed ipsa recusandae qui commodi labore ab iure nesciunt At quis recusandae. Sit nisi nulla sed accusamus labore est dolore modi ut nisi incidunt nam incidunt voluptatibus',
      items: [],
      status: 'PENDING',
    },
    summaryCategories: {
      items: [],
    },
  });

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

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? false;
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

  const translationsOptions = computed(() => {
    return [
      { label: 'Español', value: 'es' },
      { label: 'Inglés', value: 'en' },
      { label: 'Francés', value: 'fr' },
      { label: 'Alemán', value: 'de' },
    ];
  });

  const activeCategoriesWithContent = computed(() => {
    return activeCategoryIds.value
      .map((id) => {
        const category = categoriesSummary.value.find((c) => c.id === id);
        return category ? { ...category, content: categoryContents[id] || '' } : null;
      })
      .filter(Boolean) as Array<{ id: number; text: string; content: string }>;
  });

  const toggleHelpOverlay = () => {
    isHelpOverlayOpen.value = !isHelpOverlayOpen.value;
  };

  const toggleItineraryHelpOverlay = () => {
    isItineraryHelpOpen.value = !isItineraryHelpOpen.value;
  };

  const markAsReviewedItinerary = () => {
    formState.itinerary.status = 'REVIEWED';
  };

  const toggleMenuHelpOverlay = () => {
    isMenuHelpOpen.value = !isMenuHelpOpen.value;
  };

  const markAsReviewedMenu = () => {
    formState.menu.status = 'REVIEWED';
  };

  const toggleSummaryHelpOverlay = () => {
    isSummaryHelpOpen.value = !isSummaryHelpOpen.value;
  };

  const markAsReviewedSummary = () => {
    formState.summary.status = 'REVIEWED';
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

  const stripHtml = (html: string) => {
    if (!html) return '';
    return html.replace(/<[^>]*>/g, '').trim();
  };

  const toggleCategory = (categoryId: number) => {
    const index = activeCategoryIds.value.indexOf(categoryId);
    if (index === -1) {
      activeCategoryIds.value.push(categoryId);
    } else {
      activeCategoryIds.value.splice(index, 1);
    }
  };

  const isCategoryActive = (categoryId: number) => activeCategoryIds.value.includes(categoryId);

  const updateCategoryContent = (categoryId: number, content: string) => {
    categoryContents[categoryId] = content;
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
    handleSave();
  };

  const handleConfirm = () => {
    console.log('handleConfirm');
  };

  const handleCancel = () => {
    console.log('handleCancel');
  };

  return {
    formState,
    rules,
    totalFields,
    completedFields,
    isHelpOverlayOpen,
    isItineraryHelpOpen,
    isMenuHelpOpen,
    isSummaryHelpOpen,
    activeCategoriesWithContent,
    activeCategoryIds,
    categoryContents,

    isEditingContent,
    categoriesSummary,
    translationsOptions,

    isEditingName,
    isEditingItinerary,
    isEditingSummary,
    isEditingMenu,

    toggleEditName,
    toggleEditItinerary,
    toggleEditSummary,
    toggleEditMenu,

    toggleItineraryHelpOverlay,
    markAsReviewedItinerary,
    toggleHelpOverlay,
    toggleMenuHelpOverlay,
    markAsReviewedMenu,
    toggleSummaryHelpOverlay,
    markAsReviewedSummary,
    stripHtml,
    isCategoryActive,
    toggleCategory,
    updateCategoryContent,
    handleSave,

    handleConfirmSaveModal,
    handleConfirm,
    handleCancel,
    titleModalConfirm,
    textModalConfirm,
    actionButtonTextModalConfirm,
    showModalConfirm,
  };
};
