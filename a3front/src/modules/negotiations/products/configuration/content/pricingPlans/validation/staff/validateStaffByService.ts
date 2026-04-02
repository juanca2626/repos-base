import { validateStaffBase } from './validateStaffBase';
import { validateStaffPackage } from './validateStaffPackage';

export function validateStaffStep(state: any, serviceType: string) {
  const validators = [validateStaffBase];

  if (serviceType === 'PACKAGE') {
    validators.push(validateStaffPackage);
  }

  const errors: any = {};

  validators.forEach((validator) => {
    const result = validator(state);

    Object.assign(errors, result.errors);
  });

  return {
    valid: Object.keys(errors).length === 0,
    errors,
  };
}
