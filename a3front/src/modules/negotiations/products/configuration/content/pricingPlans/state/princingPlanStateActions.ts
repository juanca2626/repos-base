import { createInitialBasicState } from './createInitialBasicState';

export function resetBasicStateAfterTariffTypeChange(state: any) {
  const initial = createInitialBasicState();

  state.specificSeries = initial.specificSeries;
  state.specificClients = initial.specificClients;
  state.specificMarkets = initial.specificMarkets;

  state.promotionName = initial.promotionName;
  state.tariffSegmentation = initial.tariffSegmentation;

  state.periods = initial.periods;
  state.selectedDays = initial.selectedDays;
  state.selectedHolidays = initial.selectedHolidays;

  state.differentiatedTariff = initial.differentiatedTariff;
  state.selectedDays = initial.selectedDays;
  state.standardDays = initial.standardDays;

  state.includeHolidayTariffs = initial.includeHolidayTariffs;
  state.years = initial.years;
  state.selectedYear = initial.selectedYear;
  state.selectedCategoryId = initial.selectedCategoryId;
  state.selectedHolidays = initial.selectedHolidays;

  state.travelTo = initial.travelTo;
}

export function resetBasicStateAfterTravelDatesChange(state: any) {
  const initial = createInitialBasicState();

  state.includeHolidayTariffs = initial.includeHolidayTariffs;
  state.years = initial.years;
  state.selectedYear = initial.selectedYear;
  state.selectedCategoryId = initial.selectedCategoryId;
  state.selectedHolidays = initial.selectedHolidays;
}

export function resetBasicStateAfterModifyBookingPeriodChange(state: any) {
  const initial = createInitialBasicState();

  state.bookingFrom = initial.bookingFrom;
  state.bookingTo = initial.bookingTo;
}

export function resetBasicStateAfterSelectedDaysChange(state: any) {
  const initial = createInitialBasicState();
  state.selectedDays = initial.selectedDays;
}
