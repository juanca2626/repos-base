export const haveInvoiceClasses = {
  SI: 'billed', // invoiced
  NO: 'unbilled', // not-invoiced
  '00': '', // no-facturable
  PL: '', // facturado-parcial
};

export const createHaveInvoiceAdapter = (field) => ({
  iso: field.iso,
  name: field.name,
  detail: field.detail,
  // custom fields
  type: haveInvoiceClasses[field.iso],
});
