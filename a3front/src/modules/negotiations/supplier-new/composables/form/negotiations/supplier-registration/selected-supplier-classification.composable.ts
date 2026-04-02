import { storeToRefs } from 'pinia';
import { computed, onMounted } from 'vue';
import { useSelectedSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/selected-supplier-classification.store';
import { useSupplierClassificationStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/supplier-classification-store-facade.composable';

export function useSelectedSupplierClassificationComposable() {
  const { setSupplierClassificationId } = useSupplierClassificationStoreFacade();

  const selectedSupplierClassificationStore = useSelectedSupplierClassificationStore();

  // Clasificación preseleccionada (por navegación desde listado)
  const { selectedClassificationId } = storeToRefs(selectedSupplierClassificationStore);

  const { setSelectedClassificationId } = selectedSupplierClassificationStore;

  // Asignar clasificación preseleccionada a supplierClassificationId en supplier-classification-component
  const initSupplierClassificationId = () => {
    // console.log('selectedClassificationId', selectedClassificationId.value);

    if (selectedClassificationId.value) {
      setSupplierClassificationId(selectedClassificationId.value);
    }
  };

  // Deshabilitar select cuando hay una clasificación preseleccionada (por navegación desde listado)
  const isSupplierClassificationDisabled = computed(() => !!selectedClassificationId.value);

  // Inicializar automáticamente cuando el composable se monta
  onMounted(() => {
    initSupplierClassificationId();
  });

  return {
    selectedClassificationId,
    isSupplierClassificationDisabled,

    initSupplierClassificationId,
    setSelectedClassificationId,
  };
}
