import moment from 'moment';

export const createTemplateAdapter = (template) => ({
  ...template,
  label: `${template.name}`,
  value: `${template._id}`,
  newType: template.type === 'shared' ? 'Compartido' : 'Privado',
  duration: `${template.durationDays === 1 ? `Full - Day` : `${template.durationDays} día${template.durationDays > 1 ? `s` : ``}`}${template.durationDays > 1 ? ` / ${template.durationDays - 1} noche${template.durationDays > 2 ? `s` : ``}` : ``}`,
  validity: `${moment(template.effectiveStartDate).format('DD/MM/YYYY')} - ${moment(template.effectiveEndDate).format('DD/MM/YYYY')} `,
  updatedAt: `${moment(template.updatedAt).format('DD/MM/YYYY HH:mm')} `,
  startDate: `${moment(template.effectiveStartDate).format('YYYY-MM-DD')} `,
  endDate: `${moment(template.effectiveEndDate).format('YYYY-MM-DD')} `,
});

export const createTemplateServiceAdapter = (service) => ({
  ...service,
  date: moment(service.startDate.substring(0, 10)).format('DD/MM/YYYY'),
  provider: service.providers?.[0]?.name ?? '-',
  observations: service.observations ?? '-',
});

export const createBreakpointAdapter = (breakpoint, index) => ({
  pax: index + 1,
  cost: breakpoint.totalWithOpe,
  selected: false,
});
