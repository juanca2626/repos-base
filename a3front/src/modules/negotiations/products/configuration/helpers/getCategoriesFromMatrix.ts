import type { ProductSupplierBehaviorMatrixItem } from '@/modules/negotiations/products/configuration/interfaces';

export const getCategoriesFromMatrixByLocation = (
  matrix: ProductSupplierBehaviorMatrixItem[],
  operatingLocationKey: string
): string[] => {
  return matrix
    .filter((item) => item.operatingLocationKey === operatingLocationKey)
    .map((item) => item.supplierCategoryCode)
    .filter((code, index, array) => array.indexOf(code) === index); // Eliminar duplicados
};
