import { onMounted, reactive } from 'vue';
import { Form } from 'ant-design-vue';
import { supplierApi, supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { useSupplierForm } from '@/modules/negotiations/supplier/register/composables/useSupplierForm';
import { useNegotiationContactsStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useNegotiationContactsStore';
import { storeToRefs } from 'pinia';

export function useNegotiationContacts() {
  const useForm = Form.useForm;
  const { getRouteSupplierId } = useSupplierForm();

  const negotiationContactsStore = useNegotiationContactsStore();
  const { formStateContact, formRules, showModal, isEditModal } =
    storeToRefs(negotiationContactsStore);
  const { setShowModal, resetFormStateNegotiation, setIsEditModal } = negotiationContactsStore;

  const { resetFields, validate, validateInfos } = useForm(formStateContact, formRules);

  const state = reactive({
    resources: [],
    statesResources: [],
    isLoading: false,
    isLoadingForm: false,
    dataSource: [],
    pagination: {
      current: 1,
      pageSize: 10,
      total: 0,
      showSizeChanger: false,
    } as PaginationInterface,
  });

  const handleOk = () => {
    validate()
      .then(async (attributes) => {
        await storeApiContact(attributes);
      })
      .catch((err) => {
        console.warn('Form incomplete 😕', err);
      });
  };

  const handleCancel = () => {
    resetFormStateNegotiation();
    resetFields();
    setShowModal(false);
  };

  const handleOnChange = async (page: number, perSize: number) => {
    await fetchApiContact(page, perSize);
  };

  const handleVisibleModal = () => {
    resetFormStateNegotiation();
    resetFields();
    setIsEditModal(false);
    setShowModal(true);
  };

  const fetchResourceContactData = async () => {
    try {
      const response = await supplierApi.get('supplier/resources', {
        params: {
          'keys[]': ['typeContact', 'departments'],
        },
      });

      state.resources = response.data.data;
    } catch (error) {
      console.error('Error fetching resource contact data:', error);
    }
  };

  const fetchStatesData = async () => {
    try {
      const response = await supportApi.get('support/resources', {
        params: {
          'keys[]': ['states'],
          country_id: 89,
        },
      });

      state.statesResources = response.data.data.states;
    } catch (error) {
      console.error('Error fetching states data:', error);
    }
  };

  const fetchApiContact = async (page: number = 1, pageSize: number = 10) => {
    try {
      state.isLoading = true;
      const response = await supplierApi.get(`supplier-contacts/${getRouteSupplierId()}`, {
        params: {
          page: page,
          per_page: pageSize,
        },
      });

      state.dataSource = response.data.data;
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

  const storeApiContact = async (attributes: any) => {
    try {
      state.isLoadingForm = true;
      await supplierApi.post(`supplier-contact`, attributes).then(() => {
        state.isLoadingForm = false;
      });
    } catch (error) {
      state.isLoadingForm = false;
      console.error('Error store states data:', error);
    }
  };

  const deleteApiContact = async (id: number) => {
    try {
      await supplierApi.delete(`supplier-contact/${id}`).then(() => {
        fetchApiContact(state.pagination.current, state.pagination.pageSize);
      });
    } catch (error) {
      console.error('Error delete states data:', error);
    }
  };

  const editApiContact = async (id: number, record: any) => {
    try {
      setIsEditModal(true);
      setShowModal(true);

      const { firstname, typeContact, department, supplierBranchOffice, phone, email } = record;
      Object.assign(formStateContact.value, {
        firstname,
        type_contact_id: typeContact.id,
        department_id: department.id,
        supplier_branch_office_id: supplierBranchOffice.id,
        phone,
        email,
      });

      state.isLoadingForm = true;
      await supplierApi.put(`supplier-contact/${id}`, {
        firstname,
        type_contact_id: typeContact.id,
        department_id: department.id,
        supplier_branch_office_id: supplierBranchOffice.id,
        phone,
        email,
      });

      await fetchApiContact(state.pagination.current, state.pagination.pageSize);
      state.isLoadingForm = false;
    } catch (error) {
      state.isLoadingForm = false;
      console.error('Error editing contact data:', error);
    }
  };

  onMounted(async () => {
    await Promise.all([fetchResourceContactData(), fetchStatesData(), fetchApiContact()]);
  });

  return {
    formStateContact,
    handleOk,
    handleCancel,
    handleVisibleModal,
    handleOnChange,
    resetFormStateNegotiation,
    validateInfos,
    state,
    showModal,
    setShowModal,
    deleteApiContact,
    editApiContact,
    isEditModal,
  };
}
