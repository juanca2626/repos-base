/*
 * Socket Helper - FILES
 * @team FILES
 * @since 2025
 */

import { notification } from 'ant-design-vue';
import { getUserCode, getAccessTokenCognito } from '@/utils/auth';
import { useSocketsStore } from '@/stores/global';
import {
  useFilesStore,
  useItineraryStore,
  useServiceNotesStore,
  useVipsStore,
} from '@/stores/files';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const audio_success = new Audio(
  `https://res.cloudinary.com/dt4nv0isx/video/upload/TI/sounds/success.mp3`
);
const audio_error = new Audio(
  `https://res.cloudinary.com/dt4nv0isx/video/upload/TI/sounds/error.mp3`
);
const audio_login = new Audio(
  `https://res.cloudinary.com/dt4nv0isx/video/upload/TI/sounds/login.mp3`
);
const audio_send = new Audio(
  `https://res.cloudinary.com/dt4nv0isx/video/upload/TI/sounds/send.mp3`
);

export const useSocketHelper = () => {
  const router = useRouter();
  const socketsStore = useSocketsStore();
  const vipsStore = useVipsStore();
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();
  const serviceNotesStore = useServiceNotesStore();

  const { t } = useI18n({
    useScope: 'global',
  });

  const validateItineraryId = async (itineraryId) => {
    await itineraryStore.getById({
      fileId: filesStore.getFile.id,
      itineraryId,
    });

    const itinerary = itineraryStore.getItinerary;
    return itinerary;
  };

  const showNotification = (response, sound = true, show_notification = true) => {
    if (response.success && response.message && response.description) {
      if (sound) {
        audio_success.play().catch(console.warn);
      }

      if (response.message || response.description) {
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
      if (response.user_code === getUserCode()) {
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

  const handleSocketMessage = async (response) => {
    if (response?.type === 'ping') {
      return false;
    }

    if (response?.ignore === socketsStore.getToken) return false;

    const isTargetFile =
      response?.file_number === filesStore.getFile?.fileNumber ||
      response?.file_id === filesStore.getFile?.id;

    if (!response.success && isTargetFile && response.user_code === getUserCode()) {
      if (response.type === 'update_itinerary') {
        await itineraryStore.getById({
          fileId: filesStore.getFile.id,
          itineraryId: response.itinerary_id,
        });

        const itinerary = itineraryStore.getItinerary;

        itinerary.isError = true;
        itinerary.errorDetail = JSON.stringify(response);
        filesStore.updateItinerary(itinerary);

        if (response.action === 'confirmation-code') {
          await filesStore.fetchFileReports(filesStore.getFile.id, false, response.itinerary_id);
        }

        showNotification(response);
      }

      if (response.type === 'update_file') {
        if (response.action === 'delete') {
          await itineraryStore.getById({
            fileId: filesStore.getFile.id,
            itineraryId: response.itinerary_id,
          });

          const itinerary = itineraryStore.getItinerary;

          itinerary.isError = true;
          itinerary.errorDetail = JSON.stringify(response);
          filesStore.updateItinerary(itinerary);
          showNotification(response);
        } else {
          showNotification(response);

          const index = socketsStore.getNotifications.findIndex(
            (item) => item.date === response.date && item.time === response.time
          );
          socketsStore.clearReservationRequests(index);

          await filesStore.fetchFileErrors({ fileNumber: filesStore.getFile.fileNumber });
        }

        filesStore.finished();
      }

      if (response.type === 'update_paxs') {
        await filesStore.getPassengersById({ fileId: filesStore.getFile.id });
        showNotification(response);
      }

      return false;
    }

    if (response?.type === 'processing-reservation' && isTargetFile) {
      audio_send.play().catch(console.warn);

      const description = t('files.notification.content_processing_reservation');
      const message = t('files.notification.title_processing_reservation');

      response = {
        ...response,
        success: true,
        show_icon: false,
        message: message,
        description: description,
        user_code: getUserCode(),
      };

      notification.open({
        message: message,
        description: description,
        duration: 10,
        placement: 'topRight',
      });

      socketsStore.putNotification(response);

      return false;
    }

    if (response.type === 'user_connected_in_file' && isTargetFile) {
      if (response.code !== getUserCode()) {
        audio_login.play().catch(console.warn);

        notification.info({
          message: response.message,
          description: response.description,
        });
      }
    }

    if (
      response.type === 'user_disconnected_in_file' &&
      filesStore.getFile?.fileNumber === response.file_number &&
      response.token === socketsStore.getToken
    ) {
      response.message = `${t('files.notification.user_disconnected')} ${response.file_number}`;
      response.description = `${t('files.notification.user')} ${response.user_name} (${response.user_code}) ${t('files.notification.kick_from_file')}.`;

      notification.info({
        message: response.message,
        description: response.description,
        duration: 15,
      });

      router.push({ name: 'files' });
    }

    if (response.type === 'update_paxs' && isTargetFile) {
      if (localStorage.getItem('save_passengers') !== 'true') {
        localStorage.removeItem('save_passengers');
        showNotification(response);
        await filesStore.getPassengersById({ fileId: filesStore.getFile.id });
      }

      return false;
    }

    if (response.type === 'update_statement' && isTargetFile) {
      showNotification(response);

      if (
        response.action === 'categories' &&
        parseInt(localStorage.getItem('change_categories')) === 1
      ) {
        localStorage.removeItem('change_categories');
        return false;
      }

      await filesStore.getInfoBasic({ fileId: filesStore.getFile.id });
      if (response.action !== 'categories') {
        await filesStore.searchStatementChanges(filesStore.getFile.id);
      }

      return false;
    }

    if (response.type === 'update_file' && isTargetFile) {
      if (response.action === 'vip') {
        showNotification(response);
        await vipsStore.fetchAll();
        await filesStore.getInfoBasic({ fileId: filesStore.getFile.id, loading: false });
        return false;
      }
      if (response.action === 'status') {
        showNotification(response);
        await filesStore.getInfoBasic({ fileId: filesStore.getFile.id });
        return false;
      }

      if (response.action === 'delete') {
        const itineraryId = parseInt(response.itinerary_id);

        showNotification(response);
        await itineraryStore.getById({
          fileId: filesStore.getFile.id,
          itineraryId,
        });

        const itinerary = itineraryStore.getItinerary;

        if (itinerary?.id === itineraryId) {
          filesStore.updateItinerary(itinerary, true);

          if (
            socketsStore.getNotifications.some(
              (item) => item?.itinerary_id === itineraryId && item.action === 'delete'
            ) &&
            parseInt(itinerary.status) === 0
          ) {
            serviceNotesStore.fetchAllFileNotes({ file_id: filesStore.getFile.id });
            serviceNotesStore.fetchAllRequirementFileNotes({ file_id: filesStore.getFile.id });

            if (!(itinerary.total_amount > 0)) {
              setTimeout(() => {
                filesStore.removeItinerary(itineraryId);
              }, 10000);
            }
          }
        } else {
          filesStore.removeItinerary(itineraryId);
        }
      }

      if (response.action === 'new') {
        const itineraries = filesStore.getFileItineraries.filter(
          (itinerary) => parseInt(itinerary.status) === 1
        );
        const maxId = itineraries.length
          ? Math.max(...itineraries.map((itinerary) => itinerary.id))
          : 0;

        await filesStore.getNewItineraries({
          fileNumber: filesStore.getFile.fileNumber,
          itineraryId: maxId,
        });

        showNotification(response);

        let newItineraries = [];

        for (const itineraryId of filesStore.getNewItinerariesIds) {
          const itinerary = await validateItineraryId(itineraryId);
          itinerary.isNew = true;
          if (itinerary.entity === 'hotel' || itinerary.entity === 'flight') {
            itinerary.isCompleted = true;
          }
          newItineraries.push(itinerary);
          await sleep(500);
        }

        filesStore.putNewItineraries({ newItineraries });

        const index = socketsStore.getNotifications.findIndex(
          (item) => item.date === response.date && item.time === response.time
        );
        socketsStore.clearReservationRequests(index);
      }

      await filesStore.searchStatementChanges(filesStore.getFile.id);
      await filesStore.fetchFileErrors({ fileNumber: filesStore.getFile.fileNumber });
      return false;
    }

    if (response.type === 'update_itinerary' && isTargetFile) {
      if (response.action === 'master-services' && response.itineraries_ids.length === 0) {
        response.itineraries_ids = (filesStore.getFileItineraries ?? [])
          .filter(
            (itinerary) =>
              !itinerary.show_master_services &&
              (itinerary.entity === 'service' || itinerary.entity === 'service-mask')
          )
          .map((itinerary) => itinerary.id);
      }

      if (response.itineraries_ids && response.itineraries_ids.length > 0) {
        let itineraries = [];

        for (const itineraryId of response.itineraries_ids) {
          const itinerary = await validateItineraryId(itineraryId);
          itineraries.push(itinerary);
          await sleep(500);
        }

        if (response.action === 'master-services') {
          let allCodes = [];

          for (const itinerary of itineraries) {
            const codes = itinerary?.services?.map((s) => s.code_ifx) || [];
            allCodes.push(...codes);
          }

          for (const itinerary of filesStore.getFileItineraries.filter(
            (itinerary) => itinerary.entity === 'service'
          )) {
            const codes = itinerary?.services.map((s) => s.code_ifx);
            allCodes.push(...codes);
          }

          const uniqueCodes = [...new Set(allCodes)];

          await Promise.all([
            filesStore.searchServicesGroups({ codes: uniqueCodes, loading: false }),
            filesStore.searchServicesFrequences({ codes: uniqueCodes, loading: false }),
            // filesStore.searchServiceSchedules({ codes: uniqueCodes, loading: false }),
          ]);

          await sleep(500);

          for (const itinerary of itineraries) {
            filesStore.updateItinerary(itinerary, true);

            response = {
              ...response,
              itinerary_id: itinerary.id,
              message: 'files.notification.update_itinerary',
              description: 'files.notification.master_services_success',
            };
            showNotification(response);
          }
        } else {
          for (const itinerary of itineraries) {
            filesStore.updateItinerary(itinerary, true);

            response = {
              ...response,
              itinerary_id: itinerary.id,
            };

            if (response?.action === 'status') {
              showNotification(response, false, false);
            } else {
              showNotification(response);
            }
          }
        }
      } else {
        if (response.itinerary_id) {
          let itinerary = await validateItineraryId(response.itinerary_id);
          filesStore.updateItinerary(itinerary, true);

          if (response?.action === 'status') {
            showNotification(response, false, false);
          } else {
            showNotification(response);
          }
        }
      }

      if (response.action === 'confirmation-code') {
        await filesStore.fetchFileReports(filesStore.getFile.id, false, response.itinerary_id);
      }

      await sleep(500);
      filesStore.executeSortItineraries();
      return false;
    }

    // NOTES
    if (response.type === 'update_note_itinerary' && isTargetFile) {
      const itineraryId = response.itinerary_id;
      showNotification(response);
      filesStore.updateItineraryNote(itineraryId, false, true);
      if (response.id != 0) {
        console.log('Este es el response del socket: ', response);
        serviceNotesStore.editNote({
          file_id: response.file_id,
          data: response,
          isCreated: false,
          isUpdated: true,
          ids: [response.id],
          recharge: false,
        });
      }
    }

    if (response.type === 'create_external_housing' && isTargetFile) {
      showNotification(response);
      if (response.id != 0) {
        const externalHousingId = response.id;
        const { success, data } = await serviceNotesStore.findExternalHousing({
          file_id: filesStore.getFile.id,
          id: externalHousingId,
        });
        if (success) {
          serviceNotesStore.addExternalHousing(data);
        }
      }
    }

    if (response.type === 'update_external_housing' && isTargetFile) {
      showNotification(response);
      if (response.id != 0) {
        const externalHousingId = response.id;
        const { success, data } = await serviceNotesStore.findExternalHousing({
          file_id: filesStore.getFile.id,
          id: externalHousingId,
        });
        if (success) {
          serviceNotesStore.editExternalHousing(data);
        }
      }
    }

    if (response.type === 'delete_external_housing' && isTargetFile) {
      showNotification(response);
      if (response.id != 0) {
        serviceNotesStore.quitExternalHousing({
          file_id: filesStore.getFile.id,
          id: response.id,
        });
      }
    }

    if (response.type === 'create_note' && isTargetFile) {
      showNotification(response);
      if (response.id != 0) {
        await serviceNotesStore.editNote({
          file_id: response.file_id,
          data: response,
          isCreated: true,
          isUpdated: false,
          ids: response.ids,
          recharge: true,
        });
      }
    }

    if (response.type === 'update_note' && isTargetFile) {
      showNotification(response);
      if (response.id != 0) {
        console.log('esta llegando el editar ids: ', response.ids);
        await serviceNotesStore.editNote({
          file_id: response.file_id,
          data: response,
          isCreated: false,
          isUpdated: true,
          ids: response.ids,
          recharge: true,
        });
      }
    }

    if (response.type === 'delete_note' && isTargetFile) {
      showNotification(response);
      if (response.id != 0) {
        await serviceNotesStore.quitNote({ file_id: response.file_id, data: response });
      }
    }
    // CREATE NOTE GENERAL
    if (response.type === 'create_note_general' && isTargetFile) {
      console.log(response);
      showNotification(response);
      if (response.id != 0) {
        await serviceNotesStore.editNoteGeneral({
          file_id: response.file_id,
          data: response,
          recharge: true,
        });
      }
    }
  };

  const connectSocketFileId = (fileId) => {
    socketsStore.disconnect();

    setTimeout(() => {
      socketsStore.connect({
        callback: handleSocketMessage,
        link: `files/${fileId}/edit`,
      });
    }, 10);
  };

  const connectSocket = () => {
    if (!filesStore.getFile?.id) return;
    socketsStore.disconnect();

    setTimeout(() => {
      socketsStore.connect({
        callback: handleSocketMessage,
        link: `files/${filesStore.getFile.id}/edit`,
      });
    }, 10);
  };

  const reconnectOnVisibility = async () => {
    if (document.visibilityState === 'visible' && filesStore.getFile?.id > 0) {
      if (!socketsStore.isConnected && socketsStore.isPin) {
        const token = getAccessTokenCognito();
        await axios
          .get(`${window.url_auth_cognito}auth/me`, {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          })
          .then(() => {
            notification.open({
              message: `Aurora ${t('files.notification.inactive')}`,
              description: t('files.notification.page_inactive'),
            });

            setTimeout(() => {
              connectSocket();
              console.log('✅ WebSocket reconectado');
            }, 100);
          })
          .catch((error) => {
            console.log(error);

            notification.info({
              message: t('files.notification.connection_lost'),
              description: t('files.notification.content_connection_lost'),
            });

            setTimeout(() => {
              window.location.reload();
            }, 1000);
          });
      }
    }
  };

  return {
    handleSocketMessage,
    connectSocketFileId,
    connectSocket,
    reconnectOnVisibility,
  };
};
