import { defineStore } from 'pinia';
import type { Payload } from '@/components/files/temporary/interfaces/types.interface';
import {
  addServiceMaster,
  cancelServiceMaster,
  getCommunicationsTemporary,
  saveServiceTemporary,
  saveServiceTemporaryToFile,
  sendCommunicationMasterService,
  updateCommunicationsTemporary,
} from '@/services/files/index.js';
import { useServiceItineraryPenaltyStore } from '@/components/files/temporary/store/serviceItineraryPenaltyStore';

export const useFileServiceStore = defineStore('fileService', {
  state: () => ({
    isLoading: false,
    isSuccess: false,
    errorMessage: '',
    serviceTemporaryCommunications: null,
  }),
  actions: {
    setServiceTemporaryCommunications(serviceTemporaryCommunications) {
      this.serviceTemporaryCommunications = serviceTemporaryCommunications;
    },
    async sendFileService(fileId, serviceId, itinerary) {
      this.isLoading = true;
      this.isSuccess = false;
      this.errorMessage = '';
      const serviceItineraryPenaltyStore = useServiceItineraryPenaltyStore();
      const payloadServiceTemporary = {
        file_id: fileId,
        entity: 'service-temporary',
        object_id: itinerary.id,
        object_code: itinerary.object_code,
        name: itinerary.name || null,
        category: null,
        country_in_iso: itinerary.country_in_iso || null,
        country_in_name: itinerary.country || null,
        city_in_name: itinerary.city_in_name || null,
        city_in_iso: itinerary.city_in_iso || null,
        zone_in_id: itinerary.zone_in_id || null,
        zone_in_iso: null,
        zone_in_airport: itinerary.zone_in_airport || null,
        start_time: itinerary.start_time || null,
        departure_time: itinerary.departure_time || null,
        country_out_iso: itinerary.country_out_iso || null,
        country_out_name: itinerary.country_out_name || null,
        city_out_iso: itinerary.city_out_iso || null,
        city_out_name: itinerary.city_out_name || null,
        zone_out_iso: itinerary.zone_out_iso || null,
        zone_out_id: itinerary.zone_out_id || null,
        zone_out_airport: itinerary.zone_out_airport || null,
        date: itinerary.date_in || null,
        adult_num: itinerary.adults || 0,
        child_num: itinerary.children || 0,
        markup_created: itinerary.markup_created || 0,
        total_amount: itinerary.total_amount || 0,
        total_cost_amount: itinerary.total_cost_amount || 0,
        service_category_id: itinerary.service_category_id || 0,
        service_sub_category_id: itinerary.service_sub_category_id || 0,
        service_type_id: itinerary.service_type_id || 0,
        services: this.getServiceAndCompositionsPayload(itinerary.services),
        details: itinerary.details || [],
      };

      const payloadNewServiceMaster = this.getServiceMasterNewPayload(itinerary.services);
      const payloadDeletedServiceMaster = this.getServiceMasterDeletedPayload(itinerary.services);
      const payloadCommunications = this.getCommunicationsPayload();

      try {
        // Paso 1: Llamada a `saveServiceTemporary`
        const responseServiceTemporary = await saveServiceTemporary(payloadServiceTemporary);

        await saveServiceTemporaryToFile(serviceId, {
          file_temporary_service_id: responseServiceTemporary.data.data.id,
        });

        // Paso 2: Llamadas a `addServiceMaster` para cada nuevo servicio en secuencia
        for (const serviceMaster of payloadNewServiceMaster) {
          await addServiceMaster(serviceId, serviceMaster);
        }

        // Paso 3: Llamadas a `cancelServiceMaster` para cada servicio eliminado en secuencia
        for (const serviceMaster of payloadDeletedServiceMaster) {
          const params = {
            services: [
              {
                id: serviceMaster.id,
                compositions: serviceMaster.compositions.map((composition) => composition.id), // Extraer solo los IDs
              },
            ],
            executive_id: serviceItineraryPenaltyStore.getAssumesPenalty.executive_id,
            file_id: serviceItineraryPenaltyStore.getAssumesPenalty.file_id,
            status_reason_id: serviceItineraryPenaltyStore.getAssumesPenalty.status_reason_id,
            motive: serviceItineraryPenaltyStore.getAssumesPenalty.motive,
          };

          await cancelServiceMaster(serviceId, params);
        }

        // const serviceForDelete = [];
        // for (const serviceMaster of payloadDeletedServiceMaster) {
        //   serviceForDelete.push({
        //     id: serviceMaster.id,
        //     compositions: serviceMaster.compositions.map((composition) => composition.id), // Extraer solo los IDs
        //   });
        // }
        //
        // await deleteServiceMaster(serviceId, { services: serviceForDelete });

        if (payloadCommunications.length > 0) {
          // Paso 4: Envio de comunicaciones
          for (const communication of payloadCommunications) {
            await sendCommunicationMasterService(
              communication.supplier_emails,
              communication.type,
              communication.attachments,
              communication.html
            );
          }
        }

        // Si todo ha ido bien, marcamos el proceso como exitoso
        this.isSuccess = true;
        return responseServiceTemporary.data; // Retornamos la respuesta final
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error al enviar los datos';
        this.isSuccess = false;
        throw new Error(this.errorMessage);
      } finally {
        this.isLoading = false;
      }
    },
    getCommunicationsPayload() {
      const payload = [];

      if (!this.serviceTemporaryCommunications) return payload;

      // Procesa reservations
      for (const reservation of this.serviceTemporaryCommunications.reservations) {
        if (reservation.supplier_emails.length > 0) {
          payload.push({
            type: 'reservations',
            supplier_emails: reservation.supplier_emails,
            html: reservation.html,
            attachments: reservation.attachments,
          });
        }
      }

      // Procesa cancellation
      for (const cancellation of this.serviceTemporaryCommunications.cancellation) {
        if (cancellation.supplier_emails.length > 0) {
          payload.push({
            type: 'cancellation',
            supplier_emails: cancellation.supplier_emails,
            html: cancellation.html,
            attachments: cancellation.attachments,
          });
        }
      }

      // Procesa modification dependiendo de hasOneCommunications
      for (const modification of this.serviceTemporaryCommunications.modification) {
        if (modification.hasOneCommunications) {
          // Si hasOneCommunications es true, toma supplier_emails y html de modification
          if (modification.supplier_emails.length > 0) {
            payload.push({
              type: 'modification',
              supplier_emails: modification.supplier_emails,
              html: modification.html,
              attachments: modification.attachments,
            });
          }
        } else {
          // Si hasOneCommunications es false, accede a modification.cancellation y modification.reservations como objetos
          for (const modCancellation of modification.cancellation || []) {
            if (modCancellation.supplier_emails.length > 0) {
              payload.push({
                type: 'modification',
                supplier_emails: modCancellation.supplier_emails,
                html: modCancellation.html,
                attachments: modCancellation.attachments,
              });
            }
          }
          for (const modReservation of modification.reservations || []) {
            if (modReservation.supplier_emails.length > 0) {
              payload.push({
                type: 'modification',
                supplier_emails: modReservation.supplier_emails,
                html: modReservation.html,
                attachments: modReservation.attachments,
              });
            }
          }
        }
      }

      return payload;
    },
    getServiceMasterNewPayload(services) {
      return services
        .filter((service) => {
          return service.isNew === true && service.isDeleted === false;
        })
        .map((service) => ({
          id: null,
          master_service_id: service.master_service_id || null,
          name: service.name || null,
          code: service.code_ifx || service.code || '',
          type_ifx: service.type_ifx || null,
          date_in: this.getFormatDate(service.date_in) || null,
          start_time: service.start_time || null,
          departure_time: service.departure_time || null,
          amount_cost: service.amount_cost || 0,
          confirmation_status: service.confirmation_status || 0,
          compositions: (service.compositions || []).map((composition) => ({
            id: null,
            file_classification_id: composition.file_classification_id || 6, // cambiar por valore reales
            type_composition_id: composition.type_composition_id || 3, // cambiar por valore reales
            type_component_service_id: composition.type_component_service_id || 1, // cambiar por valore reales
            composition_id: composition.composition_id || null,
            code: composition.code || '',
            name: composition.name || '',
            item_number: composition.item_number || 0,
            rate_plan_code: composition.rate_plan_code || '',
            duration_minutes: composition.duration_minutes || 0,
            total_adults: composition.total_adults || 0,
            total_children: composition.total_children || 0,
            total_infants: composition.total_infants || 0,
            total_extra: composition.total_extra || 0,
            is_programmable: composition.is_programmable || 0,
            is_in_ope: composition.is_in_ope || '',
            country_in_iso: composition.country_in_iso || null,
            country_in_name: composition.country_in_name || '',
            city_in_iso: composition.city_in_iso || null,
            city_in_name: composition.city_in_name || null,
            country_out_iso: composition.country_out_iso || '',
            country_out_name: composition.country_out_name || '',
            city_out_iso: composition.city_out_iso || '',
            city_out_name: composition.city_out_name || '',
            start_time: composition.start_time || null,
            departure_time: composition.departure_time || null,
            date_in: composition.date_in || null,
            date_out: composition.date_out || null,
            currency: composition.currency || 'USD',
            amount_sale: composition.amount_sale || 0,
            amount_cost: composition.amount_cost || 0,
            amount_sale_origin: composition.amount_sale_origin || 0,
            amount_cost_origin: composition.amount_cost_origin || 0,
            markup_created: composition.markup_created || 0,
            taxes: composition.taxes || 0,
            total_services: composition.total_services || 0,
            use_voucher: composition.use_voucher || 0,
            use_itinerary: composition.use_itinerary || 0,
            voucher_sent: composition.voucher_sent || 0,
            voucher_number: composition.voucher_number || null,
            use_ticket: composition.use_ticket || 0,
            use_accounting_document: composition.use_accounting_document || 0,
            ticket_sent: composition.ticket_sent || 0,
            accounting_document_sent: composition.accounting_document_sent || 0,
            branch_number: composition.branch_number || null,
            document_skeleton: composition.document_skeleton || 0,
            document_purchase_order: composition.document_purchase_order || 0,
            status: composition.status || true,
            units: (composition.units || []).map((unit) => ({
              id: null,
              status: unit.status || '',
              amount_sale: unit.amount_sale || 0,
              amount_cost: unit.amount_cost || 0,
              amount_sale_origin: unit.amount_sale_origin || 0,
              amount_cost_origin: unit.amount_cost_origin || 0,
              accommodations: (unit.accommodations || []).map((accommodation) => ({
                file_passenger_id: accommodation.file_passenger_id || null,
                room_key: accommodation.room_key || null,
              })),
            })),
          })),
        }));
    },
    getServiceMasterDeletedPayload(services) {
      return services.filter((service) => {
        return service.isDeleted === true && service.isNew === false && service.status == true;
      });
    },
    getServiceAndCompositionsPayload(services) {
      return services
        .filter((service) => {
          return (
            (service.isNew === true || service.isNew === false) &&
            service.isDeleted === false &&
            service.isReplaced === false &&
            service.replacedBy === null
          );
        })
        .map((service) => ({
          id: null,
          master_service_id: service.master_service_id || null,
          name: service.name || null,
          code: service.code_ifx || service.code || '',
          type_ifx: service.type_ifx || null,
          date_in: this.getFormatDate(service.date_in) || null,
          start_time: service.start_time || null,
          departure_time: service.departure_time || null,
          amount_cost: service.amount_cost || 0,
          confirmation_status: service.confirmation_status || 0,
          compositions: (service.compositions || []).map((composition) => ({
            id: null,
            file_classification_id: composition.file_classification_id || 6, // cambiar por valore reales
            type_composition_id: composition.type_composition_id || 3, // cambiar por valore reales
            type_component_service_id: composition.type_component_service_id || 1, // cambiar por valore reales
            composition_id: composition.composition_id || null,
            code: composition.code || '',
            name: composition.name || '',
            item_number: composition.item_number || 0,
            rate_plan_code: composition.rate_plan_code || '',
            duration_minutes: composition.duration_minutes || 0,
            total_adults: composition.total_adults || 0,
            total_children: composition.total_children || 0,
            total_infants: composition.total_infants || 0,
            total_extra: composition.total_extra || 0,
            is_programmable: composition.is_programmable || 0,
            is_in_ope: composition.is_in_ope || '',
            country_in_iso: composition.country_in_iso || null,
            country_in_name: composition.country_in_name || '',
            city_in_iso: composition.city_in_iso || null,
            city_in_name: composition.city_in_name || null,
            country_out_iso: composition.country_out_iso || '',
            country_out_name: composition.country_out_name || '',
            city_out_iso: composition.city_out_iso || '',
            city_out_name: composition.city_out_name || '',
            start_time: composition.start_time || null,
            departure_time: composition.departure_time || null,
            date_in: composition.date_in || null,
            date_out: composition.date_out || null,
            currency: composition.currency || 'USD',
            amount_sale: composition.amount_sale || 0,
            amount_cost: composition.amount_cost || 0,
            amount_sale_origin: composition.amount_sale_origin || 0,
            amount_cost_origin: composition.amount_cost_origin || 0,
            markup_created: composition.markup_created || 0,
            taxes: composition.taxes || 0,
            total_services: composition.total_services || 0,
            use_voucher: composition.use_voucher || 0,
            use_itinerary: composition.use_itinerary || 0,
            voucher_sent: composition.voucher_sent || 0,
            voucher_number: composition.voucher_number || null,
            use_ticket: composition.use_ticket || 0,
            use_accounting_document: composition.use_accounting_document || 0,
            ticket_sent: composition.ticket_sent || 0,
            accounting_document_sent: composition.accounting_document_sent || 0,
            branch_number: composition.branch_number || null,
            document_skeleton: composition.document_skeleton || 0,
            document_purchase_order: composition.document_purchase_order || 0,
            status: composition.status || true,
            units: (composition.units || []).map((unit) => ({
              id: null,
              status: unit.status || '',
              amount_sale: unit.amount_sale || 0,
              amount_cost: unit.amount_cost || 0,
              amount_sale_origin: unit.amount_sale_origin || 0,
              amount_cost_origin: unit.amount_cost_origin || 0,
              accommodations: (unit.accommodations || []).map((accommodation) => ({
                file_passenger_id: accommodation.file_passenger_id || null,
                room_key: accommodation.room_key || null,
              })),
            })),
          })),
        }));
    },
    getFormatDate(date: string): string {
      // Regular expressions to identify date formats
      const dmyFormat = /^(\d{2})\/(\d{2})\/(\d{4})$/; // Format DD/MM/YYYY
      const ymdFormat = /^(\d{4})-(\d{2})-(\d{2})$/; // Format YYYY-MM-DD

      if (dmyFormat.test(date)) {
        // If the format is DD/MM/YYYY, reorganize to YYYY-MM-DD
        const [, day, month, year] = date.match(dmyFormat)!;
        return `${year}-${month}-${day}`;
      } else if (ymdFormat.test(date)) {
        // If the format is already YYYY-MM-DD, return it directly
        return date;
      } else {
        throw new Error('Unrecognized date format');
      }
    },
    getCurrentDateTime() {
      const date = new Date();
      const formattedDate = `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
      const formattedTime = `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`;

      return { date: formattedDate, time: formattedTime };
    },
    async getCommunicationTemporaryService(fileId: string, serviceId: string, payload: Payload) {
      this.isLoading = true;
      this.isSuccess = false;
      this.errorMessage = '';

      try {
        const response = await getCommunicationsTemporary(fileId, serviceId, payload);
        this.isSuccess = true;
        return response.data; // Retornar datos exitosos
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error al enviar los datos';
        this.isSuccess = false;
        throw new Error(this.errorMessage);
      } finally {
        this.isLoading = false;
      }
    },
    async updateCommunicationsTemporary(fileId, serviceId, payload) {
      this.isLoading = true;
      this.isSuccess = false;
      this.errorMessage = '';

      try {
        const response = await updateCommunicationsTemporary(fileId, serviceId, payload);
        this.isSuccess = true;
        this.isLoading = false;
        return response.data; // Retornar datos exitosos
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error al enviar los datos';
        this.isSuccess = false;
        throw new Error(this.errorMessage);
      } finally {
        this.isLoading = false;
      }
    },
  },
});
