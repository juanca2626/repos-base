import { defineStore } from 'pinia';
import { handleError } from '@/utils/errorHandling';
import dayjs from 'dayjs';

import {
  activateFile,
  addItinerary,
  cancelFile,
  cloneBasicFile,
  createBasicFile,
  deleteItem,
  fetchAllProviders,
  fetchFiles,
  fetchItineraryDetailsData,
  fetchRoomingListData,
  fetchServiceInformation,
  fetchHotelInformation,
  findMasterServices,
  getAllModifyPaxs,
  getCategoriesHotel,
  getDestiniesByClient,
  getFile,
  getFilePublic,
  getFileCategories,
  getFileReasons,
  getStatementReasons,
  getFileStatusReasons,
  getFilterSearchHotels,
  getFilterSearchServices,
  getFilterSearchTemporaryServices,
  getItinerary,
  getMasterServices,
  getReasonStatement,
  passengerDownload,
  passengerDownloadAmadeus,
  passengerDownloadPerurail,
  putConfirmationCode,
  putFlagFileRateProtected,
  putFlagRateProtected,
  putServiceNotes,
  searchCommunicationsCancellation,
  searchCommunicationsNew,
  searchFileReports,
  sendQuoteA2,
  updateAccommodations,
  updateAllPassengers,
  updateAmounts,
  updateCategoriesFile,
  updateFile,
  updateQuantityPassengers,
  verifyQuoteA2,
  putStatusRQWL,
  putStatusWLOK,
  fetchStatements,
  fetchStatementDetails,
  fetchLogsAWS,
  createDebitNote,
  createCreditNote,
  putUpdateStatement,
  importFileStella,
  findStatementChanges,
  filterServicesFrequences,
  filterServicesGroups,
  filterGroupSchedule,
  sendNotificationService,
  changeNotificationService,
  handleStoreRepository,
  validateItinerary,
  searchFileServiceHotelCode,
  fetchFileReservationProvider,
  getPassengers,
  getLatestItineraries,
  generateMasterServices,
  updateStatementItineraries,
  validateFileErrors,
  getFileBasic,
  filterServicesSchedules,
  fetchFileBalanceAll,
} from '@service/files';

import { sendNotification } from '@service/brevo';

import {
  createFileAdapter,
  createFileItineraryReplaceAdapter,
  createFileItineraryRoomReplaceAdapter,
  createFileItineraryServiceReplaceAdapter,
  createFilesAdapter,
  normalizeItineries,
} from '@store/files/adapters';

import { createFilePassengerAdapter } from './adapters/files';
import { notification } from 'ant-design-vue';
import { useMasterServiceStore } from '@/components/files/temporary/store/masterServiceStore';

const DEFAULT_PER_PAGE = 9;
const DEFAULT_PAGE = 1;

