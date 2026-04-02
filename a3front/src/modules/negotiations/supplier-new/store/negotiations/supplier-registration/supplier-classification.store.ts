import { defineStore } from 'pinia';
import { ref } from 'vue';
import type {
  SupplierClassificationInterface,
  SupplierSubClassificationInterface,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-negotiations.interface';

export const useSupplierClassificationStore = defineStore('supplierClassificationStore', () => {
  /** typeCode del tipo de proveedor (ej: 'TRP', 'STA') — estable entre entornos */
  const supplierClassificationId = ref<string | null>(null);
  /** subtypeCode del subtipo de proveedor (ej: 'RST', 'DOM') — usado también en el POST */
  const supplierSubClassificationId = ref<string | null>(null);

  // Guardar los nombres para modo lectura
  const supplierClassificationName = ref<string>('');
  const supplierSubClassificationName = ref<string>('');

  const supplierClassifications = ref<Array<SupplierClassificationInterface>>([]);
  const supplierSubClassifications = ref<Array<SupplierSubClassificationInterface>>([]);

  const loadingForm = ref<boolean>(false);
  const disabledButton = ref<boolean>(true);
  const loadingButton = ref<boolean>(false);
  const isEditMode = ref<boolean>(true); // true = formulario, false = lectura
  const justSaved = ref<boolean>(false); // Flag para evitar parpadeo después de guardar
  /** true cuando el subtipo tiene sub-subtipos (ej: AER → DOM/INT); false cuando es hoja (ej: TRN, LNC) */
  const supplierSubClassificationHasSubSubs = ref<boolean | null>(null);

  const setSupplierClassificationId = (value: string | null) => {
    supplierClassificationId.value = value;
  };

  const setSupplierSubClassificationId = (value: string | null) => {
    supplierSubClassificationId.value = value;
  };

  const setSupplierClassificationName = (value: string) => {
    supplierClassificationName.value = value;
  };

  const setSupplierSubClassificationName = (value: string) => {
    supplierSubClassificationName.value = value;
  };

  const setSupplierSubClassificationHasSubSubs = (value: boolean) => {
    supplierSubClassificationHasSubSubs.value = value;
  };

  const resetStore = () => {
    supplierClassificationId.value = null;
    supplierSubClassificationId.value = null;
    supplierClassificationName.value = '';
    supplierSubClassificationName.value = '';
    supplierClassifications.value = [];
    supplierSubClassifications.value = [];
    loadingForm.value = false;
    disabledButton.value = true;
    loadingButton.value = false;
    isEditMode.value = true;
    justSaved.value = false;
    supplierSubClassificationHasSubSubs.value = null;
  };

  return {
    supplierClassificationId,
    supplierSubClassificationId,
    supplierClassificationName,
    supplierSubClassificationName,

    supplierClassifications,
    supplierSubClassifications,
    loadingForm,
    disabledButton,
    loadingButton,
    isEditMode,
    justSaved,

    setSupplierClassificationId,
    setSupplierSubClassificationId,
    setSupplierClassificationName,
    setSupplierSubClassificationName,
    setSupplierSubClassificationHasSubSubs,
    supplierSubClassificationHasSubSubs,
    resetStore,
  };
});
