import { ref, reactive, computed, onMounted, watch } from 'vue';
import type { Rule, FormInstance } from 'ant-design-vue/es/form';
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import {
  useSupplierCompleteDataRegistrationQuery,
  extractPhoneCountriesForRegistration,
  extractContactInfo,
  getSupplierEditQueryKey,
  SUPPLIER_EDIT_KEYS,
} from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-complete.query';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useSupplierContactInformationService } from '@/modules/negotiations/supplier-new/service/supplier-contact-information.service';
import { Country } from '@/modules/negotiations/supplier-new/enums/countries.enum';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import { handleCompleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import { getCityPhonePrefix } from '@/modules/negotiations/supplier-new/data/cityPhonePrefixes';

// Types
interface Contact {
  id?: number;
  typeContactId?: number;
  typeContactName?: string;
  fullName: string;
  phone: string;
  email: string;
  stateId?: number;
  stateName?: string;
  isEditMode: boolean;
}

interface ContactFormState {
  web: string;
  countryPhoneCode?: string;
  statePhoneCode?: string;
  phone: string;
  email: string;
  contacts: Contact[];
}

interface ContactReadData {
  web: string;
  phone: string;
  email: string;
}

interface TouchedFields {
  countryPhoneCode: boolean;
  statePhoneCode: boolean;
  phone: boolean;
}

export function useContactComposable() {
  // Composables y servicios (deben llamarse en el nivel superior)
  const {
    supplierId,
    markItemComplete,
    isEditMode: isGlobalEditMode,
    countryId, // ID del país seleccionado en el componente de ubicación
    cityId, // ID de la ciudad seleccionada en el componente de ubicación
    cityName, // Nombre de la ciudad seleccionada
    handleSetActiveItem,
    openNextSection,
  } = useSupplierGlobalComposable();
  const { loadSupplierModules } = useSupplierModulesComposable();
  const { updateSupplierContact } = useSupplierContactInformationService;

  // Estado del modo edición local
  // En modo registro: true (mostrar formulario)
  // En modo edición: false inicialmente (mostrar lectura)
  const isEditMode = ref(!isGlobalEditMode.value);
  const isSaving = ref(false);
  const typePhone = ref(1); // 1: Fijo (por defecto), 2: Móvil
  const phoneCache = ref<Record<number, string>>({}); // Caché temporal de teléfonos por tipo
  const showAllContacts = ref(false);
  const shouldShowPhoneValidation = ref(false);

  // Form ref
  const formRef = ref<FormInstance>();

  const touchedFields = reactive<TouchedFields>({
    countryPhoneCode: false,
    statePhoneCode: false,
    phone: false,
  });

  // Form state
  const formState = reactive<ContactFormState>({
    web: '',
    countryPhoneCode: undefined,
    statePhoneCode: undefined,
    phone: '',
    email: '',
    contacts: [
      {
        typeContactId: undefined,
        typeContactName: '',
        fullName: '',
        phone: '',
        email: '',
        stateId: undefined,
        stateName: '',
        isEditMode: true,
      },
    ],
  });

  // Query para modo REGISTRO (obtener solo recursos)
  const { data: registrationData, isLoading: isLoadingRegistrationData } =
    useSupplierCompleteDataRegistrationQuery(
      {
        keys: ['countries_phone', 'state_phone'],
      },
      {
        enabled: computed(() => !isGlobalEditMode.value), // Solo en modo registro
      }
    );

  // Modo EDICIÓN: useQuery con el MISMO queryKey compartido. Sin HTTP extra, con reactividad.
  const queryClient = useQueryClient();
  const { data: supplierData, isLoading: isLoadingSupplierData } = useQuery({
    queryKey: computed(() =>
      supplierId.value
        ? getSupplierEditQueryKey(supplierId.value)
        : (['supplier', 'edit-complete', '__disabled__'] as const)
    ),
    queryFn: async () =>
      useSupplierService.showSupplierCompleteData(supplierId.value!, {
        keys: [...SUPPLIER_EDIT_KEYS],
      }),
    enabled: computed(() => !!supplierId.value && isGlobalEditMode.value),
    staleTime: 5 * 60 * 1000,
    refetchOnMount: false,
    refetchOnWindowFocus: false,
  });
  // Para forzar refetch del cache unificado tras guardar
  const refetchSupplierData = async () => {
    if (supplierId.value) {
      await queryClient.invalidateQueries({ queryKey: getSupplierEditQueryKey(supplierId.value) });
    }
  };

  // Refs para typeContacts y states (se cargan desde supplier/resources)
  const typeContactsData = ref<any[]>([]);
  const statesData = ref<any[]>([]);
  const isLoadingResources = ref(false);

  const { fetchResources, fetchStatesByCountry } = useSupplierContactInformationService;

  // Cargar typeContacts y states desde supplier/resources
  const loadContactResources = async (targetCountryId?: number | null) => {
    try {
      isLoadingResources.value = true;
      // Usar el país pasado como argumento, o el del store global, o fallback a PERU
      const countryToUse = targetCountryId ?? countryId.value ?? Country.PERU;

      // Cargar recursos generales (typeContacts) y específicos (states) en paralelo
      const [resourcesResponse, statesResponse] = await Promise.all([
        fetchResources(Country.PERU), // TypeContacts tomados de PERU por defecto (son genéricos)
        fetchStatesByCountry(countryToUse),
      ]);

      typeContactsData.value = resourcesResponse.typeContacts || [];
      statesData.value = statesResponse.data || [];
    } catch (error) {
      console.error('[ContactComposable] Error loading resources', error);
    } finally {
      isLoadingResources.value = false;
    }
  };

  // Cargar recursos al montar
  onMounted(() => {
    loadContactResources();
  });

  // Recargar recursos cuando cambia el país de ubicación
  watch(
    countryId,
    async (newCountryId) => {
      if (newCountryId) {
        await loadContactResources(newCountryId);
      }
    },
    { immediate: false }
  );

  const phoneCountriesData = computed(() => {
    if (isGlobalEditMode.value && supplierData.value) {
      return extractContactInfo(supplierData.value);
    } else if (registrationData.value) {
      return extractPhoneCountriesForRegistration(registrationData.value);
    }
    return { phoneCountries: [] };
  });

  const phoneCodeOptions = computed(() => {
    return phoneCountriesData.value.phoneCountries.map((country: any) => ({
      phone_code: country.phone_code,
      icon: country.icon,
      name: country.name,
      id: country.id,
    }));
  });

  // Cargar datos del supplier al formulario cuando están disponibles (solo en modo edición)
  watch(
    () => supplierData.value,
    (data) => {
      if (data && isGlobalEditMode.value) {
        const contactInfo = extractContactInfo(data);

        if (contactInfo.contacts) {
          // Poblar el formulario con los datos del supplier
          formState.web = contactInfo.contacts.web || '';

          // NUEVA FUNCIONALIDAD: Auto-seleccionar phone_code basado en el país de ubicación
          // Si NO hay country_phone_code guardado, intentar auto-seleccionar desde countryId
          if (!contactInfo.contacts.country_phone_code && countryId.value) {
            const matchingCountry = (contactInfo.phoneCountries || []).find(
              (country: any) => country.id === countryId.value
            );

            if (matchingCountry) {
              // Auto-seleccionar el phone_code del país de ubicación
              formState.countryPhoneCode = String(matchingCountry.phone_code);
            } else {
              formState.countryPhoneCode = undefined;
            }
          } else {
            formState.countryPhoneCode = contactInfo.contacts.country_phone_code || undefined;
          }

          formState.statePhoneCode = contactInfo.contacts.state_phone_code || undefined;
          formState.phone = contactInfo.contacts.phone || '';
          formState.email = contactInfo.contacts.email || '';

          // Poblar los contactos
          if (contactInfo.contacts.contacts && contactInfo.contacts.contacts.length > 0) {
            formState.contacts = contactInfo.contacts.contacts.map((contact: any) => ({
              id: contact.id,
              typeContactId: contact.type_contact?.id,
              typeContactName: contact.type_contact?.name,
              fullName: contact.full_name || '',
              phone: contact.phone || '',
              email: contact.email || '',
              stateId: contact.state?.id,
              stateName: contact.state?.name,
              isEditMode: false, // Modo lectura para contactos existentes
            }));
          }

          // Si no hay datos reales, mantener en modo edición para que el usuario pueda ingresar info
          const hasData = !!(
            contactInfo.contacts.email ||
            contactInfo.contacts.phone ||
            contactInfo.contacts.web
          );
          isEditMode.value = !hasData;
        }
      }
    },
    { immediate: true }
  );

  watch(
    [phoneCodeOptions, countryId],
    ([options, currentCountryId]) => {
      // Si cambia el país de Ubicación, sincronizar siempre el phone_code.
      if (!currentCountryId) {
        formState.countryPhoneCode = undefined;
        return;
      }

      if (!options || options.length === 0) {
        // Esperar a que carguen las opciones; el watcher volverá a disparar
        return;
      }

      const matchingCountry = options.find((country: any) => country.id === currentCountryId);
      formState.countryPhoneCode = matchingCountry ? String(matchingCountry.phone_code) : undefined;
    },
    { immediate: true }
  );

  // Auto-rellenar prefijo (código de área) cuando el tipo de teléfono es Fijo
  // y se conoce la ciudad del proveedor
  watch(
    [() => typePhone.value, cityName, cityId],
    ([currentTypePhone, currentCityName]) => {
      // Solo aplicar cuando el tipo es Fijo (1)
      if (currentTypePhone !== 1) return;

      const prefix = getCityPhonePrefix(currentCityName as string | null);
      if (prefix !== undefined) {
        // Solo auto-rellenar si el campo está vacío (no sobreescribir entrada manual)
        if (!formState.statePhoneCode) {
          formState.statePhoneCode = prefix;
        }
      }
    },
    { immediate: true }
  );

  // Cuando cambia el tipo de teléfono: guarda el número actual, limpia y restaura el del nuevo tipo
  watch(
    () => typePhone.value,
    (newTypePhone, oldTypePhone) => {
      // Guardar el número actual en caché para el tipo anterior
      phoneCache.value[oldTypePhone] = formState.phone;

      // Restaurar el número del nuevo tipo si existe en caché, si no limpiar
      formState.phone = phoneCache.value[newTypePhone] ?? '';

      // Auto-rellenar prefijo si el nuevo tipo es Fijo y no tiene prefijo
      if (newTypePhone === 1 && !formState.statePhoneCode) {
        const prefix = getCityPhonePrefix(cityName.value as string | null);
        if (prefix !== undefined) {
          formState.statePhoneCode = prefix;
        }
      }
    }
  );

  // Cuando la ciudad cambia, actualizar el prefijo si es Fijo (siempre actualizar al cambio de ciudad)
  watch([cityName, cityId], ([newCityName]) => {
    if (typePhone.value === 1) {
      const prefix = getCityPhonePrefix(newCityName as string | null);
      // Al cambiar la ciudad siempre actualizamos (la ciudad nueva puede tener un prefijo diferente)
      formState.statePhoneCode = prefix ?? undefined;
    }
  });

  // Extraer type contacts desde el ref cargado
  const typeContacts = computed(() => {
    return typeContactsData.value.map((type: any) => ({
      label: type.name,
      value: type.id,
    }));
  });

  // Extraer states desde el ref cargado
  const states = computed(() => {
    return statesData.value.map((state: any) => ({
      label: state.name,
      value: state.id,
    }));
  });

  // Extraer prefixes para teléfono desde state_phone
  const statePhoneCodeOptions = computed(() => {
    let rawData;
    if (isGlobalEditMode.value && supplierData.value) {
      rawData = supplierData.value?.data?.data?.resources?.state_phone;
    } else if (registrationData.value) {
      rawData = registrationData.value?.data?.data?.resources?.state_phone;
    }
    if (!rawData) return [];
    return rawData.map((prefix: any) => ({
      value: prefix.prefix,
      label: `${prefix.prefix} - ${prefix.name}`,
      name: prefix.name,
    }));
  });

  const statePhoneCodeFormattedOptions = computed(() => {
    return statePhoneCodeOptions.value;
  });

  // Opciones de tipo de teléfono
  const optionsTypePhone = [
    { label: 'Fijo', value: 1 },
    { label: 'Móvil', value: 2 },
  ];

  // Loading para mostrar overlay (durante carga inicial Y al guardar)
  const isLoading = computed(() => {
    return (
      isSaving.value ||
      isLoadingRegistrationData.value ||
      isLoadingSupplierData.value ||
      isLoadingResources.value
    );
  });

  // Loading combinado para deshabilitar campos
  const isLoadingFields = computed(() => {
    return (
      isLoadingRegistrationData.value || isLoadingSupplierData.value || isLoadingResources.value
    );
  });

  // Verificar si hay contactos reales
  const hasRealContacts = computed(() => {
    return formState.contacts.some(
      (c) =>
        c.id ||
        c.fullName?.trim() ||
        c.phone?.trim() ||
        c.email?.trim() ||
        c.typeContactId ||
        c.stateId
    );
  });

  // Total de contactos reales
  const totalContacts = computed(() => {
    return formState.contacts.filter(
      (c) =>
        c.id ||
        c.fullName?.trim() ||
        c.phone?.trim() ||
        c.email?.trim() ||
        c.typeContactId ||
        c.stateId
    ).length;
  });

  // Data para modo lectura
  const readData = computed<ContactReadData>(() => {
    const phoneDisplay = formState.phone
      ? `${formState.countryPhoneCode ? '+' + formState.countryPhoneCode : ''}${
          formState.statePhoneCode ? ' (' + formState.statePhoneCode + ')' : ''
        } ${formState.phone}`
      : '-';

    return {
      web: formState.web || '-',
      phone: phoneDisplay,
      email: formState.email || '-',
    };
  });

  // Reglas de validación
  const rules: Record<string, Rule[]> = {
    email: [
      {
        required: true,
        message: 'Por favor ingrese el correo genérico',
      },
      {
        type: 'email',
        message: 'Por favor ingrese un correo válido',
      },
    ],
  };

  // Reglas de validación para contactos en tabla
  // Todos los campos son opcionales
  const contactsFormRules = {
    typeContactId: [
      {
        required: false,
      },
    ],
    fullName: [
      {
        required: false,
      },
    ],
    phone: [
      {
        required: false,
      },
      {
        pattern: /^[0-9]+$/,
        message: 'Solo números',
      },
    ],
    email: [
      {
        required: false,
      },
      {
        type: 'email',
        message: 'Correo inválido',
      },
    ],
    stateId: [
      {
        required: false,
      },
    ],
  };

  // Columnas de la tabla
  const columns = [
    {
      title: 'Tipo de contacto',
      dataIndex: 'typeContactName',
      key: 'typeContactName',
      width: 150,
    },
    {
      title: 'Nombre',
      dataIndex: 'fullName',
      key: 'fullName',
      width: 200,
    },
    {
      title: 'Teléfono',
      dataIndex: 'phone',
      key: 'phone',
      width: 150,
    },
    {
      title: 'Correo',
      dataIndex: 'email',
      key: 'email',
      width: 200,
    },
    {
      title: 'Ciudad',
      dataIndex: 'stateName',
      key: 'stateName',
      width: 150,
    },
    {
      title: 'Acciones',
      dataIndex: 'actions',
      key: 'actions',
      width: 100,
      align: 'center' as const,
    },
  ];

  // Métodos
  const handleEditMode = () => {
    isEditMode.value = true;
    // Actualizar el ítem activo del sidebar
    handleSetActiveItem('supplier', 'supplier-negotiations', 'contacts');
  };

  const handleCancel = () => {
    isEditMode.value = false;
    // Reset form state si es necesario
  };

  const handleSaveForm = async () => {
    if (!supplierId.value) {
      console.error('No se encontró el ID del proveedor');
      return false;
    }

    try {
      isSaving.value = true;

      // Filtrar y mapear contactos con datos significativos
      const contacts = formState.contacts
        .filter((c) => c.typeContactId || c.stateId || c.fullName || c.phone || c.email)
        .map((row) => ({
          id: row.id,
          type_contact_id: row.typeContactId,
          state_id: row.stateId,
          full_name: row.fullName,
          phone: row.phone,
          email: row.email,
        }));

      // Preparar request con la estructura esperada por el backend
      const request = {
        web: formState.web,
        country_phone_code: formState.countryPhoneCode,
        state_phone_code: formState.statePhoneCode,
        phone: formState.phone,
        email: formState.email,
        contacts,
      };

      // Llamar al servicio de backend
      const response = await updateSupplierContact(supplierId.value, request);

      if (response?.success) {
        handleCompleteResponse(response);

        // Recargar módulos del supplier
        await loadSupplierModules();

        // Refrescar los datos del supplier para actualizar el modo lectura
        await refetchSupplierData();

        // Marcar item como completo en el sidebar
        markItemComplete('contacts');

        // Abrir la siguiente sección (información comercial) en modo registro
        openNextSection(FormComponentEnum.COMMERCIAL_INFORMATION);

        // Cambiar a modo lectura
        isEditMode.value = false;
        return true;
      } else {
        console.error('Error al guardar información de contacto');
        return false;
      }
    } catch (error) {
      handleError(error as Error);
      return false;
    } finally {
      isSaving.value = false;
    }
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  const addContact = () => {
    formState.contacts.push({
      typeContactId: undefined,
      typeContactName: '',
      fullName: '',
      phone: '',
      email: '',
      stateId: undefined,
      stateName: '',
      isEditMode: true,
    });
  };

  const editContact = (index: number) => {
    formState.contacts[index].isEditMode = true;
  };

  const removeContact = (index: number) => {
    formState.contacts.splice(index, 1);

    // Si no quedan contactos, agregar uno vacío
    if (formState.contacts.length === 0) {
      addContact();
    }
  };

  const filterOption = (input: string, option: any) => {
    return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
  };

  const filterOptionPhone = (input: string, option: any) => {
    const searchText = input.toLowerCase();

    // Buscar el país correspondiente en phoneCodeOptions
    const country = phoneCodeOptions.value.find(
      (c: any) => c.phone_code.toString() === option.value.toString()
    );

    if (!country) return false;

    // Buscar en el código de teléfono y en el nombre del país
    const phoneCode = country.phone_code.toString();
    const countryName = country.name.toLowerCase();

    return phoneCode.indexOf(input) >= 0 || countryName.indexOf(searchText) >= 0;
  };

  const handleStatePhoneCodeChange = (value: string) => {
    formState.statePhoneCode = value;
  };

  const handlePrefixSearch = (searchText: string) => {
    // Aquí se podría filtrar los prefijos si es necesario
    console.log('Searching prefix:', searchText);
  };

  const handlePrefixBlur = () => {
    touchedFields.statePhoneCode = true;
  };

  const handlePrefixKeyDown = (e: KeyboardEvent) => {
    // Permitir solo números en el prefijo
    if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab') {
      e.preventDefault();
    }
  };

  // Computed: validar si el formulario es válido (todos los campos requeridos completos)
  const isFormValid = computed(() => {
    // Email es el único campo requerido en el form principal
    const hasEmail =
      !!formState.email?.trim() && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formState.email);

    return hasEmail;
  });

  const getStateName = (id?: number) => {
    if (!id) return '';
    const found = states.value.find((s) => s.value === id);
    return found ? found.label : '';
  };

  const getTypeContactName = (id?: number) => {
    if (!id) return '';
    const found = typeContacts.value.find((t) => t.value === id);
    return found ? found.label : '';
  };

  return {
    // Estado
    formState,
    formRef,
    isEditMode,
    isLoading,
    isLoadingFields, // Para deshabilitar campos durante carga inicial
    typePhone,
    showAllContacts,
    shouldShowPhoneValidation,
    touchedFields,

    // Datos computados
    phoneCodeOptions,
    typeContacts,
    states,
    statePhoneCodeFormattedOptions,
    optionsTypePhone,
    hasRealContacts,
    totalContacts,
    readData,
    columns,
    isFormValid,

    // Reglas de validación
    rules,
    contactsFormRules,

    // Métodos
    handleEditMode,
    handleCancel,
    handleSave,
    addContact,
    editContact,
    removeContact,
    getTypeContactName,
    getStateName,
    filterOption,
    filterOptionPhone,
    handleStatePhoneCodeChange,
    handlePrefixSearch,
    handlePrefixBlur,
    handlePrefixKeyDown,
  };
}
