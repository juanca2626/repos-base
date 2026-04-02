import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';
import { getBasicStepIssues } from '../shared/basicRules';
import type { BasicSection } from '../state/createInitialBasicState';

export function validateBasicStep(state: BasicSection, _serviceType: ServiceType) {
  const issues = getBasicStepIssues(state);

  const errors: Record<string, string> = {};

  issues.forEach((key) => {
    errors[key] = mapErrorMessage(key);
  });

  return {
    valid: issues.length === 0,
    errors,
  };
}

function mapErrorMessage(key: string): string {
  const messages: Record<string, string> = {
    tariffType: 'Debe seleccionar un tipo de tarifa',
    bookingCode: 'Debe ingresar un código de reserva',
    travelFrom: 'Debe seleccionar una fecha de inicio',
    travelTo: 'Debe seleccionar una fecha de fin',
    periods: 'Debe agregar al menos un periodo',
    promotionName: 'Debe seleccionar una promoción',
    tariffSegmentation: 'Debe seleccionar segmentación',
    specificMarkets: 'Debe seleccionar mercados',
    specificClients: 'Debe seleccionar clientes',
    specificSeries: 'Debe seleccionar series',
    bookingFrom: 'Debe seleccionar fecha inicio booking',
    bookingTo: 'Debe seleccionar fecha fin booking',
    selectedDays: 'Debe seleccionar días',
    selectedHolidays: 'Debe seleccionar feriados',
    currencyBuy: 'Debe seleccionar moneda compra',
    currencySell: 'Debe seleccionar moneda venta',
  };

  return messages[key] ?? 'Campo inválido';
}
