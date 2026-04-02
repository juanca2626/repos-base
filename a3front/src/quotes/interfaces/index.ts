export type { UpdateServicesOrderRequest } from '@/quotes/interfaces/quote-update-services-order.request';

export type { QuoteHotelCategoryListResponse } from './quote-hotel-category-list.response';
export type { QuoteHotelCategory } from './quote-hotel-category';
export type {
  QuoteResponse,
  QuoteService,
  GroupedServices,
  HotelService,
  HotelTypeClass,
  ServiceRoom,
  QuoteServiceServiceImport,
  Person,
  OpenQuoteCategory,
  Passenger,
  PassengerAgeChild,
  QuoteRange,
} from './quote.response';

export type { DestinationsResponse, Destination } from './destinations.response';
export type {
  Destinations,
  DestinationsCountry,
  DestinationsState,
  DestinationsCity,
  DestinationsZone,
} from './destinations';

export type {
  QuoteHotelsResponse,
  SearchParameters,
  Hotel,
  Room,
  RoomRate,
  RateRate,
} from './quote-hotels-list.response';
export type { QuoteHotelsSearchRequest } from './quote-hotels-search-request';

export type { QuoteServiceAddRequest } from './quote-service-add.request';

export type {
  QuoteServiceHotelsGenerateOccupationResponse,
  QuoteServiceHotelsOccupation,
  QuoteServiceHotelsOccupationPassenger,
  QuoteDistributionUpdate,
} from './quote-service-hotels-generate-occupation.response';

export type { TokenRefresh } from './token-refresh';
export type { QuoteSaveRequest } from './quote-save.request';
export type { LanguagesResponse, Language } from './languages.response';
export type { DoctypesResponse, Doctype } from './doctypes.response';

export type { CountriesResponse, Country } from './countries.response';
export type { StatesResponse, State } from './states.response';

export type { OriginResponse, Origin } from './origin.response';
export type { AirlineResponse, Airline } from './airline.response';

export type {
  QuotePricePassengersResponse,
  QuotePricePassenger,
  ServicePassenger,
} from './quote-price-passenger.response';
export type {
  QuotePriceRangesResponse,
  QuotePriceRange,
  ServiceRanger,
} from './quote-price-range.response';

export type { QuoteReservastionRequest } from './quote-reservations-request';
export type { QuoteReservationResponse, QuoteReservation } from './quote-reservations-response';

export type { PassengersRequest } from './passengers-request';
export type { PassengersResponse, PassengerR } from './passengers-response';

export type { ReservationsRequest } from './reservations-request';
export type { ReservationsResponse, Reservation } from './reservations-response';

export type { StatementsRequest } from './statements-request';
export type { StatementsResponse } from './statements-response';

export type { RemindersRequest } from './reminders-request';
export type { RemindersResponse } from './reminders-response';

export type { QuoteCategoryCopyRequest } from './quote-category-copy-request';
export type { QuoteCategoryCopyResponse } from './quote-category-copy-response';

export type { PackageRequest } from './quote-packages.request';
export type { PackageResponse, Package } from './quote-packages.response';
