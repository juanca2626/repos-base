import { Form } from 'ant-design-vue';
import { storeToRefs } from 'pinia';
import { useSupplierBankInformationTreasuryStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useSupplierBankInformationTreasuryStore';
import { onMounted, reactive, watch, watchEffect } from 'vue';
import { supplierApi, supportApi } from '@/modules/negotiations/api/negotiationsApi';
import useNotification from '@/quotes/composables/useNotification';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';

export function useSupplierBankInformationTreasury() {
  const useForm = Form.useForm;

  const { showErrorNotification, showSuccessNotification, showWarningNotification } =
    useNotification();

  const state = reactive({
    typeBankAccount: [],
    bank: [],
    typeDocument: [],
    countries: [],
    states: [],
    idForm: undefined,
    statesLoading: false,
    statesDisabled: true,
    countryLoading: false,
    countryDisabled: true,
  });

  const supplierBankInformationTreasuryStore = useSupplierBankInformationTreasuryStore();

  const { formBankInformationTreasury, rules, isLoadingForm, emails, isLoadingButton } =
    storeToRefs(supplierBankInformationTreasuryStore);

  const {
    setIsLoadingForm,
    resetFormBankInformationTreasury,
    setAddEmails,
    setRemoveEmails,
    setIsLoadingButton,
  } = supplierBankInformationTreasuryStore;

  const { resetFields, validate, validateInfos } = useForm(formBankInformationTreasury, rules);

  const { formStateNegotiation, configSubClassification } = useSupplierFormStoreFacade();

  const handleOk = () => {
    validate()
      .then(async (attributes) => {
        attributes.emails = emails.value.length === 0 ? [] : [...emails.value];
        if (state.idForm) {
          await editApiBankInformation(state.idForm, attributes);
        } else {
          await storeApiBankInformation(attributes);
        }
      })
      .catch((error) => {
        console.error('Error store supplier bank information treasury data:', error);
      });
  };

  const handleCancel = () => {
    resetFields();
    resetFormBankInformationTreasury();

    state.statesLoading = false;
    state.statesDisabled = false;
  };

  const getSubClassificationSupplierId = () => {
    return formStateNegotiation.classifications.find(
      (item: any) => item.supplier_sub_classification_id === configSubClassification.value
    )?.operations?.[0]?.sub_classification_supplier_id;
  };

  const setFormCreateOrUpdate = (attributes: any) => {
    return {
      sub_classification_supplier_id: getSubClassificationSupplierId(),
      bank_id: attributes.bank_id,
      branch_office: attributes.branch_office,
      address: attributes.address,
      country_id: attributes.country_id,
      state_id: attributes.state_id,
      number_aba: attributes.number_aba,
      accounts: [
        {
          type_bank_accounts_id: attributes.accounts_national_type_bank_accounts_id,
          type: 'NATIONAL',
          bank_account_number: attributes.accounts_national_bank_account_number,
        },
        {
          type_bank_accounts_id: attributes.accounts_foreign_type_bank_accounts_id,
          type: 'FOREIGN',
          bank_account_number: attributes.accounts_foreign_bank_account_number,
        },
      ],
      type_document_id: attributes.type_document_id,
      document_number: attributes.document_number,
      deduction_percentage: attributes.deduction_percentage,
      national_bank_account: attributes.national_bank_account,
      main_name: attributes.main_name,
      main_email: attributes.main_email,
      emails: attributes.emails,
    };
  };

  const setFormShow = (attributes: any) => {
    Object.assign(formBankInformationTreasury.value, {
      sub_classification_supplier_id: attributes.sub_classification_supplier_id ?? undefined,
      bank_id: attributes.bank?.id ?? undefined,
      branch_office: attributes.branch_office ?? undefined,
      address: attributes.address ?? undefined,
      country_id: attributes.country?.id ?? undefined,
      state_id: attributes.state?.id ?? undefined,
      number_aba: attributes.number_aba ?? undefined,
      accounts_national_type_bank_accounts_id:
        attributes.bank_accounts?.[0]?.type_bank_account?.id ?? undefined,
      accounts_national_bank_account_number:
        attributes.bank_accounts?.[0]?.bank_account_number ?? undefined,
      accounts_foreign_type_bank_accounts_id:
        attributes.bank_accounts?.[1]?.type_bank_account?.id ?? undefined,
      accounts_foreign_bank_account_number:
        attributes.bank_accounts?.[1]?.bank_account_number ?? undefined,
      type_document_id: attributes.type_document?.id ?? undefined,
      document_number: attributes.document_number ?? undefined,
      deduction_percentage: attributes.deduction_percentage ?? undefined,
      national_bank_account: attributes.national_bank_account ?? undefined,
      main_name: attributes.main_name ?? undefined,
      main_email: attributes.main_email ?? undefined,
      emails: attributes.beneficiaryEmails ?? undefined,
    });
  };

  const fetchResourcesData = async () => {
    try {
      const response = await supplierApi.get('supplier/bank-information/resources', {
        params: {
          'keys[]': ['typeBankAccount', 'bank', 'typeDocument'],
        },
      });

      const { typeBankAccount, bank, typeDocument } = response.data.data;

      Object.assign(state, { typeBankAccount, bank, typeDocument });
    } catch (error) {
      console.error('Error fetching resource data:', error);
    }
  };

  const fetchCountriesData = async (countryId?: string) => {
    try {
      state.statesDisabled = true;

      let params: any = {};
      if (!countryId) {
        state.countryDisabled = true;
        state.countryLoading = true;
        params = {
          'keys[]': ['countries'],
        };
      } else {
        params['keys[]'] = ['states'];
        params.country_id = countryId;
        state.statesLoading = true;
      }

      const { data } = await supportApi.get('support/resources', { params });

      const { countries, states } = data.data;

      if (!state.countries.length) {
        state.countries = countries;
      }
      state.states = states;

      if (countryId && data.success) {
        state.statesLoading = false;
        state.statesDisabled = false;
      }

      if (!countryId) {
        state.countryDisabled = false;
        state.countryLoading = false;
      }

      Object.assign(formBankInformationTreasury.value, {
        state_id: null,
      });
    } catch (error) {
      console.error('Error fetching resource data support:', error);
    }
  };

  const storeApiBankInformation = async (attributes: any) => {
    try {
      const form = setFormCreateOrUpdate(attributes);

      setIsLoadingButton(true);
      await supplierApi.post(`supplier/bank-information`, form).then(() => {
        setTimeout(() => {
          setIsLoadingButton(false);
        }, 3000);
        showSuccessNotification('La información bancaria se ha creado satisfactoriamente.');
      });
    } catch (error) {
      setIsLoadingButton(false);
      showErrorNotification('Error al guardar la información bancaria.');
      console.error('Error store bank information data:', error);
    }
  };

  const showApiBankInformation = async (): Promise<any> => {
    try {
      setIsLoadingForm(true);
      const idSubClassificationSupplier: number = getSubClassificationSupplierId();

      const { data } = await supplierApi.post(`supplier/bank-information-show`, {
        sub_classification_supplier_id: idSubClassificationSupplier,
      });

      const attributes = data.data;
      state.idForm = attributes?.id || undefined;
      if (attributes?.id) {
        setFormShow(attributes);

        setTimeout(() => {
          setIsLoadingForm(false);
        }, 500);
      } else {
        state.idForm = undefined;
        setIsLoadingForm(false);
      }
    } catch (error) {
      setIsLoadingForm(false);
      console.error('Error show bank information data:', error);
    }
  };

  const editApiBankInformation = async (id: any, attributes: any) => {
    try {
      setIsLoadingButton(true);
      const form = setFormCreateOrUpdate(attributes);
      await supplierApi
        .put(`supplier/bank-information/${id}`, form)
        .then(async () => {
          setTimeout(() => {
            setIsLoadingButton(false);
          }, 3000);
          showSuccessNotification('La información bancaria se ha actualizado satisfactoriamente.');
        })
        .catch(() => {
          setIsLoadingButton(false);
          showWarningNotification('Error al guardar la información bancaria.');
        });
    } catch (error) {
      showErrorNotification('Error al guardar la información bancaria.');
      setIsLoadingButton(false);
      console.error('Error editing tributary information data:', error);
    }
  };

  const changeSubClassification = async (): Promise<void> => {
    try {
      setIsLoadingForm(true);
      const idSubClassificationSupplier: number = getSubClassificationSupplierId();

      const { data } = await supplierApi.post('supplier/bank-information-show', {
        sub_classification_supplier_id: idSubClassificationSupplier,
      });

      const attributes = data.data;
      state.idForm = attributes?.id || undefined;
      if (attributes.length == 0 || attributes?.id) {
        state.idForm = undefined;
        resetFields();
        setFormShow(attributes);
        setIsLoadingForm(false);
        return;
      }

      setFormShow(attributes);
      setIsLoadingForm(false);
    } catch (error) {
      console.error('Error change sub classification data:', error);
    }
  };

  onMounted(async () => {
    await Promise.all([fetchResourcesData(), fetchCountriesData(), showApiBankInformation()]);
  });

  watchEffect(async () => {
    const countryId = formBankInformationTreasury.value.country_id;
    if (countryId) {
      await fetchCountriesData(countryId);
    }
  });

  watch(
    configSubClassification,
    async (newSubClassification) => {
      if (newSubClassification !== null) {
        await changeSubClassification();
      }
    },
    { deep: true }
  );

  return {
    formBankInformationTreasury,
    validateInfos,
    rules,
    isLoadingForm,
    state,
    handleOk,
    handleCancel,
    setAddEmails,
    setRemoveEmails,
    emails,
    isLoadingButton,
  };
}
