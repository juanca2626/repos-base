export const createPaginationAdapter = (pagination) => ({
  current: pagination.page,
  pageSize: pagination.limit,
  total: pagination.total,
});