export const useFilesStore = defineStore({
  id: 'files',
  state: () => ({
    // loading
    loading: true,
    loading_itinerary: true,
    loading_passengers: false,
    loading_async: false,
    loading_download: false,
    loading_reports: false,
    loading_notes: false,
    loading_basic: false,
    loading_statements: false,
    loading_balance: false,
    loading_modify_paxs: false,
    // pagination
    total: 0,
    currentPage: DEFAULT_PAGE,
    defaultPerPage: DEFAULT_PER_PAGE,
    perPage: DEFAULT_PER_PAGE,
    // data
    files: [],
    filesCompleted: [],
    file: {},
    file_public: {},
    file_hotel_code: [],
    file_providers: [],
    itineraries: [],
    passengers: [],
    reasons: [],
    statement_reasons: [],
    fileCategories: [],
    fileReasonStatement: [],
    // filters
    filter: null,
    // sort
    filterBy: null,
    filterByType: null,
    filterNextDays: null,
    revisionStages: null,
    opeAssignStages: null,
    client_id: null,
    executiveCode: null,
    dateRange: null,
    new_itinerary: {},
    itinerary: {},
    itineraries_replace: [],
    itineraries_services_replace: [],
    categories: [],
    destinies: [],
    city: {},
    allHotels: [],
    hotels: [],
    hotelsTop: [],
    filter_hotels: {},
    token_search_hotels: '',
    search_parameters_hotels: {},
    filter_services: {},
    token_search_services: '',
    search_parameters_services: {},
    services: [],
    status_reasons: [],
    all_penality: 0,
    penality_adl: 0,
    penality_chd: 0,
    all_penality_cost: 0,
    penality_adl_cost: 0,
    penality_chd_cost: 0,
    penality_sgl: 0,
    penality_sgl_cost: 0,
    penality_dbl: 0,
    penality_dbl_cost: 0,
    penality_tpl: 0,
    penality_tpl_cost: 0,
    penality_services: [],
    penality_hotels: [],
    provider: {},
    providers: [],
    flag_in_board: false,
    flag_send_board: false,
    loaded: false,
    serviceEdit: null,
    serviceTemporaryCreated: null,
    serviceTemporaryCommunications: null,
    serviceMasterReplace: null,
    services_search: [],
    services_selected: [],
    quantity_adults: 0,
    quantity_children: 0,
    pages: 1,
    total_services: 0,
    search_passengers: [],
    modify_paxs: [],
    flag_search_hotels: false,
    flag_search_services: false,
    simulations: [],
    error: '',
    service_information: false,
    hotel_information: false,
    options_asumed_by: [],
    itineraries_trash: [],
    communications: [],
    master_services: [],
    file_reports: [],
    roomingList: [],
    itineraryDetails: [],
    includeTemporaryInSearch: false,
    temporaryServices: [],
    report_statements: {},
    statement_details: [],
    logs_aws: [],
    statements_changes: null,
    service_frequences: {},
    group_schedule: {},
    service_groups: {},
    service_schedules: {},
    reservations: [],
    has_pending_processes: false,
    file_process: null,
    file_balance: {},
    file_pagination: {},
    new_itineraries_ids: [],
  }),
  getters: {
    isLoaded: (state) => state.loaded,
    isLoading: (state) => state.loading,
    isLoadingItinerary: (state) => state.loading_itinerary,
    isLoadingAsync: (state) => state.loading_async,
    isLoadingDownload: (state) => state.loading_download,
    isLoadingModifyPaxs: (state) => state.loading_modify_paxs,
    isLoadingReports: (state) => state.loading_reports,
    isLoadingPassengers: (state) => state.loading_passengers,
    isLoadingNotes: (state) => state.loading_notes,
    isLoadingBasic: (state) => state.loading_basic,
    isLoadingStatements: (state) => state.loading_statements,
    isLoadingBalance: (state) => state.loading_balance,
    getError: (state) => state.error,
    getLang: (state) => state.file.lang,
    getFiles: (state) => state.files,
    getFilesCompleted: (state) => state.filesCompleted,
    getFilesByClone: (state) => {
      return state.filesCompleted.map((file) => ({
        label: file.fileNumber + ' - ' + file.description,
        value: file.id,
      }));
    },
    getFile: (state) => state.file,
    getFilePublic: (state) => state.file_public,
    getReasons: (state) => state.reasons,
    getStatementModificationReasons: (state) => state.statement_reasons,
    getCustomReasons: (state) => {
      return state.reasons.map((reason) => ({
        label: reason.name,
        value: reason.id,
      }));
    },
    getFileServices: (state) => state.services,
    getFileItineraries: (state) => state.itineraries,
    getFileItinerariesProtected: (state) =>
      state.itineraries.filter(
        (itinerary) => itinerary.view_rate_protected && !itinerary.protected_rate
      ),
    getFilePassengers: (state) => state.passengers,
    getTotal: (state) => state.total,
    getCurrentPage: (state) => state.currentPage,
    getDefaultPerPage: (state) => state.defaultPerPage,
    getPerPage: (state) => state.perPage,
    getFilter: (state) => state.filter,
    getFilterByType: (state) => state.filterByType,
    _hasFilterBy: (state) => state.filterBy === null || state.filterBy,
    getFileItinerary: (state) => state.new_itinerary,
    getFileItinerariesReplace: (state) => state.itineraries_replace,
    getFileItinerariesServicesReplace: (state) => state.itineraries_services_replace,
    getCategories: (state) => state.categories,
    getDestinies: (state) => state.destinies,
    getTokenSearchHotels: (state) => state.token_search_hotels,
    getTokenSearchServices: (state) => state.token_search_services,
    getSearchParametersHotels: (state) => state.search_parameters_hotels,
    getSearchParametersServices: (state) => state.search_parameters_services,
    getFlagSearchHotels: (state) => state.flag_search_hotels,
    getFlagSearchServices: (state) => state.flag_search_services,
    getCity: (state) => state.city,
    getAllHotels: (state) => state.allHotels,
    getHotelsTop: (state) => state.hotelsTop,
    getHotels: (state) => state.hotels,
    getServices: (state) => state.services,
    getTotalServices: (state) => state.total_services,
    getPage: (state) => state.page,
    getPages: (state) => state.pages,
    getFilterServices: (state) => state.filter_services,
    getFilterHotels: (state) => state.filter_hotels,
    getStatusReasons: (state) => state.status_reasons,
    getAllPenality: (state) => state.all_penality,
    getAllPenalityCost: (state) => state.all_penality_cost,
    getPenalityADL: (state) => state.penality_adl,
    getPenalityCHD: (state) => state.penality_chd,
    getPenalityADLCost: (state) => state.penality_adl_cost,
    getPenalityCHDCost: (state) => state.penality_chd_cost,
    getPenalitySGL: (state) => state.penality_sgl,
    getPenalitySGLCost: (state) => state.penality_sgl_cost,
    getPenalityDBL: (state) => state.penality_dbl,
    getPenalityDBLCost: (state) => state.penality_dbl_cost,
    getPenalityTPL: (state) => state.penality_tpl,
    getPenalityTPLCost: (state) => state.penality_tpl_cost,
    getPenalityServices: (state) => {
      return [...new Set(state.penality_services)];
    },
    getPenalityHotels: (state) => {
      return [...new Set(state.penality_hotels)];
    },
    getProvider: (state) => state.provider,
    getFlagBoard: (state) => state.flag_in_board,
    getFlagSendBoard: (state) => state.flag_send_board,
    getFileCategories: (state) =>
      state.fileCategories.map((category) => ({
        value: category.id,
        label: category.name,
      })),
    getFileReasonStatement: (state) =>
      state.fileReasonStatement.map((reason) => ({
        value: reason.id,
        label: reason.name,
      })),
    getServiceEdit: (state) => state.serviceEdit,
    getServiceTemporaryCommunications: (state) => state.serviceTemporaryCommunications,
    getSearchServices: (state) => state.services_search,
    getServicesSelected: (state) => state.services_selected,
    getDefaultAdults: (state) => state.quantity_adults,
    getDefaultChildren: (state) => state.quantity_children,
    getSearchPassengers: (state) => state.search_passengers,
    getModifyPaxs: (state) => state.modify_paxs,
    getServiceMasterReplace: (state) => state.serviceMasterReplace,
    getServiceTemporaryNew: (state) => {
      if (state.serviceEdit && Array.isArray(state.serviceEdit.itinerary.services)) {
        const services = state.serviceEdit.itinerary.services;

        // Filtrar los servicios que cumplen con la condición de ser "nuevos"
        const newServices = services.filter((service) => {
          return (
            service.isNew === true &&
            service.isDeleted === false &&
            service.isReplaced === false &&
            service.replacedBy === null
          );
        });

        // Filtrar los servicios eliminados que tienen reemplazo
        const deletedReplacedServices = services.filter((service) => {
          return service.isDeleted === true && service.isReplaced === true;
        });

        // Filtrar los servicios nuevos que no están en replacedBy de ningún servicio eliminado
        return newServices.filter((newService) => {
          return !deletedReplacedServices.some(
            (deletedService) => deletedService.replacedBy === newService._id
          );
        }); // Retornar solo los servicios nuevos válidos
      }

      return []; // Devuelve un arreglo vacío si no se cumple la condición o si no hay servicios
    },
    getServiceTemporaryDeleted: (state) => {
      if (state.serviceEdit && Array.isArray(state.serviceEdit.itinerary.services)) {
        return state.serviceEdit.itinerary.services.filter((service) => {
          return (
            service.isDeleted === true && // Solo los eliminados
            service.isNew === false && // No debe ser nuevo
            service.isReplaced === false && // No debe estar reemplazado
            service.replacedBy === null // No debe haber sido reemplazado por otro
          );
        });
      }
      return [];
    },
    getServiceTemporaryReplaced: (state) => {
      if (state.serviceEdit && Array.isArray(state.serviceEdit.itinerary.services)) {
        return state.serviceEdit.itinerary.services
          .filter((service) => service.isReplaced === true) // Filtrar los servicios reemplazados
          .map((service) => {
            // Buscar el servicio con _id igual a replacedBy
            const replacedService = state.serviceEdit.itinerary.services.find(
              (s) => s._id === service.replacedBy
            );

            return {
              service_id: service.id, // El id del servicio reemplazado
              service_chance: replacedService || null, // El servicio reemplazante, o null si no se encuentra
            };
          });
      }
      return []; // Devuelve un arreglo vacío si no hay servicios reemplazados
    },
    getSimulations: (state) => state.simulations,
    getServiceInformation: (state) => state.service_information,
    getHotelInformation: (state) => state.hotel_information,
    getAsumedBy: (state) => state.options_asumed_by,
    getItinerariesTrash: (state) => state.itineraries_trash,
    getCommunications: (state) => state.communications,
    getMasterServicesCommunications: (state) => state.master_services,
    getFileReports: (state) => state.file_reports,
    getRoomingList: (state) => state.roomingList,
    getItineraryDetails: (state) => state.itineraryDetails,
    getIsIncludeTemporaryInSearch: (state) => state.includeTemporaryInSearch,
    getReportStatements: (state) => state.report_statements,
    getStatementDetails: (state) => state.statement_details,
    getLogsAWS: (state) => state.logs_aws,
    getStatementChanges: (state) => state.statements_changes,
    getServiceFrequences: (state) => state.service_frequences,
    getGroupSchedule: (state) => state.group_schedule,
    getServiceGroups: (state) => state.service_groups,
    getServiceHotelCode: (state) => state.file_hotel_code,
    getServiceProviders: (state) => state.file_providers,
    getReservations: (state) => state.reservations,
    getFilePendingProcesses: (state) => state.has_pending_processes,
    getFileProcess: (state) => state.file_process,
    getFileBalance: (state) => state.file_balance,
    getFileBalancePagination: (state) => state.file_pagination,
    getNewItinerariesIds: (state) => state.new_itineraries_ids,
  },
  actions: {
    clearSearchHotels() {
      this.flag_search_hotels = false;
    },
    clearSearchServices() {
      this.flag_search_services = false;
    },
    clearFile() {
      this.loading = true;
      this.file = {};
      this.statements_changes = null;
    },
    changeLoaded(_loaded) {
      this.loaded = _loaded;
    },
    initedAsync() {
      this.loading_async = true;
    },
    inited() {
      this.loading = true;
      this.loading_itinerary = true;
    },
    finished() {
      this.loading = false;
      this.loading_itinerary = false;
      this.loading_async = false;
    },
    initedStatements() {
      this.loading_statements = true;
    },
    finishedStatements() {
      this.loading_statements = false;
    },
    fetchAll({
      currentPage,
      perPage = DEFAULT_PER_PAGE,
      filter,
      filterBy,
      filterByType,
      executiveCode,
      clientId,
      dateRange,
      flag_stella,
    }) {
      this.loading = true;
      this.error = '';

      const filterNextDays = this.filterNextDays;
      const revisionStages = this.revisionStages;
      const opeAssignStages = this.opeAssignStages;

      const _dateRange =
        Array.isArray(dateRange) && dateRange.length === 2 ? dateRange.join(',') : '';

      return fetchFiles({
        currentPage: currentPage,
        perPage: perPage,
        filter: filter,
        filterBy: filterBy || this.filterBy,
        filterByType: filterByType || this.filterByType,
        executiveCode: executiveCode,
        clientId: clientId,
        dateRange: _dateRange,
        filterNextDays: filterNextDays,
        revisionStages: revisionStages,
        opeAssignStages: opeAssignStages,
        complete: 0,
        flag_stella,
      })
        .then(({ data }) => {
          this.total = data.pagination.total;
          this.currentPage = data.pagination.page;
          this.perPage = data.pagination.per_page;
          this.dateRange = dateRange;

          this.filter = filter;
          this.filterBy = filterBy;
          this.filterByType = filterByType;
          this.executiveCode = executiveCode;
          this.clientId = clientId;
          this.filterNextDays = filterNextDays;
          this.revisionStages = revisionStages;
          this.opeAssignStages = opeAssignStages;

          this.files = data.data.map((d) => createFilesAdapter(d));
          this.file = {};
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.error = error;
          this.loading = false;
        });
    },
    getInfoBasic({ fileId, loading = true }) {
      this.loading_basic = loading;
      return getFileBasic({ fileId })
        .then(({ data }) => {
          if (data.success) {
            this.file.statement = data.data.statement;
            this.file.lang = data.data.lang ?? '';
            this.file.suggested_accommodation_sgl = data.data.suggested_accommodation_sgl;
            this.file.suggested_accommodation_dbl = data.data.suggested_accommodation_dbl;
            this.file.suggested_accommodation_tpl = data.data.suggested_accommodation_tpl;
            this.file.clientCode = data.data.client_code;
            this.file.clientName = data.data.client_name;
            this.file.status = data.data.status.toLowerCase();
            this.file.statusReason = data.data.status_reason;
            this.file.statusReasonId = data.data.status_reason_id;
            this.file.processing = data.data.processing;
            this.file.dateIn = data.data.date_in;
            this.file.dateOut = data.data.date_out;
            this.file.revisionStages = data.data.revision_stages;
            this.file.opeAssignStages = data.data.ope_assign_stages;
            this.file.categories = data.data.categories;
            this.file.vips = data.data.vips.map(({ file_id, vip_id, vip, id }) => ({
              fileId: file_id,
              vipId: vip_id,
              vipName: vip.name,
              id,
            }));
          }

          this.loading_basic = false;
        })
        .catch((error) => {
          this.loading_basic = false;
          console.log(error);
        });
    },
    getById({ id, loading }) {
      if (loading == undefined || loading) {
        this.loading = true;
      }
      return getFile({ id })
        .then(({ data }) => {
          if (data.success) {
            localStorage.setItem('client_id', data.data.client_id);

            this.file = createFileAdapter(data.data);
            const normalizedItineraries = normalizeItineries(data.data.itineraries);
            this.itineraries = normalizedItineraries;
            this.executeSortItineraries();
          }

          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          console.log(error);
          // window.location.href = window.url_app + 'files/dashboard';
        });
    },
    getNewItineraries({ fileNumber, itineraryId }) {
      this.new_itineraries_ids = [];
      return getLatestItineraries({ fileNumber, itineraryId })
        .then(({ data }) => {
          this.new_itineraries_ids = data.data.map((itinerary) => itinerary.id);
        })
        .catch((error) => {
          console.log(error);
          // window.location.href = window.url_app + 'files/dashboard';
        });
    },
    putNewItineraries({ newItineraries }) {
      const itineraries_ = [
        ...this.itineraries.filter(
          (existing) => !newItineraries.some((newItem) => newItem.id === existing.id)
        ),
        ...newItineraries,
      ];

      const normalizedItineraries = normalizeItineries(itineraries_, false, true);
      this.itineraries = normalizedItineraries;
      this.executeSortItineraries();
    },
    getPassengersById({ fileId, loading = true }) {
      this.loading_passengers = loading;
      this.passengers = [];
      return getPassengers({ fileId })
        .then(({ data }) => {
          if (data.success) {
            this.passengers = data.data.map((s) => createFilePassengerAdapter(s));
          }
          this.loading_passengers = false;
        })
        .catch((error) => {
          this.loading_passengers = false;
          console.log(error);
          // window.location.href = window.url_app + 'files/dashboard';
        });
    },
    async searchServicesFrequences({ codes, loading = true }) {
      this.loading_async = loading;
      this.service_frequences = {};

      return filterServicesFrequences({ codes })
        .then(({ data }) => {
          if (!data.error) {
            this.service_frequences = data.data;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    getServiceSchedules(itinerary, day) {
      itinerary.flag_schedule_group = false;

      if (itinerary.entity !== 'hotel' && itinerary.entity !== 'flight') {
        if (
          this.service_schedules[itinerary.object_code] &&
          this.service_schedules[itinerary.object_code].schedule[day]
        ) {
          itinerary.flag_schedule_group = true;
          return this.service_schedules[itinerary.object_code].operations;
        }
      }

      return false;
    },
    async searchServiceSchedules({ codes, loading = true }) {
      this.loading_async = loading;
      this.service_schedules = {};

      return filterServicesSchedules({ codes })
        .then(({ data }) => {
          if (data.success) {
            this.service_schedules = data.data.services;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    async searchServicesGroups({ codes, loading = true }) {
      this.loading_async = loading;
      this.service_groups = {};

      return filterServicesGroups({ codes })
        .then(({ data }) => {
          if (!data.error) {
            this.service_groups = data.data;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    searchGroupSchedule({ code, group_code }) {
      this.group_schedule = {};

      return filterGroupSchedule({ code, group_code })
        .then(({ data }) => {
          if (!data.error) {
            this.group_schedule[code] = data.data;
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    getByNumber({ nrofile, loading }) {
      if (loading == undefined || loading) {
        this.loading = true;
      }

      return getFile({ nrofile })
        .then(({ data }) => {
          // this.file = data.data.data.map(d => createFileAdapter(d))
          localStorage.setItem('client_id', data.data.client_id);

          this.file = createFileAdapter(data.data);
          const normalizedItineraries = normalizeItineries(data.data.itineraries);
          this.itineraries = normalizedItineraries;
          this.executeSortItineraries();
          // this.passengers = data.data.passengers.map((s) => createFilePassengerAdapter(s));
          this.passengers = Array.isArray(data?.data?.passengers)
            ? data.data.passengers.map((s) => createFilePassengerAdapter(s))
            : [];
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          console.log(error);
          // window.location.href = window.url_app + 'files/dashboard';
        });
    },
    getByNumberPublic({ nrofile, data, loading }) {
      if (loading == undefined || loading) {
        this.loading = true;
      }
      return getFilePublic({ nrofile, data })
        .then(({ data }) => {
          this.loading = false;
          this.file_public = data.data;
          return data.data;
        })
        .catch((error) => {
          this.loading = false;
          console.log(error);
          return error;
          // window.location.href = window.url_app + 'files/dashboard';
        });
    },
    fetchStatusReasons() {
      this.loading_async = true;
      return getFileStatusReasons()
        .then(({ data }) => {
          if (data.success) {
            this.status_reasons = data.data;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    cancel(data) {
      this.loading = true;
      this.error = '';
      return cancelFile(data)
        .then(() => {
          this.file.id = 0;
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.error = error.data.error;
          this.loading = false;
        });
    },
    activate(fileId, params) {
      this.loading = true;
      this.error = '';
      return activateFile(fileId, params)
        .then(() => {
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.error = error.data.error;
          this.loading = false;
        });
    },
    async getFileItineraryById({ id, object_id }) {
      this.loading_itinerary = true;
      if (this.itineraries.length == 0) {
        await this.getById({ id, loading: false });
      }

      let response = this.itineraries.filter((itinerary) => itinerary.id == object_id);
      this.new_itinerary = response[0];

      if (this.new_itinerary.entity === 'hotel') {
        this.new_itinerary = {
          ...this.new_itinerary,
          rooms: this.new_itinerary.rooms
            .filter((room) => room.status === 1)
            .map((room) => ({
              ...room,
              units: room.units.filter((unit) => unit.status === 1),
            })),
        };
      }

      if (this.new_itinerary.entity == 'service') {
        const services = [];

        this.new_itinerary.send_communication = true;
        this.new_itinerary.penality = 0;

        this.new_itinerary.services.forEach((service) => {
          if (service.status === 1) {
            const compositions = [];

            service.compositions.forEach((composition) => {
              if (composition.status === 1) {
                compositions.push(composition);

                if (composition.penality && composition.penality.penality_price) {
                  this.new_itinerary.penality += parseFloat(composition.penality.penality_price);
                }

                this.new_itinerary.send_communication =
                  composition.supplier.send_communication.toUpperCase() === 'S';
              }
            });

            service.compositions = compositions;
            services.push(service);
          }
        });

        // Opcional: guardar los estados en el itinerario si los necesitas después
        this.new_itinerary.services = services;
      }

      setTimeout(() => {
        this.loading_itinerary = false;
      }, 100);
    },
    async getFileItineraryByMasterServiceId({ object_id }) {
      this.new_itinerary = this.itineraries.filter((itinerary) =>
        itinerary.services.filter((service) => service.id == object_id)
      );

      setTimeout(() => {
        this.loading_itinerary = false;
      }, 100);
    },
    async getFileItineraryByCompositionId({ object_id }) {
      this.new_itinerary = this.itineraries.filter((itinerary) =>
        itinerary.services.filter((service) =>
          service.compositions.filter((composition) => composition.id == object_id)
        )
      );

      setTimeout(() => {
        this.loading_itinerary = false;
      }, 100);
    },
    async getFileItineraryByRoomId({ object_id }) {
      this.loading_itinerary = true;

      this.itineraries.filter((itinerary) => {
        if (itinerary.entity == 'hotel') {
          itinerary.rooms.filter((room) => {
            if (room.id == object_id) {
              this.new_itinerary = itinerary;
              this.new_itinerary.rooms = [room];
            }
          });
        }
      });

      setTimeout(() => {
        this.loading_itinerary = false;
      }, 100);
    },
    getItineraryById({ itinerary_id }) {
      this.error = '';
      return getItinerary({ itinerary_id })
        .then(({ data }) => {
          this.itinerary = data.data;
        })
        .catch((error) => {
          this.error = error;
          console.log(error);
        });
    },
    search({ filter, perPage, clientId, executiveCode, dateRange, complete, flag_stella }) {
      this.fetchAll({
        currentPage: DEFAULT_PAGE,
        perPage: perPage,
        filter: filter,
        filterBy: this.filterBy,
        filterByType: this.filterByType,
        clientId: clientId,
        executiveCode: executiveCode,
        dateRange: dateRange,
        complete,
        flag_stella,
      });
    },
    fetchReasons() {
      this.error = '';
      this.reasons = [];
      return getFileReasons()
        .then(({ data }) => {
          if (data.success) {
            this.reasons = data.data;
          }
        })
        .catch((error) => {
          this.error = error;
          console.log(error);
        });
    },
    fetchStatementReasons() {
      this.error = '';
      this.statement_reasons = [];
      return getStatementReasons()
        .then(({ data }) => {
          if (data.success) {
            this.statement_reasons = data.data;
          }
        })
        .catch((error) => {
          this.error = error;
          console.log(error);
        });
    },
    fetchCategoriesHotel({ lang, client_id }) {
      this.loading_async = true;
      return getCategoriesHotel({ lang, client_id })
        .then(({ data }) => {
          this.categories = data.data;
          this.categories.unshift({
            class_id: '',
            class_name: 'Todos',
          });

          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    fetchDestiniesByClient({ client_id }) {
      this.loading_async = true;
      return getDestiniesByClient({ client_id })
        .then(({ data }) => {
          this.destinies = data.map((destiny) => {
            let chunks = destiny.label.split(',');
            let label = '';

            chunks.forEach((chunk, c) => {
              chunk = chunk.trim();
              label += c > 1 ? ', ' : label;
              label += c > 0 ? chunk : label;
            });

            // let label = destiny.label.replace(', g', ',').replace(',g', ', ');
            return {
              code: destiny.code,
              label: label,
            };
          });
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    fetchHotels(params, flag_promotion = false) {
      this.loading_async = true;
      this.hotelsTop = [];
      this.hotels = [];
      this.allHotels = [];
      this.city = {};

      if (flag_promotion) {
        params.promotional_rate = 1;
      }

      this.filter_hotels = params;
      this.flag_search_hotels = false;

      return getFilterSearchHotels(params)
        .then(({ data }) => {
          this.flag_search_hotels = true;

          if (data.success) {
            this.token_search_hotels = data.data[0].city.token_search;
            this.search_parameters_hotels = data.data[0].city.search_parameters;
            this.city = data.data[0].city;

            data.data[0].city.hotels.forEach((_hotel) => {
              this.allHotels.push(_hotel);
              if ((_hotel.popularity == 1 && this.hotelsTop.length < 7) || flag_promotion) {
                this.hotelsTop.push(_hotel);
              } else {
                this.hotels.push(_hotel);
              }
            });
          }

          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    changePageServices(page, perPage) {
      this.filter_services.page = page;
      this.filter_services.limit = perPage;
      this.fetchServices(this.filter_services);
    },
    fetchServices(params, flag_promotion = false) {
      this.loading_async = true;
      this.services = [];

      if (flag_promotion) {
        params.promotional_rate = 1;
      }

      this.filter_services = params;
      this.flag_search_services = false;

      return getFilterSearchServices(params)
        .then(async ({ data }) => {
          this.flag_search_services = true;

          if (data.success) {
            this.token_search_services = data.data.token_search;
            this.search_parameters_services = data.data.search_parameters;
            this.services = data.data.services;

            if (this.includeTemporaryInSearch) {
              let codes = this.services.map((service) => service.code);
              await this.fetchTemporaryServices({
                file_id: this.getFile.id,
                date: this.getFile.dateIn,
                codes: codes,
              });
              console.log('TemporaryServices: ', this.temporaryServices);

              // Combinar resultados
              this.temporaryServices.forEach((tempService) => {
                let index = this.services.findIndex(
                  (service) => service.code === tempService.object_code
                );

                if (index !== -1) {
                  // Insertar el servicio temporal en el siguiente índice
                  this.services.splice(index + 1, 0, tempService);
                }
              });
            }

            this.total_services = data.data.quantity_services;
            this.pages = data.data.last_page;
          } else {
            this.token_search_services = '';
            this.search_parameters_services = {};
            this.services = [];
            this.total_services = 0;
            this.pages = 0;
          }

          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    changePage({ currentPage, perPage, filter, clientId, executiveCode, dateRange, flag_stella }) {
      this.currentPage = currentPage;
      this.fetchAll({
        currentPage: currentPage,
        perPage,
        filter,
        filterBy: this.filterBy,
        filterByType: this.filterByType,
        clientId,
        executiveCode,
        dateRange,
        flag_stella,
      });
    },
    sortBy({ filterBy, filterByType, flag_stella }) {
      this.filterBy = filterBy;
      this.filterByType = filterByType;
      this.fetchAll({
        currentPage: DEFAULT_PAGE,
        filter: this.filter,
        filterBy,
        filterByType,
        clientId: this.clientId,
        executiveCode: this.executiveCode,
        dateRange: this.dateRange,
        flag_stella,
      });
    },
    update({ id, description, dateIn, passengers, lang }) {
      this.loading_basic = true;
      return updateFile({
        id,
        description,
        dateIn,
        passengers,
        lang,
      })
        .then((data) => {
          console.log('RESPONSE: ', data);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    accommodations({ file_number, type, itinerary_id, object_id, passengers }) {
      return updateAccommodations({
        file_number,
        itinerary_id,
        type,
        object_id,
        passengers,
      })
        .then(() => {
          // this.loading_async = false;
          /*
          if (type == 'room' || type == 'unit') {
            this.itineraries.forEach((itinerary) => {
              if (itinerary.id == itinerary_id) {
                itinerary.isLoading = true;
              }
            });
          }
          */
        })
        .catch((error) => {
          console.log(error);
        });
    },
    putUpdateAmounts(data) {
      return updateAmounts(data)
        .then(() => {
          // this.loading = false
        })
        .catch((error) => {
          console.log(error);
        });
    },
    add_modify(data, loading) {
      this.loading = true;
      return addItinerary(data)
        .then(() => {
          if (typeof loading == 'undefined' || !loading) {
            this.loading = false;
          }
        })
        .catch((error) => {
          console.log(error);
          if (typeof loading == 'undefined' || !loading) {
            this.loading = false;
          }
        });
    },
    delete(data, loading = true) {
      this.loading = loading;
      return deleteItem(data)
        .then(() => {
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    putSearchPassengers(data) {
      this.search_passengers = data;
    },
    putFileItinerariesServiceReplace(data, flag_return) {
      let passengers = [];
      for (const pax of this.passengers) {
        if (data.passengers !== undefined && data.passengers.indexOf(pax.id) > -1) {
          passengers.push(pax);
        }
      }

      let fileItineraryServiceReplace = createFileItineraryServiceReplaceAdapter(
        data.service,
        data.rate,
        data.adults,
        data.children,
        data.quantity,
        data.price,
        data.token_search,
        data.search_parameters_services,
        passengers
      );

      if (typeof flag_return == 'undefined' || !flag_return) {
        this.itineraries_services_replace.push(fileItineraryServiceReplace);
      } else {
        return fileItineraryServiceReplace;
      }
    },
    clearFileItineraryHotelsReplace() {
      this.itineraries_replace = [];
    },
    clearFileItineraryServiceReplace() {
      this.itineraries_services_replace = [];
    },
    removeFileItineraryServiceReplace(index) {
      this.itineraries_services_replace.splice(index, 1);
    },
    putFileItinerariesReplace(data, flag_return) {
      const passengers = this.passengers.filter((pax) => data.passengers?.includes(pax.id));

      let fileItineraryReplace = createFileItineraryReplaceAdapter(
        data.hotel,
        data.quantity,
        data.token_search,
        data.search_parameters,
        passengers
      );

      let flag_new_room = true;

      for (const itinerary of this.itineraries_replace) {
        if (itinerary.id === fileItineraryReplace.id) {
          console.log('Coincidencia de itinerario encontrada:', itinerary.id); // DEBUG

          for (const room of itinerary.rooms) {
            // Antes estaba `fileItineraryReplace.rooms`
            console.log('Comparando room_id:', room.room_id, 'con', data.room.room_id); // DEBUG
            if (room.room_id === data.room.room_id) {
              flag_new_room = false;
              room.rates.push(data.rate);
              console.log('Se encontró la habitación, flag_new_room ahora es false'); // DEBUG
              break; // Optimizamos saliendo del bucle al encontrar coincidencia
            }
          }
        }
      }

      console.log('Flag después del bucle:', flag_new_room); // DEBUG

      if (flag_new_room) {
        let fileItineraryRoomReplace = createFileItineraryRoomReplaceAdapter(data.room);
        fileItineraryReplace.rooms.push(fileItineraryRoomReplace);
        fileItineraryReplace.rooms[0].rates.push(data.rate);
      }

      return typeof flag_return === 'undefined' || !flag_return
        ? this.itineraries_replace.push(fileItineraryReplace)
        : fileItineraryReplace;
    },
    removeFileItineraryReplace(index) {
      this.itineraries_replace.splice(index, 1);
    },
    calculatePenalityService(type, object_id) {
      if (type !== 'itinerary') return 0;

      const itinerary = this.itineraries.find((itinerary) => itinerary.id === object_id);
      if (!itinerary) return 0;

      const total = itinerary.services.reduce((totalPenality, service) => {
        const servicePenality = service.compositions.reduce(
          (sum, composition) => sum + (composition.penality?.penality_sale || 0),
          0
        );
        return parseFloat(totalPenality) + parseFloat(servicePenality);
      }, 0);

      itinerary.penalty = total;

      return total;
    },
    calculatePenalityRoomsCost(rooms = [], _rooms = []) {
      const totalPenality = rooms
        .filter(({ id }) => _rooms.includes(id)) // Filtra habitaciones cuyos `id` están en `_rooms`
        .reduce(
          (roomTotal, { units }) =>
            roomTotal +
            units.reduce((unitTotal, { penality }) => unitTotal + penality.penalty_cost, 0),
          0
        );

      return totalPenality;
    },
    calculatePenalityRoomUnitsCost(rooms = [], _units = []) {
      const totalPenality = rooms.reduce(
        (roomTotal, { units }) =>
          roomTotal +
          units
            .filter(({ id }) => _units.includes(id))
            .reduce((unitTotal, { penality }) => unitTotal + penality.penalty_cost, 0),
        0
      );

      return totalPenality;
    },
    calculatePenalityRoomsSale(rooms = [], _rooms = []) {
      const totalPenality = rooms
        .filter(({ id }) => _rooms.includes(id)) // Filtra habitaciones cuyos `id` están en `_rooms`
        .reduce(
          (roomTotal, { units }) =>
            roomTotal +
            units.reduce((unitTotal, { penality }) => unitTotal + penality.penalty_sale, 0),
          0
        );

      return totalPenality;
    },
    calculatePenalityRoomUnitsSale(rooms = [], _units = []) {
      const totalPenality = rooms.reduce(
        (roomTotal, { units }) =>
          roomTotal +
          units
            .filter(({ id }) => _units.includes(id))
            .reduce((unitTotal, { penality }) => unitTotal + penality.penalty_sale, 0),
        0
      );

      return totalPenality;
    },
    calculatePenalties(price, price_cost, adults, children) {
      this.penality_adl += adults > 0 ? price / adults : 0;
      this.penality_chd += children > 0 ? price / children : 0;

      this.penality_adl_cost += adults > 0 ? price_cost / adults : 0;
      this.penality_chd_cost += children > 0 ? price_cost / children : 0;

      this.all_penality += price;
      this.all_penality_cost += price_cost;
    },
    clearPenality() {
      this.all_penality = 0;
      this.all_penality_cost = 0;

      this.penality_adl = 0;
      this.penality_chd = 0;

      this.penality_adl_cost = 0;
      this.penality_chd_cost = 0;

      this.penality_services = [];
      this.penality_hotels = [];
    },
    processPenalties(price, price_cost, adults, children, collection, itinerary) {
      if (price > 0) {
        this.calculatePenalties(price, price_cost, adults, children);
        collection.push(itinerary);
        return true; // Marca que se agregó una penalidad
      }
      return false; // No se agregó ninguna penalidad
    },
    calculatePenality(type, items = []) {
      this.clearPenality();
      this.loading_async = true;
      // Inicialización de variables
      this.all_penality = 0;
      this.all_penality_cost = 0;
      this.penality_adl = 0;
      this.penality_chd = 0;
      this.penality_sgl = 0;
      this.penality_dbl = 0;
      this.penality_tpl = 0;
      this.penality_services = [];
      this.penality_hotels = [];

      // Iteración de itinerarios
      this.itineraries.forEach((itinerary) => {
        if (itinerary.status) {
          itinerary.penality = 0;
          itinerary.penality_cost = 0;

          if (
            (itinerary.entity === 'service' || itinerary.entity === 'service-temporary') &&
            (!type || ['itinerary', 'service', 'composition'].includes(type))
          ) {
            if (items.length === 0 || items.includes(itinerary.id)) {
              itinerary.services.forEach((service) => {
                service.compositions.forEach((composition) => {
                  const price = parseFloat(composition.penality?.penality_sale || 0);
                  const price_cost = parseFloat(composition.penality?.penality_cost || 0);
                  if (
                    this.processPenalties(
                      price,
                      price_cost,
                      itinerary.adults,
                      itinerary.children,
                      this.penality_services,
                      itinerary
                    )
                  ) {
                    itinerary.penality += price;
                    itinerary.penality_cost += price_cost;
                  }
                });
              });
            }
          }

          if (itinerary.entity === 'hotel' && (!type || ['hotel', 'room', 'unit'].includes(type))) {
            itinerary.rooms.forEach((room) => {
              if (!type || type !== 'room' || items.length === 0 || items.includes(room.id)) {
                room.units.forEach((unit) => {
                  const price_ = parseFloat(unit.penality?.penalty_sale || 0);
                  const price_cost_ = parseFloat(unit.penality?.penalty_cost || 0);

                  let price = 0;
                  let price_cost = 0;

                  switch (unit.accommodations.length) {
                    case 1:
                      {
                        price = price_;
                        price_cost = price_cost_;

                        this.penality_sgl += price_;
                        this.penality_sgl_cost += price_cost_;
                      }
                      break;
                    case 2:
                      {
                        price = price_ / 2;
                        price_cost = price_cost_ / 2;

                        this.penality_dbl += price;
                        this.penality_dbl_cost += price_cost;
                      }
                      break;
                    case 3:
                      {
                        price = price_ / 3;
                        price_cost = price_cost_ / 3;

                        this.penality_tpl += price;
                        this.penality_tpl_cost += price_cost;
                      }
                      break;
                  }

                  if (
                    (!type || type !== 'unit' || items.length === 0 || items.includes(unit.id)) &&
                    this.processPenalties(
                      price,
                      price_cost,
                      unit.adult_num,
                      unit.child_num,
                      this.penality_hotels,
                      itinerary
                    )
                  ) {
                    itinerary.penality += price;
                    itinerary.penality_cost += price_cost;
                  }
                });
              }
            });
          }
        }
      });

      setTimeout(() => {
        this.loading_async = false;
      }, 100);
    },
    showRoomType(room_type) {
      if (room_type == 1) {
        return 'files.label.single';
      }

      if (room_type == 2) {
        return 'files.label.double';
      }

      if (room_type == 3) {
        return 'files.label.triple';
      }
    },
    showServiceIcon(service_category_id, service_sub_category_id = 0, service_type_id = 2) {
      service_category_id = parseInt(service_category_id);
      service_sub_category_id = parseInt(service_sub_category_id);
      service_type_id = parseInt(service_type_id);

      if (service_category_id === 1) {
        if (service_type_id === 2) {
          return ['fas', 'car-rear']; // Traslado privado..
        } else {
          return ['fas', 'bus-simple']; // Traslado compartido..
        }
      }

      if (service_category_id === 2) {
        // Tour Paquete..
        return ['fas', 'route'];
      }

      if (service_category_id === 7) {
        return ['fas', 'ticket']; // Entrada..
      }

      if (service_category_id === 9 && service_sub_category_id === 4) {
        // Tour..
        if (service_type_id === 2) {
          return ['fas', 'person-hiking']; // Tour..
        } else {
          return ['fas', 'people-group'];
        }
      }

      if (service_category_id === 9) {
        if (service_sub_category_id === 3) {
          if (service_type_id === 2) {
            return ['fas', 'person-hiking']; // Caminata..
          } else {
            return ['fas', 'people-group'];
          }
        }
        if (service_sub_category_id === 5) {
          // Nocturno..
          return ['fas', 'clipboard-check'];
        }
      }

      if (service_category_id === 10) {
        return ['fas', 'utensils']; // Almuerzo..
      }

      if (service_category_id === 11) {
        return ['fas', 'star']; // Guía..
      }

      if (service_category_id === 12) {
        return ['fas', 'life-ring']; // Asistencia..
      }

      if (service_category_id === 13) {
        return ['fas', 'train-subway']; // Tren..
      }

      if (service_category_id === 14) {
        return ['fas', 'tags']; // Misc..
      }

      return '';
    },
    fetchProviders(object_code) {
      this.loading_async = true;
      this.provider = {
        contacts: [],
      };

      return fetchAllProviders(object_code)
        .then(({ data }) => {
          if (!data.data.error) {
            console.log(data.data[0]);
            this.provider = data.data[0];
          }
          this.loading_async = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading_async = false;
        });
    },
    verifyQuote() {
      this.loading_async = true;
      return verifyQuoteA2()
        .then(({ data }) => {
          if (data.success) {
            this.flag_in_board = data.data.flag_in_board.success;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading_async = false;
        });
    },
    sendQuote(fileId, params) {
      this.loading_async = true;
      this.error = '';
      return sendQuoteA2(fileId, params)
        .then(({ data }) => {
          this.flag_send_board = data.success;
          this.loading_async = false;
        })
        .catch((error) => {
          console.log(error);
          this.error = error;
          this.loading_async = false;
        });
    },
    downloadGenerate(data, fileName) {
      const url = window.URL.createObjectURL(new Blob([data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', fileName ?? 'passengers.xlsx');
      document.body.appendChild(link);
      link.click();
      window.URL.revokeObjectURL(url); // Limpia el URL del blob
      this.loading = false;
    },
    downloadPassengerExcel({ fileId }) {
      this.loading_download = true;

      return passengerDownload({ fileId })
        .then(({ data }) => {
          this.downloadGenerate(data);
          this.loading_download = false;
        })
        .catch((error) => {
          this.loading_download = false;
          console.log(error);
        });
    },
    downloadPassengerExcelAmadeus({ fileId }) {
      this.loading_async = true;

      return passengerDownloadAmadeus({ fileId })
        .then(({ data }) => {
          this.downloadGenerate(data);
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    downloadPassengerExcelPerurail({ fileId }) {
      this.loading_async = true;

      return passengerDownloadPerurail({ fileId })
        .then(({ data }) => {
          this.downloadGenerate(data);
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    updatePassengers({ fileId, fileNumber, data }) {
      this.loading_passengers = true;
      return updateAllPassengers({ fileId, fileNumber, data })
        .then(() => {
          // this.loading_passengers = false;
        })
        .catch((error) => {
          this.loading_passengers = false;
          notification.error({
            message: 'Modificación de Pasajeros',
            description: error.message,
          });
          console.log(error);
        });
    },
    storeRepository({ fileNumber, data }) {
      const flagSend = data.some((pax) => !!pax.document_url);

      if (flagSend) {
        return handleStoreRepository({ fileNumber, data })
          .then(() => {
            this.loading_async = false;
          })
          .catch((error) => {
            console.log(error);
            this.loading_async = false;
          });
      }
    },
    formatGuests(passengers) {
      let guests = [];

      passengers.forEach((pax) => {
        guests.push({
          id: pax.id,
          country_iso: pax.country_iso,
          date_birth: dayjs(pax.date_birth, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          dietary_restrictions: pax.dietary_restrictions,
          doctype_iso: pax.doctype_iso,
          document_number: pax.document_number,
          document_type_id: pax.document_type_id,
          email: pax.email,
          genre: pax.genre,
          medical_restrictions: pax.medical_restrictions,
          name: pax.name,
          phone: pax.phone,
          phone_code: pax.phone_code,
          room_type: pax.room_type,
          room_type_description: pax.room_type_description,
          surnames: pax.surnames,
          label: `${pax.name} ${pax.surnames}`,
          document_url: pax.document_url,
          given_name: pax.name,
          surname: pax.surnames,
          type: pax.type,
        });
      });

      return guests;
    },
    isConfirmationCodeRoom(room) {
      const confirmation_code = room.units[0]?.confirmation_code || '';
      const flag_confirmation = room.units.every(
        (unit) => unit.confirmation_code && unit.confirmation_code === confirmation_code
      );

      return {
        confirmation_code,
        flag_confirmation,
      };
    },
    validateConfirmationCode(hotel) {
      let confirmation_code = '';
      let items = [];

      hotel.rooms.forEach((room) => {
        room.units.forEach((unit) => {
          if (items.indexOf(unit.confirmation_code) > -1) {
            confirmation_code = unit.confirmation_code;
          }
        });
      });

      return confirmation_code;
    },
    async storeBasicFile(data) {
      this.loading_async = true;
      try {
        const response = await createBasicFile(data);
        return response.data;
      } catch (error) {
        console.error('Error en storeBasicFile:', error);
        handleError(error);
        throw error;
      } finally {
        this.loading_async = false;
      }
    },
    async cloneBasicFile(fileId, data) {
      this.loading_async = true;
      try {
        const response = await cloneBasicFile(fileId, data);
        return response.data;
      } catch (error) {
        console.error('Error en cloneBasicFile:', error);
        handleError(error.data.error);
        throw error;
      } finally {
        this.loading_async = false;
      }
    },
    fetchFileCategories() {
      return getFileCategories()
        .then(({ data }) => {
          this.fileCategories = data.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },
    fetchFileReasonStatement() {
      return getReasonStatement()
        .then(({ data }) => {
          this.fileReasonStatement = data.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },
    fetchCompletedFiles({ currentPage, perPage = DEFAULT_PER_PAGE, filter = '' }) {
      this.loading = true;
      this.error = '';

      return fetchFiles({
        currentPage,
        perPage,
        filter,
        complete: 1,
      })
        .then((response) => {
          if (response && response.data && Array.isArray(response.data.data)) {
            this.filesCompleted = response.data.data.map((d) => createFilesAdapter(d));
          } else {
            throw new Error('Unexpected API response structure');
          }
        })
        .catch((error) => {
          console.error('Error fetching completed files:', error);
          this.error = error.message || 'An error occurred while fetching completed files';
          this.filesCompleted = [];
          throw error; // Re-throw the error to allow further handling
        })
        .finally(() => {
          this.loading = false;
        });
    },
    searchCompleted({ filter = '', perPage = 10 }) {
      return this.fetchCompletedFiles({
        currentPage: DEFAULT_PAGE,
        perPage,
        filter,
      }).catch((error) => {
        console.error('Error in searchCompleted:', error);
        this.error = error.message || 'An error occurred while searching completed files';
      });
    },
    searchMasterServices({ type_service = '', filter = '', page = 1, perPage = 10 }) {
      return getMasterServices({
        currentPage: page,
        perPage,
        type_service,
        filter,
      }).catch((error) => {
        console.error('Error in searchCompleted:', error);
        this.error = error.message || 'An error occurred while searching completed files';
      });
    },
    setServiceTemporaryCreated(service) {
      this.serviceTemporaryCreated = service;
    },
    setServiceTemporaryCommunications(service) {
      // Crear una copia del servicio y agregar showNotes a cada objeto en los arreglos
      this.serviceTemporaryCommunications = {
        ...service,
        reservations: service.reservations.map((item) => ({
          ...item,
          showNotes: false,
          notas: '',
          attachments: [],
        })),
        cancellation: service.cancellation.map((item) => ({
          ...item,
          showNotes: false,
          notas: '',
          attachments: [],
        })),
        modification: service.modification.map((item) => {
          const hasRequiredFields =
            item.hasOwnProperty('code_request_book') &&
            item.hasOwnProperty('supplier_name') &&
            item.hasOwnProperty('html');

          return {
            ...item,
            hasOneCommunications: hasRequiredFields,
            showNotes: false,
            notas: '',
            attachments: [],
          };
        }),
      };
    },

    getEmailsServiceTemporaryCommunications(type, index, typeFrom = null, indexFrom = null) {
      if (typeFrom == null || indexFrom == null) {
        return this.serviceTemporaryCommunications[type][index].supplier_emails;
      } else {
        return this.serviceTemporaryCommunications[type][index][typeFrom][indexFrom]
          .supplier_emails;
      }
    },
    setEmailsSuppliersServiceTemporaryCommunications(
      emails,
      type,
      index,
      typeFrom = null,
      indexFrom = null
    ) {
      if (typeFrom == null || indexFrom == null) {
        this.serviceTemporaryCommunications[type][index].supplier_emails.push(...emails);
      } else {
        this.serviceTemporaryCommunications[type][index][typeFrom][indexFrom].supplier_emails.push(
          ...emails
        );
      }
    },
    setDeleteEmailSuppliersServiceTemporaryCommunications(type, indexSupplier, indexEmail) {
      // Validamos que el tipo y el índice sean válidos antes de proceder
      if (
        this.serviceTemporaryCommunications[type] &&
        this.serviceTemporaryCommunications[type][indexSupplier] &&
        this.serviceTemporaryCommunications[type][indexSupplier].supplier_emails[indexEmail] !==
          undefined
      ) {
        // Eliminamos el correo usando splice en el índice específico
        this.serviceTemporaryCommunications[type][indexSupplier].supplier_emails.splice(
          indexEmail,
          1
        );
      }
    },
    setServiceEdit(service) {
      this.serviceEdit = service;
      // Reiniciamos los estados de los servicios para limpiar cualquier marca previa
      if (this.serviceEdit && Array.isArray(this.serviceEdit.itinerary.services)) {
        let totalPenalties = 0;
        this.serviceEdit.itinerary.name_original = service.itinerary.name;
        this.serviceEdit.itinerary.details = [
          {
            language_id: 1,
            language_iso: 'es',
            name: service.itinerary.name,
            itinerary: service.itinerary.service_itinerary,
            skeleton: service.itinerary.service_summary,
          },
        ];
        this.serviceEdit.itinerary.services.forEach((service) => {
          service._id = service.id + '_' + service.master_service_id; // Indicar que estos servicios no son nuevos (originales)
          service.isNew = false; // Indicar que estos servicios no son nuevos (originales)
          service.isDeleted = false; // No están marcados como eliminados
          service.isReplaced = false; // No están marcados como reemplazados
          service.replacedBy = null; // Ningún servicio los ha reemplazado
          service.totalPenalties = 0; // Total de penalidades
          service.date_in = this.serviceEdit.itinerary.date_in;
          service.start_time = this.serviceEdit.itinerary.start_time;
          // Verificamos que el servicio tenga composiciones y que sea un arreglo, si no lo es, lo añadimos
          // Verificamos que el servicio tenga composiciones y que sea un arreglo
          if (Array.isArray(service.compositions)) {
            // Iteramos sobre las composiciones y sumamos las penalidades
            service.compositions.forEach((composition) => {
              if (composition.penality && composition.penality.penality_sale > 0) {
                // Sumamos el precio de penalidad
                totalPenalties += parseFloat(composition.penality.penality_sale) || 0;
                service.totalPenalties += parseFloat(composition.penality.penality_sale) || 0;
              }
            });
          }
        });
        this.serviceEdit.itinerary.total_amount_penalties = totalPenalties;
      }
    },
    removeServiceFromEdit(serviceId) {
      // Verificar la cantidad de servicios activos (no eliminados)
      const activeServices = this.serviceEdit.itinerary.services.filter(
        (service) => !service.isDeleted
      );

      if (activeServices.length <= 1) {
        const message =
          'No se puede eliminar el último servicio. Debe haber al menos un servicio en la lista.';
        return { success: false, message };
      }

      // Buscar el servicio en `serviceEdit.itinerary.services`
      const service = this.serviceEdit.itinerary.services.find((s) => s._id === serviceId);

      if (service) {
        if (service.isNew) {
          //Actualizar todos los servicios que apuntan a este como replacedBy
          const itineraryServices = this.serviceEdit.itinerary.services;
          itineraryServices.forEach((service) => {
            // Si algún servicio tiene reemplazado por el que estamos reemplazando, actualizarlo
            if (service.isReplaced && service.replacedBy === serviceId) {
              service.replacedBy = null;
              service.isReplaced = false;
            }
          });

          // Si el servicio es nuevo, lo eliminamos completamente de la lista
          this.serviceEdit.itinerary.services = this.serviceEdit.itinerary.services.filter(
            (s) => s._id !== serviceId
          );
        } else {
          // Si es un servicio existente, lo marcamos como eliminado
          service.isDeleted = true;
        }
        return { success: true, message: 'Servicio eliminado correctamente.' };
      } else {
        const message = 'El servicio no se encontró en la lista.';
        return { success: false, message };
      }
    },
    addSelectedMasterServicesToItinerary() {
      const masterServiceStore = useMasterServiceStore();
      // Verificar si `serviceEdit` y `serviceEdit.itinerary.services` existen
      if (!this.serviceEdit || !this.serviceEdit.itinerary) {
        console.error('El objeto serviceEdit o itinerary no está definido.');
        return;
      }

      if (!Array.isArray(this.serviceEdit.itinerary.services)) {
        this.serviceEdit.itinerary.services = [];
      }

      // Agregar los servicios seleccionados al `itinerary.services`
      masterServiceStore.getSelectedMasterServices.forEach((service) => {
        // Evita agregar servicios duplicados
        if (!this.serviceEdit.itinerary.services.some((s) => s._id === service._id)) {
          this.serviceEdit.itinerary.services.push(service);
        }
      });
    },
    updateNameServiceEdit(name) {
      if (this.serviceEdit.itinerary.name !== name) {
        this.serviceEdit.itinerary.name = name;
      }
    },
    updateCategoryServiceEdit(service_category_id) {
      if (this.serviceEdit.itinerary.service_category_id !== service_category_id) {
        this.serviceEdit.itinerary.service_category_id = service_category_id;
      }
    },
    updateSubCategoryServiceEdit(service_sub_category_id) {
      if (this.serviceEdit.itinerary.service_sub_category_id !== service_sub_category_id) {
        this.serviceEdit.itinerary.service_sub_category_id = service_sub_category_id;
      }
    },
    updateTypeServiceEdit(service_type_id) {
      if (this.serviceEdit.itinerary.service_type_id !== service_type_id) {
        this.serviceEdit.itinerary.service_type_id = service_type_id;
      }
    },
    updateTextServiceEdit(languages, textSkeleton, textItineraries) {
      this.serviceEdit.itinerary.details = [];
      languages.value.forEach((language) => {
        const languageIso = language.value;
        const languageId = language.id;

        // Buscar los textos traducidos para el idioma actual
        const itineraryText = textItineraries[languageIso] || '';
        const skeletonText = textSkeleton[languageIso] || '';
        if (itineraryText || skeletonText) {
          // Crear el objeto de detalles para el idioma
          this.serviceEdit.itinerary.details.push({
            language_id: languageId,
            language_iso: languageIso,
            itinerary: itineraryText,
            skeleton: skeletonText,
          });
        }
      });
    },
    setSearchServices: function (_services, _search_parameters) {
      this.services_search = _services;
      this.search_parameters_services = _search_parameters;
    },
    putServicesSelected: function (_service) {
      this.services_selected.push(_service);
    },
    getQuantityAdults: function (_passengers) {
      let quantity = 0;
      this.passengers.forEach((passenger) => {
        if (_passengers.indexOf(passenger.id) > -1 && passenger.type == 'ADL') {
          quantity += 1;
        }
      });
      this.quantity_adults = quantity;
      return quantity;
    },
    getQuantityChildren: function (_passengers) {
      let quantity = 0;
      this.passengers.forEach((passenger) => {
        if (_passengers.indexOf(passenger.id) > -1 && passenger.type == 'CHD') {
          quantity += 1;
        }
      });
      this.quantity_children = quantity;
      return quantity;
    },
    putQuantityPassengers: async function (object_id, data) {
      return updateQuantityPassengers(object_id, data)
        .then(({ data }) => {
          console.log('DATA: ', data);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    fetchModifyPaxs: async function () {
      this.loading_modify_paxs = true;
      this.modify_paxs = [];

      return getAllModifyPaxs(this.file.id)
        .then(({ data }) => {
          if (data.success) {
            this.modify_paxs = data.data.map((pax) => {
              pax.label = `${pax.name} ${pax.surnames}`;
              return pax;
            });
          }

          this.loading_modify_paxs = false;
        })
        .catch((error) => {
          this.loading_modify_paxs = false;
          console.log(error);
        });
    },
    setServiceMasterReplace(service) {
      this.serviceMasterReplace = service;
    },
    addSimulation(simulation) {
      this.simulations.push(simulation);
    },
    clearSimulations() {
      this.simulations = [];
    },
    findServiceInformation(object_id, date_out, paxs) {
      const lang = localStorage.getItem('lang');
      this.loading_async = true;
      this.service_information = false;

      return fetchServiceInformation(object_id, lang, date_out, paxs)
        .then(({ data }) => {
          this.service_information = data;
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    findHotelInformation(object_id) {
      const lang = localStorage.getItem('lang');
      this.hotel_information = false;

      return fetchHotelInformation(object_id, lang)
        .then(({ data }) => {
          if (data.success) {
            this.hotel_information = data.data;
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    async fetchAsumedBy() {
      this.loading_async = true;

      return getFileReasons({ process: 'exonerar_penalidad' })
        .then(({ data }) => {
          if (data.success) {
            this.options_asumed_by = data.data.map((asumed_by) => {
              let asumed = { label: asumed_by.name, value: asumed_by.id };
              return asumed;
            });
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    updateFlagRateProtected(fileId, itineraryId) {
      // Actualizar la vista de los servicios con markups modificados..
      return putFlagRateProtected(fileId, itineraryId)
        .then(({ data }) => {
          console.log('DATA: ', data);
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    updateFlagFileRateProtected(fileId) {
      // Actualizar la vista del file con markups modificados..
      return putFlagFileRateProtected(fileId)
        .then(({ data }) => {
          console.log('DATA: ', data);
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    async saveCategoriesFile(fileId, params) {
      return updateCategoriesFile(fileId, params)
        .then(({ data }) => {
          console.log('ACTUALIZACION DE CATEGORIAS: ', data);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    clearItinerariesTrash() {
      this.itineraries_trash = [];
    },
    updateItineraryTrash(_itinerary) {
      const items = this.itineraries_trash.map((itinerary) => itinerary.id);
      let index = items.indexOf(_itinerary.id);

      if (index > -1) {
        this.itineraries_trash.splice(index, 1);
      } else {
        this.itineraries_trash.push(_itinerary);
      }
    },
    fetchCommunicationsNew(type, fileId, fileItineraryId, params) {
      this.loading_async = true;
      this.communications = [];

      return searchCommunicationsNew(type, fileId, fileItineraryId, params)
        .then(({ data }) => {
          if (data.success) {
            this.communications = data.data;
          }

          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    fetchCommunicationsCancellation(fileId, serviceId, params) {
      this.loading_async = true;
      this.communications = [];

      return searchCommunicationsCancellation(fileId, serviceId, params)
        .then(({ data }) => {
          if (data.success) {
            this.communications = data.data;
          }

          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    fetchRoomingList(fileId) {
      this.loading_async = true;
      this.roomingList = [];

      return fetchRoomingListData(fileId)
        .then(({ data }) => {
          if (data.success) {
            this.roomingList = data.data.hotels;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    async fetchMasterServices(codes) {
      this.loading_async = true;
      return findMasterServices(codes)
        .then(({ data }) => {
          if (data.success) {
            this.master_services = data.data;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    async fetchFileReports(fileId, loading = true, fileItineraryId = 0) {
      this.loading_reports = loading;
      return searchFileReports(fileId)
        .then(({ data }) => {
          if (data.success) {
            const reports = data.data;

            if (loading) {
              this.file_reports = reports;
            } else {
              this.file_reports.total_ws = reports.total_ws;
              this.file_reports.total_rq = reports.total_rq;

              for (const section in reports) {
                const sectionData = reports[section];

                let ignore = [];

                if (Array.isArray(sectionData) || typeof sectionData === 'object') {
                  sectionData.forEach((report) => {
                    if (report.file_itinerary_id == fileItineraryId) {
                      const sectionData = this.file_reports[section];

                      if (Array.isArray(sectionData)) {
                        const index = sectionData.findIndex(
                          (item) =>
                            item.file_itinerary_id == fileItineraryId &&
                            ignore.indexOf(item.id) === -1
                        );

                        if (index !== -1) {
                          const updatedItem = {
                            ...sectionData[index],
                            ...report,
                            ...{
                              isLoading: false,
                            },
                          };

                          // Reasignar de forma reactiva (por ejemplo, en Vue)
                          this.file_reports[section][index] = updatedItem;
                          ignore.push(updatedItem.id);
                        }
                      }
                    }
                  });
                }
              }
            }
          }

          this.loading_reports = false;
        })
        .catch((error) => {
          this.loading_reports = false;
          console.log(error);
        });
    },
    async updateServiceNotes({ type, fileId, itineraryId = 0, params }) {
      this.loading_notes = true;
      return putServiceNotes({ type, fileId, itineraryId, params })
        .then(({ data }) => {
          this.loading_notes = false;
          if (data.success) {
            return data.data;
          }
        })
        .catch((error) => {
          this.loading_notes = false;
          console.log(error);
        });
    },
    fetchItineraryDetails(fileId, lang = 'es') {
      this.loading_async = true;
      this.itineraryDetails = [];
      console.log('DATA: ', lang);
      return fetchItineraryDetailsData(fileId, lang)
        .then(({ data }) => {
          if (data.success) {
            this.itineraryDetails = data.data.itineraries;
          }
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    async saveConfirmationCode({ type, id, confirmation_code, file_number, itinerary_id }) {
      this.error = '';
      return putConfirmationCode(file_number, type, id, confirmation_code, itinerary_id)
        .then(() => {
          console.log('Procesado en Lambda..');
        })
        .catch((error) => {
          this.error = error;
          console.log(error);
        });
    },
    setFile(file) {
      this.file = file;
    },
    setIncludeTemporaryInSearch(status) {
      this.includeTemporaryInSearch = status;
    },

    fetchTemporaryServices(params) {
      this.loading_async = true;
      this.temporaryServices = [];
      this.flag_search_services = false;

      return getFilterSearchTemporaryServices(params)
        .then(({ data }) => {
          this.flag_search_services = true;
          if (data.success) {
            this.temporaryServices = data.data;
          } else {
            this.temporaryServices = [];
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    updateStatusRQWL(type, object_id, params) {
      return putStatusRQWL(type, object_id, params)
        .then(({ data }) => {
          console.log('RESPONSE: ', data);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    updateStatusWLOK(type, object_id, confirmation_code) {
      return putStatusWLOK(type, object_id, confirmation_code)
        .then(({ data }) => {
          console.log('RESPONSE: ', data);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    getStatements(fileId) {
      this.loading_statements = true;
      this.report_statements = {};
      return fetchStatements(fileId)
        .then(({ data }) => {
          this.loading_statements = false;
          if (data.success) {
            this.report_statements = data.data;
          }
        })
        .catch((error) => {
          this.loading_statements = false;
          console.log(error);
        });
    },
    searchStatementDetails(fileId) {
      this.loading_async = true;
      this.statement_details = [];
      return fetchStatementDetails(fileId)
        .then(({ data }) => {
          this.loading_async = false;
          if (data.success) {
            this.statement_details = data.data;
          }
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    searchLogsAWS(fileId, params) {
      this.loading_async = true;
      this.logs_aws = [];
      return fetchLogsAWS(fileId, params)
        .then(({ data }) => {
          console.log('DATA: ', data);
          this.loading_async = false;
          this.logs_aws = data.data;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    storeCreditNote(fileId, details) {
      this.loading_async = true;
      return createCreditNote(fileId, details)
        .then(({ data }) => {
          console.log('DATA: ', data);
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log('Error: ', error);
        });
    },
    storeDebitNote(fileId, details) {
      this.loading_async = true;
      return createDebitNote(fileId, details)
        .then(({ data }) => {
          console.log('DATA: ', data);
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log('Error: ', error);
        });
    },
    updateStatement(fileId, deadline, details, restore) {
      this.loading_statements = true;
      this.error = '';
      return putUpdateStatement(fileId, deadline, details, restore)
        .then(() => {
          this.loading_statements = false;
        })
        .catch(({ data }) => {
          this.loading_statements = false;
          this.error = data.error;
        });
    },
    async handleImportStella(file) {
      file.stela_processing = 1;
      this.loading_async = true;
      this.error = '';
      return importFileStella(file)
        .then(({ data }) => {
          console.log('DATA: ', data);
          this.loading_async = false;
        })
        .catch(({ data }) => {
          this.loading_async = false;
          this.error = data.error;
        });
    },
    searchStatementChanges(fileId) {
      this.error = '';
      this.statements_changes = null;
      return findStatementChanges(fileId)
        .then(({ data }) => {
          if (data.success) {
            this.statements_changes = data.data;
          }
        })
        .catch(({ data }) => {
          this.error = data.error;
        });
    },
    putUpdateStatementItineraries({ fileId, reasonId, otherReason, itineraries }) {
      this.error = '';
      return updateStatementItineraries({ fileId, reasonId, otherReason, itineraries })
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message;
          }
        })
        .catch(({ data }) => {
          this.error = data.error;
        });
    },
    processNotificationService({ itinerary_id, composition_code, composition_id }) {
      this.loading_async = true;
      this.error = '';
      const file_id = this.file.id;
      this.reservations = [];
      return sendNotificationService({ file_id, itinerary_id, composition_code, composition_id })
        .then(({ data }) => {
          if (data.success) {
            const executive_email = data.data.executive_email;
            this.reservations = data.data.reservations.map((reservation) => ({
              ...reservation,
              executive_email,
            }));
          }
          this.loading_async = false;
        })
        .catch((data) => {
          this.loading_async = false;
          console.log(data);
          // this.error = data.error;
        });
    },
    updateNotificationService({ composition_id }) {
      this.loading_async = true;
      this.error = '';

      return changeNotificationService({ composition_id })
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message;
          }
          this.loading_async = false;
        })
        .catch((data) => {
          this.loading_async = false;
          console.log(data);
          // this.error = data.error;
        });
    },
    handleResendNotification(params) {
      this.error = '';
      return sendNotification(params)
        .then((response) => {
          console.log('Response: ', response);
          if (!response.success) {
            this.error = response.error;
          }
        })
        .catch((error) => {
          console.log(error);
          this.error = error.data.error;
        });
    },
    handleValidateItinerary({ services, file_itinerary_id, loadingRequest = true }) {
      this.error = '';
      if (loadingRequest) {
        this.loading_async = true;
      }
      return validateItinerary({ services, file_itinerary_id })
        .then((response) => {
          console.log('Response: ', response);
          return response;
        })
        .catch((error) => {
          this.error = error?.data?.error;
          return error;
        })
        .finally(() => {
          if (loadingRequest) {
            this.loading_async = false;
          }
        });
    },
    updateHotelsUnitProperty(reportId, unitId, key, value) {
      console.log('reports Id: ' + reportId, 'unitID:' + unitId, key, value);
      const report = this.file_reports.hotels.find((r) => r.id === reportId);
      if (report) {
        const unit = report.units.find((u) => u.id === unitId);
        if (unit) {
          unit[key] = value;
        }
      }
    },
    addNewProperty(reportId, unitId, key, defaultValue) {
      const report = this.file_reports.hotels.find((r) => r.id === reportId);
      if (report) {
        const unit = report.units.find((u) => u.id === unitId);
        if (unit && !unit.hasOwnProperty(key)) {
          unit[key] = defaultValue;
        }
      }
    },

    async fetchFileServiceHotelCode(fileId, hotelCode) {
      this.loading_async = true;
      return searchFileServiceHotelCode(fileId, hotelCode)
        .then(({ data }) => {
          if (data.success) {
            this.file_hotel_code = data.data;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },

    updateHotelsUnitPropertyCode(reportId, unitId, key, value) {
      console.log('reports Id: ' + reportId, 'unitID:' + unitId, key, value);
      const report = this.file_hotel_code.find((r) => r.id === reportId);
      if (report) {
        const unit = report.units.find((u) => u.id === unitId);
        if (unit) {
          unit[key] = value;
        }
      }
    },
    addNewHotelsUnitPropertyCode(reportId, unitId, key, defaultValue) {
      const report = this.file_hotel_code.find((r) => r.id === reportId);
      if (report) {
        const unit = report.units.find((u) => u.id === unitId);
        if (unit && !unit.hasOwnProperty(key)) {
          unit[key] = defaultValue;
        }
      }
    },
    async fetchFileReservationProvider({ executive_code, hotel_id, client_id }) {
      this.loading_async = true;
      return fetchFileReservationProvider({ executive_code, hotel_id, client_id })
        .then(({ data }) => {
          if (data.success) {
            // console.log(data.data);
            this.file_providers = data.data;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
    executeSortItineraries() {
      // Ordenar por fecha y hora
      this.itineraries = this.itineraries.slice().sort((a, b) => {
        const dateA = new Date(`${a.date_in}T${a.start_time || '00:00:00'}`);
        const dateB = new Date(`${b.date_in}T${b.start_time || '00:00:00'}`);
        return dateA - dateB;
      });
    },
    updateItinerary(itinerary, isUpdated = false, isNew = false) {
      const index = this.itineraries.findIndex((it) => it.id === itinerary.id);
      if (index !== -1) {
        const original = this.itineraries[index];

        const updated = {
          ...original,
          ...itinerary,
          isLoading: true,
          isUpdated,
          isNew,
        };

        this.itineraries.splice(index, 1, updated);

        setTimeout(() => {
          const finished = {
            ...updated,
            isLoading: false,
            isCompleted: true,
          };
          this.itineraries.splice(index, 1, finished);
        }, 100);
      }
    },
    removeItinerary(itineraryId) {
      const index = this.itineraries.findIndex((it) => it.id === itineraryId);
      if (index !== -1) {
        this.itineraries[index].isLoading = true;

        setTimeout(() => {
          // localStorage.removeItem(`itinerary_${itineraryId}`);
          this.itineraries.splice(index, 1);
        }, 100);
      }
    },
    async processMasterServices({ fileId }) {
      this.loading_async = true;
      this.error = '';
      return generateMasterServices({ fileId })
        .then(({ data }) => {
          if (data.success) {
            this.error = '';
            this.loading_async = true;
          } else {
            this.error = true;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.error = true;
          this.loading_async = false;
          console.log(error);
        });
    },
    async fetchFileErrors({ fileNumber }) {
      this.has_pending_processes = false;
      this.file_process = null;
      return validateFileErrors({ fileNumber })
        .then(({ data }) => {
          if (data.success) {
            this.has_pending_processes = data.data.has_pending_processes;
            this.file_process = data.data.process;
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    async fetchFileBalanceAll({
      currentPage = DEFAULT_PAGE,
      perPage = DEFAULT_PER_PAGE,
      filter = '',
      filterBy = '',
      filterByType = '',
      executiveCode = '',
      clientId = '',
      dateRange = '',
    }) {
      this.loading = true;
      return fetchFileBalanceAll({
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
          if (data.success) {
            this.file_balance = data.data;
            this.file_pagination = data.pagination;
          }
        })
        .catch((err) => {
          console.log(err);
          this.loading = false;
        })
        .finally(() => {
          this.loading = false;
        });
    },
    updateItineraryNote(itinerary, isUpdated = false, isNew = false) {
      const index = this.itineraries.findIndex((it) => it.id === itinerary);
      if (index !== -1) {
        this.itineraries[index].isUpdated = true;
        this.itineraries[index].isNew = false;
        this.itineraries[index].isUpdatedNote = isUpdated;
        this.itineraries[index].isNewNote = isNew;
      }
    },
  },
  persist: {
    pick: ['serviceEdit', 'serviceTemporaryCreated', 'serviceTemporaryCommunications'],
    storage: localStorage,
  },
});
