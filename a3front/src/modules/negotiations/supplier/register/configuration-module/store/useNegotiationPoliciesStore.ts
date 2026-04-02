import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';

export const useNegotiationPoliciesStore = defineStore('negotiationPoliciesStore', () => {
  const initialFormStatePolicy: any = {
    sub_classification_supplier_id: undefined,
    name: null,
    date_from: null,
    date_to: null,
    pax_min: null,
    pax_max: null,
    business_group_id: undefined,
    assignments: [],
  };

  const initialRules = [
    {
      type_penalty_id: undefined,
      unit_duration_id: undefined,
      min_num: null,
      max_num: null,
      penalty: null,
      charged_taxes: false,
      charged_services: false,
    },
  ];

  const formStatePolicy = reactive<any>(structuredClone(initialFormStatePolicy));
  const rules = ref(structuredClone(initialRules));

  const formRulesPolicy = reactive({
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
    rules: {
      type_penalty_id: [
        { required: true, message: 'Selecciona un tipo de penalización', trigger: 'change' },
      ],
      unit_duration_id: [
        { required: true, message: 'Selecciona una unidad de duración', trigger: 'change' },
      ],
      min_num: [{ required: true, message: 'Ingresa el número mínimo', trigger: 'blur' }],
      max_num: [{ required: true, message: 'Ingresa el número máximo', trigger: 'blur' }],
      penalty: [{ required: true, message: 'Ingresa la penalización', trigger: 'blur' }],
      charged_taxes: [
        { required: true, message: 'Selecciona si se cobran impuestos', trigger: 'change' },
      ],
      charged_services: [
        { required: true, message: 'Selecciona si se cobran servicios', trigger: 'change' },
      ],
    },
    assignments: [{ required: false, message: 'Selecciona asignaciones', trigger: 'change' }],
  });

  const resetFormStatePolicy = () => {
    Object.assign(formStatePolicy, structuredClone(initialFormStatePolicy));
    rules.value = structuredClone(initialRules);
  };

  const showModalPolicy = ref<boolean>(false);
  const setShowModalPolicy = (value: boolean) => {
    showModalPolicy.value = value;
  };

  const isEditModal = ref<boolean>(false);
  const setIsEditModal = (value: boolean) => {
    isEditModal.value = value;
  };

  const isLoadingForm = ref<boolean>(true);
  const setIsLoadingForm = (value: boolean) => {
    isLoadingForm.value = value;
  };

  const setAddRule = (rule: any) => {
    rules.value.push({ ...rule });
  };

  const setRemoveRule = (index: number) => {
    rules.value.splice(index, 1);
  };

  const dataSource = ref<Array<any>>([]);
  const setDataSource = (value: any) => {
    dataSource.value = value;
  };

  const idForm = ref<number | null>(null);
  const setIdForm = (value: any) => {
    idForm.value = value;
  };

  return {
    formStatePolicy,
    rules,
    formRulesPolicy,
    showModalPolicy,
    setShowModalPolicy,
    resetFormStatePolicy,
    isEditModal,
    setIsEditModal,
    isLoadingForm,
    setIsLoadingForm,
    setAddRule,
    setRemoveRule,
    dataSource,
    setDataSource,
    idForm,
    setIdForm,
  };
});
