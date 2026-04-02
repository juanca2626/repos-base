import { notification } from 'ant-design-vue';

export const handleError = (error) => {
  if (error.type === 'validation' && error.errors) {
    handleValidationErrors(error.errors);
  } else if (typeof error === 'string') {
    // Si el error es un string, pásalo directamente a handleValidationErrors
    handleValidationErrors({ general: error });
  } else {
    notification.error({
      message: 'Error',
      description: error.message || 'Ocurrió un error inesperado. Por favor, intente nuevamente.',
    });
  }
};

export const handleValidationErrors = (errors) => {
  Object.entries(errors).forEach(([field, messages]) => {
    if (Array.isArray(messages)) {
      messages.forEach((message) => {
        notification.error({
          message: `Error en ${formatFieldName(field)}`,
          description: message,
        });
      });
    } else if (typeof messages === 'string') {
      notification.error({
        message: `Error en ${formatFieldName(field)}`,
        description: messages,
      });
    }
  });
};

export const formatFieldName = (field) => {
  return field
    .split('_')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};
