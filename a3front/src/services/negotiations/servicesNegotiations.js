import axios from 'axios';

class ServicesNegotiation {
  constructor() {}

  //getSearchFromServer
  getSearchFromServer = async (
    serverOptions,
    searchQuery,
    marketQuery,
    clientQuery,
    serviceQuery
  ) => {
    const filter = searchQuery != '' ? '&filter=' + searchQuery + '' : '';
    const market = marketQuery != null ? '&market_id=' + marketQuery['id'] + '' : '';
    const client = clientQuery != null ? '&client_id=' + clientQuery['id'] + '' : '';
    const service = serviceQuery != null ? '&service_type_id=' + serviceQuery['id'] + '' : '';
    const { rowsPerPage } = serverOptions;

    const { data } = await axios.get(
      window.API +
        'base_rate?per_page=' +
        rowsPerPage +
        '&page=1' +
        filter +
        market +
        client +
        service,
      { Accept: 'application/json' }
    );

    const array = [];

    console.log('dataLoad', data.data);

    if (status == 200)
      for (const level1 in data.data) {
        const marketRes = [];
        const seriesRes = [];

        for (const level2 in data.data[level1]['markets']) {
          marketRes.push(data.data[level1]['markets'][level2]['name']);
        }

        for (const level2 in data.data[level1]['series']) {
          seriesRes.push(data.data[level1]['series'][level2]['name']);
        }
        //?? desacoplar funcion

        const id = data.data[level1]['id'];
        const has_flat_rate = data.data[level1]['has_flat_rate'];
        const name = data.data[level1]['name'];
        const status = data.data[level1]['status'];
        const status_complete = data.data[level1]['status_complete_metadata'];
        const type =
          data.data[level1]['service_type'] == null || 0
            ? 'Sin datos'
            : data.data[level1]['service_type']['name'];
        const city =
          data.data[level1]['state'] == null || 0
            ? 'Sin datos'
            : data.data[level1]['state']['name'];
        const client =
          data.data[level1]['client'] == null || 0
            ? 'Sin datos'
            : data.data[level1]['client']['name'];
        const market = data.data[level1]['markets'].length == 0 ? 'Sin datos' : marketRes;
        const serie = data.data[level1]['series'].length == 0 ? 'Sin datos' : seriesRes;
        const pax =
          data.data[level1]['type_passenger'] == null || 0
            ? 'Sin datos'
            : data.data[level1]['type_passenger']['name'];
        const range = data.data[level1]['pax_from'] + '-' + data.data[level1]['pax_to'];
        const service_category =
          data.data[level1]['service_category_id'] == 0
            ? 'Sin datos'
            : data.data[level1]['service_category_id'];
        let codes = data.data[level1]['codes'];
        let code = data.data[level1]['base_code'];

        if (!has_flat_rate) {
          if (codes.start !== codes.end) {
            code = codes.start + ' ... ' + codes.end;
          }
        }
        array.push({
          id: id,
          Code: code,
          has_flat_rate: has_flat_rate,
          Name: name,
          Range: range,
          Type: type,
          Pax: pax,
          City: city,
          Market: market,
          Client: client,
          Serie: serie,
          Service_category: service_category,
          Status: status,
          Status_complete: status_complete,
        });
      }
    return {
      serverCurrentPageItems: array,
      serverTotalItemsLength: data.pagination.total || 0,
    };
  };

  //getMarkets
  getMarkets = async (lang) => {
    const { data } = await axios.get(window.API + 'markets?lang=' + lang, {
      Accept: 'application/json',
    });

    const array = [];

    for (const index in data.data) {
      array.push({
        id: data.data[index]['value'],
        name: data.data[index]['text'],
      });
    }

    return array;
  };

  //getServices
  getServices = async (lang) => {
    const { data } = await axios.get(window.API + 'service_types?lang=' + lang, {
      Accept: 'application/json',
    });
    const array = [];

    for (const index in data.data) {
      array.push({
        id: data.data[index]['id'],
        name: data.data[index]['translations'][0]['value'],
      });
    }

    return array;
  };

  //getClients
  searchClientsByMarket = async (idmarket) => {
    if (idmarket != null) {
      const array = [];
      const { data } = await axios.get(window.API + 'markets/clients?markets[]=' + idmarket.id, {
        Accept: 'application/json',
      });
      for (const index in data.data) {
        array.push({
          id: data.data[index]['id'],
          value: data.data[index]['name'],
        });
      }
      return array;
    } else {
      return [];
    }
  };

  //deleteItemModal
  deleteItemModal = async (id) => {
    const { status } = await axios.delete(window.API + 'base_rate/' + id, {
      Accept: 'application/json',
    });
    if (status == 200) return true;
  };

