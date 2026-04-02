export const STEP_IDS = {
  SERVICE_DETAILS: 'service-details',
  CONFIGURATION: 'configuration',
  CONTENT: 'content',
  PRICING_PLANS: 'pricing-plans',
  IMAGES: 'images',
} as const;

const STEP_ORDER: readonly string[] = [
  STEP_IDS.SERVICE_DETAILS,
  STEP_IDS.CONFIGURATION,
  STEP_IDS.CONTENT,
  STEP_IDS.PRICING_PLANS,
  STEP_IDS.IMAGES,
];

export const isStepEnabled = (stepId: string, completedSteps: string[]): boolean => {
  const stepIndex = STEP_ORDER.indexOf(stepId);

  // Si el paso no está en el orden, no está habilitado
  if (stepIndex === -1) {
    return false;
  }

  // El primer paso siempre está habilitado
  if (stepIndex === 0) {
    return true;
  }

  // Verificar que todos los pasos previos estén completados
  for (let i = 0; i < stepIndex; i++) {
    if (!completedSteps.includes(STEP_ORDER[i])) {
      return false;
    }
  }

  return true;
};

export const getNextStepId = (currentStepId: string): string | null => {
  const currentIndex = STEP_ORDER.indexOf(currentStepId);

  if (currentIndex === -1 || currentIndex === STEP_ORDER.length - 1) {
    return null;
  }

  return STEP_ORDER[currentIndex + 1];
};

export const canEditStep = (stepId: string, completedSteps: string[]): boolean => {
  const stepIndex = STEP_ORDER.indexOf(stepId);

  if (stepIndex === -1) {
    return false;
  }

  // Si el paso no está completado, no puede editarse
  if (!completedSteps.includes(stepId)) {
    return false;
  }

  // Pasos 3 y 4 (content y pricing-plans) pueden editarse si están completados
  // y los pasos 1 y 2 también están completados
  if (stepIndex >= 2) {
    return (
      completedSteps.includes(STEP_IDS.SERVICE_DETAILS) &&
      completedSteps.includes(STEP_IDS.CONFIGURATION)
    );
  }

  // Pasos 1 y 2 pueden editarse si están completados
  return true;
};
