import esES from 'ant-design-vue/es/date-picker/locale/es_ES';

export const datePickerLocale = {
  ...esES,
  lang: {
    ...esES.lang,
    monthFormat: 'MMMM',
    yearMonthFormat: 'MMMM YYYY',
    shortWeekDays: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
  },
};
