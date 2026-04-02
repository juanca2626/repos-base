/*
 * Socket Helper - QUOTES
 * @team QUOTES
 * @since 2026
 */

import { getUserCode, getUserName } from '@/utils/auth';
import { useSocketsStore } from '@/stores/global';
import { useQuote } from '@/quotes/composables/useQuote';
import { useQuoteStore } from '@/quotes/store/quote.store';
import { notification } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';

const audio_success = new Audio(
  `https://res.cloudinary.com/dt4nv0isx/video/upload/TI/sounds/success.mp3`
);
const audio_error = new Audio(
  `https://res.cloudinary.com/dt4nv0isx/video/upload/TI/sounds/error.mp3`
);
const audio_login = new Audio(
  `https://res.cloudinary.com/dt4nv0isx/video/upload/TI/sounds/login.mp3`
);

export const useQuoteSocket = () => {
  const socketsStore = useSocketsStore();
  const quoteStore = useQuoteStore();
  const { getQuote, quote } = useQuote();
  const { t } = useI18n();

  const showNotification = (response: any, sound = true, show_notification = true) => {
    if (response.success && response.message && response.description) {
      if (sound) {
        audio_success.play().catch(console.warn);
      }

      if (response.message || response.description) {
        // Specific Alert Logic Override
        let hasCustomUpdate = false;

        // Check if we have a quote in the store to compare against
        const currentQuote = quoteStore.quote;

        // Ensure we have both current and new quote data to compare
        if (
          currentQuote &&
          response.quote &&
          response.quote.categories &&
          response.type === 'update_quote_service'
        ) {
          // Map for quick lookup of current services
          const currentServices = currentQuote.categories
            ? currentQuote.categories.flatMap((c) =>
                c.services.flatMap((s) => (s as any).service || s)
              )
            : [];
          const currentServiceMap = new Map(currentServices.map((s: any) => [s.id, s]));

          response.quote.categories.forEach((category: any) => {
            if (category.services) {
              category.services.forEach((groupedService: any) => {
                const service = groupedService.service || groupedService;
                const currentService = currentServiceMap.get(service.id);

                if (currentService) {
                  // Check for significance updates
                  const isOptionalChanged = service.optional !== currentService.optional;
                  const isDateChanged = service.date_in !== currentService.date_in;
                  const isDateOutChanged = service.date_out !== currentService.date_out;

                  if (isOptionalChanged || isDateChanged || isDateOutChanged) {
                    hasCustomUpdate = true;
                  }
                }
              });
            }
          });
        }

        if (hasCustomUpdate) {
          response.message = 'quote.notification.service_modified_title';
          if (response.success) {
            response.description = 'quote.notification.service_modified_success';
          } else {
            response.description = 'quote.notification.service_modified_error';
          }
        } else if (response.type === 'update_quote') {
          // Fallback for general quote updates
          response.message = 'quote.notification.quote_updated_title';
          if (response.success) {
            response.description =
              response.description || 'quote.notification.quote_updated_success';
          }
        }

        if (show_notification) {
          notification.success({
            message: t(response.message),
            description: t(response.description),
            duration: 10,
            placement: 'topRight',
          });
        }

        socketsStore.putNotification(response);
      }
    }

    if (!response.success && response.message && response.description) {
      if (response.user_id !== getUserCode()) {
        // Assuming getUserCode() now returns user_id or similar
        audio_error.play().catch(console.warn);

        if (show_notification) {
          notification.error({
            message: t(response.message),
            description: t(response.description),
            duration: 10,
            placement: 'topRight',
          });
        }

        socketsStore.putNotification(response);
      }
    }
  };

  const handleSocketMessage = async (response: any) => {
    if (response?.type === 'ping') {
      return false;
    }

    console.log('Socket Message:', response);

    if (response?.ignore === socketsStore.getToken) return false;

    const quoteId = quoteStore.quote?.id;
    const isTargetQuote = response?.quote_id === quoteId;

    // Handle user connected notification
    if (response.type === 'user_connected_in_quote' && isTargetQuote) {
      if (response.code !== getUserCode()) {
        audio_login.play().catch(console.warn);

        notification.info({
          message: t(response.message || 'quote.notification.user_connected'),
          description: t(
            response.description || `${response.user_name} está viendo esta cotización`
          ),
          placement: 'topRight',
        });

        const notificationData = {
          ...response,
          success: true,
          show_icon: false,
          message: response.message || 'quote.notification.user_connected',
          description: response.description || `${response.user_name} está viendo esta cotización`,
        };
        socketsStore.putNotification(notificationData);
      }
    }

    // Handle user disconnected notification
    if (response.type === 'user_disconnected_in_quote' && isTargetQuote) {
      if (response.code !== getUserCode()) {
        notification.info({
          message: t(response.message || 'quote.notification.user_disconnected'),
          description: t(
            response.description || `${response.user_name} dejó de ver esta cotización`
          ),
          placement: 'topRight',
        });

        const notificationData = {
          ...response,
          success: true,
          show_icon: false,
          message: response.message || 'quote.notification.user_disconnected',
          description: response.description || `${response.user_name} dejó de ver esta cotización`,
        };
        socketsStore.putNotification(notificationData);
      }
    }

    // Handle quote update notifications
    if (response.type === 'update_quote' && isTargetQuote) {
      if (!response.processing) {
        await getQuote(null, response.user_code === getUserCode(), true);
        showNotification(response);
      }
      quoteStore.setProcessing(response.processing);
    }

    // Handle quote service specific updates
    if (response.type === 'update_quote_service' && isTargetQuote) {
      if (!response.processing) {
        await getQuote(null, false, true);
      }

      // Handle processing state
      if (response.hasOwnProperty('processing')) {
        if (response.service_ids && Array.isArray(response.service_ids)) {
          if (response.processing) {
            response.service_ids.forEach((id: number) => {
              quoteStore.setLoadingService(id, true);
            });
          } else {
            quoteStore.clearLoadingServices(response.service_ids);
          }
        }
      }

      if (quote.value) {
        // Calculate isNew and isUpdated flags
        const currentServices = quote.value.categories
          ? quote.value.categories.flatMap((c) =>
              c.services.flatMap((s) => (s as any).service || s)
            )
          : [];

        // Map for quick lookup
        const currentServiceMap = new Map(currentServices.map((s: any) => [s.id, s]));

        if (quote.value.categories) {
          quote.value.categories.forEach((category: any) => {
            if (category.services) {
              category.services.forEach((groupedService: any) => {
                const service = groupedService.service || groupedService;

                const currentService = currentServiceMap.get(service.id);

                // Action-based logic
                if (response.action === 'new') {
                  if (!currentService) {
                    service.isNew = true;
                  }
                } else if (response.action === 'update') {
                  if (currentService) {
                    // Check for significant updates
                    const isOptionalChanged = service.optional !== currentService.optional;
                    const isDateChanged = service.date_in !== currentService.date_in;
                    const isAdultChanged = service.adult !== currentService.adult;
                    const isChildChanged = service.child !== currentService.child;

                    if (isOptionalChanged || isDateChanged || isAdultChanged || isChildChanged) {
                      service.isUpdated = true;
                    }
                  }
                } else if (response.action === 'delete') {
                  // Handle delete visualization if needed, or rely on store update removing it
                }
              });
            }
          });
        }

        // Specific Alert Logic
        if (!response.processing && (response.message || response.description)) {
          showNotification(response);
        }
      }
    }

    // Handle service loading states
    if (response.type === 'service_loading' && isTargetQuote) {
      if (response.service_ids && Array.isArray(response.service_ids)) {
        if (response.loading) {
          response.service_ids.forEach((id: number) => {
            quoteStore.setLoadingService(id, true);
          });
        } else {
          quoteStore.clearLoadingServices(response.service_ids);
        }
      }
    }
  };

  const connectSocketQuoteId = (quoteId: number | string) => {
    if (!quoteId) return;

    socketsStore.disconnect();

    setTimeout(() => {
      socketsStore.connect({
        callback: handleSocketMessage,
        link: `quotes/${quoteId}/edit`,
      });

      socketsStore.send({
        success: true,
        type: 'user_connected_in_quote',
        message: 'Usuario Conectado',
        code: getUserCode(),
        name: getUserName(),
        quote_id: quoteId,
        description: `${getUserName()} (${getUserCode()}) acaba de conectarse a la COTIZACIÓN N° ${quoteId}`,
      });
    }, 10);
  };

  const connectSocket = () => {
    const quoteId = quoteStore.quote?.id;
    if (!quoteId) return;

    socketsStore.disconnect();

    setTimeout(() => {
      socketsStore.connect({
        callback: handleSocketMessage,
        link: `quotes/${quoteId}/edit`,
      });

      socketsStore.send({
        success: true,
        type: 'user_connected_in_quote',
        message: 'Usuario Conectado',
        code: getUserCode(),
        name: getUserName(),
        quote_id: quoteId,
        description: `${getUserName()} (${getUserCode()}) acaba de conectarse a la COTIZACIÓN N° ${quoteId}`,
      });
    }, 10);
  };

  const disconnectSocket = () => {
    socketsStore.disconnect();
  };

  return {
    handleSocketMessage,
    connectSocketQuoteId,
    connectSocket,
    disconnectSocket,
    showNotification,
  };
};
