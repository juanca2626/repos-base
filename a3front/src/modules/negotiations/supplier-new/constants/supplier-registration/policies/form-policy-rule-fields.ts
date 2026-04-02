export const cancellationRequiredFields = [
  'timeLimitUnit',
  'timeLimitValue',
  'penaltyType',
  'penaltyValue',
];

export const reconfirmationRequiredFields = ['confirmationType', 'timeUnit', 'timeValue'];

export const releasedRequiredFields = ['timeLimitValue', 'releaseType', 'releaseQuantity'];

export const requiredMainPaymentTermFields = ['paymentTypeId', 'conditionTypeId', 'conditionValue'];

export const requiredAllPaymentTermFields = [
  ...requiredMainPaymentTermFields,
  'partialConditionTypeId',
  'partialConditionValue',
  'partialAmountType',
  'partialAmount',
];

export const requiredChildrenInfantFields = ['infantAgeMin', 'infantAgeMax'];

export const requiredChildrenChildFields = ['childAgeMin', 'childAgeMax'];