  //deleteAllItems
  deleteBundleItem = async (array) => {
    for (const key in array) {
      await axios.delete(window.API + 'base_rate/' + array[key]['id'], {
        Accept: 'application/json',
      });
    }
    return 'complete';
  };

  //getPaginateServer
  getListPaginateServer = async (
    serverOptions,
    searchQuery,
    marketQuery,
    clientQuery,
    serviceQuery
  ) => {
    const filter = searchQuery != '' ? '&filter=' + searchQuery + '' : '';
    const market = marketQuery != null ? '&market_id=' + marketQuery['id'] + '' : '';
    const client = clientQuery != null ? '&client_id=' + clientQuery['id'] + '' : '';
    const service = serviceQuery != null ? '&service_type_id=' + serviceQuery['id'] + '' : '';

    const { page, rowsPerPage } = serverOptions;

    const { data } = await axios.get(
      window.API +
        'base_rate?per_page=' +
        rowsPerPage +
        '&page=' +
        page +
        filter +
        market +
        client +
        service,
      //this.headers()
      { Accept: 'application/json' }
    );

    const array = [];

    if (status == 200)
      for (const level1 in data.data) {
        console.log('getListPaginate', data.data);
        const marketRes = [];
        const seriesRes = [];

        for (const level2 in data.data[level1]['markets']) {
          marketRes.push(data.data[level1]['markets'][level2]['name']);
        }

        for (const level2 in data.data[level1]['series']) {
          seriesRes.push(data.data[level1]['series'][level2]['name']);
        }

        const id = data.data[level1]['id'];
        const name = data.data[level1]['name'];
        const has_flat_rate = data.data[level1]['has_flat_rate'];
        const status = data.data[level1]['status'];
        const status_complete = data.data[level1]['status_complete_metadata'];
        const type =
          data.data[level1]['service_type'] == null || 0
            ? 'Sin datos'
            : data.data[level1]['service_type']['name'];
        const city =
          data.data[level1]['state'] == null || 0
            ? 'Sin datos'
            : data.data[level1]['state']['name'];
        const client =
          data.data[level1]['client'] == null || 0
            ? 'Sin datos'
            : data.data[level1]['client']['name'];
        const market = data.data[level1]['markets'].length === 0 ? 'Sin datos' : marketRes;
        const serie = data.data[level1]['series'].length === 0 ? 'Sin datos' : seriesRes;
        const pax =
          data.data[level1]['type_passenger'] == null || 0
            ? 'Sin datos'
            : data.data[level1]['type_passenger']['name'];
        const range = data.data[level1]['pax_from'] + '-' + data.data[level1]['pax_to'];
        const service_category =
          data.data[level1]['service_category_id'] === 0
            ? 'Sin datos'
            : data.data[level1]['service_category_id'];

        let codes = data.data[level1]['codes'];
        let code = data.data[level1]['base_code'];

        if (!has_flat_rate) {
          if (codes.start !== codes.end) {
            code = codes.start + ' ... ' + codes.end;
          }
        }
        array.push({
          id: id,
          Code: code,
          Name: name,
          Range: range,
          Type: type,
          Pax: pax,
          City: city,
          Market: market,
          Client: client,
          Serie: serie,
          Service_category: service_category,
          Status: status,
          Status_complete: status_complete,
        });
      }
    return {
      serverCurrentPageItems: array,
      serverTotalItemsLength: data.pagination.total || 0,
    };
  };

