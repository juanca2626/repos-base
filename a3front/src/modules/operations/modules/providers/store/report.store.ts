import { defineStore } from 'pinia';
import { reactive, toRefs } from 'vue';

export const useReportStore = defineStore('reportStore', () => {
  const state = reactive({
    status_services: {
      ok: false,
      incidence: false,
    },
    operational_service_id: '',
    description: '',
    pnr: '',
    luggage: 1,
    passport: false,
    email: 'test@mail.com',
    passengerName: 'test',
    phone: '912345678',
    images: [
      {
        url: 'https://res.cloudinary.com/dt4nv0isx/image/upload/v1507774688/Reportes/delay1.jpg',
        add_by: 'agent789',
        origin: 'PROVIDER',
      },
      {
        url: 'https://res.cloudinary.com/dt4nv0isx/image/upload/v1507774688/Reportes/delay2.jpg',
        add_by: 'admin001',
        origin: 'NOTE',
      },
    ],
    notes: [
      {
        user: 'agent789',
        description: 'El vuelo tuvo un retraso inesperado',
        action: 'PENDING',
        createdAt: '2025-03-22T10:45:30.500Z',
      },
      {
        user: 'admin001',
        description: 'Se ha informado a los pasajeros sobre la nueva hora de salida',
        action: 'OK',
        createdAt: '2025-03-22T11:10:15.200Z',
      },
    ],
    incident_type: [],
    assignFile: false,
    files: [],
  });

  const setReportData = (data: any) => Object.assign(state, data);

  const resetReport = () => {
    Object.assign(state, {
      status_services: {
        ok: false,
        incidence: false,
      },
      operational_service_id: '',
      provider_id: '',
      description: '',
      pnr: '',
      luggage: '',
      passport: false,
      email: 'test@mail.com',
      passengerName: 'test',
      phone: '912345678',
      images: [],
      notes: [],
      incident_type: [],
      assignFile: false,
      files: [],
    });
  };

  return {
    ...toRefs(state),
    setReportData,
    resetReport,
  };
});
