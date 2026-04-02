export interface ServiceExperiencesResponse {
  success: boolean;
  data: ServiceExperience[];
}

export interface ServiceExperience {
  id: number;
  name: string;
}
