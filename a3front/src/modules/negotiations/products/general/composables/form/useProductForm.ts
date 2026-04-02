import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import type { FormInstance, Rule } from 'ant-design-vue/es/form';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import {
  filterOption,
  mapItemsToOptions,
} from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import { useProductFormStoreFacade } from '@/modules/negotiations/products/general/composables/form/useProductFormStoreFacade';
import { useProductFormSidebarStore } from '@/modules/negotiations/products/general/store/useProductFormSidebarStore';
import { MenuItemEnum } from '@/modules/negotiations/products/general/enums/menu-item.enum';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
import { productResourceService } from '@/modules/negotiations/products/general/services/productResourceService';
import { productService } from '@/modules/negotiations/products/general/services/productService';
import type { ServiceTypeListItem } from '@/modules/negotiations/products/general/interfaces/resources';
import type {
  FieldAvailability,
  FieldsAvailability,
  ProductForm,
  ProductFormRequest,
  ProductFormResponse,
} from '@/modules/negotiations/products/general/interfaces/form';
import {
  handleFieldMessageErrors,
  handleSuccessResponse,
} from '@/modules/negotiations/api/responseApi';
import { useProductFormManager } from '@/modules/negotiations/products/general/composables/useProductFormManager';

export function useProductForm() {
  const router = useRouter();

  const { openSummaryMode, closeSummaryMode } = useProductFormManager();

  const { markMenuComplete, markMenuActive, resetMenuItems } = useProductFormSidebarStore();

  const { fetchServiceTypes } = productResourceService;

  const { showProduct, storeProduct, updateProduct, checkCodeAvailability, checkNameAvailability } =
    productService;

  const {
    formState,
    isEditMode,
    productId,
    setIsEditMode,
    resetFormState,
    setFormState,
    startLoading,
    stopLoading,
    setProductId,
  } = useProductFormStoreFacade();

  const { setSelectedServiceTypeId } = useSelectedServiceType();

  const formRefProduct = ref<FormInstance | null>(null);

  const allServiceTypes = ref<ServiceTypeListItem[]>([]);
  const serviceTypes = ref<SelectOption[]>([]);

  const fieldsAvailability = reactive<FieldsAvailability>({
    code: {
      isAvailable: true,
      isLoading: false,
    },
    name: {
      isAvailable: true,
      isLoading: false,
    },
  });

  const formRules: Record<string, Rule[]> = {
    serviceTypeId: [
      { required: true, message: 'Debe seleccionar el tipo de servicio', trigger: 'change' },
    ],
    code: [
      { required: true, message: 'Debe ingresar el código', trigger: 'change' },
      { pattern: /^[A-Z0-9]+$/, message: 'El código debe tener letras mayúsculas y números' },
      { len: 6, message: 'El código debe tener 6 caracteres', trigger: 'change' },
    ],
    name: [{ required: true, message: 'Debe ingresar el nombre', trigger: 'change' }],
  };

  const getServiceTypes = async () => {
    serviceTypes.value = [];
    const { data } = await fetchServiceTypes();
    allServiceTypes.value = data;
    serviceTypes.value = mapItemsToOptions(data);
  };

  const fetchResources = async () => {
    try {
      startLoading();
      await getServiceTypes();
    } catch (error) {
      console.error('Error fetching resources:', error);
    } finally {
      stopLoading();
    }
  };

  const resetForm = () => {
    formRefProduct.value?.resetFields();
    resetFormState();
  };

  const getRequestData = (): ProductFormRequest => {
    return {
      serviceTypeId: formState.serviceTypeId!,
      code: formState.code!,
      name: formState.name!,
      status: true,
    };
  };

  const saveForm = async () => {
    const request = getRequestData();

    try {
      startLoading();

      const response =
        isEditMode.value && formState.id
          ? await updateProduct(formState.id, request)
          : await storeProduct(request);

      if (response.success) {
        handleSuccessResponse(response);
        redirectEdit(response.data.id);
      }
    } catch (error: any) {
      handleFieldMessageErrors(error);
      console.error('Error save product:', error);
    } finally {
      stopLoading();
    }
  };

  const redirectEdit = async (id: string) => {
    router.replace({
      name: 'productEdit',
      params: { id },
    });

    setProductId(id);
    initializeFormSummary();
    await loadData();
  };

  const initializeFormSummary = () => {
    setIsEditMode(false);
    openSummaryMode();
    markMenuComplete(MenuItemEnum.GENERAL_INFORMATION);
    markMenuActive(MenuItemEnum.SUPPLIERS);
  };

  const handleSave = async () => {
    try {
      await formRefProduct.value?.validate();
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  const handleChangeServiceType = () => {
    const item = allServiceTypes.value.find((item) => item.id === formState.serviceTypeId);

    if (item) {
      formState.serviceTypeName = item.name;

      // asignar id original para controlar flujo y tipos
      setSelectedServiceTypeId(item.originalId);
    }
  };

  const updateFieldAvailability = (
    field: keyof FieldsAvailability,
    property: keyof FieldAvailability,
    value: boolean
  ) => {
    fieldsAvailability[field][property] = value;
  };

  const setFieldLoading = (field: keyof FieldsAvailability, value: boolean) => {
    updateFieldAvailability(field, 'isLoading', value);
  };

  const setFieldAvailable = (field: keyof FieldsAvailability, value: boolean) => {
    updateFieldAvailability(field, 'isAvailable', value);
  };

  const handleInputCode = () => {
    formState.code = formState.code?.toUpperCase() || null;
    validateCode();
  };

  const validateCode = debounce(async () => {
    const field = 'code';
    const value = formState.code;

    if (value?.length !== 6) return;

    try {
      setFieldLoading(field, true);
      const { data } = await checkCodeAvailability(value);
      setFieldAvailable(field, data.available);
    } catch (error) {
      console.error('Error check code availability:', error);
    } finally {
      setFieldLoading(field, false);
    }
  }, 500);

  const handleInputName = debounce(async () => {
    const field = 'name';
    const value = formState.name ?? '';

    if (value.length < 3) return;

    try {
      setFieldLoading(field, true);
      const { data } = await checkNameAvailability(value);
      setFieldAvailable(field, data.available);
    } catch (error) {
      console.error('Error check name availability:', error);
    } finally {
      setFieldLoading(field, false);
    }
  }, 500);

  const setDefaultServiceType = () => {
    formState.serviceTypeId =
      serviceTypes.value.length > 0 ? `${serviceTypes.value[0].value}` : null;
  };

  const initData = () => {
    setDefaultServiceType();
    handleChangeServiceType();
  };

  const isFormInvalid = computed(() => {
    const requiredFields: (keyof ProductForm)[] = ['serviceTypeId', 'code', 'name'];

    return (
      !fieldsAvailability.code.isAvailable ||
      !fieldsAvailability.name.isAvailable ||
      requiredFields.some((field) => !formState[field])
    );
  });

  const disabledSaveButton = computed(() => {
    return (
      isFormInvalid.value || fieldsAvailability.code.isLoading || fieldsAvailability.name.isLoading
    );
  });

  const exitForm = () => {
    resetForm();
    setIsEditMode(false);
    closeSummaryMode();
    resetMenuItems();
  };

  const setDataToForm = (data: ProductFormResponse) => {
    setFormState({
      id: data.id,
      serviceTypeId: data.serviceTypeId,
      serviceTypeName: null,
      code: data.code,
      name: data.name,
    });

    handleChangeServiceType();
  };

  const loadData = async () => {
    if (!productId.value) return;

    try {
      startLoading();

      const { success, data } = await showProduct(productId.value);

      if (success) {
        setDataToForm(data);
      }
    } catch (error) {
      console.error('Error fetching resources:', error);
    } finally {
      stopLoading();
    }
  };

  onMounted(async () => {
    if (productId.value) {
      initializeFormSummary();
    }
    await fetchResources();
    initData();
    await loadData();
  });

  onUnmounted(() => {
    exitForm();
  });

  return {
    isEditMode,
    formState,
    formRules,
    formRefProduct,
    serviceTypes,
    fieldsAvailability,
    isFormInvalid,
    handleInputName,
    disabledSaveButton,
    filterOption,
    handleSave,
    handleChangeServiceType,
    handleInputCode,
  };
}
