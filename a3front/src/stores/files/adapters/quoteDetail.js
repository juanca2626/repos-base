export const createQuoteDetailAdapter = (field) => ({
  type: field.type,
  code: field.code,
  adult: field.adult,
  child: field.child,
  infant: field.infant,
  origin: field.origin,
  destiny: field.destiny,
  chenIn: field.chen_in,
  checkInTime: field.check_in_time,
  timestamp: field.timestamp,
  // custom fields
});
