import { computed, unref } from 'vue';
import { useQuery, type UseQueryOptions } from '@tanstack/vue-query';
import type { Ref } from 'vue';

import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';

export interface MarketData {
  _id: string;
  code?: string;
  name: string;
  active: boolean;
  createdAt: string;
  updatedAt: string;
  __v: number;
}

export interface ClientData {
  _id: string;
  code: string;
  name: string;
  active: boolean;
  createdAt: string;
  updatedAt: string;
  __v: number;
}

export interface MarketsResponse {
  success: boolean;
  data: MarketData[];
}

export interface ClientsResponse {
  success: boolean;
  data: ClientData[];
}

export const useMarketsQuery = (
  enabled?: Ref<boolean> | boolean,
  options?: Omit<UseQueryOptions<MarketsResponse>, 'queryKey' | 'queryFn'>
): ReturnType<typeof useQuery<MarketsResponse>> => {
  const isEnabled = computed(() => unref(enabled) ?? false);

  return useQuery({
    queryKey: ['supplier', 'policies', 'markets'],
    queryFn: async () => {
      const response = await useSupplierService.showMarketsPolicies();
      return response;
    },
    enabled: isEnabled,
    staleTime: 5 * 60 * 1000, // 5 minutos
    gcTime: 10 * 60 * 1000,
    retry: 1,
    retryDelay: 1000,
    refetchOnWindowFocus: false,
    refetchOnMount: false,
    refetchOnReconnect: true,
    networkMode: 'online',
    structuralSharing: false,
    ...options,
  });
};

export const useClientsQuery = (
  enabled?: Ref<boolean> | boolean,
  options?: Omit<UseQueryOptions<ClientsResponse>, 'queryKey' | 'queryFn'>
): ReturnType<typeof useQuery<ClientsResponse>> => {
  const isEnabled = computed(() => unref(enabled) ?? false);

  return useQuery({
    queryKey: ['supplier', 'policies', 'clients'],
    queryFn: async () => {
      const response = await useSupplierService.showClientsPolicies();
      return response;
    },
    enabled: isEnabled,
    staleTime: 5 * 60 * 1000, // 5 minutos
    gcTime: 10 * 60 * 1000,
    retry: 1,
    retryDelay: 1000,
    refetchOnWindowFocus: false,
    refetchOnMount: false,
    refetchOnReconnect: true,
    networkMode: 'online',
    structuralSharing: false,
    ...options,
  });
};
