import { defineStore } from 'pinia';
import ServicesOperationsResources from '@service/operations/servicesOperationsResources';

export const useOperationsResourcesStore = defineStore({
  id: 'operations-resources',
  state: () => ({
    campus: [],
    languages: [],
    markets: [],
    ejecutives: [],
    clients: [],
    vehicles: [],
    items: [],
    subItems: [],
    guides: [],
    transfers: [],
    escorts: [],
    specialties: [],
    servicesOperationsResources: new ServicesOperationsResources(),
  }),
  actions: {
    async getOperationsResources() {
      this.campus = await this.servicesOperationsResources.getCampus();
      this.languages = await this.servicesOperationsResources.getLanguages();
      this.markets = await this.servicesOperationsResources.getMarkets();
      this.ejecutives = await this.servicesOperationsResources.getEjecutives();
      this.items = await this.servicesOperationsResources.getItems();
      this.guides = await this.servicesOperationsResources.getGuides();
      this.transfers = await this.servicesOperationsResources.getTransfers();
      this.escorts = await this.servicesOperationsResources.getEscorts();
      this.vehicles = await this.servicesOperationsResources.getTypeVehicles();
      /// order vehicles by capacity
      this.vehicles = this.vehicles.sort((a, b) => a.capacity - b.capacity);
      this.specialties = await this.servicesOperationsResources.getSpecialties();
    },
    async getCampus() {
      if (this.campus.length === 0) {
        this.campus = await this.servicesOperationsResources.getCampus();
      }
      return this.campus;
    },
    async getLanguages() {
      if (this.languages.length === 0) {
        this.languages = await this.servicesOperationsResources.getLanguages();
      }
      return this.languages;
    },
    async getMarkets() {
      if (this.markets.length === 0) {
        this.markets = await this.servicesOperationsResources.getMarkets();
      }
      return this.markets;
    },
    async getVehicles() {
      this.vehicles = await this.servicesOperationsResources.getTypeVehicles();
      this.vehicles = this.vehicles.sort((a, b) => a.capacity - b.capacity);
      return this.vehicles;
    },
    async getEjecutives() {
      if (this.ejecutives.length === 0) {
        this.ejecutives = await this.servicesOperationsResources.getEjecutives();
      }
      return this.ejecutives;
    },
    async getClients(query = '', lang = 'es') {
      console.log(query);
      this.clients = await this.servicesOperationsResources.getClients(query, lang);
      return this.clients;
    },
    async getItems() {
      if (this.items.length === 0) {
        this.items = await this.servicesOperationsResources.getItems();
        console.log(this.items);
      }
      return this.items;
    },
    async getSubItems(id) {
      this.subItems = await this.servicesOperationsResources.getSubItems(id);
      return this.subItems;
    },
    async getGuides() {
      return await this.servicesOperationsResources.getGuides();
    },
    async getTransfers() {
      return await this.servicesOperationsResources.getTransfers();
    },
    async getEscorts() {
      return await this.servicesOperationsResources.getEscorts();
    },
    async getSpecialties() {
      return await this.servicesOperationsResources.getSpecialties();
    },
    getCampusById(id) {
      return this.campus.find((campus) => campus.id === id);
    },
    getLanguageById(id) {
      return this.languages.find((language) => language.id === id);
    },
    getGuideById(id) {
      return this.guides.find((guide) => guide.id === id);
    },
    getTransferById(id) {
      return this.transfers.find((transfer) => transfer.id === id);
    },
    getEscortById(id) {
      return this.escorts.find((escort) => escort.id === id);
    },
    getSpecialtyById(id) {
      return this.specialties.find((specialty) => specialty.id === id);
    },
    getMarketById(id) {
      return this.markets.find((market) => market.value === id);
    },
  },
});
