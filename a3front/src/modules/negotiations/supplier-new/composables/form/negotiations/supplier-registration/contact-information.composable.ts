import type { Rule, RuleObject } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierGlobalStore } from '@/modules/negotiations/supplier-new/store/supplier-global.store';
import { handleCompleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';

import { useContactInformationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/contact-information.store';
import type {
  ListItemGlobal,
  ContactInformationSummary,
  ContactForm,
  SupplierContactResponse,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import type { ListItemGlobalValue } from '@/modules/negotiations/supplier-new/types/supplier-registration';

import { useSupplierContactInformationService } from '@/modules/negotiations/supplier-new/service/supplier-contact-information.service';
import { Country } from '@/modules/negotiations/supplier-new/enums/countries.enum';
import type { SelectOption } from '@/modules/negotiations/supplier/interfaces';
import {
  filterOption,
  mapItemsToOptions,
} from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';

import { useSupplierCompleteData } from '@/modules/negotiations/supplier-new/composables/form/negotiations/use-supplier-complete-data.composable';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';

export function useContactInformationComposable() {
  const isLoading = ref(false);
  const isLoadingButton = ref(false);
  const isInitialLoading = ref(false);
  const resourcesLoaded = ref(false);
  const contactStore = useContactInformationStore();

  const touchedFields = ref({
    countryPhoneCode: false,
    statePhoneCode: false,
    phone: false,
  });

  const typeContacts = ref<SelectOption[]>([]);
  const states = ref<SelectOption[]>([]);
  const typePhone = ref<number>(1);
  const phoneCodeOptions = ref<any[]>([]);
  const statePhoneCodeOptions = ref<any[]>([]);
  const persistedSnapshot = ref<any | null>(null);

  const showAllContacts = ref<boolean>(false);

  const clone = <T>(v: T): T => JSON.parse(JSON.stringify(v));

  const {
    supplierId,
    isEditMode,
    getShowFormComponent,
    getIsEditFormComponent,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    handleSavedFormSpecific,
    markItemComplete,
  } = useSupplierGlobalComposable();

  const { selectedCountryName } = storeToRefs(useSupplierGlobalStore());

  const {
    fetchResources,
    // ❌ ELIMINADO: fetchPhoneCountryResources y fetchPhoneStateResources ya no se usan - datos vienen de completeData
    updateSupplierContact,
  } = useSupplierContactInformationService;

  const contactInformationStore = useContactInformationStore();
  const { formRef } = storeToRefs(contactInformationStore);
  const { formState, resetFormState, addContact, editContact, removeContact } =
    contactInformationStore;

  const { completeData, refetch: refetchCompleteData } = useSupplierCompleteData();
  const { loadSupplierModules } = useSupplierModulesComposable();

  const optionsTypePhone = [
    { label: 'Fijo', value: 1 },
    { label: 'Celular', value: 2 },
  ];

  const columns = [
    { title: 'Tipo de contacto', dataIndex: 'typeContactName', width: '20%' },
    { title: 'Nombre', dataIndex: 'fullName', width: '20%' },
    { title: 'Teléfono', dataIndex: 'phone', width: '15%' },
    { title: 'Correo', dataIndex: 'email', width: '15%' },
    { title: 'Ciudad', dataIndex: 'stateName', width: '15%' },
    { title: 'Acciones', dataIndex: 'actions', align: 'center' as const, width: '15%' },
  ];

  const formRules = {
    web: [
      {
        validator: (_: RuleObject, value: string) => {
          if (!value) {
            return Promise.resolve();
          }
          const regex = /^(https?:\/\/|www\.)[^\s$.?#].[^\s]*$/i;
          if (regex.test(value)) {
            return Promise.resolve();
          }
          return Promise.reject('Ingrese una URL válida (ej: https://..., http://... o www...)');
        },
        trigger: ['blur', 'change'],
      },
    ],
    email: [
      { required: true, message: 'El correo es requerido', trigger: 'blur' },
      { type: 'email', message: 'Ingrese un correo válido', trigger: ['blur', 'change'] },
    ],
  };

  const contactsFormRules = computed<Record<string, Rule[]>>(() => ({
    typeContactId: [],
    stateId: [],
    fullName: [],
    phone: [],
    email: [],
  }));

  const isFormValid = computed(() => {
    const hasValidWeb =
      !formState.web?.trim() || /^(https?:\/\/|www\.)[^\s$.?#].[^\s]*$/i.test(formState.web);

    const hasValidEmail =
      !!formState.email?.trim() && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formState.email);

    const hasRequiredCountry = !!formState.countryPhoneCode;
    const hasRequiredPrefix = typePhone.value !== 1 || !!formState.statePhoneCode;
    const hasRequiredPhone = !!formState.phone?.trim();
    const hasValidPhone = !formState.phone || /^[0-9]+$/.test(formState.phone);

    let phoneValidationsPass = false;

    if (typePhone.value === 1) {
      phoneValidationsPass =
        hasRequiredCountry && hasRequiredPrefix && hasRequiredPhone && hasValidPhone;
    } else {
      phoneValidationsPass = hasRequiredCountry && hasRequiredPhone && hasValidPhone;
    }

    return hasValidWeb && hasValidEmail && phoneValidationsPass;
  });

  const shouldShowPhoneValidation = computed(() => {
    return getIsEditFormComponent.value(FormComponentEnum.CONTACT_INFORMATION);
  });

  const spinTip = computed(() => (isLoadingButton.value ? 'Guardando...' : 'Cargando...'));
  const spinning = computed(
    () => isLoading.value || isLoadingButton.value || isInitialLoading.value
  );

  const toRequest = () => {
    const contacts = formState.contacts
      .filter((c) =>
        ['typeContactId', 'stateId', 'fullName', 'phone', 'email'].some((k) => (c as any)[k])
      )
      .map((row) => ({
        id: row.id,
        type_contact_id: row.typeContactId,
        state_id: row.stateId,
        full_name: row.fullName,
        phone: row.phone,
        email: row.email,
      }));

    return {
      web: formState.web,
      country_phone_code: formState.countryPhoneCode,
      state_phone_code: formState.statePhoneCode,
      phone: formState.phone,
      email: formState.email,
      contacts,
    };
  };

  const hasSignificantData = computed(() => {
    return !!(
      formState.web?.trim() ||
      formState.phone?.trim() ||
      formState.email?.trim() ||
      (formState.contacts &&
        formState.contacts.length > 0 &&
        formState.contacts.some(
          (c) => c.fullName || c.phone || c.email || c.typeContactId || c.stateId
        ))
    );
  });

  const hasRealContacts = computed(() => {
    return (
      formState.contacts &&
      formState.contacts.length > 0 &&
      formState.contacts.some(
        (c) =>
          c.id ||
          c.fullName?.trim() ||
          c.phone?.trim() ||
          c.email?.trim() ||
          c.typeContactId ||
          c.stateId
      )
    );
  });

  const totalContacts = computed(() => {
    return formState.contacts
      ? formState.contacts.filter(
          (c) =>
            c.id ||
            c.fullName?.trim() ||
            c.phone?.trim() ||
            c.email?.trim() ||
            c.typeContactId ||
            c.stateId
        ).length
      : 0;
  });

  const ensureEmptyRow = () => {
    const showForm = getShowFormComponent.value(FormComponentEnum.CONTACT_INFORMATION);
    const isEditMode = getIsEditFormComponent.value(FormComponentEnum.CONTACT_INFORMATION);

    if (showForm || isEditMode) {
      const hasEmptyRow = formState.contacts.some(
        (c) => !c.id && !c.typeContactId && !c.fullName && !c.phone && !c.email && !c.stateId
      );

      if (!hasEmptyRow) {
        addContact();
      }
    }
  };

  watch(
    [
      () => getShowFormComponent.value(FormComponentEnum.CONTACT_INFORMATION),
      () => getIsEditFormComponent.value(FormComponentEnum.CONTACT_INFORMATION),
    ],
    () => {
      nextTick(() => {
        ensureEmptyRow();
      });
    },
    { immediate: true }
  );

  const hydrateForm = async (data: SupplierContactResponse) => {
    const hasDataFromServer = !!(
      data.web?.trim() ||
      data.phone?.trim() ||
      data.email?.trim() ||
      (data.contacts && data.contacts.length > 0)
    );

    if (!hasDataFromServer) {
      resetFormState();
      persistedSnapshot.value = null;

      touchedFields.value = {
        countryPhoneCode: false,
        statePhoneCode: false,
        phone: false,
      };

      handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      return;
    }

    formState.countryPhoneCode =
      data.country_phone_code ?? (data as any).phone_code ?? formState.countryPhoneCode ?? '51';

    if (formState.countryPhoneCode) {
      // ✅ Usar datos de completeData en lugar de llamada individual
      if (completeData.value?.data?.data?.resources?.state_phone) {
        statePhoneCodeOptions.value = (
          completeData.value.data.data.resources.state_phone || []
        ).map((s: any) => ({
          ...s,
          phone_code: String(s.phone_code),
        }));
      } else {
        statePhoneCodeOptions.value = [];
      }
    }

    formState.statePhoneCode = data.state_phone_code ?? null;
    formState.web = data.web;
    formState.phone = data.phone;
    formState.email = data.email;

    if (data.state_phone_code) {
      typePhone.value = 1;
    } else {
      typePhone.value = 2;
    }

    formState.contacts = Array.isArray(data.contacts)
      ? data.contacts.map(
          (row): ContactForm => ({
            id: row.id,
            typeContactId: row.type_contact?.id,
            typeContactName: row.type_contact?.name,
            stateId: row.state?.id,
            stateName: row.state?.name,
            fullName: row.full_name,
            phone: row.phone,
            email: row.email,
            isEditMode: false,
          })
        )
      : [];

    handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
    handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
    handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);

    await nextTick();
    markItemComplete('contacts');
    persistedSnapshot.value = {
      ...clone(formState),
      typePhone: typePhone.value,
    };
  };

  const autoSelectCountryFromGlobalState = (forceUpdate = false) => {
    if (!selectedCountryName.value || !phoneCodeOptions.value.length) {
      return;
    }

    const matchingCountry = phoneCodeOptions.value.find((country: any) => {
      if (!country.name) return false;

      const countryName = country.name.toLowerCase().trim();
      const globalCountryName = selectedCountryName.value!.toLowerCase().trim();

      return countryName === globalCountryName || countryName.includes(globalCountryName);
    });

    if (matchingCountry) {
      const newPhoneCode = String(matchingCountry.phone_code);

      if (forceUpdate || !formState.countryPhoneCode) {
        formState.countryPhoneCode = newPhoneCode;
      }
    }
  };

  const loadData = async () => {
    if (!supplierId.value) {
      resetFormState();
      persistedSnapshot.value = null;

      touchedFields.value = {
        countryPhoneCode: false,
        statePhoneCode: false,
        phone: false,
      };

      handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      isInitialLoading.value = false;
      return;
    }

    try {
      if (completeData.value?.data?.data?.contacts) {
        await hydrateForm(completeData.value.data.data.contacts);
      } else {
        resetFormState();
        persistedSnapshot.value = null;

        touchedFields.value = {
          countryPhoneCode: false,
          statePhoneCode: false,
          phone: false,
        };

        handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
        handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
        handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      }
    } catch (err) {
      handleError(err as Error);
      resetFormState();
      persistedSnapshot.value = null;

      touchedFields.value = {
        countryPhoneCode: false,
        statePhoneCode: false,
        phone: false,
      };

      handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
    }
    isInitialLoading.value = false;
  };

  const fetchAndHydrateContact = async (id: number | null) => {
    if (!id) return;
    await loadData();
  };

  const handleClose = () => {
    if (persistedSnapshot.value) {
      const { typePhone: savedTypePhone, ...savedFormState } = persistedSnapshot.value;
      const clonedState = clone(savedFormState);
      Object.keys(formState).forEach((key) => {
        if (clonedState.hasOwnProperty(key)) {
          (formState as any)[key] = clonedState[key];
        }
      });
      if (savedTypePhone !== undefined) {
        typePhone.value = savedTypePhone;
      }
      formRef.value?.clearValidate?.();
      handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
      return;
    }
    resetFormState();

    touchedFields.value = {
      countryPhoneCode: false,
      statePhoneCode: false,
      phone: false,
    };

    handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
    handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
  };

  const handleSaveForm = async () => {
    if (!supplierId.value) return;
    try {
      isLoadingButton.value = true;

      const response = await updateSupplierContact(supplierId.value, toRequest());
      if (response?.success) {
        handleCompleteResponse(response);

        // ✅ IMPORTANTE: Refrescar la query unificada para obtener los datos actualizados
        await refetchCompleteData();

        await fetchAndHydrateContact(supplierId.value);

        // ✅ REINTEGRADO: loadSupplierModules() es NECESARIO para sincronizar el estado
        // del sidebar con los datos reales del backend (supplier/modules)
        // Sin esto, el icono de completado y la barra de progreso NO se actualizan
        await loadSupplierModules();

        handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
        handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
        handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
        await nextTick();
        persistedSnapshot.value = {
          ...clone(formState),
          typePhone: typePhone.value,
        };
      }
    } catch (error: any) {
      handleError(error);
    } finally {
      isLoadingButton.value = false;
    }
  };

  const handleSave = async () => {
    formRef.value?.clearValidate?.();
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch {}
  };

  const handleShowForm = () => {
    if (!persistedSnapshot.value) {
      resetFormState();

      touchedFields.value = {
        countryPhoneCode: false,
        statePhoneCode: false,
        phone: false,
      };

      handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
      handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);

      autoSelectCountryFromGlobalState(false);

      nextTick(() => {
        ensureEmptyRow();
      });
    } else {
      persistedSnapshot.value = {
        ...clone(formState),
        typePhone: typePhone.value,
      };
      handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);

      nextTick(() => {
        ensureEmptyRow();
      });
    }
  };

  watch(
    () => formState.countryPhoneCode,
    async (newValue, oldValue) => {
      if (newValue === oldValue) return;
      const country = phoneCodeOptions.value.find((c: any) => c.phone_code === newValue);
      if (!country) {
        statePhoneCodeOptions.value = [];
        formState.statePhoneCode = null;
        return;
      }
      statePhoneCodeOptions.value = [];
      formState.statePhoneCode = null;

      // ✅ Usar datos de completeData en lugar de llamada individual
      if (completeData.value?.data?.data?.resources?.state_phone) {
        statePhoneCodeOptions.value = completeData.value.data.data.resources.state_phone || [];
      } else {
        statePhoneCodeOptions.value = [];
      }
    }
  );

  watch(
    selectedCountryName,
    (newCountryName, oldCountryName) => {
      if (newCountryName !== oldCountryName && phoneCodeOptions.value.length > 0) {
        autoSelectCountryFromGlobalState(true);
      }
    },
    { immediate: false }
  );

  watch(
    phoneCodeOptions,
    () => {
      if (selectedCountryName.value && !formState.countryPhoneCode) {
        autoSelectCountryFromGlobalState(false);
      }
    },
    { immediate: false }
  );

  watch(
    supplierId,
    async (newId, oldId) => {
      resetFormState();
      persistedSnapshot.value = null;

      touchedFields.value = {
        countryPhoneCode: false,
        statePhoneCode: false,
        phone: false,
      };

      handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);

      // Reset resourcesLoaded cuando cambia el supplierId
      resourcesLoaded.value = false;

      if (!newId) {
        isInitialLoading.value = false;
        return;
      }
      if (newId === oldId) return;

      // Los recursos se cargarán automáticamente desde el watcher de completeData
      try {
        await loadData();
      } catch (error) {
        console.error('Error al cargar datos:', error);
        isInitialLoading.value = false;
      }
    },
    { immediate: false }
  );

  watch(
    () => completeData.value,
    async (newData) => {
      // Si no hay supplierId (nuevo registro) y no hay newData, no hacer nada aquí
      if (!supplierId.value && !newData) {
        return;
      }

      // Si hay supplierId pero no hay newData, esperar a que se cargue
      if (supplierId.value && !newData) {
        return;
      }

      if (newData?.data?.data?.resources) {
        // Solo cargar recursos si aún no se han cargado
        if (!resourcesLoaded.value) {
          if (
            newData.data.data.resources.contacts_states &&
            newData.data.data.resources.contacts_states !== null
          ) {
            const contactsStates = newData.data.data.resources.contacts_states;
            typeContacts.value = mapItemsToOptions(contactsStates.typeContacts || []);
            states.value = mapItemsToOptions(contactsStates.states || []);
            resourcesLoaded.value = true;
          } else if (supplierId.value) {
            // Fallback: Si contacts_states es null, hacer la llamada individual
            try {
              const response = await fetchResources(Country.PERU, supplierId.value);
              typeContacts.value = mapItemsToOptions(response.typeContacts || []);
              states.value = mapItemsToOptions(response.states || []);
              resourcesLoaded.value = true;
            } catch (error) {
              console.error('[ContactInfo] Error loading resources fallback', error);
            }
          }
        }

        if (newData.data.data.resources.countries_phone?.countries_phone) {
          phoneCodeOptions.value = newData.data.data.resources.countries_phone.countries_phone;
        }

        autoSelectCountryFromGlobalState(false);
      }

      if (supplierId.value && newData?.data?.data?.contacts) {
        await hydrateForm(newData.data.data.contacts);
      }

      isInitialLoading.value = false;
    },
    { immediate: true, deep: true }
  );

  const initializeContactInformation = async () => {
    resetFormState();
    persistedSnapshot.value = null;

    touchedFields.value = {
      countryPhoneCode: false,
      statePhoneCode: false,
      phone: false,
    };

    handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
    handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
    handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, false);

    // Los recursos se cargarán automáticamente desde el watcher de completeData
    if (supplierId.value) {
      try {
        await loadData();
      } catch (error) {
        console.error('Error al inicializar información de contacto:', error);
        isInitialLoading.value = false;
      }
    } else {
      isInitialLoading.value = false;
    }
  };

  nextTick(() => {
    initializeContactInformation();
  });

  watch(typePhone, () => {
    if (typePhone.value === 2) {
      formState.statePhoneCode = null;
    }

    touchedFields.value.statePhoneCode = false;

    nextTick(() => {
      formRef.value?.validateFields(['statePhoneCode']).then(() => {
        formRef.value?.validateFields(['phone']);
      });
    });
  });

  const isNewRecord = computed(() => !supplierId);

  onMounted(() => {
    if (isNewRecord.value) {
      contactStore.resetFormState(true);
    } else {
      contactStore.resetFormState();
    }
  });

  const contactInformationSummary = computed<ListItemGlobal[]>(() => {
    const summaryFields: ContactInformationSummary[] = [
      { key: 'web', label: 'Web:' },
      { key: 'phone', label: 'Teléfono del establecimiento:' },
      { key: 'email', label: 'Correo electrónico:' },
    ];

    return summaryFields
      .filter(({ key }) => {
        const rawValue = (formState as any)[key];
        return rawValue && rawValue.toString().trim() !== '';
      })
      .map(({ key, label, format }) => {
        const rawValue = (formState as any)[key];
        const value = format ? format(rawValue) : rawValue;
        return { title: label, value: value as ListItemGlobalValue };
      });
  });

  const statePhoneCodeFormattedOptions = computed(() => {
    const baseOptions = statePhoneCodeOptions.value.map((state: any) => ({
      value: String(state.phone_code),
      label: `${state.iso} +${state.phone_code}`,
      iso: state.iso,
    }));

    if (
      formState.statePhoneCode &&
      !baseOptions.some((option) => option.value === formState.statePhoneCode) &&
      /^[0-9]+$/.test(formState.statePhoneCode)
    ) {
      baseOptions.unshift({
        value: formState.statePhoneCode,
        label: formState.statePhoneCode,
        iso: 'CUSTOM',
      });
    }

    return baseOptions;
  });

  const handleStatePhoneCodeChange = (value: string) => {
    if (!value || value.trim() === '') {
      formState.statePhoneCode = null;
      return;
    }

    if (!/^[0-9]+$/.test(value.trim())) {
      return;
    }

    formState.statePhoneCode = value.trim();
  };

  const handlePrefixSearch = (value: string) => {
    if (!value || value.trim() === '') {
      formState.statePhoneCode = null;
      return;
    }

    if (!/^[0-9]+$/.test(value.trim())) {
      return;
    }

    formState.statePhoneCode = value.trim();
  };

  const handlePrefixBlur = () => {
    touchedFields.value.statePhoneCode = true;

    const currentValue = formState.statePhoneCode;
    if (currentValue && /^[0-9]+$/.test(currentValue)) {
      nextTick(() => {
        if (formState.statePhoneCode !== currentValue) {
          formState.statePhoneCode = currentValue;
        }
      });
    }
  };

  const handlePrefixKeyDown = (event: KeyboardEvent) => {
    const allowedKeys = [
      'Backspace',
      'Delete',
      'Tab',
      'Escape',
      'Enter',
      'ArrowLeft',
      'ArrowRight',
      'ArrowUp',
      'ArrowDown',
    ];

    if (allowedKeys.includes(event.key)) {
      return;
    }

    if (!/^[0-9]$/.test(event.key)) {
      event.preventDefault();
    }
  };

  function filterOptionPhone(input: string, option: any) {
    const country = phoneCodeOptions.value[option.key];
    if (!country) return false;
    return (
      String(country.phone_code).toLowerCase().includes(input.toLowerCase()) ||
      (country.name && country.name.toLowerCase().includes(input.toLowerCase()))
    );
  }

  function filterOptionStatePhone(input: string, option: any) {
    const state = statePhoneCodeOptions.value.find((_: any, index: number) => index === option.key);
    if (!state) return false;
    return (
      (state.phone_code && String(state.phone_code).toLowerCase().includes(input.toLowerCase())) ||
      (state.iso && state.iso.toLowerCase().includes(input.toLowerCase())) ||
      (state.name && state.name.toLowerCase().includes(input.toLowerCase()))
    );
  }

  return {
    isLoading,
    isLoadingButton,
    isInitialLoading,
    spinTip,
    spinning,
    formState,
    formRef,
    formRules,
    isFormValid,
    contactsFormRules,
    filterOption,
    phoneCodeOptions,
    statePhoneCodeOptions,
    statePhoneCodeFormattedOptions,
    columns,
    typeContacts,
    states,
    optionsTypePhone,
    typePhone,
    contactInformationSummary,
    getShowFormComponent,
    getIsEditFormComponent,
    hasSignificantData,
    hasRealContacts,
    totalContacts,
    showAllContacts,
    isEditMode,
    handleClose,
    handleSave,
    handleShowForm,
    addContact,
    editContact,
    removeContact,
    filterOptionPhone,
    filterOptionStatePhone,
    handleStatePhoneCodeChange,
    handlePrefixSearch,
    handlePrefixBlur,
    handlePrefixKeyDown,
    touchedFields,
    shouldShowPhoneValidation,
  };
}
