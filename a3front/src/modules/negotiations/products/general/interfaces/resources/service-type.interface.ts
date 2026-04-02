export interface ServiceType {
  id: string;
  name: string;
}
export interface ServiceTypeListItem extends ServiceType {
  originalId: number;
  code: string;
}
