import moment from 'moment';

export const createServiceAdapter = (service) => ({
  ...service,
  startDate: moment(service.startDate).format('DD/MM/YYYY'),
  categoryName: service?.category?.name ?? '-',
  providerCount: service?.providers?.length ?? 0,
  priceUnit: service?.priceUnit ?? 0,
  pax: service?.paxQuantity ?? 0,
  priceTotal: service?.priceTotal ?? 0,
  label: `${service.name}`,
  value: `${service._id}`,
});
