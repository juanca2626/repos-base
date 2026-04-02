export interface ServiceDestinyResponse {
  success: boolean;
  data: ServiceDestiny[];
}

export interface ServiceDestiny {
  id: string;
  description: string;
}
