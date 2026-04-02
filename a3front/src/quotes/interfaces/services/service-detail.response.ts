export interface ServiceDetailResponse {
  id: number;
  name: string;
  code: string;
  coordinates: Coordinates;
  reserve_from_days: number;
  equivalence: string;
  affected_igv: number;
  affected_markup: number;
  allows_guide: number;
  allows_child: number;
  allows_infant: number;
  confirmation_hours_limit: number;
  include_accommodation: number;
  service_type: ServiceType;
  descriptions: Descriptions;
  experiences: Experience[];
  restrictions: any[];
  operations: Operations;
  reservation_time: string;
  inclusions: Inclusion[];
}

export interface Coordinates {
  latitude: number;
  longitude: number;
}

export interface Descriptions {
  name_commercial: string;
  description: string;
  itinerary: Itinerary[];
  summary: string;
}

export interface Itinerary {
  day: number;
  description: string;
}

export interface Experience {
  id: number;
  name: string;
  color: string;
}

export interface Inclusion {
  include: Include[];
  no_include: Include[];
}

export interface Include {
  day: number;
  date: Date | string;
  name: string;
  available_days: AvailableDays;
}

export interface AvailableDays {
  available_day: boolean;
  days: AvailableDaysDays;
}

export interface AvailableDaysDays {
  monday: boolean;
  tuesday: boolean;
  wednesday: boolean;
  thursday: boolean;
  friday: boolean;
  saturday: boolean;
  sunday: boolean;
}

export interface Operations {
  turns: Array<Turn[]>;
  days: OperationsDays;
  schedule: Schedule[];
}

export interface OperationsDays {
  monday: number;
  tuesday: number;
  wednesday: number;
  thursday: number;
  friday: number;
  saturday: number;
  sunday: number;
}

export interface Schedule {
  monday: string;
  tuesday: string;
  wednesday: string;
  thursday: string;
  friday: string;
  saturday: string;
  sunday: string;
}

export interface Turn {
  day: number;
  departure_time: string;
  shifts_available: string;
  detail: Detail[];
}

export interface Detail {
  detail: string;
  start_time: string;
  end_time: string;
}

export interface ServiceType {
  id: number;
  name: string;
  code: string;
}
