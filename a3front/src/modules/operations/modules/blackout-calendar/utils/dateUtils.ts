/**
 * Formatea una fecha en formato YYYY-M-D a YYYY-MM-DD.
 *
 * @param inputDate - La fecha a formatear.
 * @returns La fecha formateada como YYYY-MM-DD.
 */
export function formatDate(inputDate: string): string {
  const [year, month, day] = inputDate.split('-');
  const formattedMonth = month.padStart(2, '0');
  const formattedDay = day.padStart(2, '0');
  return `${year}-${formattedMonth}-${formattedDay}`;
}

export function formatDateRange(datetimeStart, datetimeEnd, completeDay: boolean) {
  const months = [
    'Ene.',
    'Feb.',
    'Mar.',
    'Abr.',
    'May.',
    'Jun.',
    'Jul.',
    'Ago.',
    'Sep.',
    'Oct.',
    'Nov.',
    'Dic.',
  ];

  const startDate = new Date(datetimeStart);
  const endDate = new Date(datetimeEnd);

  const startMonth = months[startDate.getMonth()];
  const endMonth = months[endDate.getMonth()];

  const startDay = startDate.getDate();
  const endDay = endDate.getDate();

  const startYear = startDate.getFullYear();
  const endYear = endDate.getFullYear();

  const startHours = startDate.getHours().toString().padStart(2, '0');
  const startMinutes = startDate.getMinutes().toString().padStart(2, '0');

  const endHours = endDate.getHours().toString().padStart(2, '0');
  const endMinutes = endDate.getMinutes().toString().padStart(2, '0');

  if (completeDay) {
    if (startYear === endYear && startMonth === endMonth && startDay === endDay) {
      return `${startDay} ${startMonth} ${startYear}`;
    } else {
      return `${startDay} ${startMonth} ${startYear} - ${endDay} ${endMonth} ${endYear}`;
    }
  } else {
    if (startYear === endYear && startMonth === endMonth && startDay === endDay) {
      if (`${startHours}:${startMinutes}` === `${endHours}:${endMinutes}`) {
        return `${startDay} ${startMonth} ${startYear} | ${startHours}:${startMinutes}`;
      }
      return `${startDay} ${startMonth} ${startYear} | ${startHours}:${startMinutes} - ${endHours}:${endMinutes}`;
    } else {
      return `${startDay} ${startMonth} ${startYear} - ${endDay} ${endMonth}. ${endYear}\n${startHours}:${startMinutes} - ${endHours}:${endMinutes}`;
    }
  }
}

// Obtener fecha actual con formato YYYY-MM-DD
export function getDateNow() {
  const date = new Date();
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  const day = date.getDate();
  return `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
}

export function isHoliday(fecha: string) {
  const holidays = [
    '2025-01-01',
    '2025-04-17',
    '2025-04-18',
    '2025-05-01',
    '2025-06-07',
    '2025-06-29',
    '2025-07-23',
    '2025-07-28',
    '2025-07-29',
    '2025-08-06',
    '2025-08-30',
    '2025-10-08',
    '2025-11-01',
    '2025-12-08',
    '2025-12-09',
    '2025-12-25',
  ];
  return holidays.includes(fecha);
}
