import { computed, reactive } from 'vue';
import type {
  FormFieldError,
  SupplierPolicyRuleForm,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import type {
  FormError,
  FormSectionErrors,
} from '@/modules/negotiations/supplier-new/types/supplier-registration/policies';
import {
  validatePaymentTermSection,
  validatePartialPaymentsSection,
} from '@/modules/negotiations/supplier-new/validators/supplier-registration/policies/payment-term-validator';
import { validateCancellationsSection } from '@/modules/negotiations/supplier-new/validators/supplier-registration/policies/cancellation-validator';
import {
  cancellationRequiredFields,
  reconfirmationRequiredFields,
  releasedRequiredFields,
} from '@/modules/negotiations/supplier-new/constants/supplier-registration/policies/form-policy-rule-fields';
import { validateReconfirmationsSection } from '@/modules/negotiations/supplier-new/validators/supplier-registration/policies/reconfirmation-validator';
import { validateReleasedSection } from '@/modules/negotiations/supplier-new/validators/supplier-registration/policies/released-validator';
import { validateChildrenSection } from '@/modules/negotiations/supplier-new/validators/supplier-registration/policies/children-validator';
import { useNotificationsComposable } from '@/modules/negotiations/suppliers/composables/notifications.composable';

export function usePolicyRuleErrorComposable(formState: SupplierPolicyRuleForm) {
  const { showNotificationError } = useNotificationsComposable();

  const initialFormErrors: FormError = {
    paymentTerm: {
      paymentTypeId: {
        isInvalid: false,
        message: 'El campo tipo de pago es obligatorio',
      },
      conditionValue: {
        isInvalid: false,
        message: 'El campo valor de condición es obligatorio',
      },
      conditionTypeId: {
        isInvalid: false,
        message: 'El campo condición de pago es obligatorio',
      },
    },
    partialPayments: [],
    cancellations: [],
    reconfirmations: [],
    released: [],
    children: {
      infantAgeMin: {
        isInvalid: false,
        message: 'El campo edad mínima de infante es obligatorio',
      },
      infantAgeMax: {
        isInvalid: false,
        message: 'El campo edad máxima de infante es obligatorio',
      },
      childAgeMin: {
        isInvalid: false,
        message: 'El campo edad mínima de niño es obligatorio',
      },
      childAgeMax: {
        isInvalid: false,
        message: 'El campo edad máxima de niño es obligatorio',
      },
    },
  };

  const initialCancellationErrors: FormSectionErrors = {
    timeLimitValue: {
      isInvalid: false,
      message: 'El campo tiempo límite es obligatorio',
    },
    timeLimitUnit: {
      isInvalid: false,
      message: 'El campo unidad de tiempo es obligatorio',
    },
    penaltyValue: {
      isInvalid: false,
      message: 'El campo valor de penalidad es obligatorio',
    },
    penaltyType: {
      isInvalid: false,
      message: 'El campo tipo de penalidad es obligatorio',
    },
    cancellationScope: {
      isInvalid: false,
      message: 'El campo alcance de cancelación es obligatorio',
    },
    cancellationPartialValue: {
      isInvalid: false,
      message: 'El campo valor parcial es obligatorio',
    },
    cancellationPartialUnit: {
      isInvalid: false,
      message: 'El campo unidad parcial es obligatorio',
    },
  };

  const initialReconfirmationErrors: FormSectionErrors = {
    confirmationType: {
      isInvalid: false,
      message: 'El campo tipo de confirmación es obligatorio',
    },
    timeValue: {
      isInvalid: false,
      message: 'El campo valor de tiempo es obligatorio',
    },
    timeUnit: {
      isInvalid: false,
      message: 'El campo unidad de tiempo es obligatorio',
    },
    listSendTimeValue: {
      isInvalid: false,
      message: 'El campo tiempo de envío de lista es obligatorio',
    },
    listSendTimeUnit: {
      isInvalid: false,
      message: 'El campo unidad de tiempo de envío es obligatorio',
    },
    unassignedQuota: {
      isInvalid: false,
      message: 'El campo cupo no asignados es obligatorio',
    },
  };

  const initialReleasedErrors: FormSectionErrors = {
    timeLimitValue: {
      isInvalid: false,
      message: 'El campo valor límite de tiempo es obligatorio',
    },
    releaseType: {
      isInvalid: false,
      message: 'El campo tipo de liberados es obligatorio',
    },
    releaseQuantity: {
      isInvalid: false,
      message: 'El campo cantidad de liberados es obligatorio',
    },
    maximumCapValue: {
      isInvalid: false,
      message: 'Debe ingresar un valor para el tope máximo',
    },
    benefitType: {
      isInvalid: false,
      message: 'Campo obligatorio',
    },
    breakfastIncluded: {
      isInvalid: false,
      message: 'Campo obligatorio',
    },
  };

  const initialPartialPaymentErrors: FormSectionErrors = {
    partialConditionTypeId: {
      isInvalid: false,
      message: 'El campo condición de pago parcial es obligatorio',
    },
    partialConditionValue: {
      isInvalid: false,
      message: 'El campo valor de condición parcial es obligatorio',
    },
    partialAmount: {
      isInvalid: false,
      message: 'El campo monto parcial es obligatorio',
    },
    partialAmountType: {
      isInvalid: false,
      message: 'El campo tipo de monto parcial es obligatorio',
    },
  };

  const formErrors = reactive<FormError>(structuredClone(initialFormErrors));

  // Estado para almacenar errores del backend por sección
  const backendErrors = reactive<{
    paymentTerm: string[];
    partialPayments: Record<number, string[]>;
    cancellations: Record<number, string[]>;
    reconfirmations: Record<number, string[]>;
    released: Record<number, string[]>;
    children: string[];
  }>({
    paymentTerm: [],
    partialPayments: {},
    cancellations: {},
    reconfirmations: {},
    released: {},
    children: [],
  });

  /**
   * Normaliza mensajes técnicos del backend a mensajes amigables para el usuario
   */
  const normalizeBackendMessage = (message: string): string => {
    let normalized = message;

    // Mapeo de campos técnicos a nombres amigables
    const fieldMappings: Record<string, string> = {
      // Payment Term (Specific first)
      partial_condition_type_id: 'Condición de pago parcial',
      partial_condition_value: 'Valor de condición parcial',
      partial_amount_type: 'Tipo de monto parcial',
      partial_amount: 'Monto parcial',
      partial_payments: 'Pagos parciales',
      partial_payment: 'Pago parcial',

      payment_type_id: 'Tipo de pago',
      condition_type_id: 'Condición de pago',
      condition_value: 'Valor de condición',
      payment_term: 'Condición de pago',
      'payment_term.': 'Condición de pago - ',

      // Cancellations
      cancellation_partial_value: 'Valor parcial de cancelación',
      cancellation_partial_unit: 'Unidad parcial de cancelación',
      cancellation_scope: 'Alcance de cancelación',
      time_limit_value: 'Tiempo límite',
      time_limit_unit: 'Unidad de tiempo',
      penalty_value: 'Valor de penalidad',
      penalty_type: 'Tipo de penalidad',
      'cancellations[': 'Cancelación ',
      cancellations: 'Cancelaciones',
      cancellation: 'Cancelación',

      // Reconfirmations
      list_send_time_value: 'Tiempo de envío de lista',
      list_send_time_unit: 'Unidad de tiempo de envío',
      list_type: 'Tipo de lista',
      send_list_enabled: 'Envío de lista',
      confirmation_type: 'Tipo de confirmación',
      time_value: 'Valor de tiempo',
      time_unit: 'Unidad de tiempo',
      unassigned_quota: 'Cupo no asignados',
      'reconfirmations[': 'Reconfirmación ',
      reconfirmations: 'Reconfirmaciones',
      reconfirmation: 'Reconfirmación',

      // Released
      release_type: 'Tipo de liberado',
      release_quantity: 'Cantidad de liberados',
      benefit_type: 'Tipo de beneficio',
      room_occupancy_type: 'Tipo de ocupación de habitación',
      room_benefit_type: 'Tipo de beneficio de habitación',
      has_maximum_cap: 'Tope máximo',
      maximum_cap_value: 'Valor del tope máximo',
      'released[': 'Liberado ',
      released: 'Liberados',

      // Children
      infant_age_min: 'Edad mínima de infante',
      infant_age_max: 'Edad máxima de infante',
      child_age_min: 'Edad mínima de niño',
      child_age_max: 'Edad máxima de niño',
      children: 'Edades',
      additional_information: 'Información adicional',
      inclusions_enabled: 'Inclusiones habilitadas',
      inclusions: 'Inclusiones',

      // Mensajes comunes
      'is required': 'es obligatorio',
      'must be a number': 'debe ser un número',
      'must be a string': 'debe ser texto',
      'must be a boolean': 'debe ser verdadero o falso',
      'must be an array': 'debe ser una lista',
      'must be one of': 'debe ser uno de',
      'should not be empty': 'no debe estar vacío',
      'should not be null': 'no debe estar vacío',
      'is invalid': 'es inválido',
    };

    // Reemplazar campos técnicos
    Object.entries(fieldMappings).forEach(([technical, friendly]) => {
      // Reemplazar con regex para manejar mayúsculas/minúsculas
      const regex = new RegExp(technical.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
      normalized = normalized.replace(regex, friendly);
    });

    // Limpiar caracteres técnicos comunes
    normalized = normalized
      .replace(/\[(\d+)\]/g, ' $1') // [0] -> " 0"
      .replace(/\./g, ' ') // . -> espacio
      .replace(/_/g, ' ') // _ -> espacio
      .replace(/\s+/g, ' ') // múltiples espacios -> un espacio
      .trim();

    // Capitalizar primera letra
    if (normalized.length > 0) {
      normalized = normalized.charAt(0).toUpperCase() + normalized.slice(1);
    }

    return normalized;
  };

  /**
   * Mapea mensajes del backend a las secciones correspondientes.
   * Soporta:
   * 1. Array de mensajes planos (string[])
   * 2. Objeto de errores con claves (Record<string, string[]>) - Laravel standard errors
   * 3. Array de objetos con propiedad y mensaje ({ property: string, message: string }[]) - Custom API pattern
   */
  const mapBackendMessagesToSections = (
    messages: string[] | Record<string, string[]> | { property: string; message: string }[]
  ): void => {
    // Limpiar errores anteriores del backend con el tipo correcto
    backendErrors.paymentTerm = [];
    backendErrors.partialPayments = {};
    backendErrors.cancellations = {};
    backendErrors.reconfirmations = {};
    backendErrors.released = {};
    backendErrors.children = [];

    // Normalizar entrada a una lista de entradas procesables
    const entries: { key: string | null; message: string }[] = [];

    if (Array.isArray(messages)) {
      messages.forEach((m) => {
        if (typeof m === 'string') {
          entries.push({ key: null, message: m });
        } else if (typeof m === 'object' && m !== null && 'property' in m && 'message' in m) {
          // Soporte para { property: "...", message: "..." }
          entries.push({ key: m.property, message: m.message });
        }
      });
    } else {
      // Soporte para Record<string, string[]>
      Object.entries(messages).forEach(([key, msgs]) => {
        const msgsArray = Array.isArray(msgs) ? msgs : [msgs];
        msgsArray.forEach((m) => entries.push({ key, message: m }));
      });
    }

    entries.forEach(({ key, message }) => {
      // 1. Intentar determinar sección e índice directamente desde la CLAVE (Más preciso)
      let section: keyof typeof backendErrors | null = null;
      let index: number | null = null;

      if (key) {
        if (key.includes('cancellations') || key.includes('cancellation'))
          section = 'cancellations';
        else if (key.includes('reconfirmations') || key.includes('reconfirmation'))
          section = 'reconfirmations';
        else if (key.includes('released')) section = 'released';
        else if (key.includes('partial_payments') || key.includes('partialPayments'))
          section = 'partialPayments';
        else if (key.includes('children')) section = 'children';
        // Payment Term suele ser raíz o 'payment_term', si no coincide con otros
        else if (key.includes('payment_term') || key.includes('paymentTerm'))
          section = 'paymentTerm';

        // Extraer índice de la clave (ej: cancellations.0.field o cancellation.rules.0.field)
        const indexMatch = key.match(/\.(\d+)(\.|$)/) || key.match(/\[(\d+)\]/);
        if (indexMatch) {
          index = parseInt(indexMatch[1] || indexMatch[2] || '0', 10);
        }
      }

      // Normalizar el mensaje antes de mapearlo
      const normalizedMessage = normalizeBackendMessage(message);
      const lowerMessage = message.toLowerCase();

      // Helper para agregar error a la sección correspondiente
      const addErrorToSection = (targetSection: keyof typeof backendErrors) => {
        const target = backendErrors[targetSection];
        if (Array.isArray(target)) {
          target.push(normalizedMessage);
        } else {
          // Si es un Record (secciones con filas múltiples)
          const idx = index ?? 0; // Si no hay índice, asignar a la fila 0 por defecto
          if (!target[idx]) target[idx] = [];
          target[idx].push(normalizedMessage);
        }
      };

      // Si la sección se determinó desde la clave, usarla directamente (más preciso)
      if (section) {
        addErrorToSection(section);
      } else if (
        lowerMessage.includes('infantage') ||
        lowerMessage.includes('childage') ||
        lowerMessage.includes('infantagemin') ||
        lowerMessage.includes('infantagemax') ||
        lowerMessage.includes('childagemin') ||
        lowerMessage.includes('childagemax') ||
        lowerMessage.includes('infant age') ||
        lowerMessage.includes('child age') ||
        lowerMessage.includes('infante') ||
        lowerMessage.includes('niño') ||
        lowerMessage.includes('edad') ||
        lowerMessage.includes('age') ||
        lowerMessage.includes('infant') ||
        lowerMessage.includes('child') ||
        lowerMessage.includes('children')
      ) {
        addErrorToSection('children');
      } else if (
        lowerMessage.includes('cancel') ||
        lowerMessage.includes('penalidad') ||
        lowerMessage.includes('penalty') ||
        lowerMessage.includes('tiempo límite') ||
        lowerMessage.includes('time limit') ||
        lowerMessage.includes('límite de tiempo') ||
        lowerMessage.includes('valor del límite de tiempo')
      ) {
        addErrorToSection('cancellations');
      } else if (
        lowerMessage.includes('reconfirm') ||
        lowerMessage.includes('confirmación') ||
        lowerMessage.includes('confirmation') ||
        lowerMessage.includes('cupo') ||
        lowerMessage.includes('quota') ||
        lowerMessage.includes('list') ||
        lowerMessage.includes('valor de tiempo') ||
        lowerMessage.includes('valor del tiempo')
      ) {
        addErrorToSection('reconfirmations');
      } else if (
        lowerMessage.includes('liberado') ||
        lowerMessage.includes('release') ||
        lowerMessage.includes('tope máximo') ||
        lowerMessage.includes('maximum cap') ||
        lowerMessage.includes('benefit')
      ) {
        addErrorToSection('released');
      } else if (
        lowerMessage.includes('pago parcial') ||
        lowerMessage.includes('partial payment') ||
        lowerMessage.includes('partialcondition') ||
        lowerMessage.includes('partial_') ||
        lowerMessage.includes('monto parcial') ||
        lowerMessage.includes('condición parcial') ||
        lowerMessage.includes('partial amount') ||
        lowerMessage.includes('partial condition')
      ) {
        addErrorToSection('partialPayments');
      } else if (
        lowerMessage.includes('pago') ||
        lowerMessage.includes('payment') ||
        lowerMessage.includes('condición') ||
        lowerMessage.includes('condition') ||
        lowerMessage.includes('payment_term')
      ) {
        addErrorToSection('paymentTerm');
      } else {
        // Si no se puede mapear, agregar a paymentTerm como fallback
        addErrorToSection('paymentTerm');
      }
    });
  };

  /**
   * Limpia los errores del backend
   */
  const clearBackendErrors = () => {
    Object.keys(backendErrors).forEach((key) => {
      const k = key as keyof typeof backendErrors;
      if (Array.isArray(backendErrors[k])) {
        (backendErrors[k] as string[]) = [];
      } else {
        (backendErrors[k] as Record<number, string[]>) = {};
      }
    });
  };

  const addCancellationError = (index?: number) => {
    const error = structuredClone(initialCancellationErrors);
    if (typeof index === 'number') {
      formErrors.cancellations.splice(index, 0, error);
    } else {
      formErrors.cancellations.push(error);
    }
  };

  const deleteCancellationError = (index: number) => {
    formErrors.cancellations.splice(index, 1);
  };

  const addReconfirmationError = (index?: number) => {
    const error = structuredClone(initialReconfirmationErrors);
    if (typeof index === 'number') {
      formErrors.reconfirmations.splice(index, 0, error);
    } else {
      formErrors.reconfirmations.push(error);
    }
  };

  const deleteReconfirmationError = (index: number) => {
    formErrors.reconfirmations.splice(index, 1);
  };

  const addReleasedError = (index?: number) => {
    const error = structuredClone(initialReleasedErrors);
    if (typeof index === 'number') {
      formErrors.released.splice(index, 0, error);
    } else {
      formErrors.released.push(error);
    }
  };

  const deleteReleasedError = (index: number) => {
    formErrors.released.splice(index, 1);
  };

  const addPartialPaymentError = (index?: number) => {
    const error = structuredClone(initialPartialPaymentErrors);
    if (typeof index === 'number') {
      formErrors.partialPayments.splice(index, 0, error);
    } else {
      formErrors.partialPayments.push(error);
    }
  };

  const deletePartialPaymentError = (index: number) => {
    formErrors.partialPayments.splice(index, 1);
  };

  const syncPartialPaymentErrors = (count: number) => {
    // Sincronizar el array de errores con el número de pagos parciales
    while (formErrors.partialPayments.length < count) {
      addPartialPaymentError();
    }
    while (formErrors.partialPayments.length > count) {
      formErrors.partialPayments.pop();
    }
  };

  const resetBaseFormErrors = () => {
    Object.assign(formErrors, structuredClone(initialFormErrors));
    clearBackendErrors();
  };

  const resetFormErrors = () => {
    Object.assign(formErrors.paymentTerm, structuredClone(initialFormErrors.paymentTerm));

    formErrors.cancellations.forEach((row) => {
      cancellationRequiredFields.forEach((fieldKey) => {
        row[fieldKey].isInvalid = false;
      });
    });

    formErrors.reconfirmations.forEach((row) => {
      reconfirmationRequiredFields.forEach((fieldKey) => {
        row[fieldKey].isInvalid = false;
      });
    });

    formErrors.released.forEach((row) => {
      releasedRequiredFields.forEach((fieldKey) => {
        row[fieldKey].isInvalid = false;
      });
      // Reset condicional de maximumCapValue
      if (row['maximumCapValue']) {
        row['maximumCapValue'].isInvalid = false;
      }
    });

    // Reset partial payments errors
    formErrors.partialPayments.forEach((row) => {
      Object.keys(row).forEach((fieldKey) => {
        row[fieldKey].isInvalid = false;
      });
    });

    Object.assign(formErrors.children, structuredClone(initialFormErrors.children));
  };

  const getFieldFormError = (
    section: string,
    field: string,
    index?: number
  ): FormFieldError | undefined => {
    const sectionErrors = formErrors[section];

    if (Array.isArray(sectionErrors)) {
      return index !== undefined ? sectionErrors[index]?.[field] : undefined;
    }

    return sectionErrors?.[field];
  };

  const isInvalidField = (section: string, field: string, index?: number): boolean => {
    return getFieldFormError(section, field, index)?.isInvalid || false;
  };

  const getInputNumberErrorClass = (section: string, field: string, index?: number) => {
    return {
      'ant-input-number-status-error': isInvalidField(section, field, index),
    };
  };

  const getSelectErrorClass = (section: string, field: string, index?: number) => {
    return {
      'ant-select-status-error': isInvalidField(section, field, index),
    };
  };

  const validateErrors = () => {
    resetFormErrors();

    // Sincronizar errores de partial payments con el formulario
    syncPartialPaymentErrors(formState.paymentTerm.partialPayments?.length || 0);

    const validatePaymentTerm = validatePaymentTermSection(
      formState.paymentTerm,
      formErrors.paymentTerm
    );

    // Validar partial payments solo si están habilitados
    const validatePartialPayments = formState.paymentTerm.partialPaymentsEnabled
      ? validatePartialPaymentsSection(
          formState.paymentTerm.partialPayments,
          formErrors.partialPayments
        )
      : true;

    const validateCancellations = validateCancellationsSection(
      formState.cancellations,
      formErrors.cancellations
    );

    // Si automaticReconfirmation está activado, no validar reconfirmaciones
    const isAutomaticReconfirmationEnabled =
      formState.cancellations.length > 0 &&
      formState.cancellations.every((c) => c.automaticReconfirmation);

    const validateReconfirmations =
      isAutomaticReconfirmationEnabled || formState.reconfirmationsNotApplicable
        ? true
        : validateReconfirmationsSection(formState.reconfirmations, formErrors.reconfirmations);

    const validateReleased = formState.releasedNotApplicable
      ? true
      : validateReleasedSection(formState.released, formErrors.released);

    const validateChildren = validateChildrenSection(formState.children, formErrors.children);

    const validationSections = [
      { name: 'Condición de pago', valid: validatePaymentTerm && validatePartialPayments },
      { name: 'Cancelación', valid: validateCancellations },
      { name: 'Reconfirmación', valid: validateReconfirmations },
      { name: 'Liberados', valid: validateReleased },
      { name: 'Edades', valid: validateChildren },
    ];

    const failedSections = validationSections.filter(({ valid }) => !valid);

    if (failedSections.length === 0) return;

    const joinSectionNames = failedSections.map((section) => section.name).join(', ');

    showNotificationError(
      `Por favor, revisa los campos de las siguientes secciones: ${joinSectionNames}`
    );

    throw new Error('Errores en validaciones de campos');
  };

  const getInvalidMessages = (
    formSectionErrors: FormSectionErrors | FormSectionErrors[]
  ): string[] => {
    return Object.values(formSectionErrors)
      .filter((field) => field.isInvalid)
      .map((field) => field.message);
  };

  const extractErrorMessages = (sectionErrors: FormSectionErrors[]): string[][] => {
    const errors: string[][] = [];

    sectionErrors.forEach((row, index) => {
      errors[index] = [...getInvalidMessages(row)];
    });

    return errors;
  };

  const errorsPaymentTerm = computed(() => {
    const frontendErrors = getInvalidMessages(formErrors.paymentTerm);
    return [...frontendErrors, ...backendErrors.paymentTerm];
  });

  const errorsChildren = computed(() => {
    const frontendErrors = getInvalidMessages(formErrors.children);
    return [...frontendErrors, ...backendErrors.children];
  });

  const errorsCancellations = computed(() => {
    const frontendErrors = extractErrorMessages(formErrors.cancellations);

    // Merge backend errors by index
    Object.entries(backendErrors.cancellations).forEach(([key, msgs]) => {
      const idx = parseInt(key, 10);
      if (!frontendErrors[idx]) frontendErrors[idx] = [];
      // Safety check to ensure msgs is an array
      const messagesToAdd = Array.isArray(msgs) ? msgs : [msgs];
      frontendErrors[idx].push(...messagesToAdd);
    });

    return frontendErrors;
  });

  const errorsReconfirmations = computed(() => {
    const frontendErrors = extractErrorMessages(formErrors.reconfirmations);

    // Merge backend errors by index
    Object.entries(backendErrors.reconfirmations).forEach(([key, msgs]) => {
      const idx = parseInt(key, 10);
      if (!frontendErrors[idx]) frontendErrors[idx] = [];
      // Safety check to ensure msgs is an array
      const messagesToAdd = Array.isArray(msgs) ? msgs : [msgs];
      frontendErrors[idx].push(...messagesToAdd);
    });

    return frontendErrors;
  });

  const errorsReleased = computed(() => {
    const frontendErrors = extractErrorMessages(formErrors.released);

    // Merge backend errors by index
    Object.entries(backendErrors.released).forEach(([key, msgs]) => {
      const idx = parseInt(key, 10);
      if (!frontendErrors[idx]) frontendErrors[idx] = [];
      // Safety check to ensure msgs is an array
      const messagesToAdd = Array.isArray(msgs) ? msgs : [msgs];
      frontendErrors[idx].push(...messagesToAdd);
    });

    return frontendErrors;
  });

  // Computed que combina todos los errores de partial payments en un array plano
  const errorsPartialPayments = computed(() => {
    const allErrors: string[] = [];
    formErrors.partialPayments.forEach((row) => {
      allErrors.push(...getInvalidMessages(row));
    });

    // Flatten backend errors for summary
    Object.values(backendErrors.partialPayments).forEach((msgs) => {
      allErrors.push(...msgs);
    });

    return allErrors;
  });

  const errorsPartialPaymentsRows = computed(() => {
    const frontendErrors = extractErrorMessages(formErrors.partialPayments);

    // Merge backend errors by index
    Object.entries(backendErrors.partialPayments).forEach(([key, msgs]) => {
      const idx = parseInt(key, 10);
      if (!frontendErrors[idx]) frontendErrors[idx] = [];
      // Safety check to ensure msgs is an array
      const messagesToAdd = Array.isArray(msgs) ? msgs : [msgs];
      frontendErrors[idx].push(...messagesToAdd);
    });

    return frontendErrors;
  });

  return {
    formErrors,
    errorsPaymentTerm,
    errorsPartialPayments,
    errorsPartialPaymentsRows,
    errorsCancellations,

    errorsReconfirmations,
    errorsReleased,
    errorsChildren,
    getInputNumberErrorClass,
    getSelectErrorClass,
    addCancellationError,
    addReconfirmationError,
    addReleasedError,
    addPartialPaymentError,
    deletePartialPaymentError,
    syncPartialPaymentErrors,
    validateErrors,
    deleteCancellationError,
    deleteReconfirmationError,
    deleteReleasedError,
    resetBaseFormErrors,
    mapBackendMessagesToSections,
    clearBackendErrors,
  };
}
