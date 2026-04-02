import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';

interface BuildTariffNameInput {
  tariffType: TariffType;
  promotionName?: string;
  markets?: string[];
  clients?: string[];
}

export function buildTariffName({
  tariffType,
  promotionName,
  markets = [],
  clients = [],
}: BuildTariffNameInput): string {
  switch (tariffType) {
    case TariffType.FLAT:
      return 'Tarifa Plana';

    case TariffType.PERIODS:
      return 'Tarifa Periodos';

    case TariffType.PROMOTIONAL:
      return promotionName ? `Tarifa Promocional - ${promotionName}` : 'Tarifa Promocional';

    case TariffType.SPECIFIC:
      return buildSpecificName(markets, clients);

    default:
      return 'Tarifa';
  }
}

function buildSpecificName(markets: string[], clients: string[]): string {
  const items = [...markets, ...clients].filter(Boolean);

  if (!items.length) return 'Tarifa Específica';

  return `Tarifa Específica (${formatList(items)})`;
}

function formatList(items: string[]): string {
  if (items.length === 1) return items[0];
  if (items.length === 2) return `${items[0]} y ${items[1]}`;

  const last = items[items.length - 1];
  const rest = items.slice(0, -1);

  return `${rest.join(', ')} y ${last}`;
}
