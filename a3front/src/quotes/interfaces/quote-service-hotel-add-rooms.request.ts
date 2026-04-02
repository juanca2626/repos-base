export interface QuoteServiceHotelAddRoomsRequest {
  cant?: string;
  lang: string;
  quote_id: number;
  quote_service?: string | number;
  quote_service_id: number;
  rate_plan_room_ids: number[];
  rate_plan_rooms_choose: RatePlanRoomsChoose[];
}

export interface RatePlanRoomsChoose {
  rate_plan_room_id: number;
  choose: boolean;
  occupation: number;
  on_request: number;
}
