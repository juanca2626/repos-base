import type DocumentExtensionFormComponentVue from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/DocumentExtensionFormComponent.vue';

export type DocumentStatusKey =
  | 'status_soat'
  | 'status_inspection_certificate'
  | 'status_secure'
  | 'status_property_card'
  | 'status_circulation_card'
  | 'status_gps_certificate';

export type DocumentExtensionFormRef = InstanceType<typeof DocumentExtensionFormComponentVue>;
