import { notification } from 'ant-design-vue';
import type { ValidationErrors, HttpError } from 'src/modules/negotiations/api/responseApi';
import type {
  FieldMessageError,
  HttpFieldMessageError,
} from '@/modules/negotiations/interfaces/api-response.interface';

export const parseValidationErrors = (validationResponse?: ValidationErrors): string[] => {
  const errorMessages: string[] = [];

  if (validationResponse) {
    Object.values(validationResponse).forEach((errors) => {
      if (Array.isArray(errors)) {
        errors.forEach((error) => {
          errorMessages.push(error);
        });
      }
    });
  }

  return errorMessages;
};

export const parseErrors = (error: HttpError): string[] => {
  const unknownError = 'Error desconocido';
  const errors: string[] = [];

  if (error.response && error.response.status === 422) {
    errors.push(...parseValidationErrors(error.response.data.data));
  } else if (error.response && [400, 500].includes(error.response.status)) {
    errors.push(error.response.data.error || `${unknownError}: ${error}`);
  } else {
    errors.push(`${unknownError}: ${error}`);
  }

  return errors;
};

export const notifyGenericError = (title: string, error: unknown) => {
  notification.error({
    message: title,
    description: `Error al procesar la solicitud: ${error}`,
  });
  console.error('HTTP error:', error);
};

export const showFieldMessages = (title: string, messages: FieldMessageError[]) => {
  messages.forEach(({ field, message }) => {
    notification.warning({
      message: title,
      description: `${field}: ${message}`,
    });
  });
};

export const showPlainMessages = (title: string, messages: string[]) => {
  messages.forEach((description) => {
    notification.error({
      message: title,
      description,
    });
  });
};

export const processBadRequestFieldMessages = (
  title: string,
  messages: FieldMessageError[] | string[]
) => {
  if (isTypeFieldMessage(messages[0])) {
    showFieldMessages(title, messages as FieldMessageError[]);
  } else {
    showPlainMessages(title, messages as string[]);
  }
};

export const processGeneralFieldMessages = (
  title: string,
  messages: FieldMessageError[] | string[],
  error: HttpFieldMessageError
) => {
  if (typeof messages[0] === 'string') {
    showPlainMessages(title, messages as string[]);
  } else {
    notifyGenericError(title, error);
  }
};

export const isTypeFieldMessage = (value: unknown): value is FieldMessageError => {
  return typeof value === 'object' && value !== null && 'field' in value;
};
