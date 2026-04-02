export const getHour = (hour?: string) => {
  return hour?.trim().substring(0, 5);
};

export const getHours = (hour?: string) => {
  if (!hour) {
    return '';
  }
  const result: Array<string> | [] = hour?.split('-');
  let hours: string | undefined = '';

  if (result.length > 1) {
    hours = getHour(result[0]) + ' - ' + getHour(result[1]);
  } else if (result.length == 1) {
    hours = getHour(result[0]);
  }

  return hours;
};
