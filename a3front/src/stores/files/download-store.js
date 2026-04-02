import { defineStore } from 'pinia';
import {
  download,
  downloadFileFlights,
  downloadFileInvoice,
  downloadFileItinerary,
  downloadFileListHotels,
  downloadFilePassengers,
  downloadFileServiceSchedule,
  downloadFileSkeleton,
  downloadRoomingListExcel,
  fetchListHotelsByFileId,
  fetchSkeletonByFileId,
  fetchStatementDetailsByFileId,
  downloadStatementCreditNote,
  downloadStatementDebitNote,
  downloadFileBalance,
} from '@service/files';
import { formatDate } from '@/utils/files';

import { notification } from 'ant-design-vue';

export const useDownloadStore = defineStore({
  id: 'download',
  state: () => ({
    loading: false,
    loadingFiles: {},
  }),
  getters: {
    isLoading: (state) => state.loading,
    isFileLoading: (state) => (fileId) => !!state.loadingFiles[fileId],
  },
  actions: {
    setLoading(fileId, isLoading) {
      this.loadingFiles = { ...this.loadingFiles, [fileId]: isLoading };
    },
    download({ currentPage, filter, perPage, clientId, executiveCode, dateRange }) {
      this.loading = true;
      const now = formatDate(new Date()).split('/').reverse().join('-');
      return download({
        currentPage,
        perPage,
        filter,
        clientId,
        executiveCode,
        dateRange,
      })
        .then(({ data }) => {
          const url = window.URL.createObjectURL(new Blob([data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', `files-${now}.xlsx`);
          link.click();
          window.URL.revokeObjectURL(url); // Limpia el URL del blob
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          notification['error']({
            message: 'Descargar lista',
            description:
              'No se pudo descargar el archivo, intente nuevamente en un momento por favor.',
            duration: 0,
          });
          this.loading = false;
        });
    },
    downloadFileDocuments(fileId, nameDocument, type_extensions = 'xlsx', lang) {
      this.setLoading(fileId, true);
      const now = formatDate(new Date()).split('/').reverse().join('-');
      const fileMap = {
        passengers: downloadFilePassengers,
        flights: downloadFileFlights,
        invoice: downloadFileInvoice,
        skeleton: downloadFileSkeleton,
        serviceSchedule: downloadFileServiceSchedule,
        listHotels: downloadFileListHotels,
        roomingListExcel: downloadRoomingListExcel,
      };
      const downloadFunction = fileMap[nameDocument];

      if (!downloadFunction) {
        this.setLoading(fileId, false);
        this.loading = false;
        notification['error']({
          message: 'Descargar lista',
          description: 'Tipo de documento no válido.',
          duration: 0,
        });
        return;
      }

      downloadFunction(fileId, lang)
        .then(({ data }) => {
          this.createAndDownloadFile(data, `file-${nameDocument}-${now}.${type_extensions}`);
        })
        .catch((error) => {
          this.showDownloadError(error);
        })
        .finally(() => {
          this.setLoading(fileId, false);
        });
    },
    createAndDownloadFile(data, fileName) {
      const url = window.URL.createObjectURL(new Blob([data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', fileName);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    },
    showDownloadError(error) {
      console.log(error);
      if (error.code === 404) {
        notification['warning']({
          message: 'Documento no se encontro',
          description: 'No se encontro el statement para file.',
          duration: 10,
        });
      } else {
        notification['error']({
          message: 'Descargar lista',
          description:
            'No se pudo descargar el archivo, intente nuevamente en un momento por favor.',
          duration: 10,
        });
      }
    },
    getStatementByFileId(fileId) {
      this.loading = true;
      return fetchStatementDetailsByFileId(fileId)
        .then(({ data }) => {
          return data.data;
        })
        .catch(() => {
          notification['error']({
            message: 'Error en la petición',
            description: 'Intente nuevamente en un momento por favor.',
            duration: 0,
          });
        })
        .finally(() => {
          this.loading = false;
        });
    },
    getListHotelsByFileId(fileId) {
      this.loading = true;
      return fetchListHotelsByFileId(fileId)
        .then(({ data }) => {
          return data.data.hotels;
        })
        .catch(() => {
          notification['error']({
            message: 'Error en la petición',
            description: 'Intente nuevamente en un momento por favor.',
            duration: 0,
          });
        })
        .finally(() => {
          this.loading = false;
        });
    },
    getReportServiceByFileId(fileId, only = 'services') {
      this.loading = true;
      return fetchSkeletonByFileId(fileId)
        .then(({ data }) => {
          if (only === 'services') {
            return data.data.services;
          } else {
            return data.data;
          }
        })
        .catch(() => {
          notification['error']({
            message: 'Error en la petición',
            description: 'Intente nuevamente en un momento por favor.',
            duration: 0,
          });
        })
        .finally(() => {
          this.loading = false;
        });
    },
    downloadFileItinerary(fileId, imageCover, lang = 'es', type = 'pdf') {
      this.loading = true;
      let extension = 'pdf';
      if (type === 'word') {
        extension = 'docx';
      }
      return downloadFileItinerary(fileId, imageCover, lang, type)
        .then(({ data }) => {
          const now = formatDate(new Date()).split('/').reverse().join('-');
          this.createAndDownloadFile(data, `file-itinerary-${now}.${extension}`);
        })
        .catch(() => {
          this.showDownloadError();
        })
        .finally(() => {
          this.loading = false;
        });
    },
    downloadStatementCreditNote(fileId, type = 'pdf') {
      this.loading = true;
      return downloadStatementCreditNote(fileId, (type = 'pdf'))
        .then(({ data }) => {
          const now = formatDate(new Date()).split('/').reverse().join('-');
          const filename = `statement-credit-note-${now}.${type}`;

          const blob = new Blob([data], { type: 'application/pdf' });
          const url = window.URL.createObjectURL(blob);

          const a = document.createElement('a');
          a.href = url;
          a.download = filename;
          document.body.appendChild(a);
          a.click();

          setTimeout(() => {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
          }, 100);
          return true;
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    downloadStatementDebitNote(fileId, type = 'pdf') {
      this.loading = true;
      return downloadStatementDebitNote(fileId, type)
        .then(({ data }) => {
          const now = formatDate(new Date()).split('/').reverse().join('-');
          const filename = `statement-debit-note-${now}.${type}`;

          const blob = new Blob([data], { type: 'application/pdf' });
          const url = window.URL.createObjectURL(blob);

          const a = document.createElement('a');
          a.href = url;
          a.download = filename;
          document.body.appendChild(a);
          a.click();

          setTimeout(() => {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
          }, 100);
          return true;
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    downloadFileBalance({
      currentPage,
      filter,
      filterBy = '',
      filterByType = '',
      perPage,
      clientId,
      executiveCode,
      dateRange,
    }) {
      this.loading = true;
      const now = formatDate(new Date()).split('/').reverse().join('-');
      return downloadFileBalance({
        currentPage,
        perPage,
        filter,
        filterBy,
        filterByType,
        executiveCode,
        clientId,
        dateRange,
      })
        .then(({ data }) => {
          const url = window.URL.createObjectURL(new Blob([data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', `files-balance-${now}.xlsx`);
          link.click();
          window.URL.revokeObjectURL(url); // Limpia el URL del blob
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          notification['error']({
            message: 'Descargar lista',
            description:
              'No se pudo descargar el archivo, intente nuevamente en un momento por favor.',
            duration: 0,
          });
          this.loading = false;
        });
    },
  },
});
