import moment from 'moment';

export const createEffectiveAdapter = (effective) => ({
  ...effective,
  templateName: effective.template.name ?? '',
  serviceCode: effective.template.serviceCode ?? '',
  typeName: effective.template.type === 'shared' ? 'Compartido' : 'Privado',
  durationName: effective.template.durationType ?? '',
  totalAmount: parseFloat(effective.summary.totalAmount ?? 0).toFixed(2),
  realAmount: parseFloat(effective.summary.realAmount ?? 0).toFixed(2),
  summary: parseFloat(effective.summary.difference ?? 0).toFixed(2),
  startDate: moment(effective.template.startDate).format('DD/MM/YYYY'),
  commercialFiles: (effective?.departure?.commercialFiles ?? []).map((item) => item.file.number),
});
