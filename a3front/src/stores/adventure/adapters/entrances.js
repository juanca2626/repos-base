import moment from 'moment';

export const createEntranceAdapter = (entrance) => {
  return {
    ...entrance,
    departureDate: moment(entrance.departureInfo?.startDate).format('DD/MM/YYYY'),
    startDate: moment(entrance.startDate).format('DD/MM/YYYY'),
    opeFile: entrance.departureInfo?.opeFile ?? '-',
    templateName: entrance.template.name,
    commercialFiles: entrance.departureInfo?.commercialFiles || [],
    serviceCode: entrance.template.serviceCode,
    paxs: entrance.totals.paxCount,
    providerCode: entrance.providers?.[0]?.code ?? '-',
    costPax: parseFloat(entrance.totals.unitCost ?? 0).toFixed(2),
    totalPax: parseFloat(entrance.totals.total ?? 0).toFixed(2),
    quantityPor: entrance?.providerTotals?.count ?? 0,
    costPor: parseFloat(entrance?.providerTotals?.unitCost ?? 0).toFixed(2),
    totalPor: parseFloat(entrance?.providerTotals?.total ?? 0).toFixed(2),
    totalAmount: parseFloat(entrance.totalAmount ?? 0).toFixed(2),
    ticketCode: entrance.ticketInformation?.reservationCode ?? '-',
    ticketDescription: entrance.ticketInformation?.reservationDescription ?? '-',
    attachments: entrance.ticketInformation?.attachments ?? [],
  };
};
