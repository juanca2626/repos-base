export const createStatisticsAdapter = (field) => ({
  dateMax: field.date_max,
  dateMonth: field.date_month,
  dateQuarter: field.date_quarter,
  dateWeekly: field.date_weekly,
  filesMax: field.files_max,
  filesMonth: field.files_month,
  filesQuarter: field.files_quarter,
  filesWeekly: field.files_weekly,
  filesTotal: field.files_total,
  filesTotalOpe: field.files_total_ope,
  filesTotalDtr: field.files_total_dtr,
  // custom fields
});
