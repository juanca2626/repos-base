import request from '../../utils/requestFilesOneDB.js';

export function fetchStatements({
  currentPage,
  perPage,
  filter = '',
  filterBy = '',
  clientCode,
  dateFrom,
  dateTo,
  searchOption = '',
}) {
  console.log('Solicitando datos de la página:', currentPage);
  return request({
    method: 'GET',
    url: `statements/clients/${clientCode}`,
    params: {
      page: currentPage,
      limit: perPage,
      filter,
      filter_by: filterBy,
      date_from: dateFrom,
      date_to: dateTo,
      search_option: searchOption,
    },
  });
}
