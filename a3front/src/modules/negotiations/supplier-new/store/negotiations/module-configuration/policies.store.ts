import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useSupplierPoliciesService } from '@/modules/negotiations/supplier-new/service/supplier-policies.service';
import type { FormInstance } from 'ant-design-vue';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';

export const usePoliciesStore = defineStore('policiesStore', () => {
  const initialFormData = ref<any>({
    id: undefined,
    sub_classification_supplier_id: undefined,
    name: null,
    date_from: null,
    date_to: null,
    pax_min: null,
    pax_max: null,
    business_group_id: undefined,
    assignments: [],
    rules: [
      {
        id: null,
        type_penalty_id: undefined,
        unit_duration_id: undefined,
        min_num: null,
        max_num: null,
        penalty: null,
        charged_taxes: false,
        charged_services: false,
      },
    ],
  });
  const formState = ref<any>({ ...initialFormData.value });
  const formRef = ref<FormInstance | null>(null);
  const columns = ref<any>([
    {
      title: 'Politicas',
      dataIndex: 'policy',
      sorter: true,
      width: '20%',
    },
    {
      title: 'Nombre',
      dataIndex: 'name',
      sorter: true,
      width: '20%',
    },
    {
      title: 'Pax',
      dataIndex: 'pax',
      sorter: true,
    },
    {
      title: 'Periodo',
      dataIndex: 'period',
      sorter: true,
    },
    {
      title: 'Estado',
      dataIndex: 'status',
      sorter: true,
      width: '10%',
    },
    {
      title: 'Acciones',
      dataIndex: 'actions',
      width: '10%',
      align: 'center',
    },
  ]);
  const currentPage = ref<number>(1);
  const pageSize = ref<number>(10);
  const total = ref<number>(0);
  const sourceData = ref<any>([]);
  const businessGroup = ref<any>([]);
  const typePenalty = ref<any>([]);
  const unitDuration = ref<any>([]);
  const market = ref<any>([]);
  const client = ref<any>([]);
  const calendar = ref<any>([]);
  const loading = ref<boolean>(false);
  const searchQuery = ref<string>('');
  const showForm = ref<boolean>(false);
  const isLoadingForm = ref<boolean>(false);
  const loadingButton = ref<boolean>(false);
  const disabledButton = ref<boolean>(false);

  const {
    storeSupplierPolicies,
    updateSupplierPolicies,
    getSupplierPolicies,
    deleteSupplierPolicies,
    updateSupplierPoliciesStatus,
    showSupplierPolicies,
  } = useSupplierPoliciesService;

  const { fetchPoliciesResource } = useSupplierResourceService;

  return {
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    loading,
    searchQuery,
    showForm,
    isLoadingForm,
    initialFormData,
    formState,
    formRef,
    businessGroup,
    typePenalty,
    unitDuration,
    market,
    client,
    calendar,
    loadingButton,
    disabledButton,
    storeSupplierPolicies,
    updateSupplierPolicies,
    showSupplierPolicies,
    getSupplierPolicies,
    deleteSupplierPolicies,
    updateSupplierPoliciesStatus,
    fetchPoliciesResource,
  };
});
