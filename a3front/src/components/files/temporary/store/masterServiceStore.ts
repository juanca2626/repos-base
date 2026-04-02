import { defineStore } from 'pinia';
import type { MasterService } from '@/components/files/temporary/interfaces/master-service.interface';

export const useMasterServiceStore = defineStore('masterServiceStore', {
  state: () => ({
    selectedMasterServices: [] as MasterService[], // Lista de servicios seleccionados
  }),
  actions: {
    addService(service: MasterService) {
      if (!this.selectedMasterServices.some((s) => s._id === service._id)) {
        service.id = null;
        service._id = service.id + '_' + service.master_service_id;
        service.isNew = true;
        service.isDeleted = false;
        service.isReplaced = false;
        service.replacedBy = null;
        service.totalPenalties = 0;
        this.selectedMasterServices.push(service);
      } else {
        console.error('El servicio ya existe en la lista.');
      }
    },
    removeService(masterServiceId: string) {
      this.selectedMasterServices = this.selectedMasterServices.filter(
        (service) => service._id !== masterServiceId
      );
    },
    clearSelectedMasterServices() {
      this.selectedMasterServices = [];
    },
  },
  getters: {
    getSelectedMasterServices: (state) => {
      return state.selectedMasterServices;
    },
    totalAmountCost: (state) => {
      const total = state.selectedMasterServices.reduce((acc, service) => {
        return acc + parseFloat(service.amount_cost) || 0;
      }, 0);
      return total.toFixed(2);
    },
  },
});
