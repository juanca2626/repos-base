import negotiationsApi from '@/modules/negotiations/negotiationsApi.ts';

class Negotiations {
  constructor() {}

  //getSearchFromServer
  getSheetNegotiationsFromServer = async (
    serverOptions,
    searchInput,
    priorityVal,
    specialistVal,
    statusVal
  ) => {
    let filter = searchInput != '' ? '?name=' + searchInput + '' : '';
    let priority_val = priorityVal != null ? '&priority=' + priorityVal + '' : '';
    let status_id = statusVal != null ? '?status_id=' + statusVal + '' : '';
    let specialist_code = specialistVal != null ? '&specialist=' + specialistVal.name + '' : '';

    if (filter == '' || specialist_code == '' || status_id == '') {
      priority_val = priorityVal != null ? '?priority=' + priorityVal + '' : '';
    }

    if (filter == '' && priority_val == '') {
      specialist_code = specialistVal != null ? '?specialist=' + specialistVal.name + '' : '';
    }

    const { data } = await negotiationsApi.get(
      window.API + 'sheet/negotiation' + filter + priority_val + status_id + specialist_code
    );

    return {
      serverCurrentItems: data.data,
      totalRows: data.meta.total,
      perPage: data.meta.per_page,
    };
  };

  //getPriority
  getPriorities = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/product/filters');
    console.log('getFiltersPriority', data.priorities);
    return data.priorities;
  };

  //getSpecialist
  //typeServices
  getSpecialist = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/negotiation/tables');
    const array = [];
    for (const level1 in data.specialist) {
      array.push({
        id: data.specialist[level1].id,
        name: data.specialist[level1].code,
      });
    }

    console.log('getSpecialist', array);
    return array;
  };

  getListSelectsInNegotiation = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/negotiation/tables');

    const arraySpecialist = [];
    for (const level1 in data.specialist) {
      arraySpecialist.push({
        id: data.specialist[level1].id,
        name: data.specialist[level1].code,
      });
    }

    const arrayClasifications = [];

    for (const level1 in data.clasifications) {
      for (const level2 in data.clasifications[level1]['translations']) {
        arrayClasifications.push({
          id: data.clasifications[level1]['translations'][level2]['object_id'],
          name: data.clasifications[level1]['translations'][level2]['value'],
        });
      }
    }

    const arrayUnits = [];

    for (const level1 in data.units) {
      for (const level2 in data.units[level1]['translations']) {
        arrayUnits.push({
          id: data.units[level1]['translations'][level2]['object_id'],
          name: data.units[level1]['translations'][level2]['value'],
        });
      }
    }

    const arrayLanguages = [];
    for (const level1 in data.languages) {
      arrayLanguages.push({
        id: data.languages[level1]['id'],
        name: data.languages[level1]['name'],
      });
    }
    console.log('getLanguages', arrayLanguages);

    return {
      listSpecialist: arraySpecialist,
      listUnitDurations: data.unit_durations,
      listTurns: data.turns,
      listPhysicalIntensities: data.physical_intensities,
      listPreRequeriments: data.pre_requirements,
      listRestrictions: data.restrictions,
      listLanguages: arrayLanguages,
      listClasifications: arrayClasifications,
      listUnits: arrayUnits,
    };
  };
}

export default Negotiations;
