import { defineStore } from 'pinia';
import { createFilePassengerCommunicationAdapter } from '@store/files/adapters';
import { viewCommunication } from '@/services/global';

function parseHtmlContent(rawHtml) {
  return rawHtml;
}

export const useCommunicationsStore = defineStore({
  id: 'communications',
  state: () => ({
    loading: false,
    html: '',
    hotel_html: '',
    executive_html: '',
    client_html: '',
    subject: '',
    error: '',
  }),
  getters: {
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getHtml: (state) => state.html,
    getHotelHtml: (state) => state.hotel_html,
    getExecutiveHtml: (state) => state.executive_html,
    getClientHtml: (state) => state.client_html,
    getSubject: (state) => state.subject,
  },
  actions: {
    async previewCommunication(object_id, params, type, action) {
      this.loading = true;
      this.error = '';
      this.html = '';
      this.hotel_html = '';
      this.executive_html = '';
      this.client_hotel = '';
      this.subject = '';

      try {
        if (action !== 'cancellation') {
          params.reservation_add.guests = params.reservation_add.guests.map((guest) =>
            createFilePassengerCommunicationAdapter(guest)
          );
        }

        const { data } = await viewCommunication(object_id, params, type, action);

        this.loading = false;

        if (data.success) {
          this.subject = data.data.subject;
          this.html = typeof data.data.html === 'string' ? parseHtmlContent(data.data.html) : '';
          this.hotel_html = parseHtmlContent(data.data.html.hotel ?? '');
          this.executive_html = parseHtmlContent(data.data.html.executive ?? '');
          this.client_html = parseHtmlContent(data.data.html.client ?? '');
        } else {
        }
      } catch (error) {
        console.error(error);
        this.error = error?.data?.error || 'Error inesperado';
        this.loading = false;
      }
    },
  },
});