  //getPaginateServer
  getListFilterPaginate = async (
    serverOptions,
    rangoPaxInit,
    rangPaxFin,
    estado,
    statusProgress
  ) => {
    const rango_pax_init = rangoPaxInit != '' ? '&pax_from=' + rangoPaxInit + '' : '';
    const rango_pax_fin = rangPaxFin != '' ? '&pax_to=' + rangPaxFin + '' : '';
    const filtro_estado = estado != '' ? '&status=' + estado + '' : '';
    const status_progress = statusProgress != '' ? '&status_complete=' + statusProgress + '' : '';

    const { page, rowsPerPage } = serverOptions;

    const { data } = await axios.get(
      window.API +
        'base_rate?per_page=' +
        rowsPerPage +
        '&page=' +
        page +
        rango_pax_init +
        rango_pax_fin +
        filtro_estado +
        status_progress,
      //this.headers()
      { Accept: 'application/json' }
    );

    const array = [];

    if (status == 200) console.log('data listFilterPaginate', data.data);
    for (const level1 in data.data) {
      console.log('getListPaginate', data.data);
      const marketRes = [];
      const seriesRes = [];

      for (const level2 in data.data[level1]['markets']) {
        marketRes.push(data.data[level1]['markets'][level2]['name']);
      }

      for (const level2 in data.data[level1]['series']) {
        seriesRes.push(data.data[level1]['series'][level2]['name']);
      }

      const id = data.data[level1]['id'];
      const name = data.data[level1]['name'];
      const has_flat_rate = data.data[level1]['has_flat_rate'];
      const status = data.data[level1]['status'];
      const status_complete = data.data[level1]['status_complete_metadata'];
      const type =
        data.data[level1]['service_type'] == null || 0
          ? 'Sin datos'
          : data.data[level1]['service_type']['name'];
      const city =
        data.data[level1]['state'] == null || 0 ? 'Sin datos' : data.data[level1]['state']['name'];
      const client =
        data.data[level1]['client'] == null || 0
          ? 'Sin datos'
          : data.data[level1]['client']['name'];
      const market = data.data[level1]['markets'].length === 0 ? 'Sin datos' : marketRes;
      const serie = data.data[level1]['series'].length === 0 ? 'Sin datos' : seriesRes;
      const pax =
        data.data[level1]['type_passenger'] == null || 0
          ? 'Sin datos'
          : data.data[level1]['type_passenger']['name'];
      const range = data.data[level1]['pax_from'] + '-' + data.data[level1]['pax_to'];
      const service_category =
        data.data[level1]['service_category_id'] === 0
          ? 'Sin datos'
          : data.data[level1]['service_category_id'];

      let codes = data.data[level1]['codes'];
      let code = data.data[level1]['base_code'];

      if (!has_flat_rate) {
        if (codes.start !== codes.end) {
          code = codes.start + ' ... ' + codes.end;
        }
      }

      array.push({
        id: id,
        Code: code,
        Name: name,
        Range: range,
        Type: type,
        Pax: pax,
        City: city,
        Market: market,
        Client: client,
        Serie: serie,
        Service_category: service_category,
        Status: status,
        Status_complete: status_complete,
      });
    }
    return {
      serverCurrentPageItems: array,
      serverTotalItemsLength: data.pagination.total || 0,
    };
  };

  getServicesClass = async (lang) => {
    const { data } = await axios.get(window.API + 'service_class?lang=' + lang, {
      Accept: 'application/json',
    });
    const array = [];

    for (const index in data.data) {
      array.push({
        id: data.data[index]['id'],
        value: data.data[index]['translations'][0]['value'],
      });
    }
    return array;
  };

  getCities = async (lang) => {
    const { data } = await axios.get(window.API + 'cities?lang=' + lang, {
      Accept: 'application/json',
    });
    const array = [];

    for (const index in data.data) {
      array.push({
        id: data.data[index]['id'],
        value: data.data[index]['translations'][0]['value'],
      });
    }
    return array;
  };

  getLanguage = async () => {
    const { data } = await axios.get(window.API + 'languages', {
      Accept: 'application/json',
    });
    return data.data;
  };

  getUnidad = async () => {
    const { data } = await axios.get(window.API + 'type_unit', {
      Accept: 'application/json',
    });
    const array = [];

    for (const index in data.data) {
      array.push({
        id: data.data[index]['id'],
        value: data.data[index]['name'],
      });
    }
    return array;
  };

  getTypePax = async () => {
    const { data } = await axios.get(window.API + 'type_passenger', {
      Accept: 'application/json',
    });
    const array = [];

    for (const index in data.data) {
      array.push({
        id: data.data[index]['id'],
        value: data.data[index]['name'],
      });
    }
    return array;
  };

  getOriginPax = async () => {
    const { data } = await axios.get(window.API + 'origin_passenger', {
      Accept: 'application/json',
    });
    const array = [];

    for (const index in data.data) {
      array.push({
        id: data.data[index]['id'],
        value: data.data[index]['name'],
      });
    }
    return array;
  };

  getBussines = async () => {
    const { data } = await axios.get(window.API + 'business_group', {
      Accept: 'application/json',
    });

    return data.data;
  };

  getCategHab = async (lang) => {
    const array = [];

    const { data } = await axios.get(window.API + 'hotel/categories?lang=' + lang, {
      Accept: 'application/json',
    });

    for (const index in data.data) {
      array.push({
        id: data.data[index]['code'],
        label: data.data[index]['label'],
      });
    }

    return array;
  };

  getTypeFood = async () => {
    const { data } = await axios.get(window.API + 'type_food', {
      Accept: 'application/json',
    });

    return data.data;
  };

  getSeriesArray = async () => {
    const { data } = await axios.get(window.API + 'series', {
      Accept: 'application/json',
    });

    return data.data;
  };

  getAllRegions = async () => {
    const { data } = await axios.get(window.API + 'regions', {
      Accept: 'application/json',
    });
    return data.data;
  };

