import { defineStore } from 'pinia';
import type {
  Hotel,
  QuoteResponse,
  QuoteService,
  GroupedServices,
  QuotePricePassengersResponse,
  QuotePriceRangesResponse,
} from '@/quotes/interfaces';

interface QuoteState {
  view: string;
  page: string;
  quote: QuoteResponse;
  quoteNew: null;
  quoteServiceTypeId: number;
  quoteLanguageId: number | null;
  selectedCategory: number;
  quoteResponse: QuoteResponse;
  serviceSelected: QuoteService | GroupedServices;
  showDesign: Boolean;
  serviceSearch: QuoteService | GroupedServices;
  hotelSelected?: Hotel;
  hotelRoomSelected?: Hotel;
  quotePricePassenger: QuotePricePassengersResponse | null;
  quotePriceRanger: QuotePriceRangesResponse | null;
  downloadItinerary: null;
  currentReportQuote: null;
  downloadSkeletonUse: null;
  quoteRowExtentions: number;
  itemsExtensiones: null;
  selectedHotelDetails: number | null;
  reportsList: null;
  reportsDestinity: null;
  marketList: null;
  sellersList: null;
  openItemService: boolean;
  alertCategory: number | null;
  showPassengersForm: boolean;
  loadingServices: Record<number, boolean>;
  processing: boolean;
  isLoading: boolean;
}

export const useQuoteStore = defineStore({
  id: 'quoteStore',
  state: () =>
    ({
      view: 'table',
      page: 'details',
      quote: {} as QuoteResponse,
      quoteNew: {},
      quoteServiceTypeId: 0,
      quoteLanguageId: null,
      quoteResponse: {} as QuoteResponse,
      serviceSelected: {} as QuoteService,
      showDesign: true,
      serviceSearch: {} as QuoteService,
      selectedCategory: 0,
      selectedHotelDetails: null,
      quotePricePassenger: {} as QuotePricePassengersResponse,
      quotePriceRanger: {} as QuotePriceRangesResponse,
      downloadItinerary: {},
      currentReportQuote: {},
      downloadSkeletonUse: {},
      quoteRowExtentions: {},
      itemsExtensiones: {},
      reportsList: {},
      reportsDestinity: {},
      marketList: {},
      sellersList: {},
      openItemService: false,
      alertCategory: null,
      showPassengersForm: false,
      loadingServices: {},
      processing: false,
      isLoading: false,
    }) as QuoteState,
  actions: {
    setView(view: string) {
      this.view = view;
    },
    setQuote(quote: QuoteResponse) {
      if (quote && quote.categories) {
        quote.categories.forEach((cat) => {
          cat.services.forEach((gs) => {
            const service = (gs as any).service || gs;
            // Preservar el estado de carga si existe en el mapa global
            if (this.loadingServices[service.id]) {
              service.isLoading = true;
            }
          });
        });
      }
      this.quote = quote;
    },
    setQuoteResponse(quoteResponse: QuoteResponse) {
      this.quoteResponse = quoteResponse;
    },
    setSelectedCategory(c: number) {
      this.selectedCategory = c;
    },
    setHotelSelected(hotel: Hotel) {
      this.hotelSelected = hotel;
    },
    setServiceSelected(serviceSelected: QuoteService | GroupedServices, showDesign: boolean) {
      this.serviceSelected = serviceSelected;
      this.showDesign = showDesign;

      console.log('ShowDesign: ', this.showDesign);
    },
    unsetServiceSelected() {
      this.serviceSelected = {} as QuoteService;
    },
    setSearch(serviceSearch: QuoteService | GroupedServices) {
      this.serviceSearch = serviceSearch;
    },
    unsetSearch() {
      this.serviceSearch = {} as QuoteService;
    },
    unsetQuote() {
      this.quote = {} as QuoteResponse;
    },
    setShowPassengersForm(show: boolean) {
      this.showPassengersForm = show;
    },
    setLoadingService(id: number, loading: boolean) {
      this.loadingServices[id] = loading;

      // Also update the property directly on the service object in the current quote
      if (this.quote && this.quote.categories) {
        this.quote.categories.forEach((cat) => {
          cat.services.forEach((gs) => {
            const service = (gs as any).service || gs;
            if (service.id === id) {
              service.isLoading = loading;
            }
          });
        });
      }
    },
    setProcessing(processing: boolean) {
      console.log('Cambiando Processing..', processing);
      this.processing = processing;
    },
    setIsLoading(isLoading: boolean) {
      this.isLoading = isLoading;
    },
    clearLoadingServices(ids: number[]) {
      ids.forEach((id) => {
        this.loadingServices[id] = false;

        // Also update the property directly on the service object in the current quote
        if (this.quote && this.quote.categories) {
          this.quote.categories.forEach((cat) => {
            cat.services.forEach((gs) => {
              const service = (gs as any).service || gs;
              if (service.id === id) {
                service.isLoading = false;
              }
            });
          });
        }
      });
    },
  },
});
