// Ejemplo de supplierApi.ts
import axios from 'axios';

const supplierApi = {
  getSupplier: (id: string) => axios.get(`/api/supplier/${id}`),
  updateSupplier: (data: any) => axios.put(`/api/supplier/${data.id}`, data),
};

export default supplierApi; // Exportación por defecto
