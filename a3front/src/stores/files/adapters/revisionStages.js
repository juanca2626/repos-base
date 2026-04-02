export const revisionStagesClasses = {
  2: 'primary',
  1: 'secondary',
};

export const createRevisionStagesAdapter = (field) => ({
  id: field.id,
  name: field.iso,
  // custom fields
  type: revisionStagesClasses[field.id],
});
