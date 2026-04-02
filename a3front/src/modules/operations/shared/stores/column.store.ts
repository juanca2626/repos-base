import { defineStore } from 'pinia';
import type { TableColumnType } from 'ant-design-vue';
import ColumnTitle from '@/components/global/ColumnTitleComponent.vue';
import { h } from 'vue';

export const useColumnStore = defineStore('columnStore', () => {
  const columns: Record<string, TableColumnType[]> = {
    default: [
      {
        title: () => h(ColumnTitle, { title: 'Fecha/Hora' }),
        dataIndex: 'datetime_start',
        key: 'datetime_start',
        width: 150,
        align: 'left',
      },
      {
        title: () => h(ColumnTitle, { title: 'Servicio' }),
        dataIndex: 'service',
        key: 'service',
        width: 300,
        align: 'left',
      },
      {
        //TODO: Implementar en el backend si es un COMPARTIDO / PRIVADO
        title: () => h(ColumnTitle, { title: 'Tipo', subtitle: '' }),
        dataIndex: 'type',
        key: 'type',
        width: 110,
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Cliente', subtitle: '' }),
        dataIndex: 'client',
        key: 'client',
        width: 160,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'VIP',
          }),
        dataIndex: 'vip',
        key: 'vip',
        width: 60,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'File',
          }),
        dataIndex: 'file',
        key: 'file',
        width: 150,
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Idioma',
          }),
        dataIndex: 'lang',
        key: 'lang',
        width: 80,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'PAX',
          }),
        dataIndex: 'pax',
        key: 'pax',
        width: 80,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Hotel',
          }),
        dataIndex: 'hotel',
        width: 160,
        key: 'hotel',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'TRP',
          }),
        dataIndex: 'trp',
        key: 'trp',
        width: 120,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'GUI',
          }),
        dataIndex: 'gui',
        key: 'gui',
        width: 120,
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Acciones', subtitle: '' }),
        dataIndex: 'actions',
        key: 'actions',
        width: 120,
        align: 'center',
      },
    ],
    providerAssignment: [
      {
        title: () => h(ColumnTitle, { title: 'Código' }),
        dataIndex: 'code',
        key: 'code',
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Tipo' }),
        dataIndex: 'contract',
        key: 'contract',
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Nombre' }),
        dataIndex: 'fullname',
        key: 'fullname',
        align: 'left',
      },
      {
        title: () => h(ColumnTitle, { title: 'Pref' }),
        dataIndex: 'preferent',
        key: 'preferent',
        width: 100,
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Estado' }),
        dataIndex: 'state',
        key: 'state',
        align: 'center',
      },
      {
        title: '',
        key: 'selection',
        dataIndex: 'selection',
        align: 'center',
        width: 125,
      },
    ],
    providerGUI: [
      {
        title: () => h(ColumnTitle, { title: 'Fecha/Hora' }),
        dataIndex: 'datetime_start',
        key: 'datetime_start',
        width: 150,
        align: 'left',
      },
      {
        title: () => h(ColumnTitle, { title: 'Servicio' }),
        dataIndex: 'service',
        key: 'service',
        width: 200,
        align: 'left',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'File',
          }),
        dataIndex: 'file',
        key: 'file',
        width: 150,
      },
      {
        //TODO: Implementar en el backend si es un COMPARTIDO / PRIVADO
        title: () => h(ColumnTitle, { title: 'Tipo', subtitle: '' }),
        dataIndex: 'type',
        key: 'type',
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Cliente', subtitle: '' }),
        dataIndex: 'client',
        key: 'client',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'VIP',
          }),
        dataIndex: 'vip',
        key: 'vip',
        width: 60,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Idioma',
          }),
        dataIndex: 'lang',
        key: 'lang',
        width: 80,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'PAX',
          }),
        dataIndex: 'pax',
        key: 'pax',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Hotel',
          }),
        dataIndex: 'hotel',
        width: 160,
        key: 'hotel',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Guía',
          }),
        dataIndex: 'gui',
        key: 'gui',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'TRP/Placa',
          }),
        dataIndex: 'trp',
        key: 'trp',
        width: 150,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Estado',
          }),
        dataIndex: 'assignment',
        key: 'assignment',
        width: 150,
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Acciones', subtitle: '' }),
        dataIndex: 'actions',
        key: 'actions',
        width: 120,
        align: 'center',
      },
    ],
    providerTRP: [
      {
        title: () => h(ColumnTitle, { title: 'Fecha/Hora' }),
        dataIndex: 'datetime_start',
        key: 'datetime_start',
        width: 150,
        align: 'left',
      },
      {
        title: () => h(ColumnTitle, { title: 'Servicio' }),
        dataIndex: 'service',
        key: 'service',
        width: 200,
        align: 'left',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'File',
          }),
        dataIndex: 'file',
        key: 'file',
        width: 150,
      },
      {
        //TODO: Implementar en el backend si es un COMPARTIDO / PRIVADO
        title: () => h(ColumnTitle, { title: 'Tipo', subtitle: '' }),
        dataIndex: 'type',
        key: 'type',
        width: 110,
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Cliente', subtitle: '' }),
        dataIndex: 'client',
        key: 'client',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'VIP',
          }),
        dataIndex: 'vip',
        key: 'vip',
        width: 60,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Idioma',
          }),
        dataIndex: 'lang',
        key: 'lang',
        width: 80,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'PAX',
          }),
        dataIndex: 'pax',
        key: 'pax',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Hotel',
          }),
        dataIndex: 'hotel',
        width: 160,
        key: 'hotel',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Guía',
          }),
        dataIndex: 'gui',
        key: 'gui',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'TRP/Placa',
          }),
        dataIndex: 'trp',
        key: 'trp',
        width: 150,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Estado',
          }),
        dataIndex: 'assignment',
        key: 'assignment',
        width: 150,
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Acciones', subtitle: '' }),
        dataIndex: 'actions',
        key: 'actions',
        width: 120,
        align: 'center',
      },
    ],
    nested: [
      {
        title: () => h(ColumnTitle, { title: 'Fecha/Hora' }),
        dataIndex: 'datetime_start',
        key: 'datetime_start',
        width: 150,
        align: 'left',
      },
      {
        title: () => h(ColumnTitle, { title: 'Cliente', subtitle: '' }),
        dataIndex: 'client',
        key: 'client',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'VIP',
          }),
        dataIndex: 'vip',
        key: 'vip',
        width: 60,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'File',
          }),
        dataIndex: 'file',
        key: 'file',
        width: 150,
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Idioma',
          }),
        dataIndex: 'lang',
        key: 'lang',
        width: 80,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'PAX',
          }),
        dataIndex: 'pax',
        key: 'pax',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Ejecutiva',
          }),
        dataIndex: 'executive',
        key: 'executive',
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Hotel',
          }),
        dataIndex: 'hotel',
        key: 'hotel',
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Acciones', subtitle: '' }),
        dataIndex: 'actions',
        key: 'actions',
        width: 120,
        align: 'center',
      },
    ],
    tower: [
      {
        title: () => h(ColumnTitle, { title: 'Estado' }),
        dataIndex: 'monitoring',
        key: 'monitoring',
        width: 90,
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Ciudad', subtitle: 'Fecha/Hora' }),
        dataIndex: 'city_datetime_start',
        key: 'city_datetime_start',
        width: 120,
        align: 'left',
      },
      {
        title: () => h(ColumnTitle, { title: 'Servicio' }),
        dataIndex: 'service',
        key: 'service',
        width: 200,
        align: 'left',
      },
      {
        //TODO: Implementar en el backend si es un COMPARTIDO / PRIVADO
        title: () => h(ColumnTitle, { title: 'Tipo', subtitle: '' }),
        dataIndex: 'type',
        key: 'type',
        width: 110,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'File',
          }),
        dataIndex: 'file',
        key: 'file',
        width: 130,
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'VIP',
          }),
        dataIndex: 'vip',
        key: 'vip',
        width: 60,
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Cliente', subtitle: '' }),
        dataIndex: 'client',
        key: 'client',
        width: 140,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Pax',
          }),
        dataIndex: 'pax',
        key: 'pax',
        width: 70,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Id',
          }),
        dataIndex: 'lang',
        key: 'lang',
        width: 70,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Vuelo in',
          }),
        dataIndex: 'vuelo_in',
        key: 'vuelo_in',
        width: 100,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Vuelo out',
          }),
        dataIndex: 'vuelo_out',
        key: 'vuelo_out',
        width: 100,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'Hotel',
          }),
        dataIndex: 'hotel',
        key: 'hotel',
        width: 100,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'TRP',
          }),
        dataIndex: 'trp',
        key: 'trp',
        width: 100,
        align: 'center',
      },

      {
        title: () =>
          h(ColumnTitle, {
            title: 'Chofer',
            subtitle: 'Placa',
          }),
        dataIndex: 'chofer_placa',
        key: 'chofer_placa',
        width: 100,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'GUI',
          }),
        dataIndex: 'gui',
        key: 'gui',
        width: 100,
        align: 'center',
      },
      {
        title: () =>
          h(ColumnTitle, {
            title: 'MASI',
          }),
        dataIndex: 'masi',
        key: 'masi',
        width: 70,
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Acciones', subtitle: '' }),
        dataIndex: 'actions',
        key: 'actions',
        width: 90,
        align: 'center',
      },
    ],
    configTRP: [
      {
        title: () => h(ColumnTitle, { title: 'Tipo' }),
        dataIndex: 'type',
        key: 'type',
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Disponibles' }),
        dataIndex: 'available',
        key: 'available',
        align: 'center',
      },
      {
        title: () => h(ColumnTitle, { title: 'Pasajeros' }),
        dataIndex: 'paxs',
        key: 'paxs',
        align: 'center',
      },
      {
        title: '',
        key: 'selection',
        dataIndex: 'selection',
        align: 'center',
        width: 125,
      },
    ],
  };

  // Función para obtener columnas por tipo
  const getColumnsByType = (type: string = 'default'): TableColumnType[] => {
    return columns[type] || columns.default;
  };

  return {
    getColumnsByType,
  };
});
