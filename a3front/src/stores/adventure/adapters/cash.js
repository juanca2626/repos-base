import moment from 'moment';

export const createCashAdapter = (cash) => ({
  ...cash,
  dateStart: moment(cash.dateStart).format('DD-MM-YYYY'),
});
