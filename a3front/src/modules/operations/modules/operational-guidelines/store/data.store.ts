// import { Headquarter } from './../../blackout-calendar/interfaces/headquarter.interface';
// import { Owner } from './../interfaces/owner.interface';
import { defineStore } from 'pinia';
import { nextTick, ref } from 'vue';
import {
  fetchHeadquarters,
  fetchGuidelines,
  fetchProviders,
  fetchOwners,
  fetchOperationalGuidelines,
  fetchOwner,
  syncData as syncDataComplete,
} from '@operations/modules/operational-guidelines/api/operationalGuidelinesApi';
import type { Headquarter, Provider, Owner } from '../interfaces';
import { message } from 'ant-design-vue';

export const useDataStore = defineStore('dataStore', () => {
  const headquarters = ref<Headquarter[]>();
  const guidelines = ref<any>();
  const providers = ref<Provider[]>([]);
  const owners = ref<Owner[]>([]);
  const loading = ref<boolean>();
  const owner = ref<any>();
  const operationalGuidelines = ref<any>(); // Define el tipo según lo que esperas

  const resetOwners = () => {
    owners.value = [];
    loading.value = false;
  };

  // Función para obtener los owners
  const getOwners = async (type: string, query: string) => {
    if (!query || query.length < 2) {
      resetOwners(); // Limpia antes de salir
      return;
    }

    resetOwners(); // Asegura que el menú se limpie antes de cada búsqueda
    loading.value = true;

    try {
      const response = await fetchOwners(type, query);
      owners.value = response?.data ?? [];
    } catch (error) {
      console.error('❌ Error obteniendo owners:', error);
      message.error('Error obteniendo datos (Owners). Contactar con TI.');
    } finally {
      nextTick(() => {
        loading.value = false;
      });
    }
  };

  // Función para obtener los headquarters
  const getHeadquarters = async () => {
    try {
      const response = await fetchHeadquarters();
      headquarters.value = response?.data ?? [];
    } catch (error) {
      console.error('Error obteniendo headquarters:', error);
      message.error('Error obteniendo datos (Headquarters). Contactar con TI.');
    }
  };

  // Función para obtener las guidelines
  const getGuidelines = async () => {
    try {
      // throw new Error('Test error: Guidelines.');
      const response = await fetchGuidelines();
      guidelines.value = response?.data ?? [];
    } catch (error) {
      console.error('Error obteniendo guidelines:', error);
      message.error('Error obteniendo datos (Guidelines). Contactar con TI.');
    }
  };

  // Función para obtener los proveedores
  const getProviders = async (searchText: string, type: string) => {
    try {
      const response = await fetchProviders(searchText, type);
      providers.value = response?.data ?? [];
    } catch (error) {
      console.error('Error obteniendo providers:', error);
      message.error('Error obteniendo datos (Providers). Contactar con TI.');
    }
  };

  // Función para obtener las operational guidelines e información del owner
  // const getOperationalGuidelines = async (code: string) => {
  //   try {
  //     // throw new Error('Test error: Operational Guidelines.');
  //     const [responseOwner, responseOG] = await Promise.all([
  //       fetchOwner(code),
  //       fetchOperationalGuidelines(code),
  //     ]);

  //     console.log('🚀 ~ getOperationalGuidelines ~ responseOG:', responseOG);
  //     console.log('🚀 ~ getOperationalGuidelines ~ responseOwner:', responseOwner);

  //     owner.value = responseOwner?.data ?? null;
  //     operationalGuidelines.value = responseOG?.data ?? null;
  //   } catch (error) {
  //     console.error('Error obteniendo operational guidelines:', error);
  //     message.error('Error obteniendo datos (Operational Guidelines). Contactar con TI.');
  //   }
  // };

  const getOperationalGuidelines = async (code: string) => {
    try {
      const [ownerResult, ogResult] = await Promise.allSettled([
        fetchOwner(code),
        fetchOperationalGuidelines(code),
      ]);

      console.log('🚀 ~ getOperationalGuidelines ~ ownerResult:', ownerResult);
      console.log('🚀 ~ getOperationalGuidelines ~ ogResult:', ogResult);

      owner.value = ownerResult.status === 'fulfilled' ? ownerResult.value?.data : null;
      operationalGuidelines.value = ogResult.status === 'fulfilled' ? ogResult.value?.data : null;

      if (ownerResult.status === 'rejected') {
        console.warn('Error en fetchOwner:', ownerResult.reason);
      }
      if (ogResult.status === 'rejected') {
        console.warn('Error en fetchOperationalGuidelines:', ogResult.reason);
      }
    } catch (error) {
      console.error('Error inesperado:', error);
      message.error('Error obteniendo datos. Contactar con TI.');
    }
  };

  // Ejemplo de función para cargar múltiples datos simultáneamente
  const loadData = async () => {
    console.log('loadData');
    try {
      const [responseHeadquarters, responseGuidelines] = await Promise.all([
        fetchHeadquarters(),
        fetchGuidelines(),
      ]);
      headquarters.value = responseHeadquarters?.data;
      guidelines.value = responseGuidelines?.data;
    } catch (err) {
      console.error('Error loading data:', err);
    }
  };

  const syncData = async () => {
    try {
      await syncDataComplete({
        source: 'a3.general',
        detailType: 'SyncGeneralOperationalGuidelines',
        detail: {},
      });
    } catch (error) {
      console.error('Error en la sincronización de datos:', error);
      message.error('Ocurrió un error en la sincronización de datos. Contactar con TI');
    }
  };

  return {
    // Properties
    headquarters,
    guidelines,
    providers,
    owners,
    loading,
    owner,
    operationalGuidelines,

    // Actions
    getHeadquarters,
    getGuidelines,
    getProviders,
    getOwners,
    getOperationalGuidelines,
    loadData, // Si decides usar la carga simultánea
    syncData,
  };
});
