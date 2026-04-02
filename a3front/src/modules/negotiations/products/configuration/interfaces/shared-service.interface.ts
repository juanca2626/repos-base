export interface FormState {
  serviceName: string;
  subtype: string | undefined;
  profile: string | undefined;
  startPoint: string | string[] | undefined;
  endPoint: string | string[] | undefined;
  duration: string;
  measurementUnit: string | undefined;
  minCapacity: number | undefined;
  maxCapacity: number | undefined;
  includesChildren: boolean;
  includesInfants: boolean;
  status: string | undefined;
  reason: string;
  showToClient: boolean | undefined;
  typeText: string[] | undefined;
  itinerary: string;
  menu: string;
  remarks: string;
}

export interface Schedule {
  id: number;
  time: string;
  selected: boolean;
}

export interface Activity {
  duration: string;
  activity: string;
  timeRange: string;
}
