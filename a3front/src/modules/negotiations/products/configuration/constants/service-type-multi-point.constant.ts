export const MULTI_POINT_SERVICE_TYPE_CODES = ['TF', 'AS'] as const;

export type MultiPointServiceTypeCode = (typeof MULTI_POINT_SERVICE_TYPE_CODES)[number];

export function isMultiPointServiceType(code: string | undefined | null): boolean {
  return code != null && (MULTI_POINT_SERVICE_TYPE_CODES as readonly string[]).includes(code);
}

export const AUTOMATIC_SERVICE_TYPE_CODES = ['EX', 'TF', 'AS'] as const;

export type AutomaticServiceTypeCode = (typeof AUTOMATIC_SERVICE_TYPE_CODES)[number];

export function isAutomaticServiceType(code: string | undefined | null): boolean {
  return code != null && (AUTOMATIC_SERVICE_TYPE_CODES as readonly string[]).includes(code);
}
