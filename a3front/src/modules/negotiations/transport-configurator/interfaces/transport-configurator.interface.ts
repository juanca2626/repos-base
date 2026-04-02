//transport-configurator.interfaces.ts
// Importamos la interfaz de UnitTransportConfigurator
import type {
  UnitTransportConfigurator,
  Location as TransportLocation,
} from '@/modules/negotiations/transport-configurator/interfaces/unit-transport-configurator.interface';

// Definimos la interfaz para el formulario de configuración de transporte
export interface TransportConfiguratorForm {
  units: UnitTransportConfigurator[]; // Lista de unidades de transporte
  areAllUnitsValid: boolean; // Indicador de si todas las unidades son válidas
  locationOptions: TransportLocation[]; // Opciones de locaciones
}

export interface SelectOption {
  label: string;
  value: string | number;
}
