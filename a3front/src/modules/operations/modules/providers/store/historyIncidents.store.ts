import { defineStore } from 'pinia';

interface Incident {
  _id: string;
  iso: string;
  description?: string;
}

export const useHistoryIncidentsStore = defineStore('historyIncidents', {
  state: () => ({
    history_incidents: [] as Incident[],
  }),
  actions: {
    addIncident(incident: Incident) {
      this.history_incidents.push(incident);
    },
    setIncidents(incidents: Incident[]) {
      this.history_incidents = incidents;
    },
    clearIncidents() {
      this.history_incidents = [];
    },
    removeIncidentById(id: string) {
      this.history_incidents = this.history_incidents.filter((i) => i._id !== id);
    },
  },
  getters: {
    totalIncidents: (state) => state.history_incidents.length,
    getIncidentById: (state) => (id: string) => state.history_incidents.find((i) => i._id === id),
  },
});
