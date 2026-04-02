import moment from 'moment';

export const createProgrammingAdapter = (programming) => ({
  ...programming,
  label: `${programming.name ?? '-'}`,
  value: `${programming._id}`,
  departure: {
    ...programming.departure,
    startDate: moment(programming.departure?.startDate).format('DD/MM/YYYY'),
    type_iso: programming.departure?.type === 'private' ? 'PRIVADO' : 'COMPARTIDO',
  },
  service: {
    ...programming.service,
    startDate: moment(programming.service?.startDate).format('DD/MM/YYYY'),
  },
});
