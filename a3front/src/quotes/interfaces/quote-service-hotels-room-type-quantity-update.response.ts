export interface QuoteServiceHotelsRoomTypeQuantityUpdateResponse {
  message: string;
  hotels_add_rooms: HotelsAddRooms;
}

export interface HotelsAddRooms {
  [key: string]: HotelsAddRoom[];
}

export interface HotelsAddRoom {
  quote_category_id: number;
  quote_service: number | string;
  quote_service_id: number;
  hotel_id: number;
  hotel_name: string;
  date_in: Date | string;
  date_out: Date | string;
  nights: number;
  occupation: number;
  destiny_code: string;
  destiny_label: string;
  typeclass_id: number;
  cant: string;
}
