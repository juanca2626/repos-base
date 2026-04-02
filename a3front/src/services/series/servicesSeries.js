import axios from 'axios';

class ServicesSeries {
  constructor() {}

  // =========================
  // LISTADO CON PAGINACIÓN
  // =========================
  getSeriesPaginated = async (serverOptions, searchQuery = '') => {
    const { page, rowsPerPage } = serverOptions;
    const filter = searchQuery ? `&filter=${searchQuery}` : '';

    const { data } = await axios.get(
      `${window.API}series?per_page=${rowsPerPage}&page=${page}${filter}`,
      { Accept: 'application/json' }
    );

    const series = data.data.map((item) => ({
      id: item.id,
      name: item.name,
      code: item.code,
      status: item.status,
      created_at: item.created_at,
    }));

    return {
      serverCurrentPageItems: series,
      serverTotalItemsLength: data.pagination?.total || 0,
    };
  };

  // =========================
  // LISTADO SIMPLE (COMBOS)
  // =========================
  getSeries = async () => {
    const { data } = await axios.get(`${window.API}series`, {
      Accept: 'application/json',
    });

    return data.data.map((item) => ({
      id: item.id,
      name: item.name,
    }));
  };

  // =========================
  // OBTENER POR ID
  // =========================
  getSeriesById = async (id) => {
    const { data } = await axios.get(`${window.API}series/${id}`, {
      Accept: 'application/json',
    });
    return data.data;
  };

  // =========================
  // CREAR
  // =========================
  createSeries = async (payload) => {
    try {
      await axios.post(`${window.API}series`, payload, {
        Accept: 'application/json',
      });

      return {
        success: true,
        message: 'Serie creada correctamente',
      };
    } catch (e) {
      console.error(e);
      return {
        success: false,
        message: 'Error al crear la serie',
      };
    }
  };

  // =========================
  // ACTUALIZAR
  // =========================
  updateSeries = async (id, payload) => {
    try {
      await axios.put(`${window.API}series/${id}`, payload, {
        Accept: 'application/json',
      });

      return {
        success: true,
        message: 'Serie actualizada correctamente',
      };
    } catch (e) {
      console.error(e);
      return {
        success: false,
        message: 'Error al actualizar la serie',
      };
    }
  };

  // =========================
  // ELIMINAR
  // =========================
  deleteSeries = async (id) => {
    const { status } = await axios.delete(`${window.API}series/${id}`, {
      Accept: 'application/json',
    });

    return status === 200;
  };
}

export default ServicesSeries;
