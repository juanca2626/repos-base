export const parseKeyOperationLocation = (key: string, separator: string) => {
  const [countryId, stateId, cityId, zoneId] = key.split(separator).map(Number);

  return {
    countryId: countryId,
    stateId: stateId,
    cityId: cityId ?? null,
    zoneId: zoneId ?? null,
  };
};

export const joinKeyOperationLocation = (
  joiner: string,
  countryId: number,
  stateId: number,
  cityId?: number,
  zoneId?: number
): string => {
  return [countryId, stateId, cityId, zoneId].filter(Boolean).join(joiner);
};

export const joinOperationLocationNames = (
  joiner: string,
  countryName: string,
  stateName: string,
  cityName?: string,
  zoneName?: string
): string => {
  return [countryName, stateName, cityName, zoneName].filter(Boolean).join(joiner);
};

export const joinOptionalLocationNames = (
  joiner: string,
  countryName?: string,
  stateName?: string,
  cityName?: string,
  zoneName?: string
): string => {
  return [countryName, stateName, cityName, zoneName].filter(Boolean).join(joiner);
};
