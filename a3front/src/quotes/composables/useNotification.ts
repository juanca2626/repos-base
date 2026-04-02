import { notification } from 'ant-design-vue';
import { useSocketsStore } from '@/stores/global';
import router from '@/router';

const useNotification = () => {
  return {
    // Properties

    // Methods
    showErrorNotification: (message: string, redirect: boolean = false) => {
      notification['error']({
        message: 'Error',
        description: message,
      });

      const socketsStore = useSocketsStore();

      socketsStore.putNotification({
        success: false,
        message: 'Error',
        description: message,
        flag_show: true,
      });

      if (redirect) {
        router.push({
          name: 'quotes-error',
          query: {
            title: 'Error',
            subtitle: message,
            status: '500',
          },
        });
      }
    },

    showWarningNotification: (message: string) => {
      notification['warning']({
        message: 'Warning',
        description: message,
      });
    },

    showSuccessNotification: (message: string) => {
      notification['success']({
        message: 'Success',
        description: message,
      });
    },
    // Getters
  };
};

export default useNotification;
