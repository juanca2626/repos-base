import moment from 'moment';
import negotiationsApi from '@/modules/negotiations/negotiationsApi.ts';

class ProductsNegotiation {
  constructor() {}

  //getSearchFromServer
  getSheetProductsFromServer = async (
    serverOptions,
    searchInput,
    dateRange,
    priorityVal,
    areaVal,
    specialistVal,
    statusVal
  ) => {
    let filter = searchInput != '' ? '?name=' + searchInput + '' : '';
    let d_start =
      dateRange != null ? '&d_start=' + moment(dateRange[0].$d).format('YYYY-MM-DD') + '' : '';
    let d_end =
      dateRange != null ? '&d_end=' + moment(dateRange[1].$d).format('YYYY-MM-DD') + '' : '';
    let priority_val = priorityVal != null ? '&priority=' + priorityVal + '' : '';
    let area_id = areaVal != null ? '?area_id=' + areaVal.id + '' : '';
    let status_id = statusVal != null ? '?status_id=' + statusVal + '' : '';
    const specialist_code = specialistVal != null ? '&specialist=' + specialistVal.code + '' : '';

    if (filter == '') {
      if (d_start != '') {
        d_start = '?d_start=' + moment(dateRange[0].$d).format('YYYY-MM-DD');
      } else if (d_start == '') {
        if (priority_val != '') {
          priority_val = '?priority=' + priorityVal + '';
          if (area_id != '') {
            area_id = areaVal != null ? '&area_id=' + areaVal.id + '' : '';
          }
        }
      }
    } else {
      if (statusVal != null) {
        status_id = '&status_id=' + statusVal + '';
      } else if (areaVal != null) {
        area_id = '&area_id=' + areaVal.id + '';
      }
    }

    const { data } = await negotiationsApi.get(
      window.API +
        'sheet/product' +
        filter +
        d_start +
        d_end +
        priority_val +
        area_id +
        status_id +
        specialist_code
    );

    return {
      serverCurrentItems: data.data,
      totalRows: data.meta.total,
      perPage: data.meta.per_page,
    };
  };

  //getAreas
  getAreas = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/product/filters');
    console.log('getFiltersAreas', data.areas);
    return data.areas;
  };

  //getSpecialist
  getSpecialist = async (area) => {
    const { data } = await negotiationsApi.get(
      window.API + `sheet/product/tables_cascade/${area}/SPECIALIST`
    );
    console.log('getFiltersSpecilist', data.data);
    return data.data;
  };

  //getPriority
  getPriorities = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/product/filters');
    console.log('getFiltersPriority', data.priorities);
    return data.priorities;
  };

  //getExperiences
  getExperiences = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/product/tables');
    const array = [];
    for (const level1 in data.experiences) {
      array.push({
        id: data.experiences[level1]['translations'][0].object_id,
        name: data.experiences[level1]['translations'][0].value,
      });
    }
    return array;
  };
  //typeServices
  getTypeServices = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/product/tables');
    const array = [];
    for (const level1 in data.typeServices) {
      array.push({
        id: data.typeServices[level1]['translations'][0].object_id,
        name: data.typeServices[level1]['translations'][0].value,
      });
    }
    return array;
  };
  //subTypeServices
  getSubTypeServices = async (id) => {
    const { data } = await negotiationsApi.get(
      window.API + 'sheet/product/tables_cascade/' + id + '/SUBTYPESERVICE'
    );
    const array = [];
    for (const level1 in data.data) {
      array.push({
        id: data.data[level1]['translations'][0].object_id,
        name: data.data[level1]['translations'][0].value,
      });
    }
    return array;
  };

  //countries
  getCountries = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/product/tables');
    const array = [];
    for (const level1 in data.countries) {
      array.push({
        id: data.countries[level1]['translations'][0].object_id,
        name: data.countries[level1]['translations'][0].value,
      });
    }
    return array;
  };
  //state
  getState = async (id) => {
    const { data } = await negotiationsApi.get(
      window.API + 'sheet/product/tables_cascade/' + id + '/STATE'
    );
    const array = [];
    for (const level1 in data.data.data) {
      array.push({
        id: data.data.data[level1]['translations'][0].object_id,
        name: data.data.data[level1]['translations'][0].value,
      });
    }
    return array;
  };
  //city
  getCity = async (id) => {
    const { data } = await negotiationsApi.get(
      window.API + 'sheet/product/tables_cascade/' + id + '/CITY'
    );
    const array = [];
    for (const level1 in data.data.data) {
      array.push({
        id: data.data.data[level1]['translations'][0].object_id,
        name: data.data.data[level1]['translations'][0].value,
      });
    }
    return array;
  };
  //zone
  getZone = async (id) => {
    const { data } = await negotiationsApi.get(
      window.API + 'sheet/product/tables_cascade/' + id + '/ZONE'
    );
    const array = [];
    if (data.data.data != []) {
      for (const level1 in data.data.data) {
        array.push({
          id: data.data.data[level1]['translations'][0].object_id,
          name: data.data.data[level1]['translations'][0].value,
        });
      }
    }

    return array;
  };
  //languages
  getLanguages = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/product/tables');
    const array = [];
    for (const level1 in data.languages) {
      array.push({
        id: data.languages[level1]['id'],
        name: data.languages[level1]['name'],
      });
    }
    console.log('getLanguages', array);
    return array;
  };
  //categories
  getCategories = async () => {
    const { data } = await negotiationsApi.get(window.API + 'sheet/product/tables');
    const array = [];
    for (const level1 in data.categories) {
      array.push({
        id: data.categories[level1]['translations'][0].object_id,
        name: data.categories[level1]['translations'][0].value,
      });
    }
    return array;
  };
}

export default ProductsNegotiation;
