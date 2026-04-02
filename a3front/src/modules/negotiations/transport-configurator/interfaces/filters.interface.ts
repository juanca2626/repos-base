export interface TransportConfiguratorFilter {
  search: string;
  status: string;
}

export interface TransportUnit {
  id: number;
  code: string;
  type: string;
  locations: string[];
  status: string;
  trunk: boolean;
  activities: { [key: string]: number };
}