  getAllMarkets = async () => {
    const { data } = await axios.get(window.API + 'region/markets', {
      Accept: 'application/json',
    });
    return data.data;
  };

  getMarketsByRegion = async (region) => {
    //esto devuelve un array con los valores seleccionados en region
    //console.log('regionArray', region);

    let regionesId = '';
    let regionArray = '';

    for (let i = 0; i < region.length; i++) {
      regionesId = region[i].id.toString();

      if (region.length > 1) {
        regionArray += 'regions[]=' + regionesId + '&';
      } else {
        regionArray = 'regions[]=' + regionesId;
      }
    }

    const { data } = await axios.get(window.API + 'region/markets?' + regionArray, {
      Accept: 'application/json',
    });
    return data.data;
  };

  getClientsByMarket = async (mercado) => {
    //esto devuelve un array con los valores seleccionados en region
    console.log('mercadoArray', mercado);

    let mercadoId = '';
    let mercadoArray = '';

    for (let i = 0; i < mercado.length; i++) {
      mercadoId = mercado[i].id.toString();

      if (mercado.length > 1) {
        mercadoArray += 'markets[]=' + mercadoId + '&';
      } else {
        mercadoArray = 'markets[]=' + mercadoId;
      }
    }

    const { data } = await axios.get(window.API + 'markets/clients?' + mercadoArray, {
      Accept: 'application/json',
    });

    const newClients = data.data.map((obj) => {
      return {
        id: obj.id,
        name: obj.code + ' - ' + obj.name,
      };
    });

    return newClients;
  };

  getClientsByMarketSimple = async (mercado) => {
    //esto devuelve un array con los valores seleccionados en region
    console.log('mercadoArray', mercado);

    //let mercadoId= '';
    let mercadoArray = '';

    mercadoArray = 'markets[]=' + mercado;

    const { data } = await axios.get(window.API + 'markets/clients?' + mercadoArray, {
      Accept: 'application/json',
    });

    console.log('mercado simple data', data.data);

    const newClients = data.data.map((obj) => {
      return {
        id: obj.id,
        name: obj.code + ' - ' + obj.name,
      };
    });

    return newClients;
  };

  getAllClients = async () => {
    const { data } = await axios.get(window.API + 'markets/clients', {
      Accept: 'application/json',
    });

    const newClients = data.data.map((obj) => {
      return {
        id: obj.id,
        name: obj.code + ' - ' + obj.name,
      };
    });

    //cliente.value=newClients

    return newClients;
  };

  getClientsByMarketxFilter = async (mercado, value) => {
    //esto devuelve un array con los valores seleccionados en region
    //console.log('mercadoArray', mercado);

    const valor = value;

    let mercadoId = '';
    let mercadoArray = '';

    for (let i = 0; i < mercado.length; i++) {
      mercadoId = mercado[i].id.toString();

      mercadoArray += 'markets[]=' + mercadoId + '&';
    }
    if (mercadoArray.length > 0) {
      const { data } = await axios.get(
        window.API + 'markets/clients?' + mercadoArray + 'filter=' + valor,
        { Accept: 'application/json' }
      );

      const newClients = data.data.map((obj) => {
        return {
          id: obj.id,
          name: obj.code + ' - ' + obj.name,
        };
      });
      return newClients;
    }
  };

  getItem = async (id) => {
    const { data } = await axios.get(window.API + 'base_rate/' + id, {
      Accept: 'application/json',
    });
    return data.data;
  };

  saveDataValuesClass = async (item) => {
    try {
      await axios.post(window.API + 'base_rate', item, {
        Accept: 'application/json',
      });
      console.log('item enviado', item);
      return {
        success: true,
        message: 'El registro se realizo correctamente',
      };
    } catch (e) {
      const error = e.response?.data.error.base_code[0];
      /*
            forEach(e.response?.data.error, (val, key) => {
                for (const index in e.response?.data.error[key]) {
                    alert(e.response?.data.error[key][index])
                }
            })
            */
      alert(error);
      return {
        success: false,
        message: 'errors',
      };
    }
  };

  updateItemClass = async (item, id) => {
    try {
      await axios.put(window.API + 'base_rate/' + id, item, {
        Accept: 'application/json',
      });
      console.log('item + id', item);
      return {
        success: true,
        message: 'El registro se modifico correctamente',
      };
    } catch (e) {
      if (Array.isArray(e.response?.data.error)) {
        e.response.data.error.forEach((val, key) => {
          for (const index in e.response.data.error[key]) {
            alert(e.response.data.error[key][index]);
          }
        });
      } else {
        console.error('e.response?.data.error no es un array');
      }
      return {
        success: false,
        message: 'errors',
      };
    }
  };
}

export default ServicesNegotiation;
