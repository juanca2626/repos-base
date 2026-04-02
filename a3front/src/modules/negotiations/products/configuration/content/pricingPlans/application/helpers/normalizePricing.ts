export const createDefaultPricing = () => {
  const passenger = () => ({
    netRate: null as number | null,
    total: null as number | null,
    discountEnabled: false,
    discountPercent: 0,
  });

  return {
    passengers: {
      adult: passenger(),
      child: passenger(),
      infant: passenger(),
    },
    ranges: [
      {
        rangeFrom: 1,
        rangeTo: null as number | null,
        passengers: {
          adult: passenger(),
          child: passenger(),
          infant: passenger(),
        },
      },
    ],
  };
};

export const normalizePricing = (pricing: any) => {
  if (!pricing || !pricing.passengers) {
    return createDefaultPricing();
  }

  const defaultPricing = createDefaultPricing();

  return {
    ...defaultPricing,
    ...pricing,

    passengers: {
      ...defaultPricing.passengers,
      ...pricing.passengers,
    },

    ranges: pricing.ranges && pricing.ranges.length > 0 ? pricing.ranges : defaultPricing.ranges,
  };
};
