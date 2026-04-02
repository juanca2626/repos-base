// src/utils/price.ts
import { roundLito } from './utils';

interface Client {
  commission_status: number;
  commission: string | number;
}

export function getPriceWithCommission(
  basePrice: number | string,
  client: Client | null,
  userType: number,
  module: string = ''
): string {
  // ✅ Sanitizar y convertir el precio
  const parsedPrice =
    typeof basePrice === 'number' ? basePrice : parseFloat(basePrice?.toString().trim() || 'NaN');

  // 🚨 Si el precio no es válido, devolvemos "0.0"
  if (isNaN(parsedPrice)) {
    return '0.0';
  }

  if (!client) {
    console.warn('[getPriceWithCommission] Cliente no disponible.');
    return parsedPrice.toFixed(2); // sin redondeo especial
  }

  const commissionStatus = Number(client.commission_status) || 0;
  const commission = parseFloat(client.commission?.toString() ?? '0');

  if (commissionStatus === 1 && commission > 0 && userType === 4) {
    const commissionRate = commission / 100;
    const priceWithCommission = parsedPrice * (1 + commissionRate);
    return roundLito(priceWithCommission, module); // ✅ Aplica redondeo SOLO si hay comisión
  }

  // ❌ No aplica comisión: devuelve sin redondeo especial
  return parsedPrice.toFixed(2);
}

export function hasCommission(client: Client | null, userType: number): boolean {
  if (!client) return false;

  const commissionStatus = Number(client.commission_status) || 0;
  const commission = parseFloat(client.commission?.toString() ?? '0');

  return commissionStatus === 1 && commission > 0 && userType === 4;
}
