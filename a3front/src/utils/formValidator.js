import { ref } from 'vue';

export function useFormValidator(initialData = {}, validations = {}) {
  const formData = ref({ ...initialData });
  const errors = ref(
    Object.keys(initialData).reduce((acc, key) => {
      acc[key] = '';
      return acc;
    }, {})
  );

  const validateField = (fieldName) => {
    if (!validations[fieldName]) return true;

    const value = formData.value[fieldName];
    const rules = Array.isArray(validations[fieldName])
      ? validations[fieldName]
      : [validations[fieldName]];

    for (const rule of rules) {
      const result = typeof rule === 'function' ? rule(value) : rule;
      if (result !== true) {
        errors.value[fieldName] = result || 'Valor inválido';
        return false;
      }
    }

    errors.value[fieldName] = '';
    return true;
  };

  const validateForm = () => {
    return Object.keys(validations)
      .map((field) => validateField(field))
      .every((isValid) => isValid);
  };

  const resetForm = () => {
    formData.value = { ...initialData };
    Object.keys(errors.value).forEach((key) => {
      errors.value[key] = '';
    });
  };

  return {
    formData,
    errors,
    validateField,
    validateForm,
    resetForm,
    hasErrors: () => Object.values(errors.value).some((error) => error),
  };
}
