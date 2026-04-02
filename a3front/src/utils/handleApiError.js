import { notification } from 'ant-design-vue';

export function handleApiError(error) {
  if (error.response) {
    const status = error.response.status;
    const data = error.response.data;

    const baseError = {
      success: false,
      status,
      code: status,
      data,
    };

    switch (status) {
      case 401:
        notification.info({
          message: 'Conexión Perdida',
          description: 'Se ha perdido la sesión, la página se actualizará para volver a ingresar.',
        });

        setTimeout(() => {
          window.location.href = '/login';
        }, 10000);
        return Promise.reject({ ...baseError, type: 'authentication', message: 'No autorizado.' });

      case 403:
        return Promise.reject({ ...baseError, type: 'authorization', message: 'No autorizado.' });

      case 404:
        return Promise.reject({ ...baseError, type: 'not_found', message: 'No encontrado.' });

      case 422:
        return Promise.reject({
          ...baseError,
          type: 'validation',
          errors: data.error || data,
        });

      default:
        if (status >= 500) {
          return Promise.reject({
            ...baseError,
            type: 'server_error',
            message: 'Error en el servidor.',
          });
        } else {
          return Promise.reject({
            ...baseError,
            type: 'client_error',
            message: 'Error del cliente.',
          });
        }
    }
  } else if (error.request) {
    notification.error({
      message: 'Sin respuesta',
      description: 'No se recibió respuesta del servidor. Revisa tu conexión.',
    });
    return Promise.reject({
      success: false,
      type: 'network_error',
      message: 'No se recibió respuesta del servidor.',
      code: 0,
    });
  } else {
    notification.error({
      message: 'Error desconocido',
      description: 'Hubo un problema al hacer la solicitud.',
    });
    return Promise.reject({
      success: false,
      type: 'request_config_error',
      message: 'Error en configuración: ' + error.message,
      code: 0,
    });
  }
}
