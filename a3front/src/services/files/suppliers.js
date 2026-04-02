import requestNode from '../../utils/requestNode';

export function fetchSuppliers(filter, limit = 10) {
  return requestNode({
    method: 'GET',
    url: `providers`,
    params: {
      filter: filter,
      limit: limit,
    },
  });
}
