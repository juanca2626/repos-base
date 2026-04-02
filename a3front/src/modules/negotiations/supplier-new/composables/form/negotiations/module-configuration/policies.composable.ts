import { storeToRefs } from 'pinia';
import { onMounted, watch } from 'vue';
import { usePoliciesStore } from '@/modules/negotiations/supplier-new/store/negotiations/module-configuration/policies.store';
import debounce from 'lodash.debounce';
import {
  handleCompleteResponse,
  handleError,
  handleErrorResponse,
} from '@/modules/negotiations/api/responseApi';
import type { Rule } from 'ant-design-vue/es/form';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';

export function usePoliciesComposable() {
  const { subClassificationSupplierId, handleIsEditFormSpecific, handleSavedFormSpecific } =
    useSupplierGlobalComposable();

  const policiesStore = usePoliciesStore();

  const {
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    loading,
    searchQuery,
    showForm,
    isLoadingForm,
    loadingButton,
    initialFormData,
    formState,
    formRef,
    businessGroup,
    typePenalty,
    unitDuration,
    market,
    client,
    calendar,
  } = storeToRefs(policiesStore);

  const formRules: Record<string, Rule[]> = {
    sub_classification_supplier_id: [
      { required: false, message: 'Selecciona un proveedor', trigger: 'change' },
    ],
    name: [{ required: false, message: 'Ingresa nombre completo', trigger: 'blur' }],
    date_from: [{ required: false, message: 'Selecciona la fecha de inicio', trigger: 'change' }],
    date_to: [{ required: false, message: 'Selecciona la fecha de fin', trigger: 'change' }],
    pax_min: [{ required: true, message: 'Ingresa el número mínimo de personas', trigger: 'blur' }],
    pax_max: [{ required: true, message: 'Ingresa el número máximo de personas', trigger: 'blur' }],
    business_group_id: [
      { required: true, message: 'Selecciona un grupo de negocios', trigger: 'change' },
    ],
    assignments: [{ required: false, message: 'Selecciona asignaciones', trigger: 'change' }],
  };

  const {
    storeSupplierPolicies,
    updateSupplierPolicies,
    getSupplierPolicies,
    deleteSupplierPolicies,
    showSupplierPolicies,
    updateSupplierPoliciesStatus,
    fetchPoliciesResource,
  } = policiesStore;

  const fetchPoliciesListData = async (
    page: number = 1,
    perPage: number = 10,
    searchQuery: string = ''
  ) => {
    loading.value = true;

    const { data, pagination } = await getSupplierPolicies({
      page: page,
      per_page: perPage,
      sub_classification_supplier_id: subClassificationSupplierId.value,
      ...(searchQuery ? { search: searchQuery } : {}),
    });

    sourceData.value = data;
    currentPage.value = pagination.current_page;
    pageSize.value = pagination.per_page;
    total.value = pagination.total;
    loading.value = false;
  };

  const fetchModuleContactResourceData = async () => {
    const { data } = await fetchPoliciesResource();
    businessGroup.value = data.businessGroup;
    typePenalty.value = data.typePenalty;
    unitDuration.value = data.unitDuration;
    market.value = data.market;
    client.value = data.client;
    calendar.value = data.calendar;
  };

  const handleChangePagination = async (page: number, size: number): Promise<void> => {
    currentPage.value = page;
    pageSize.value = size;

    await fetchPoliciesListData(page, size, searchQuery.value);
  };

  const handleShowForm = (state: boolean) => {
    showForm.value = state;
  };

  const handleEditPolicies = async (id: number): Promise<void> => {
    const { data } = await showSupplierPolicies(id);

    formState.value = {
      id: id,
      sub_classification_supplier_id: undefined,
      name: null,
      date_from: null,
      date_to: null,
      pax_min: data.pax_min,
      pax_max: data.pax_max,
      business_group_id: data.supplierBusinessGroupPolicy.business_group_id,
      assignments: [],
      rules: data.supplierPolicyRules,
    };

    handleShowForm(true);
  };

  const handleDeletePolicies = async (id: number): Promise<void> => {
    const { success, data } = await deleteSupplierPolicies(id);

    if (success) {
      await fetchPoliciesListData();
      handleCompleteResponse(data, 'Política eliminada satisfactoriamente.');
    } else {
      handleErrorResponse();
    }
  };

  const handleUpdateSupplierPoliciesStatus = async (id: number, status: boolean): Promise<void> => {
    const { success, data } = await updateSupplierPoliciesStatus(id, status);

    if (success) {
      await fetchPoliciesListData();
      handleCompleteResponse(data, 'Política actualizada satisfactoriamente.');
    } else {
      handleErrorResponse();
    }
  };

  const handleSearch = debounce(async ({ target }: any): Promise<void> => {
    searchQuery.value = target.value;
    await fetchPoliciesListData(currentPage.value, pageSize.value, searchQuery.value);
  }, 300);

  const getRequestData = () => {
    return {
      sub_classification_supplier_id: subClassificationSupplierId.value,
      name: null,
      date_from: null,
      date_to: null,
      pax_min: formState.value.pax_min,
      pax_max: formState.value.pax_max,
      business_group_id: formState.value.business_group_id,
      rules: formState.value.rules,
      assignments: [],
    };
  };

  const handleClose = () => {
    formState.value = { ...initialFormData.value };
    formState.value.rules = [];
    formState.value.rules = [...initialFormData.value.rules];
    handleShowForm(false);
  };

  const handleSaveForm = async () => {
    const request = getRequestData();

    try {
      loadingButton.value = true;
      const { data } = formState.value.id
        ? await updateSupplierPolicies(formState.value.id, request)
        : await storeSupplierPolicies(request);

      handleCompleteResponse(data);

      handleIsEditFormSpecific(FormComponentEnum.MODULE_POLICIES, true);
      handleSavedFormSpecific(FormComponentEnum.MODULE_POLICIES, true);
      await fetchPoliciesListData();
      handleClose();
    } catch (error: any) {
      handleError(error);
      loadingButton.value = false;
      console.log('⛔ Error save: ', error.message);
    } finally {
      loadingButton.value = false;
    }
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error: any) {
      console.log('⛔ Validation error: ', error.message);
    }
  };

  const handleAddRule = () => {
    formState.value.rules.push({ ...initialFormData.value.rules[0] });
  };

  const handleRemoveRule = (index: number) => {
    formState.value.rules.value.splice(index, 1);
  };

  onMounted(async () => {
    await Promise.all([fetchPoliciesListData(), fetchModuleContactResourceData()]);
  });

  watch(
    () => subClassificationSupplierId.value,
    async () => {
      await fetchPoliciesListData();
    },
    { deep: true, immediate: true }
  );

  return {
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    loading,
    searchQuery,
    isLoadingForm,
    showForm,
    formState,
    formRef,
    formRules,
    businessGroup,
    typePenalty,
    unitDuration,
    loadingButton,
    market,
    client,
    calendar,
    handleChangePagination,
    handleShowForm,
    handleSearch,
    handleEditPolicies,
    handleDeletePolicies,
    handleUpdateSupplierPoliciesStatus,
    handleSave,
    handleClose,
    handleAddRule,
    handleRemoveRule,
  };
}
