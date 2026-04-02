import { notification } from 'ant-design-vue';
import { CheckCircleFilled, CloseOutlined } from '@ant-design/icons-vue';
import { h } from 'vue';
import type { HttpFieldMessageError } from '@/modules/negotiations/interfaces/api-response.interface';
import { HTTP_STATUS } from '@/modules/negotiations/constants';
import {
  notifyGenericError,
  processBadRequestFieldMessages,
  processGeneralFieldMessages,
} from '@/modules/negotiations/helpers/responseApiHelper';

export interface response {
  success: boolean;
  data: object;
  error: string;
  code: number;
}

export interface ValidationErrors {
  [key: string]: string[];
}

export interface HttpError {
  response?: {
    status: number;
    data: {
      data?: ValidationErrors;
      error?: string | null;
      message?: string | string[];
      success?: boolean;
      statusCode?: number;
      timestamp?: string;
      path?: string;
    };
  };
  message?: string;
}

const title: string = 'Aurora';

export const handleSuccessResponse = (response?: response) => {
  console.log(response);
  notification.success({
    message: title,
    description: 'Los datos se han guardado correctamente.',
  });
};

export const handleCompleteResponse = (response?: response, message?: any) => {
  notification.open({
    icon: () => h(CheckCircleFilled, { style: 'color: #288A5F' }),
    closeIcon: () => h(CloseOutlined, { style: 'color: #2F353A' }),
    message: message ?? 'Los cambios fueron guardados correctamente.',
    style: {
      background: '#DFFFE9',
      borderRadius: '4px',
    },
  });
};

export const handleErrorResponse = (message?: string) => {
  notification.open({
    icon: () => h(CloseOutlined, { style: 'color: #D32029' }),
    closeIcon: () => h(CloseOutlined, { style: 'color: #2F353A' }),
    message: message ?? 'Ocurrió un error al procesar la solicitud.',
    style: {
      background: '#FFEAEA',
      borderRadius: '4px',
    },
  });
};

export const handleError = (error: HttpError) => {
  if (!error.response) {
    notification.error({
      message: title,
      description: 'Error al procesar la solicitud: ' + (error.message || 'Error desconocido'),
    });
    console.error('', error);
    return;
  }

  const { status, data } = error.response;
  const backendMessage = data?.message;

  // Función para normalizar mensajes de error específicos
  const normalizeErrorMessage = (message: string): string => {
    const lowerMessage = message.toLowerCase();

    // Detectar mensajes relacionados con "Failed to fetch supplier from external service"
    if (lowerMessage.includes('failed to fetch supplier from external service')) {
      return 'No se pudo obtener el proveedor del servicio externo.';
    }

    // Normalizar mensajes técnicos comunes de políticas
    if (lowerMessage.includes('policy') || lowerMessage.includes('política')) {
      // Mapeo de campos técnicos comunes a nombres amigables
      const fieldMappings: Record<string, string> = {
        payment_term: 'Condición de pago',
        cancellation: 'Cancelación',
        reconfirmation: 'Reconfirmación',
        released: 'Liberados',
        children: 'Edades',
        'is required': 'es obligatorio',
        'must be a number': 'debe ser un número',
        'must be a string': 'debe ser texto',
        'should not be empty': 'no debe estar vacío',
        'is invalid': 'es inválido',
      };

      let normalized = message;
      Object.entries(fieldMappings).forEach(([technical, friendly]) => {
        const regex = new RegExp(technical.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
        normalized = normalized.replace(regex, friendly);
      });

      // Limpiar caracteres técnicos
      normalized = normalized
        .replace(/\[(\d+)\]/g, ' $1')
        .replace(/\./g, ' ')
        .replace(/_/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

      // Capitalizar primera letra
      if (normalized.length > 0) {
        normalized = normalized.charAt(0).toUpperCase() + normalized.slice(1);
      }

      return normalized;
    }

    return message;
  };

  // Si hay mensajes del backend (especialmente para validaciones y errores 500)
  if (backendMessage) {
    if (Array.isArray(backendMessage) && backendMessage.length > 0) {
      // Mostrar todos los mensajes del array, normalizando mensajes específicos
      backendMessage.forEach((msg) => {
        const normalizedMsg = normalizeErrorMessage(msg);
        notification.error({
          message: title,
          description: normalizedMsg,
        });
      });
      return;
    } else if (typeof backendMessage === 'string' && backendMessage.trim()) {
      // Mostrar el mensaje string directamente, normalizando si es necesario
      const normalizedMsg = normalizeErrorMessage(backendMessage);
      notification.error({
        message: title,
        description: normalizedMsg,
      });
      return;
    }
  }

  // Manejo específico por código de estado
  if (status === 422) {
    const validationErrors = data?.data;
    if (validationErrors) {
      handleValidationErrors(validationErrors);
    } else {
      notification.error({
        message: title,
        description: 'Error de validación en los datos enviados.',
      });
    }
  } else if (status === 400) {
    notification.error({
      message: title,
      description: 'Error al procesar la solicitud: ' + (data?.error ?? 'Error en la solicitud'),
    });
  } else {
    // Para otros errores (500, etc.) mostrar mensaje genérico solo si no hay mensaje del backend
    notification.error({
      message: title,
      description: data?.error || 'Error al procesar la solicitud.',
    });
    console.error('', error);
  }
};

export const handleErrorMessage = (message: any) => {
  if (Array.isArray(message) && message.length > 0) {
    message.forEach((msg) => {
      notification.error({
        message: 'Error al procesar la solicitud',
        description: msg.message,
      });
    });
  } else {
    notification.error({
      message: 'Error al procesar la solicitud',
      description: message,
    });
  }
};

const handleValidationErrors = (validationResponse?: ValidationErrors) => {
  if (validationResponse) {
    Object.values(validationResponse).forEach((errors) => {
      if (Array.isArray(errors)) {
        errors.forEach((error) => {
          notification.warning({
            message: title,
            description: error,
          });
        });
      }
    });
  }
};

// handle errors from Nest api
export const handleFieldMessageErrors = (error: HttpFieldMessageError) => {
  const { response } = error;
  const status = response?.status;
  const messages = response?.data?.message;

  if (!Array.isArray(messages) || messages.length === 0) {
    notifyGenericError(title, error);
    return;
  }

  if (status === HTTP_STATUS.BAD_REQUEST) {
    processBadRequestFieldMessages(title, messages);
    return;
  }

  processGeneralFieldMessages(title, messages, error);
};

export const handleDeleteResponse = (response?: response) => {
  if (response?.success) {
    notification.success({
      message: title,
      description: 'Los datos se han eliminado correctamente.',
    });
  }
};

export default { handleSuccessResponse, handleError, handleDeleteResponse };
