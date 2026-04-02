export function shouldConfirmHolidayReset(state: any): boolean {
  return !!state.selectedHolidays?.length;
}
