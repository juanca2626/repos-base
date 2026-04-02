export interface ServiceExtensionsRequest {
  type_class_id: number | string;
  date: Date | string;
  type_service: number | string;
  lang: string;
  filter: string;
  destination: string;
}
