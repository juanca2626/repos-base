import { defineStore } from 'pinia';
import ServicesLocations from '@/services/operations/servicesLocations';

const servicesLocations = new ServicesLocations();
export const useLocationsStore = defineStore({
  id: 'locations',
  state: () => ({
    departments: [],
    provinces: [],
    districts: [],
  }),
  actions: {
    async getDepartmentsPeru() {
      return await servicesLocations.getDepartmentsPeru();
    },
    async getProvinces(department_id) {
      return await servicesLocations.getProvinces(department_id);
    },
    async getDistricts(province_id) {
      return await servicesLocations.getDistricts(province_id);
    },
    setDepartments(departments) {
      this.departments = departments;
    },
    setProvinces(provinces) {
      this.provinces = provinces;
    },
    setDistricts(districts) {
      this.districts = districts;
    },
  },
});
