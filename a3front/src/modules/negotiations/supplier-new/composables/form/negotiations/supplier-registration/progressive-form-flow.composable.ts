import { computed } from 'vue';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';

export function useProgressiveFormFlowComposable() {
  const { isEditMode, getSavedFormComponent, getIsEditFormComponent } =
    useSupplierGlobalComposable();

  /**
   * Define el orden de los componentes en el flujo de registro
   */
  const formComponentsOrder = [
    FormComponentEnum.CLASSIFICATION_SUPPLIER,
    FormComponentEnum.GENERAL_INFORMATION,
    FormComponentEnum.COMMERCIAL_LOCATION,
    FormComponentEnum.CONTACT_INFORMATION,
    // FormComponentEnum.COMMERCIAL_INFORMATION, // Este es dinámico
    FormComponentEnum.MODULE_SERVICES,
    FormComponentEnum.MODULE_POLICIES,
  ] as const;

  /**
   * Verifica si un componente específico debe ser visible
   *
   * Lógica:
   * - En modo EDICIÓN: todos los componentes son visibles
   * - En modo REGISTRO: solo es visible si:
   *   1. Es el primer componente (CLASSIFICATION_SUPPLIER)
   *   2. O el componente anterior está guardado
   */
  const isComponentVisible = (componentKey: FormComponentEnum): boolean => {
    // En modo edición, todos los componentes son visibles
    if (isEditMode.value) {
      return true;
    }

    // En modo registro, aplicar lógica progresiva
    const componentIndex = formComponentsOrder.indexOf(componentKey as any);

    // El primer componente (clasificación) siempre es visible
    if (componentIndex === 0) {
      return true;
    }

    // Si no se encuentra en el orden, no es visible
    if (componentIndex === -1) {
      return false;
    }

    // Para los demás componentes, verificar si el anterior está guardado
    const previousComponentKey = formComponentsOrder[componentIndex - 1];
    const isPreviousSaved = getSavedFormComponent.value(previousComponentKey);

    return isPreviousSaved;
  };

  /**
   * Computed para verificar si la clasificación está completa
   * (necesario para mostrar los demás componentes en modo registro)
   */
  const isClassificationComplete = computed(() =>
    getSavedFormComponent.value(FormComponentEnum.CLASSIFICATION_SUPPLIER)
  );

  /**
   * Computed para verificar si GeneralInformation está completa
   */
  const isGeneralInformationComplete = computed(() =>
    getSavedFormComponent.value(FormComponentEnum.GENERAL_INFORMATION)
  );

  /**
   * Computed para verificar si CommercialLocation está completa
   */
  const isLocationComplete = computed(() =>
    getSavedFormComponent.value(FormComponentEnum.COMMERCIAL_LOCATION)
  );

  /**
   * Computed para verificar si ContactInformation está completa
   */
  const isContactsComplete = computed(() =>
    getSavedFormComponent.value(FormComponentEnum.CONTACT_INFORMATION)
  );

  /**
   * Computed para verificar si CommercialInformation está completa
   */
  const isCommercialInformationComplete = computed(() =>
    getSavedFormComponent.value(FormComponentEnum.CONTACT_INFORMATION)
  );

  /**
   * Computed para verificar si Services está completa
   */
  const isServicesComplete = computed(() =>
    getSavedFormComponent.value(FormComponentEnum.MODULE_SERVICES)
  );

  /**
   * Computed para verificar si GeneralInformation debe ser visible
   */
  const showGeneralInformation = computed(() =>
    isComponentVisible(FormComponentEnum.GENERAL_INFORMATION)
  );

  /**
   * Computed para verificar si CommercialLocation debe ser visible
   */
  const showCommercialLocation = computed(() =>
    isComponentVisible(FormComponentEnum.COMMERCIAL_LOCATION)
  );

  /**
   * Computed para verificar si ContactInformation debe ser visible
   */
  const showContactInformation = computed(() =>
    isComponentVisible(FormComponentEnum.CONTACT_INFORMATION)
  );

  /**
   * Computed para verificar si CommercialInformation (dinámico) debe ser visible
   * Este componente aparece después de ContactInformation
   */
  const showCommercialInformation = computed(() => {
    if (isEditMode.value) {
      return true;
    }
    return getSavedFormComponent.value(FormComponentEnum.CONTACT_INFORMATION);
  });

  /**
   * Computed para verificar si Services debe ser visible
   */
  const showServices = computed(() => {
    if (isEditMode.value) {
      return true;
    }

    // Services aparece después de CommercialInformation (si existe) o ContactInformation
    // Por simplicidad, verificamos si ContactInformation está guardado
    return getSavedFormComponent.value(FormComponentEnum.CONTACT_INFORMATION);
  });

  /**
   * Computed para verificar si SupplierPolicies debe ser visible
   */
  const showSupplierPolicies = computed(() =>
    isComponentVisible(FormComponentEnum.MODULE_POLICIES)
  );

  /**
   * Verifica si un componente está en modo lectura
   */
  const isComponentReadOnly = (componentKey: FormComponentEnum): boolean => {
    return getIsEditFormComponent.value(componentKey);
  };

  /**
   * Verifica si todos los componentes obligatorios están completos
   */
  const isFormComplete = computed(() => {
    return formComponentsOrder.every((key) => getSavedFormComponent.value(key));
  });

  /**
   * Obtiene el porcentaje de progreso del formulario
   */
  const progressPercentage = computed(() => {
    const completedCount = formComponentsOrder.filter((key) =>
      getSavedFormComponent.value(key)
    ).length;

    return Math.round((completedCount / formComponentsOrder.length) * 100);
  });

  return {
    // Estados de completitud
    isClassificationComplete,
    isGeneralInformationComplete,
    isLocationComplete,
    isContactsComplete,
    isCommercialInformationComplete,
    isServicesComplete,

    // Visibilidad de componentes
    showGeneralInformation,
    showCommercialLocation,
    showContactInformation,
    showCommercialInformation,
    showServices,
    showSupplierPolicies,

    // Utilidades
    isComponentVisible,
    isComponentReadOnly,
    isFormComplete,
    progressPercentage,

    // Constantes
    formComponentsOrder,
  };
}
