import { defineStore } from 'pinia';
import ServicesOperations from '@service/operations/servicesOperations';

export const useCurrentGuidelineStore = defineStore({
  id: 'current-guideline',
  state: () => ({
    currentGuideline: {
      id: null,
      user_id: 1,
      type: '',
      reference_id: null,
      active: true,
      items: [],
      guideline_items: [],
    },
    client: {},
    market: {},
    searchDisabled: false,
    newItems: [],
    loading: true,
    servicesGuidelines: new ServicesOperations(),
    showMarket: false,
    showClient: false,
  }),
  actions: {
    freshGuideline() {
      this.currentGuideline = {
        id: null,
        user_id: 1,
        type: '',
        reference_id: null,
        active: true,
        items: [],
        guideline_items: [],
      };
      this.client = {};
      this.market = {};
      this.searchDisabled = false;
      this.newItems = [];
      this.loading = true;
    },
    async getCurrentGuideline(reference) {
      this.currentGuideline = await this.servicesGuidelines.getGuidelinesByReference(reference);
      this.currentGuideline = this.currentGuideline[0];
      return this.currentGuideline;
    },
    setClient(client) {
      this.client = client;
    },
    setMarket(market) {
      this.market = market;
    },
    getGuidelineId() {
      return this.currentGuideline.id ?? 0;
    },
    getClientCode() {
      return this.client.code;
    },
    getClientName() {
      return ' - ' + this.client.name;
    },
    getMarketName() {
      return this.market.text;
    },
    getItemsByCampus(campus) {
      return this.currentGuideline.items.filter((item) => item.campus_id === campus);
    },
    getCampusesWithItems() {
      return [...new Set(this.currentGuideline.items.map((item) => item.campus_id))];
    },
    orderItemsByCampus() {
      let campuses = this.getCampusesWithItems();
      let orderedItems = [];
      campuses.forEach((campus) => {
        orderedItems.push({
          campus: campus,
          items: this.getItemsByCampus(campus),
        });
      });
      //sort items by campus
      orderedItems.sort((a, b) => a.campus - b.campus);
      return orderedItems;
    },
    getItemsGenerals() {
      return this.currentGuideline.items.filter((item) => item.item?.is_general === true);
    },
    getEjecutives() {
      return this.getItemsGenerals().filter((item) => item.item?.description === 'Ejecutivo');
    },
    getSubItemsByCampusAndItem(campusId, itemId) {
      const subItems = this.currentGuideline.items.filter(
        (item) => item.campus_id === campusId && item.item.belongs_to === itemId
      );
      return subItems;
    },
    getItemsByCampusFilter(campus) {
      const items = JSON.parse(JSON.stringify(this.orderItemsByCampus()));
      const itemsCampusAll = items.find((item) => item.campus === campus);
      if (itemsCampusAll) {
        //return only items is_general false
        itemsCampusAll.items = itemsCampusAll.items.filter(
          (item) => item.item.is_general === false && item.item.belongs_to === null
        );
        return [itemsCampusAll];
      }
      return [];
    },
    getValueContactEmergency() {
      const item = this.currentGuideline.items.find(
        (item) => item.item?.description === 'Contacto de emergencia'
      );
      return item?.value ? item.value : 'No existe información';
    },
    getValueEmailEmergency() {
      const item = this.currentGuideline.items.find(
        (item) => item.item?.description === 'Correo de emergencia'
      );
      return item?.value ? item.value : 'No existe información';
    },
    getValuePhoneEmergency() {
      const item = this.currentGuideline.items.find(
        (item) => item.item?.description === 'Teléfono de emergencia'
      );
      return item?.value ? item.value : 'No existe información';
    },
    getValueRemarksSpecials() {
      const item = this.currentGuideline.items.find(
        (item) => item.item?.description === 'Remarks Especiales'
      );
      return item?.value ? item.value : 'No existe información';
    },
    getIdContactEmergency() {
      const item = this.currentGuideline.items.find(
        (item) => item.item?.description === 'Contacto de emergencia'
      );
      return item?.id ?? 0;
    },
    getIdEmailEmergency() {
      const item = this.currentGuideline.items.find(
        (item) => item.item?.description === 'Correo de emergencia'
      );
      return item?.id ?? 0;
    },
    getIdPhoneEmergency() {
      const item = this.currentGuideline.items.find(
        (item) => item.item?.description === 'Teléfono de emergencia'
      );
      return item?.id ?? 0;
    },
    getIdRemarksSpecials() {
      const item = this.currentGuideline.items.find(
        (item) => item.item?.description === 'Remarks Especiales'
      );
      return item?.id ?? 0;
    },
    addNewItems(items) {
      this.newItems = items;
    },
  },
});
