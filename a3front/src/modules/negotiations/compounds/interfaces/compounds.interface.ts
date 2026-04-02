import type { Dayjs } from 'dayjs';

export interface CompuestoServicio {
  id: string;
  codigo: string;
  nombre: string;
  proveedor?: string;
  tipoServicio?: string;
  tieneEscudo?: boolean;
}

export interface CompuestoDia {
  id: string;
  nombre: string;
  servicios: CompuestoServicio[];
}

export interface CompoundsFormState {
  fechaCotizacion: Dayjs | null;
  mercado: string;
  configuracionTransporte: string | null;
  rangoInput: string;
  pasajeros: string[];
  incluirGuia: boolean;
  incluirScort: boolean;
  incluirChofer: boolean;

  // Step 2
  tipoServicio: string | null;
  busquedaServicio: string;
  dias: CompuestoDia[];
}

export interface TransportOption {
  value: string;
  label: string;
}
