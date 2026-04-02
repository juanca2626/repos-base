import { defineStore } from 'pinia';
import type { Hotel, SearchParameters } from '@/quotes/interfaces';

interface QuoteHotelsState {
  hotels: Hotel[];
  promotions: Hotel[];
  hotelSelected: Hotel | null;
  searchParameters: SearchParameters | null;
  hotelToEdit: Hotel[] | null;
}

export const useQuoteHotelsStore = defineStore({
  id: 'quoteHotelsStore',
  state: () =>
    ({
      hotels: [] as Hotel[],
      promotions: [] as Hotel[],
      hotelSelected: null,
      searchParameters: null,
      hotelToEdit: null,
    }) as QuoteHotelsState,
  actions: {
    setHotels(hotels: Hotel[]) {
      this.hotels = hotels;

      this.promotions = [];
    },
    setPromotions(promotions: Hotel[]) {
      this.promotions = promotions;
    },
    unsetHotels() {
      this.hotels = [];
      this.promotions = [];
    },
    setSearchParameters(searchParameters: SearchParameters) {
      this.searchParameters = searchParameters;
    },
    setHotelSelected(hotelIdSelected: number) {
      this.hotelSelected = this.hotels.find((h: Hotel) => h.id === hotelIdSelected) ?? null;
    },
    unsetHotelSelected() {
      this.hotelSelected = null;
      this.promotions = [];
    },
    unsetHotelSelectedPromotions() {
      this.hotelSelected = null;
    },
  },
});
