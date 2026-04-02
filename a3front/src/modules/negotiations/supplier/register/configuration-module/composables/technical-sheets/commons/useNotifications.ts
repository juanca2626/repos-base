import { notification } from 'ant-design-vue';

export const useNotifications = () => {
  const message = 'Aurora';

  const showNotificationError = (description: string) => {
    notification.error({
      message,
      description: description,
    });
  };

  const showNotificationSuccess = (description: string) => {
    notification.success({
      message,
      description: description,
    });
  };

  return {
    showNotificationError,
    showNotificationSuccess,
  };
};
