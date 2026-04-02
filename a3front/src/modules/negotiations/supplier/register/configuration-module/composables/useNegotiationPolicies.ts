import { onMounted, reactive } from 'vue';
import { Form } from 'ant-design-vue';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { storeToRefs } from 'pinia';
import { useNegotiationPoliciesStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useNegotiationPoliciesStore';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import useNotification from '@/quotes/composables/useNotification';

export function UseNegotiationPolicies() {
  const useForm = Form.useForm;

  const { formStateNegotiation, configSubClassification } = useSupplierFormStoreFacade();

  const negotiationPoliciesStore = useNegotiationPoliciesStore();
  const {
    formStatePolicy,
    formRulesPolicy,
    showModalPolicy,
    isEditModal,
    rules,
    isLoadingForm,
    dataSource,
    idForm,
  } = storeToRefs(negotiationPoliciesStore);

  const {
    setShowModalPolicy,
    resetFormStatePolicy,
    setIsEditModal,
    setAddRule,
    setRemoveRule,
    setIsLoadingForm,
    setDataSource,
    setIdForm,
  } = negotiationPoliciesStore;

  const { resetFields, validate, validateInfos } = useForm(formStatePolicy, formRulesPolicy);

  const { showErrorNotification, showSuccessNotification } = useNotification();

  const state = reactive({
    resources: [],
    isLoading: true,
    isLoadingButton: false,
    listError: [],
    pagination: {
      current: 1,
      pageSize: 10,
      total: 0,
      showSizeChanger: false,
    } as PaginationInterface,
  });

  const handleOk = () => {
    validate()
      .then(async (response) => {
        const subClassificationSupplierId = formStateNegotiation.classifications.find(
          (item: any) => item.supplier_sub_classification_id === configSubClassification.value
        )?.operations?.[0]?.sub_classification_supplier_id;

        const attributes = {
          sub_classification_supplier_id: subClassificationSupplierId,
          name: null,
          date_from: null,
          date_to: null,
          pax_min: response.pax_min,
          pax_max: response.pax_max,
          business_group_id: response.business_group_id,
          rules: JSON.parse(JSON.stringify(rules.value)),
          assignments: [],
        };

        if (isEditModal) {
          await editApiPolicy(idForm.value, attributes);
        } else {
          await storeApiPolicy(attributes);
        }
      })
      .catch((error) => {
        console.error('Error store policy data:', error);
      });
  };

  const handleCancel = () => {
    resetFields();
    resetFormStatePolicy();
    state.listError = [];
    setShowModalPolicy(false);
  };

  const handleVisibleModal = () => {
    resetFormStatePolicy();
    resetFields();
    setIsEditModal(false);
    setIsLoadingForm(false);
    setShowModalPolicy(true);
  };

  const handleOnChange = async (page: number, perSize: number) => {
    await fetchApiPolicies(page, perSize);
  };

  const handleAddRule = () => {
    setAddRule({
      type_penalty_id: undefined,
      unit_duration_id: undefined,
      min_num: null,
      max_num: null,
      penalty: null,
      charged_taxes: false,
      charged_services: false,
    });
  };

  const handleRemoveRule = (index: number) => {
    setRemoveRule(index);
  };

  const fetchResourcesData = async () => {
    try {
      const response = await supplierApi.get('supplier-policy-resources', {
        params: {
          'keys[]': ['businessGroup', 'typePenalty', 'unitDuration'],
        },
      });

      state.resources = response.data.data;
    } catch (error) {
      console.error('Error fetching resource data:', error);
    }
  };

  const fetchApiPolicies = async (page: number = 1, pageSize: number = 10) => {
    try {
      state.isLoading = true;
      const response = await supplierApi.get(`supplier-policies`, {
        params: {
          page: page,
          per_page: pageSize,
        },
      });

      setDataSource(response.data.data);
      state.pagination = {
        current: response.data.pagination.current_page,
        pageSize: response.data.pagination.per_page,
        total: response.data.pagination.total,
        showSizeChanger: false,
      };

      state.isLoading = false;
    } catch (error) {
      state.isLoading = false;
      console.error('Error fetching states data:', error);
    }
  };

  const storeApiPolicy = async (attributes: any) => {
    try {
      state.isLoadingButton = true;
      await supplierApi
        .post(`supplier-policy`, attributes)
        .then(async () => {
          setShowModalPolicy(false);
          state.isLoadingButton = false;
          state.listError = [];
          showSuccessNotification('Política creada satisfactoriamente.');
          await fetchApiPolicies(state.pagination.current, state.pagination.pageSize);
        })
        .catch((error) => {
          state.listError = error.response.data.data;
          state.isLoadingButton = false;
        });
    } catch (error) {
      state.isLoadingButton = false;
      setShowModalPolicy(false);
      console.error('Error store policy data:', error);
      showErrorNotification('Error al crear política.');
    }
  };

  const deleteApiPolicy = async (id: number) => {
    try {
      await supplierApi.delete(`supplier-policy/${id}`).then(() => {
        fetchApiPolicies(state.pagination.current, state.pagination.pageSize);
        showSuccessNotification('Política eliminada satisfactoriamente.');
      });
    } catch (error) {
      console.error('Error delete policy data:', error);
      showErrorNotification('Error al eliminar política.');
    }
  };

  const showApiPolicy = async (id: number): Promise<any> => {
    try {
      setIdForm(id);
      setIsLoadingForm(true);
      setIsEditModal(true);
      setShowModalPolicy(true);
      const { data } = await supplierApi.get(`supplier-policy/${id}`);

      const {
        pax_min,
        pax_max,
        subClassification,
        supplierPolicyRules,
        supplierBusinessGroupPolicy,
      } = data.data;

      Object.assign(formStatePolicy.value, {
        pax_min: pax_min,
        pax_max: pax_max,
        business_group_id: supplierBusinessGroupPolicy.business_group_id,
        sub_classification_supplier_id: subClassification.supplier_sub_classification_id,
      });

      rules.value = [];

      supplierPolicyRules?.forEach((rule: any) => {
        setAddRule({
          type_penalty_id: rule.type_penalty.id,
          unit_duration_id: rule.unit_duration.id,
          min_num: rule.min_num,
          max_num: rule.max_num,
          penalty: rule.penalty,
          charged_taxes: Boolean(rule.charged_taxes),
          charged_services: Boolean(rule.charged_services),
        });
      });

      setTimeout(() => {
        setIsLoadingForm(false);
      }, 500);
    } catch (error) {
      setIsLoadingForm(false);
      console.error('Error show policy data:', error);
    }
  };

  const editApiPolicy = async (id: any, attributes: any) => {
    try {
      state.isLoadingButton = true;

      await supplierApi
        .put(`supplier-policy/${id}`, attributes)
        .then(async () => {
          setIdForm(null);
          setShowModalPolicy(false);
          state.isLoadingButton = false;
          state.listError = [];
          setIsEditModal(false);
          showSuccessNotification('Política actualizada satisfactoriamente.');
          await fetchApiPolicies(state.pagination.current, state.pagination.pageSize);
        })
        .catch((error) => {
          state.listError = error.response.data.data;
          state.isLoadingButton = false;
        });
    } catch (error) {
      state.isLoadingButton = false;
      console.error('Error editing policy data:', error);
      showErrorNotification('Error al actualizar política.');
    }
  };

  const updateStatusApiPolicy = async (id: number, record: any) => {
    try {
      const { status } = record;

      await supplierApi.put(`supplier-policy-status/${id}`, {
        status: status,
      });
    } catch (error) {
      console.error('Error update status policy data:', error);
    }
  };

  onMounted(async () => {
    await Promise.all([fetchResourcesData(), fetchApiPolicies()]);
  });

  return {
    formStatePolicy,
    showModalPolicy,
    handleOk,
    handleCancel,
    handleVisibleModal,
    handleOnChange,
    handleAddRule,
    handleRemoveRule,
    validateInfos,
    state,
    isEditModal,
    rules,
    isLoadingForm,
    storeApiPolicy,
    deleteApiPolicy,
    editApiPolicy,
    updateStatusApiPolicy,
    showApiPolicy,
    dataSource,
  };
}
