import moment from 'moment';
import { createTemplateAdapter } from '@/stores/adventure/adapters';

export const createDepartureAdapter = (departure) => {
  const template = createTemplateAdapter(departure.template);

  let paxs = 0;

  departure?.commercialFiles?.forEach((file) => {
    paxs += file.paxs.length;
  });

  return {
    ...departure,
    dateStart: moment(departure.startDate.substring(0, 10)).format('DD/MM/YYYY'),
    dateEnd: moment(departure.startDate.substring(0, 10))
      .add(template.durationDays, 'days')
      .format('DD/MM/YYYY'),
    template: template,
    templateName: template.name,
    templateCode: template.serviceCode,
    newType: template.newType,
    duration: template.duration,
    paxs,
    guideCode: departure.guideCode ?? '',
    showAlert: false,
    closedAt: departure.closedAt ? moment(departure.closedAt).format('DD/MM/YYYY HH:mm:ss') : null,
    rowColor:
      departure.commercialFiles?.length === 0 || !departure.guideCode ? '#F2DEDE' : '#DFF0D8',
  };
};

export const createCalendarDepartureAdapter = (departure) => {
  return {
    id: departure._id,
    title: departure.template.name,
    file: departure.opeFile,
    start: moment(departure.startDate.substring(0, 10)).format('YYYY-MM-DD'),
  };
};
