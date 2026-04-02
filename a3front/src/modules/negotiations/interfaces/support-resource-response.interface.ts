import type { CountryResourceInterface } from '@/modules/negotiations/country-calendar/general/interfaces/country-resource.interface';
import type { CurrencyResourceInterface } from '@/modules/negotiations/country-calendar/configuration/interfaces';

interface ServiceClassification {
  id: number;
  name: string;
}

interface TypeLaws {
  id: number;
  name: string;
}

export interface SupportResourceResponseInterface {
  success: boolean;
  data: {
    service_classifications?: ServiceClassification[];
    type_laws?: TypeLaws[];
    countries?: CountryResourceInterface[];
    currencies?: CurrencyResourceInterface[];
  };
  code: number;
}
