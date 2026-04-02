import moment from 'moment';

export const createDepartureTemplateServiceAdapter = (departureTemplateService) => ({
  ...departureTemplateService,
  startDate: moment(departureTemplateService.startDate).format('DD/MM/YYYY'),
  newDays: departureTemplateService.days.length,
  codsvs: departureTemplateService.category.equivalence,
  codpro: departureTemplateService.providers,
  paxs: departureTemplateService.paxCount,
  priceTotal: parseFloat(departureTemplateService.priceTotal).toFixed(2),
  priceUnit: parseFloat(departureTemplateService.priceUnit).toFixed(2),
  realCost: parseFloat(departureTemplateService.realCost).toFixed(2),
  benefit: parseFloat(departureTemplateService.benefit).toFixed(2),
  benefitPercentage: parseFloat(departureTemplateService.benefitPercentage).toFixed(2),
});
