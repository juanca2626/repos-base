export interface DestinationsResponse {
  success: boolean;
  data: Destination[];
}

export interface Destination {
  code: string;
  label: string;
}
