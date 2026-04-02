export const completeTimeValue = (value: string): string => {
  if (!value) {
    return '';
  }

  const numericValue = value.replace(/\D/g, '');

  if (numericValue.length === 0) {
    return '';
  }

  if (numericValue.length === 1) {
    return `0${numericValue}:00`;
  }

  if (numericValue.length === 2) {
    const hours = parseInt(numericValue, 10);
    if (hours > 23) {
      return '23:00';
    }
    return `${numericValue}:00`;
  }

  if (numericValue.length === 3) {
    const hours = parseInt(numericValue.substring(0, 2), 10);
    const firstMinuteDigit = numericValue.substring(2, 3);

    if (hours > 23) {
      return '23:00';
    }

    return `${hours.toString().padStart(2, '0')}:${firstMinuteDigit}0`;
  }

  const hours = parseInt(numericValue.substring(0, 2), 10);
  const minutes = parseInt(numericValue.substring(2, 4), 10);

  const validHours = hours > 23 ? 23 : hours;
  const validMinutes = minutes > 59 ? 59 : minutes;

  return `${validHours.toString().padStart(2, '0')}:${validMinutes.toString().padStart(2, '0')}`;
};

export const validateTimeValue = (value: string): string => {
  if (!value || value.length < 5) {
    return value;
  }

  const [hours, minutes] = value.split(':');
  let validHours = parseInt(hours, 10);
  let validMinutes = parseInt(minutes, 10);

  if (validHours > 23) {
    validHours = 23;
  }

  if (validMinutes > 59) {
    validMinutes = 59;
  }

  return `${validHours.toString().padStart(2, '0')}:${validMinutes.toString().padStart(2, '0')}`;
};

export const timeToMinutes = (time: string | null): number => {
  if (!time) return 0;
  const [hours, minutes] = time.split(':').map(Number);
  return hours * 60 + (minutes || 0);
};

export const minutesToTime = (minutes: number): string => {
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
};

export const addTime = (time1: string, time2: string): string => {
  const minutes1 = timeToMinutes(time1);
  const minutes2 = timeToMinutes(time2);
  return minutesToTime(minutes1 + minutes2);
};
