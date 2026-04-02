import type { ProductSupplierOperatingLocationCountry } from './product-supplier-operating-location-country.interface';
import type { ProductSupplierOperatingLocationState } from './product-supplier-operating-location-state.interface';
import type { ProductSupplierOperatingLocationCity } from './product-supplier-operating-location-city.interface';

export interface ProductSupplierOperatingLocation {
  key: string;
  country: ProductSupplierOperatingLocationCountry;
  state: ProductSupplierOperatingLocationState;
  city?: ProductSupplierOperatingLocationCity;
}
