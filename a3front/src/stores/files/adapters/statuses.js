export const statusClasses = {
  OK: 'green', // open
  CE: 'red', // closed
  PF: 'purple', // to-bill
  BL: 'light', // blocked
  XL: 'volcano', // override
};

export const createStatusAdapter = (field) => ({
  iso: field.iso,
  name: field.name,
  // custom fields
  type: statusClasses[field.iso],
});
