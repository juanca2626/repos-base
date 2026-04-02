export const createLanguageAdapter = (field) => ({
  value: field.iso.toLowerCase(),
  label: field.name.toUpperCase(),
  id: field.id,
});
