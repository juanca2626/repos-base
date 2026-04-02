export const createVipAdapter = (field) => ({
  id: field.id,
  isoIfx: field.iso,
  userId: field.user_id,
  name: field.name,
  entity: field.entity,
  createdAt: field.created_at,
  updatedAt: field.updated_at,
  deletedAt: field.deleted_at,
  // custom fields
});
