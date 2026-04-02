import { handleCompleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import {
  BENEFIT_TYPES,
  CANCELLATION_PENALTY_SCOPES,
  CANCELLATION_SCOPES,
  CHILDREN_INCLUSIONS,
  CONDITION_TYPES,
  CONFIRMATION_TYPES,
  PAYMENT_TYPES,
  POLICIES_CLONE,
  RELEASE_TYPES,
  ROOM_BENEFIT_TYPES,
  ROOM_OCCUPANCY_TYPES,
  TIME_UNITS,
  VALUE_TYPES,
} from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/data-init/data';
import { usePolicyFormStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-form-store-facade.composable';
import { usePolicyManagerComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-manager.composable';
import { usePolicyStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-store-facade.composable';
import { usePolicyRuleErrorComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/rules/policy-rule-error.composable';
import { usePolicyRuleRequestComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/rules/policy-rule-request.composable';
import { useSupplierClassificationHelper } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/use-supplier-classification-helper.composable';
import { ConfirmationTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/confirmation-type.enum';
import { ReleaseTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/release-type.enum';
import { TimeUnitEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/time-unit.enum';
import { ValueTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/value-type.enum';
import { isCloneFormMode } from '@/modules/negotiations/supplier-new/helpers/supplier-registration/policies/form-mode-helper';
import {
  applyPluralToText,
  findLabel,
  formatValueWithSymbol,
  toRegularSingularText,
} from '@/modules/negotiations/supplier-new/helpers/supplier-registration/policies/policy-rule-helper';
import type { SupplierPolicyRuleForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import { useSupplierPoliciesService } from '@/modules/negotiations/supplier-new/service/supplier-policies.service';
import { mapItemsToOptions } from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { usePolicyRuleCloneComposable } from './policy-rule-clone.composable';

export function usePolicyRuleComposable() {
  const { backToPolicies, openInformationBasic } = usePolicyManagerComposable();

  const {
    formMode,
    policyId,
    policyCloneResponse,
    clonedPolicyData,
    setPolicyCloneResponse,
    clearClonedPolicyData,
    formState: policyFormState,
  } = usePolicyFormStoreFacade();

  const { isLoading, startLoading, stopLoading, setReloadList } = usePolicyStoreFacade();

  const { isAccommodationSupplier } = useSupplierClassificationHelper();

  const isEditMode = ref<boolean>(false);
  const isLoadingData = ref<boolean>(false);
  const conditionTypes = ref<SelectOption[]>([]);
  const paymentTypes = ref<SelectOption[]>([]);
  const valueTypes = ref<SelectOption[]>([]);
  const timeUnits = ref<SelectOption[]>([]);
  const confirmationTypes = ref<SelectOption[]>([]);
  const releaseTypes = ref<SelectOption[]>([]);
  const benefitTypes = ref<SelectOption[]>([]);
  const childrenInclusions = ref<SelectOption[]>([]);
  const cancellationPenaltyScopes = ref<SelectOption[]>([]);
  const roomOccupancyTypes = ref<SelectOption[]>([]);
  const roomBenefitTypes = ref<SelectOption[]>([]);
  const cancellationScopes = ref<SelectOption[]>([]);
  const policiesClone = ref<SelectOption[]>([]);
  const isAdditionalRulesDrawerOpen = ref<boolean>(false);
  const selectedAdditionalPolicies = ref<number[]>([]);

  // Counter for unique IDs in partial payments
  let partialPaymentUidCounter = 0;
  const generatePartialPaymentUid = () => `pp_${++partialPaymentUidCounter}_${Date.now()}`;

  // Computed para filtrar políticas ya agregadas del drawer
  const availableAdditionalPolicies = computed(() => {
    const addedPolicyIds: (string | number)[] = formState.additionalRules.map((r) => r.policyId);
    return policiesClone.value.filter((p) => !addedPolicyIds.includes(p.value));
  });

  const filteredConditionTypes = computed(() => {
    const paymentTypeId = formState.paymentTerm.paymentTypeId;
    const allConditions = conditionTypes.value;

    if (!paymentTypeId) return allConditions;

    // IDs from data.ts:
    // Prepago: 1
    // Credito: 2
    // Contado: 3
    // Antes del servicio: 1
    // Posterior a la facturación: 2
    // Al momento de la reserva: 3

    switch (paymentTypeId) {
      case 1: // Prepago
        // Antes del servicio (1) y Al momento de la reserva (3)
        return allConditions.filter((c) => [1, 3].includes(Number(c.value)));
      case 2: // Credito
        // Posterior a la facturación (2)
        return allConditions.filter((c) => [2].includes(Number(c.value)));
      case 3: // Contado
        // Ninguno
        return [];
      default:
        return allConditions;
    }
  });

  const getPartialConditionOptions = (index?: number) => {
    const usedUniqueValues = formState.paymentTerm.partialPayments
      .filter((p, i) => i !== index && p.partialConditionTypeId === 3) // 3: Al momento de la reserva
      .map((p) => p.partialConditionTypeId);

    return filteredConditionTypes.value.filter((opt) => {
      // Si la opcion es unica (3) y ya esta usada, filtrar
      // Nota: Asumimos que 3 es la unica opcion restringida a un solo uso
      if (Number(opt.value) === 3 && usedUniqueValues.includes(3)) {
        return false;
      }
      return true;
    });
  };

  const canAddPartialPayment = computed(() => {
    // Verificamos si al intentar agregar una nueva fila (index = length), existen opciones disponibles
    const nextIndex = formState.paymentTerm.partialPayments.length;
    const availableOptions = getPartialConditionOptions(nextIndex);
    return availableOptions.length > 0;
  });

  const initialFormState: SupplierPolicyRuleForm = {
    supplierPolicyId: policyId.value,
    paymentTerm: {
      paymentTypeId: null,
      conditionTypeId: null,
      conditionValue: null,
      partialPaymentsEnabled: false,
      partialPayments: [],
      isEditable: true,
    },

    cancellations: [],
    reconfirmations: [],
    reconfirmationsNotApplicable: false, // Por defecto, SÍ aplica
    released: [],
    releasedNotApplicable: false, // Por defecto, SÍ aplica
    children: {
      infantAgeMin: null,
      infantAgeMax: null,
      childAgeMin: null,
      childAgeMax: null,
      additionalInformation: null,
      inclusionsEnabled: false,
      selectedAgeType: null,
      inclusions: [],
      infantInclusions: [],
      isEditable: true,
    },
    additionalRules: [],
  };

  const formState = reactive<SupplierPolicyRuleForm>(structuredClone(initialFormState));

  const {
    formErrors,
    errorsPaymentTerm,
    errorsPartialPayments,
    errorsPartialPaymentsRows,
    errorsCancellations,
    errorsReconfirmations,
    errorsReleased,
    errorsChildren,
    resetBaseFormErrors,
    getInputNumberErrorClass,
    getSelectErrorClass,
    addCancellationError,
    addReconfirmationError,
    addReleasedError,
    deleteCancellationError,
    deleteReconfirmationError,
    deleteReleasedError,
    addPartialPaymentError,
    mapBackendMessagesToSections,
    clearBackendErrors,
  } = usePolicyRuleErrorComposable(formState);

  const { buildRequestData } = usePolicyRuleRequestComposable(formState);

  const addCancellation = (originIndex?: number) => {
    let insertIndex = 0;
    const list = formState.cancellations;

    // Lógica de inserción:
    // 1. Si solo hay 1 item -> Insertar arriba (índice 0)
    // 2. Si hay > 1 item y se especifica origen -> Insertar debajo del origen
    // 3. Default (o > 1 sin origen) -> Inicio (comportamiento original unshift)
    if (list.length === 1) {
      insertIndex = 0;
    } else if (list.length > 1 && typeof originIndex === 'number') {
      insertIndex = originIndex + 1;
    } else {
      insertIndex = 0; // Default Unshift
    }

    const newItem = {
      timeLimitUnit: 'days' as TimeUnitEnum,
      timeLimitValue: null,
      penaltyType: ValueTypeEnum.PERCENTAGE,
      penaltyValue: null,
      penaltyScope: null,
      automaticReconfirmation: false,
      cancellationScope: 'total',
      cancellationPartialValue: null,
      cancellationPartialUnit: null,
      isEditable: true,
    };

    list.splice(insertIndex, 0, newItem);
    addCancellationError(insertIndex);
  };

  const deleteCancellation = (index: number) => {
    formState.cancellations.splice(index, 1);
    deleteCancellationError(index);
  };

  const addReconfirmation = (originIndex?: number) => {
    let insertIndex = 0;
    const list = formState.reconfirmations;

    if (list.length === 1) {
      insertIndex = 0;
    } else if (list.length > 1 && typeof originIndex === 'number') {
      insertIndex = originIndex + 1;
    } else {
      insertIndex = 0; // Default Unshift
    }

    const newItem = {
      confirmationType: 'confirmation' as ConfirmationTypeEnum,
      timeUnit: 'days' as TimeUnitEnum,
      timeValue: null,
      sendListEnabled: false,
      listType: null,
      listSendTimeValue: null,
      listSendTimeUnit: null,
      unassignedQuota: null,
      isEditable: true,
    };

    list.splice(insertIndex, 0, newItem);
    addReconfirmationError(insertIndex);
  };

  const deleteReconfirmation = (index: number) => {
    formState.reconfirmations.splice(index, 1);
    deleteReconfirmationError(index);
  };

  const addReleased = (originIndex?: number) => {
    const list = formState.released;
    let insertIndex = list.length; // Default Push behavior

    if (list.length === 1) {
      insertIndex = 0;
    } else if (list.length > 1 && typeof originIndex === 'number') {
      insertIndex = originIndex + 1;
    }
    // Else -> Push (length)

    const newItem = {
      timeLimitValue: null,
      releaseType: 'rooms' as ReleaseTypeEnum,
      releaseQuantity: null,
      benefitType: null,
      roomOccupancyType: null,
      roomBenefitType: null,
      breakfastIncluded: null,
      hasMaximumCap: false,
      maximumCapValue: null,
      isEditable: true,
    };

    list.splice(insertIndex, 0, newItem);
    addReleasedError(insertIndex);
  };

  const deleteReleased = (index: number) => {
    formState.released.splice(index, 1);
    deleteReleasedError(index);
  };

  const { setDataToClone } = usePolicyRuleCloneComposable(formState, {
    addCancellation,
    addReconfirmation,
    addReleased,
    addCancellationError,
    addReconfirmationError,
    addReleasedError,
  });

  const resetFormState = () => {
    Object.assign(formState, structuredClone(initialFormState));
  };

  const getValueTypeName = (valueType: ValueTypeEnum | string) => {
    return findLabel(valueTypes, valueType);
  };

  const getTimeUnitName = (timeUnit: TimeUnitEnum | null) => {
    return findLabel(timeUnits, timeUnit);
  };

  const getConfirmationTypeName = (confirmationType: ConfirmationTypeEnum | null) => {
    return findLabel(confirmationTypes, confirmationType);
  };

  const getReleaseTypeName = (releaseType: ReleaseTypeEnum | null) => {
    return findLabel(releaseTypes, releaseType);
  };

  const getBenefitTypeName = (benefitType: string | null) => {
    return findLabel(benefitTypes, benefitType);
  };

  const enablePaymentTermEdit = () => {
    formState.paymentTerm.isEditable = true;
  };

  const enableCancellationEdit = (index: number) => {
    formState.cancellations[index].isEditable = true;
  };

  const enableReconfirmationEdit = (index: number) => {
    formState.reconfirmations[index].isEditable = true;
  };

  const enableReleasedEdit = (index: number) => {
    formState.released[index].isEditable = true;
  };

  const enableChildrenEdit = () => {
    formState.children.isEditable = true;
  };

  const loadInitialFormData = async () => {
    resetFormState();
    resetBaseFormErrors(); // Limpiar errores de validación al iniciar un nuevo formulario
    setFormSupplierPolicyId();

    // Autocompletar datos desde clonedPolicyData (nuevo flujo de clone)
    if (isCloneFormMode(formMode.value) && clonedPolicyData.value?.rules) {
      resetBaseFormErrors();
      await loadRulesFromClonedData(clonedPolicyData.value.rules);
      clearClonedPolicyData();
      return;
    }

    // Autocompletar datos en modo clone de politica (flujo anterior)
    if (isCloneFormMode(formMode.value) && policyCloneResponse.value) {
      resetBaseFormErrors();
      setDataToClone();
      setPolicyCloneResponse(null);
      return;
    }

    // Asegurar que los recursos estén cargados antes de mapear para tener las etiquetas
    if (paymentTypes.value.length === 0) {
      await fetchResourcesData();
    }

    await loadSupplierPolicyRules();

    if (!isEditMode.value) {
      addCancellation();
      addReconfirmation();
      addReleased();
    }
  };

  const setFormSupplierPolicyId = () => {
    formState.supplierPolicyId = policyId.value;
  };

  /**
   * Pre-llena las reglas desde datos clonados (nuevo flujo de clone)
   * Todas las reglas se marcan como editables ya que es una nueva política
   */
  const loadRulesFromClonedData = async (rules: any) => {
    startLoading();
    isLoadingData.value = true;

    // Asegurar que los recursos estén cargados
    if (paymentTypes.value.length === 0) {
      await fetchResourcesData();
    }

    // Mappers helper functions
    const mapPaymentType = (type: string | number | null): number | null => {
      if (typeof type === 'number') return type;
      const typeMap: Record<string, number> = { prepaid: 1, credit: 2, cash: 3, postpaid: 3 };
      return type ? typeMap[type] || null : null;
    };

    const mapConditionType = (reference: string | number | null): number | null => {
      if (typeof reference === 'number') return reference;
      const refMap: Record<string, number> = { before_service: 1, after_invoice: 2, at_booking: 3 };
      return reference ? refMap[reference] || null : null;
    };

    // 1. Payment Terms
    const pt = rules.payment || rules.payment_term || rules.paymentTerm || {};
    const paymentTypeId = mapPaymentType(pt.type || pt.payment_type_id || pt.paymentTypeId);
    const conditionTypeId = mapConditionType(
      pt.deadline?.reference || pt.condition_type_id || pt.conditionTypeId
    );
    const conditionValue = pt.deadline?.value || pt.condition_value || pt.conditionValue || null;
    const splitPayment = pt.splitPayment || {};
    const partialPaymentsEnabled =
      splitPayment.enabled ?? pt.partial_payments_enabled ?? pt.partialPaymentsEnabled ?? false;
    const installments =
      splitPayment.installments || pt.partial_payments || pt.partialPayments || [];

    Object.assign(formState.paymentTerm, {
      paymentTypeId,
      paymentTypeName: findLabel(paymentTypes, paymentTypeId),
      conditionTypeId,
      conditionTypeName: findLabel(conditionTypes, conditionTypeId),
      conditionValue,
      partialPaymentsEnabled,
      partialPayments: installments.map((pp: any) => ({
        partialConditionTypeId: mapConditionType(
          pp.timing?.reference || pp.partial_condition_type_id || pp.partialConditionTypeId
        ),
        partialConditionValue:
          pp.timing?.value || pp.partial_condition_value || pp.partialConditionValue || null,
        partialAmountType: (() => {
          const unit = pp.amount?.unit || pp.partial_amount_type || pp.partialAmountType || null;
          return unit === 'percent' ? 'percentage' : unit;
        })(),
        partialAmount: pp.amount?.value || pp.partial_amount || pp.partialAmount || null,
      })),
      isEditable: true, // Siempre editable en clone
    });

    // 2. Cancellations
    const canc = rules.cancellation || rules.cancellations || {};
    const actualRules = Array.isArray(canc) ? canc : canc.rules || [];

    formState.cancellations = actualRules.map((r: any) => ({
      timeLimitValue: r.deadline?.value || r.time_limit_value || r.timeLimitValue,
      timeLimitUnit: r.deadline?.unit || r.time_limit_unit || r.timeLimitUnit,
      penaltyValue: r.penalty?.value || r.penalty_value || r.penaltyValue,
      penaltyType:
        r.penalty?.unit === 'percent'
          ? ValueTypeEnum.PERCENTAGE
          : r.penalty?.unit || r.penalty_type || r.penaltyType,
      cancellationScope: r.scope?.type || r.cancellation_scope || r.cancellationScope || 'total',
      cancellationPartialValue:
        r.scope?.quantity || r.cancellation_partial_value || r.cancellationPartialValue,
      cancellationPartialUnit:
        r.scope?.unit || r.cancellation_partial_unit || r.cancellationPartialUnit,
      automaticReconfirmation:
        canc.automaticReconfirmation ?? canc.automatic_reconfirmation ?? false,
      isEditable: true, // Siempre editable en clone
    }));

    if (formState.cancellations.length === 0) {
      addCancellation();
    } else {
      formState.cancellations.forEach(() => addCancellationError());
    }

    // 3. Reconfirmations
    const reconf = rules.reconfirmation || rules.reconfirmations || {};
    // Soportar nueva estructura con enabled y rules
    const reconfRules = Array.isArray(reconf) ? reconf : reconf.rules || [];
    const reconfEnabled = typeof reconf.enabled === 'boolean' ? reconf.enabled : true;

    // Determinar si automaticReconfirmation está activo
    const hasAutomaticReconfirmation =
      canc.automaticReconfirmation ?? canc.automatic_reconfirmation ?? false;

    // Si automaticReconfirmation está activo, se ignora reconfirmations
    // Si enabled es false desde el backend, marcar como "No aplica"
    formState.reconfirmationsNotApplicable = hasAutomaticReconfirmation ? false : !reconfEnabled;

    formState.reconfirmations = reconfRules.map((r: any) => {
      const pl = r.passengerList || r.passenger_list || {};
      const pld = pl.details || pl.passenger_list_details || {};
      return {
        confirmationType: r.type || r.confirmation_type || r.confirmationType,
        timeValue: r.deadline?.value || r.time_value || r.timeValue,
        timeUnit: r.deadline?.unit || r.time_unit || r.timeUnit,
        sendListEnabled: pl.enabled || r.send_list_enabled || r.sendListEnabled || false,
        listType: pld.type || r.list_type || r.listType,
        listSendTimeValue: pld.deadline?.value || r.list_send_time_value || r.listSendTimeValue,
        listSendTimeUnit: pld.deadline?.unit || r.list_send_time_unit || r.listSendTimeUnit,
        unassignedQuota: pld.unassignedQuota || r.unassigned_quota || r.unassignedQuota,
        isEditable: true, // Siempre editable en clone
      };
    });

    if (formState.reconfirmations.length === 0) {
      addReconfirmation();
    } else {
      formState.reconfirmations.forEach(() => addReconfirmationError());
    }

    // 4. Released
    const rel = rules.released || {};
    // Soportar nueva estructura con enabled y rules
    const relRules = Array.isArray(rel) ? rel : rel.rules || [];
    const relEnabled = typeof rel.enabled === 'boolean' ? rel.enabled : true;

    // Si enabled es false desde el backend, marcar como "No aplica"
    formState.releasedNotApplicable = !relEnabled;

    formState.released = relRules.map((r: any) => ({
      timeLimitValue: r.time_limit_value || r.timeLimitValue,
      releaseType: r.release_type || r.releaseType,
      releaseQuantity: r.release_quantity || r.releaseQuantity,
      benefitType: r.benefit_type || r.benefitType,
      roomOccupancyType: r.room_occupancy_type || r.roomOccupancyType,
      roomBenefitType: r.room_benefit_type || r.roomBenefitType,
      hasMaximumCap: r.has_maximum_cap || r.hasMaximumCap || false,
      maximumCapValue: r.maximum_cap_value || r.maximumCapValue,
      isEditable: true, // Siempre editable en clone
    }));

    if (formState.released.length === 0) {
      addReleased();
    } else {
      formState.released.forEach(() => addReleasedError());
    }

    // 5. Children
    const child = rules.children || {};
    const ranges = child.ranges || {};
    const allInclusions = child.inclusions || [];

    const childInclusions = allInclusions
      .filter((inc: any) => inc.appliesToCategory === 'child')
      .map((inc: any) => ({
        description: inc.itemCode,
        isIncluded: inc.isIncluded ?? false,
        isVisible: inc.isVisible ?? false,
      }));

    const infantInclusionsFiltered = allInclusions
      .filter((inc: any) => inc.appliesToCategory === 'infant')
      .map((inc: any) => ({
        description: inc.itemCode,
        isIncluded: inc.isIncluded ?? false,
        isVisible: inc.isVisible ?? false,
      }));

    Object.assign(formState.children, {
      infantAgeMin: ranges.infant?.minAge ?? null,
      infantAgeMax: ranges.infant?.maxAge ?? null,
      childAgeMin: ranges.child?.minAge ?? null,
      childAgeMax: ranges.child?.maxAge ?? null,
      additionalInformation: child.additionalInformation ?? null,
      inclusionsEnabled: child.inclusionsEnabled ?? false,
      selectedAgeType:
        infantInclusionsFiltered.length > 0
          ? 'infant'
          : childInclusions.length > 0
            ? 'child'
            : null,
      inclusions: childInclusions,
      infantInclusions: infantInclusionsFiltered,
      isEditable: true, // Siempre editable en clone
    });

    // 6. Additional Rules - soportar nueva estructura con enabled y rules
    const additionalRulesContainer = rules.additionalRules || {};
    const additionalRulesArray = Array.isArray(additionalRulesContainer)
      ? additionalRulesContainer
      : additionalRulesContainer.rules || [];
    formState.additionalRules = additionalRulesArray.map((rule: any, index: number) => ({
      policyId: index,
      policyName:
        rule.ruleCode
          ?.replace(/_/g, ' ')
          .split(' ')
          .map((word: string) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
          .join(' ') ?? `Regla ${index + 1}`,
      ruleCode: rule.ruleCode ?? '',
      isActive: rule.isActive ?? true,
      contentHtml: rule.contentHtml ?? '',
      additionalInformation: rule.contentHtml ?? '',
      isEditable: true, // Siempre editable en clone
    }));

    nextTick(() => {
      isLoadingData.value = false;
    });

    stopLoading();
  };

  const fetchResourcesData = async () => {
    startLoading();

    // Usar datos crudos del archivo data.ts
    conditionTypes.value = mapItemsToOptions(CONDITION_TYPES);
    paymentTypes.value = mapItemsToOptions(PAYMENT_TYPES);
    valueTypes.value = VALUE_TYPES;
    timeUnits.value = TIME_UNITS;
    confirmationTypes.value = CONFIRMATION_TYPES;
    releaseTypes.value = RELEASE_TYPES;
    benefitTypes.value = BENEFIT_TYPES;
    childrenInclusions.value = CHILDREN_INCLUSIONS;
    cancellationPenaltyScopes.value = CANCELLATION_PENALTY_SCOPES;
    roomOccupancyTypes.value = ROOM_OCCUPANCY_TYPES;
    roomBenefitTypes.value = ROOM_BENEFIT_TYPES;
    cancellationScopes.value = CANCELLATION_SCOPES;
    policiesClone.value = mapItemsToOptions(POLICIES_CLONE);

    stopLoading();
  };

  const openAdditionalRulesDrawer = () => {
    isAdditionalRulesDrawerOpen.value = true;
  };

  const closeAdditionalRulesDrawer = () => {
    isAdditionalRulesDrawerOpen.value = false;
    // Limpiar la selección cuando se cierra el drawer
    selectedAdditionalPolicies.value = [];
  };

  const handleAdditionalRulesSave = () => {
    // Crear un card para cada regla seleccionada
    selectedAdditionalPolicies.value.forEach((policyId) => {
      const policyOption = policiesClone.value.find((p) => p.value === policyId);

      // Verificar que la regla no esté ya agregada (por policyId)
      if (policyOption && !formState.additionalRules.some((r) => r.policyId === policyId)) {
        // Crear un objeto nuevo para evitar referencias compartidas
        formState.additionalRules.push({
          policyId: policyId,
          policyName: policyOption.label ?? '',
          ruleCode: policyOption.label?.toLowerCase().replace(/\s+/g, '_') ?? '',
          isActive: true,
          contentHtml: '',
          additionalInformation: '',
          isEditable: true,
        });
      }
    });

    // Limpiar selección y cerrar drawer
    selectedAdditionalPolicies.value = [];
    closeAdditionalRulesDrawer();
  };

  const enableAdditionalRuleEdit = (index: number) => {
    formState.additionalRules[index].isEditable = true;
  };

  const deleteAdditionalRule = (index: number) => {
    formState.additionalRules.splice(index, 1);
  };

  const showPartialPaymentInputs = computed(() => {
    return formState.paymentTerm.partialPaymentsEnabled;
  });

  // Mostrar sección "Alcance de la Cancelación" solo si es Hotel
  const showCancellationPenaltyScopeSelector = computed(() => {
    return !!policyFormState.isHotel;
  });

  // Función helper para mostrar selectores extra de habitaciones en liberados
  const showRoomExtraSelectors = (releaseType: ReleaseTypeEnum | null) => {
    return isAccommodationSupplier.value && releaseType === ReleaseTypeEnum.ROOM;
  };

  const addPartialPayment = (originIndex?: number) => {
    const list = formState.paymentTerm.partialPayments;
    const nextIndex = list.length;
    // Note: Validation of available options depends on current usage.
    // If inserting in middle, logic might be tricky for "available options for next index"
    // but the options logic `getPartialConditionOptions` uses `partialPayments.length` inside `canAddPartialPayment`.
    // Here we just calculate defaults.

    // Calculate insertion index
    let insertIndex = list.length; // Default Push
    if (list.length === 1) {
      insertIndex = 0;
    } else if (list.length > 1 && typeof originIndex === 'number') {
      insertIndex = originIndex + 1;
    }

    const availableOptions = getPartialConditionOptions(nextIndex); // Use total length for new item logic? Or recalculate?
    // Since condition types are filtered by uniqueness, it doesn't strictly depend on position, just existence.
    const defaultConditionTypeId =
      availableOptions.length > 0 ? Number(availableOptions[0].value) : 1;

    const newItem = {
      _uid: generatePartialPaymentUid(),
      partialConditionTypeId: defaultConditionTypeId,
      partialConditionValue: null,
      partialAmountType: 'percentage', // Porcentaje por defecto
      partialAmount: null,
    };

    list.splice(insertIndex, 0, newItem);
    addPartialPaymentError(insertIndex);
  };

  const deletePartialPayment = (index: number) => {
    formState.paymentTerm.partialPayments.splice(index, 1);
  };

  const handlePartialPaymentsEnabled = () => {
    if (!formState.paymentTerm.partialPaymentsEnabled) {
      formState.paymentTerm.partialPayments = [];
    } else if (formState.paymentTerm.partialPayments.length === 0) {
      addPartialPayment();
    }
  };

  const isPartialPaymentsBlocked = computed(() => {
    // Bloquear si es Prepago (1) y Al momento de la reserva (3)
    return formState.paymentTerm.paymentTypeId === 1 && formState.paymentTerm.conditionTypeId === 3;
  });

  watch(isPartialPaymentsBlocked, (isBlocked) => {
    if (isBlocked) {
      formState.paymentTerm.partialPaymentsEnabled = false;
      formState.paymentTerm.partialPayments = [];
    }
  });

  const handleSendListEnabled = (index: number) => {
    if (!formState.reconfirmations[index].sendListEnabled) {
      formState.reconfirmations[index].listType = null;
      formState.reconfirmations[index].listSendTimeValue = null;
      formState.reconfirmations[index].listSendTimeUnit = null;
      formState.reconfirmations[index].unassignedQuota = null;
    } else {
      formState.reconfirmations[index].listType = 'preliminary';
    }
  };

  const handleListTypeChange = (index: number) => {
    // Clear unassignedQuota when switching to 'final'
    if (formState.reconfirmations[index].listType === 'final') {
      formState.reconfirmations[index].unassignedQuota = null;
    }
  };

  const addChildrenInclusion = () => {
    const inclusion = {
      description: null,
      isIncluded: false,
      isVisible: false,
    };

    if (formState.children.selectedAgeType === 'infant') {
      formState.children.infantInclusions.push(inclusion);
    } else {
      formState.children.inclusions.push(inclusion);
    }
  };

  const handleInclusionsEnabled = () => {
    if (formState.children.inclusionsEnabled) {
      // Si se activa y no hay tipo seleccionado, seleccionar 'infant' por defecto
      if (!formState.children.selectedAgeType) {
        formState.children.selectedAgeType = 'infant';
      }

      // Agregar inclusión inicial al array correspondiente si está vacío
      const currentArray =
        formState.children.selectedAgeType === 'infant'
          ? formState.children.infantInclusions
          : formState.children.inclusions;

      if (currentArray.length === 0) {
        addChildrenInclusion();
      }
    } else {
      formState.children.inclusions = [];
      formState.children.infantInclusions = [];
    }
  };

  const deleteChildrenInclusion = (index: number) => {
    if (formState.children.selectedAgeType === 'infant') {
      formState.children.infantInclusions.splice(index, 1);
    } else {
      formState.children.inclusions.splice(index, 1);
    }
  };

  // Computed para obtener las inclusiones actuales según el tipo de edad seleccionado
  const currentInclusions = computed(() => {
    return formState.children.selectedAgeType === 'infant'
      ? formState.children.infantInclusions
      : formState.children.inclusions;
  });

  // Manejar cambio de tipo de edad (radio button)
  const handleAgeTypeChange = () => {
    // Si el array del tipo seleccionado está vacío, agregar una inclusión inicial
    const currentArray =
      formState.children.selectedAgeType === 'infant'
        ? formState.children.infantInclusions
        : formState.children.inclusions;

    if (formState.children.inclusionsEnabled && currentArray.length === 0) {
      addChildrenInclusion();
    }
  };

  const handleSaveForm = async () => {
    const request = buildRequestData();

    try {
      startLoading();
      // Limpiar errores del backend antes de guardar
      clearBackendErrors();

      // Use PATCH /policies/:id/rules endpoint with the policy ID from store
      if (!policyId.value) {
        throw new Error('Policy ID is required to save rules');
      }

      const data = await useSupplierPoliciesService.patchSupplierPolicyRules(
        policyId.value,
        request
      );

      if (data.success) {
        handleCompleteResponse();
        setReloadList(true);
        // Note: We don't call loadInitialFormData() here because it uses the old API
        // The form data is already up to date after the save
        return true;
      }
      return false;
    } catch (error: any) {
      // Extraer mensajes del backend si están disponibles
      const backendMessages = error?.response?.data?.message;

      if (backendMessages) {
        const messagesArray = Array.isArray(backendMessages)
          ? backendMessages
          : typeof backendMessages === 'string'
            ? [backendMessages]
            : [];

        if (messagesArray.length > 0) {
          // Mapear mensajes a las secciones correspondientes
          mapBackendMessagesToSections(messagesArray);
          // No mostrar notificación, los errores se mostrarán en form-error-alert
          return;
        }
      }

      // Si no hay mensajes del backend, usar el manejo de errores estándar
      handleError(error);
      return false;
    } finally {
      stopLoading();
    }
  };

  const handleSave = async () => {
    // Validación del frontend eliminada - solo se mostrarán errores del backend
    const success = await handleSaveForm();
    if (success) {
      // Redirigir a la tabla de políticas después de guardar exitosamente
      backToPolicies();
    }
  };

  // Función para el botón "Atrás": siempre vuelve a información básica
  const handleBack = () => {
    openInformationBasic();
  };

  const loadSupplierPolicyRules = async () => {
    if (!policyId.value) return;

    startLoading();
    isEditMode.value = false;

    // Obtener la política del store local usando el ID
    const policy = usePolicyStoreFacade().getPolicyById(String(policyId.value));

    if (policy) {
      isEditMode.value = true;
      isLoadingData.value = true; // Evitar que watchers limpien valores durante la carga
      resetBaseFormErrors();

      const rules = policy.rules || {};

      // 1. Payment Terms - soportar nueva estructura del API
      const pt = rules.payment || rules.payment_term || rules.paymentTerm || {};

      // Mapear type a paymentTypeId (prepaid -> 1, credit -> 2, cash/postpaid -> 3)
      const mapPaymentType = (type: string | number | null): number | null => {
        if (typeof type === 'number') return type;
        const typeMap: Record<string, number> = {
          prepaid: 1,
          credit: 2,
          cash: 3,
          postpaid: 3, // postpaid también es Contado
        };
        return type ? typeMap[type] || null : null;
      };

      // Mapear deadline.reference a conditionTypeId (before_service -> 1, after_invoice -> 2, at_booking -> 3)
      const mapConditionType = (reference: string | number | null): number | null => {
        if (typeof reference === 'number') return reference;
        const refMap: Record<string, number> = {
          before_service: 1,
          after_invoice: 2,
          at_booking: 3,
        };
        return reference ? refMap[reference] || null : null;
      };

      const paymentTypeId = mapPaymentType(pt.type || pt.payment_type_id || pt.paymentTypeId);
      const conditionTypeId = mapConditionType(
        pt.deadline?.reference || pt.condition_type_id || pt.conditionTypeId
      );
      const conditionValue = pt.deadline?.value || pt.condition_value || pt.conditionValue || null;

      // splitPayment en nueva estructura
      const splitPayment = pt.splitPayment || {};
      const partialPaymentsEnabled =
        splitPayment.enabled ?? pt.partial_payments_enabled ?? pt.partialPaymentsEnabled ?? false;
      const installments =
        splitPayment.installments || pt.partial_payments || pt.partialPayments || [];

      // Solo marcar como no editable si tiene datos guardados
      const hasPaymentData =
        paymentTypeId !== null || conditionTypeId !== null || conditionValue !== null;

      // Si no hay datos del backend, mantener campos sin selección para mostrar placeholders
      const finalPaymentTypeId = paymentTypeId ?? null;
      const finalConditionTypeId = conditionTypeId ?? null;

      Object.assign(formState.paymentTerm, {
        id: pt.id || null,
        paymentTypeId: finalPaymentTypeId,
        paymentTypeName: findLabel(paymentTypes, finalPaymentTypeId),
        conditionTypeId: finalConditionTypeId,
        conditionTypeName: findLabel(conditionTypes, finalConditionTypeId),
        conditionValue,
        partialPaymentsEnabled,
        partialPayments: installments.map((pp: any) => ({
          partialConditionTypeId: mapConditionType(
            pp.timing?.reference || pp.partial_condition_type_id || pp.partialConditionTypeId
          ),
          partialConditionValue:
            pp.timing?.value || pp.partial_condition_value || pp.partialConditionValue || null,
          partialAmountType: (() => {
            // API retorna 'percent' pero el select espera 'percentage'
            const unit = pp.amount?.unit || pp.partial_amount_type || pp.partialAmountType || null;
            return unit === 'percent' ? 'percentage' : unit;
          })(),
          partialAmount: pp.amount?.value || pp.partial_amount || pp.partialAmount || null,
        })),
        isEditable: !hasPaymentData, // Modo edición si no tiene datos guardados
      });

      // 2. Cancellations
      const canc = rules.cancellation || rules.cancellations || {};
      const actualRules = Array.isArray(canc) ? canc : canc.rules || [];

      formState.cancellations = actualRules.map((r: any) => ({
        timeLimitValue: r.deadline?.value || r.time_limit_value || r.timeLimitValue,
        timeLimitUnit: r.deadline?.unit || r.time_limit_unit || r.timeLimitUnit,
        penaltyValue: r.penalty?.value || r.penalty_value || r.penaltyValue,
        penaltyType:
          r.penalty?.unit === 'percent'
            ? ValueTypeEnum.PERCENTAGE
            : r.penalty?.unit || r.penalty_type || r.penaltyType,
        cancellationScope: r.scope?.type || r.cancellation_scope || r.cancellationScope || 'total',
        cancellationPartialValue:
          r.scope?.quantity || r.cancellation_partial_value || r.cancellationPartialValue,
        cancellationPartialUnit:
          r.scope?.unit || r.cancellation_partial_unit || r.cancellationPartialUnit,
        automaticReconfirmation:
          canc.automaticReconfirmation ?? canc.automatic_reconfirmation ?? false,
        isEditable: false,
      }));

      // Sincronizar errores de cancellations
      if (formState.cancellations.length === 0) {
        addCancellation();
      } else {
        formState.cancellations.forEach(() => addCancellationError());
      }

      // 3. Reconfirmations
      const reconf = rules.reconfirmation || rules.reconfirmations || {};
      // Soportar nueva estructura con enabled y rules
      const reconfRules = Array.isArray(reconf) ? reconf : reconf.rules || [];
      const reconfEnabled = typeof reconf.enabled === 'boolean' ? reconf.enabled : true;

      // Determinar si automaticReconfirmation está activo
      const hasAutomaticReconfirmation =
        canc.automaticReconfirmation ?? canc.automatic_reconfirmation ?? false;

      // Si automaticReconfirmation está activo, se ignora reconfirmations
      // Si enabled es false desde el backend, marcar como "No aplica"
      formState.reconfirmationsNotApplicable = hasAutomaticReconfirmation ? false : !reconfEnabled;

      formState.reconfirmations = reconfRules.map((r: any) => {
        const pl = r.passengerList || r.passenger_list || {};
        const pld = pl.details || pl.passenger_list_details || {};
        return {
          confirmationType: r.type || r.confirmation_type || r.confirmationType,
          timeValue: r.deadline?.value || r.time_value || r.timeValue,
          timeUnit: r.deadline?.unit || r.time_unit || r.timeUnit,
          sendListEnabled: pl.enabled || r.send_list_enabled || r.sendListEnabled || false,
          listType: pld.type || r.list_type || r.listType,
          listSendTimeValue: pld.deadline?.value || r.list_send_time_value || r.listSendTimeValue,
          listSendTimeUnit: pld.deadline?.unit || r.list_send_time_unit || r.listSendTimeUnit,
          unassignedQuota: pld.unassignedQuota || r.unassigned_quota || r.unassignedQuota,
          isEditable: false,
        };
      });

      // Sincronizar errores de reconfirmations
      if (formState.reconfirmations.length === 0) {
        addReconfirmation();
      } else {
        formState.reconfirmations.forEach(() => addReconfirmationError());
      }

      // 4. Released
      const rel = rules.released || {};
      // Soportar nueva estructura con enabled y rules
      const relRules = Array.isArray(rel) ? rel : rel.rules || [];
      const relEnabled = typeof rel.enabled === 'boolean' ? rel.enabled : true;

      // Si enabled es false desde el backend, marcar como "No aplica"
      formState.releasedNotApplicable = !relEnabled;

      formState.released = relRules.map((r: any) => ({
        id: r.id,
        timeLimitValue: r.time_limit_value || r.timeLimitValue,
        releaseType: r.release_type || r.releaseType,
        releaseQuantity: r.release_quantity || r.releaseQuantity,
        benefitType: r.benefit_type || r.benefitType,
        roomOccupancyType: r.room_occupancy_type || r.roomOccupancyType,
        roomBenefitType: r.room_benefit_type || r.roomBenefitType,
        hasMaximumCap: r.has_maximum_cap || r.hasMaximumCap || false,
        maximumCapValue: r.maximum_cap_value || r.maximumCapValue,
        isEditable: false,
      }));

      // Sincronizar errores de released
      if (formState.released.length === 0) {
        addReleased();
      } else {
        formState.released.forEach(() => addReleasedError());
      }

      // 5. Children - Nueva estructura de la API
      const child = rules.children || {};
      const ranges = child.ranges || {};

      // Separar inclusiones por categoría: child e infant
      const allInclusions = child.inclusions || [];
      const childInclusions = allInclusions
        .filter((inc: any) => inc.appliesToCategory === 'child')
        .map((inc: any) => ({
          id: inc.id,
          description: inc.itemCode,
          isIncluded: inc.isIncluded ?? false,
          isVisible: inc.isVisible ?? false,
        }));

      const infantInclusionsFiltered = allInclusions
        .filter((inc: any) => inc.appliesToCategory === 'infant')
        .map((inc: any) => ({
          id: inc.id,
          description: inc.itemCode,
          isIncluded: inc.isIncluded ?? false,
          isVisible: inc.isVisible ?? false,
        }));

      // Solo marcar como no editable si tiene datos guardados
      const hasChildrenData =
        (ranges.infant?.minAge !== null && ranges.infant?.minAge !== undefined) ||
        (ranges.infant?.maxAge !== null && ranges.infant?.maxAge !== undefined) ||
        (ranges.child?.minAge !== null && ranges.child?.minAge !== undefined) ||
        (ranges.child?.maxAge !== null && ranges.child?.maxAge !== undefined) ||
        allInclusions.length > 0;

      Object.assign(formState.children, {
        id: child.id,
        // Nueva estructura: ranges.infant/child con minAge/maxAge
        infantAgeMin: ranges.infant?.minAge ?? null,
        infantAgeMax: ranges.infant?.maxAge ?? null,
        childAgeMin: ranges.child?.minAge ?? null,
        childAgeMax: ranges.child?.maxAge ?? null,
        additionalInformation: child.additionalInformation ?? null,
        inclusionsEnabled: child.inclusionsEnabled ?? false,
        selectedAgeType:
          infantInclusionsFiltered.length > 0
            ? 'infant'
            : childInclusions.length > 0
              ? 'child'
              : null,
        inclusions: childInclusions,
        infantInclusions: infantInclusionsFiltered,
        isEditable: !hasChildrenData, // Modo edición si no tiene datos guardados
      });

      // 6. Additional Rules - soportar nueva estructura con enabled y rules
      const additionalRulesContainer = rules.additionalRules || {};
      const additionalRulesArray = Array.isArray(additionalRulesContainer)
        ? additionalRulesContainer
        : additionalRulesContainer.rules || [];
      formState.additionalRules = additionalRulesArray.map((rule: any, index: number) => ({
        id: rule.id ?? index,
        policyId: index,
        policyName:
          rule.ruleCode
            ?.replace(/_/g, ' ')
            .split(' ')
            .map((word: string) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ') ?? `Regla ${index + 1}`,
        ruleCode: rule.ruleCode ?? '',
        isActive: rule.isActive ?? true,
        contentHtml: rule.contentHtml ?? '',
        additionalInformation: rule.contentHtml ?? '',
        isEditable: false,
      }));

      // Usar nextTick para asegurar que los watchers se ejecuten con la bandera activa
      nextTick(() => {
        isLoadingData.value = false; // Restaurar comportamiento normal de watchers
      });
    }

    stopLoading();
  };

  watch(
    policyId,
    () => {
      loadInitialFormData();
    },
    { immediate: true }
  );

  // ... existing code ...

  // Watcher para auto-seleccionar el primer conditionTypeId cuando cambia el tipo de pago
  watch(
    () => formState.paymentTerm.paymentTypeId,
    (newPaymentTypeId) => {
      // No cambiar durante la carga de datos (al editar una política existente)
      if (isLoadingData.value) return;

      // Sin tipo de pago seleccionado, mantener campos limpios
      if (!newPaymentTypeId) {
        formState.paymentTerm.conditionTypeId = null;
        formState.paymentTerm.conditionValue = null;
        return;
      }

      // Si el nuevo tipo de pago es "Contado" (3), no necesita conditionTypeId
      if (newPaymentTypeId === 3) {
        formState.paymentTerm.conditionTypeId = null;
        formState.paymentTerm.conditionValue = null;
        return;
      }

      // Auto-seleccionar el primer valor de las opciones filtradas disponibles
      const availableOptions = filteredConditionTypes.value;
      if (availableOptions.length > 0) {
        formState.paymentTerm.conditionTypeId = Number(availableOptions[0].value);
      } else {
        formState.paymentTerm.conditionTypeId = null;
      }
    }
  );

  onMounted(async () => {
    await fetchResourcesData();
  });

  const isPartialConditionValueDisabled = (index: number) => {
    return formState.paymentTerm.partialPayments[index]?.partialConditionTypeId === 3; // 3: Al momento de la reserva
  };

  watch(
    () => formState.paymentTerm.partialPayments,
    (newVal) => {
      // Crear una copia del array para evitar mutaciones durante la iteración
      const paymentsToUpdate = [...newVal];
      paymentsToUpdate.forEach((payment, index) => {
        if (payment.partialConditionTypeId === 3 && payment.partialConditionValue !== null) {
          // Limpiar el valor cuando el tipo de condición es "Al momento de la reserva" (3)
          formState.paymentTerm.partialPayments[index].partialConditionValue = null;
        }
      });
    },
    { deep: true }
  );

  // Watcher para limpiar valores parciales de cancelación cuando el scope cambia a 'total'
  watch(
    () => formState.cancellations,
    (newVal) => {
      newVal.forEach((cancellation) => {
        if (cancellation.cancellationScope !== 'partial') {
          // Limpiar valores parciales cuando el scope no es 'partial'
          if (cancellation.cancellationPartialValue !== null) {
            cancellation.cancellationPartialValue = null;
          }
          if (cancellation.cancellationPartialUnit !== null) {
            cancellation.cancellationPartialUnit = null;
          }
        }
      });
    },
    { deep: true }
  );

  watch(
    () => formState.children.infantAgeMin,
    (newInfantAgeMin) => {
      if (
        newInfantAgeMin !== null &&
        formState.children.infantAgeMax !== null &&
        formState.children.infantAgeMax < newInfantAgeMin
      ) {
        formState.children.infantAgeMax = newInfantAgeMin;
      }
    }
  );

  watch(
    () => formState.children.infantAgeMax,
    (newInfantAgeMax) => {
      const minimumChildAge = (newInfantAgeMax ?? 0) + 1;

      if (
        formState.children.childAgeMin !== null &&
        formState.children.childAgeMin < minimumChildAge
      ) {
        formState.children.childAgeMin = minimumChildAge;
      }

      if (
        formState.children.childAgeMax !== null &&
        formState.children.childAgeMax < minimumChildAge
      ) {
        formState.children.childAgeMax = minimumChildAge;
      }
    }
  );

  watch(
    () => formState.children.childAgeMin,
    (newChildAgeMin) => {
      if (
        newChildAgeMin !== null &&
        formState.children.childAgeMax !== null &&
        formState.children.childAgeMax < newChildAgeMin
      ) {
        formState.children.childAgeMax = newChildAgeMin;
      }
    }
  );

  return {
    isEditMode,
    isLoading,
    formState,
    conditionTypes,
    filteredConditionTypes,
    paymentTypes,
    valueTypes,
    timeUnits,
    confirmationTypes,
    releaseTypes,
    benefitTypes,
    childrenInclusions,
    cancellationPenaltyScopes,
    cancellationScopes,
    roomOccupancyTypes,
    roomBenefitTypes,
    policiesClone,
    availableAdditionalPolicies,
    // ... rest of return
    isAdditionalRulesDrawerOpen,
    selectedAdditionalPolicies,
    showPartialPaymentInputs,
    showCancellationPenaltyScopeSelector,
    showCancellationScope: showCancellationPenaltyScopeSelector, // Alias for semantic clarity
    showRoomExtraSelectors,
    isPartialConditionValueDisabled, // Expose
    formErrors,
    errorsPaymentTerm,
    errorsPartialPayments,
    errorsPartialPaymentsRows,
    errorsCancellations,
    errorsReconfirmations,
    errorsReleased,
    errorsChildren,
    addCancellation,
    addReconfirmation,
    addReleased,
    deleteCancellation,
    deleteReconfirmation,
    deleteReleased,
    handleSave,
    getInputNumberErrorClass,
    getSelectErrorClass,
    getValueTypeName,
    getTimeUnitName,
    getConfirmationTypeName,
    getReleaseTypeName,
    getBenefitTypeName,
    formatValueWithSymbol,
    toRegularSingularText,
    applyPluralToText,
    enablePaymentTermEdit,
    enableCancellationEdit,
    enableReconfirmationEdit,
    enableReleasedEdit,
    enableChildrenEdit,
    enableAdditionalRuleEdit,
    deleteAdditionalRule,
    handlePartialPaymentsEnabled,
    handleSendListEnabled,
    handleListTypeChange,
    handleInclusionsEnabled,
    addChildrenInclusion,
    deleteChildrenInclusion,
    currentInclusions,
    handleAgeTypeChange,
    openAdditionalRulesDrawer,
    closeAdditionalRulesDrawer,
    handleAdditionalRulesSave,
    backToPolicies,
    handleBack,
    addPartialPayment,
    deletePartialPayment,
    getPartialConditionOptions,
    canAddPartialPayment,
    isPartialPaymentsBlocked,
  };
}
