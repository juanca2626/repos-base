import { storeToRefs } from 'pinia';
import { computed, onMounted, ref, nextTick } from 'vue';
import { useServiceStore } from '@/modules/negotiations/supplier-new/store/negotiations/module-configuration/services.store';
import debounce from 'lodash.debounce';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useSectionsComposable } from '../supplier-registration/use-section.composable';

export function useServicesComposable() {
  const {
    isEditMode,
    getShowFormComponent,
    openNextSection,
    getIsEditFormComponent,
    handleIsEditFormSpecific,
    handleShowFormSpecific,
    handleSavedFormSpecific,
    markItemComplete,
    handleSetActiveItem,
  } = useSupplierGlobalComposable();

  const { setIsServicesCompleteTemp } = useSectionsComposable();

  const serviceStore = useServiceStore();

  const { columns, currentPage, pageSize, total, sourceData, loading, searchQuery } =
    storeToRefs(serviceStore);

  const serviceData = (i: number) => {
    return {
      code: `LIT00${i}`,
      service_name: `Servicio ${i} - Entradas`,
      pax: 'Desayuno',
      status: true,
    };
  };

  const summaryData = ref<any[]>([]);

  const fetchServiceListData = async (
    page: number = 1,
    pageSize: number = 10,
    searchQuery: string = ''
  ) => {
    loading.value = true;

    await new Promise((resolve) => setTimeout(resolve, 1000));

    const allData = [];
    for (let i = 1; i <= 50; i++) {
      allData.push(serviceData(i));
    }

    const lowerCaseQuery = searchQuery.toLowerCase();
    const filteredData = allData.filter(
      ({ service_name, code }) =>
        service_name.toLowerCase().includes(lowerCaseQuery) ||
        code.toLowerCase().includes(lowerCaseQuery)
    );

    const startIndex = (page - 1) * pageSize;
    const endIndex = startIndex + pageSize;

    sourceData.value = filteredData.slice(startIndex, endIndex);
    total.value = filteredData.length;

    loading.value = false;
  };

  const handleChangePagination = async (page: number, size: number): Promise<void> => {
    currentPage.value = page;
    pageSize.value = size;

    await fetchServiceListData(page, size, searchQuery.value);
  };

  const handleSearch = debounce(async ({ target }: any): Promise<void> => {
    searchQuery.value = target.value;
    await fetchServiceListData(currentPage.value, pageSize.value, searchQuery.value);
  }, 300);

  const handleSave = async () => {
    // TODO: Implementación temporal para maqueta estática
    // Cuando se implemente el guardado real en backend, reemplazar esta función

    // Marcar como guardado y en modo edición (lectura)
    handleIsEditFormSpecific(FormComponentEnum.MODULE_SERVICES, true);
    handleSavedFormSpecific(FormComponentEnum.MODULE_SERVICES, true);

    // Marcar la sección como completa en el sidebar
    markItemComplete('services');

    // Esperar a que el DOM se actualice para que isSectionVisible detecte el cambio
    await nextTick();

    // Activar la siguiente sección (Policies)
    openNextSection(FormComponentEnum.MODULE_POLICIES);

    // Estado temporal para el store
    setIsServicesCompleteTemp(true);

    console.log('🟠 Sección de servicios completada (modo temporal) - abriendo Policies');
  };

  const handleShowForm = () => {
    // Actualizar el ítem activo del sidebar
    handleSetActiveItem('supplier', 'supplier-negotiations', 'services');
    handleIsEditFormSpecific(FormComponentEnum.MODULE_SERVICES, false);
  };

  const columnsForSummary = computed(() => {
    return columns.value.filter((row: any) => row.forSummary);
  });

  const setSummaryData = () => {
    for (let i = 1; i <= 5; i++) {
      summaryData.value.push(serviceData(i));
    }
  };

  const handleClose = () => {
    handleShowFormSpecific(FormComponentEnum.MODULE_SERVICES, false);
  };

  onMounted(async () => {
    setSummaryData();
    await fetchServiceListData();
  });

  return {
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    isEditMode,
    loading,
    searchQuery,
    handleSearch,
    getShowFormComponent,
    getIsEditFormComponent,
    columnsForSummary,
    summaryData,
    handleChangePagination,
    handleSave,
    handleShowForm,
    handleClose,
  };
}
