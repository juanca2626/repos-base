import type { SupplierInfo } from '@/modules/negotiations/suppliers/interfaces';

const isSingular = (quantity: number) => quantity === 1;

const defaultGetText = (quantity: number) => {
  return `${quantity} ${isSingular(quantity) ? 'registro añadido' : 'registros añadidos'}`;
};

const getAddedText = (quantity: number, singularText: string, pluralText: string) => {
  return `${quantity} ${isSingular(quantity) ? `${singularText} añadido` : `${pluralText} añadidos`}`;
};

export const supplierInfo: Record<string, SupplierInfo> = {
  TRP: {
    getRecordsAddedText: defaultGetText,
    classificationName: 'Transporte',
    listTitle: 'Proveedores de Transporte terrestre',
  },
  ATT: {
    getRecordsAddedText: defaultGetText,
    classificationName: 'Atractivos turísticos',
    listTitle: 'Proveedores de Atractivos turísticos',
  },
  RES: {
    getRecordsAddedText: (quantity: number) =>
      getAddedText(quantity, 'restaurante', 'restaurantes'),
    classificationName: 'Restaurantes',
    listTitle: 'Proveedores de Restaurantes',
  },
  LOD: {
    getRecordsAddedText: (quantity: number) => getAddedText(quantity, 'lodge', 'lodges'),
    classificationName: 'Lodges',
    listTitle: 'Proveedores de lodge',
  },
  ACU: {
    getRecordsAddedText: (quantity: number) => getAddedText(quantity, 'lancha', 'lanchas'),
    classificationName: 'Lanchas',
    listTitle: 'Proveedores de Lanchas',
  },
  STA: {
    getRecordsAddedText: defaultGetText,
    classificationName: 'Staff',
    listTitle: 'Proveedores de Staff',
  },
  OTR: {
    getRecordsAddedText: defaultGetText,
    classificationName: 'Misceláneos',
    listTitle: 'Proveedores de Misceláneos',
  },
  CRC: {
    getRecordsAddedText: (quantity: number) => getAddedText(quantity, 'crucero', 'cruceros'),
    classificationName: 'Crucero',
    listTitle: 'Proveedores de Cruceros',
  },
  OPE: {
    getRecordsAddedText: (quantity: number) =>
      getAddedText(quantity, 'operador local', 'operadores locales'),
    classificationName: 'Operador Locales',
    listTitle: 'Proveedores de Operadores locales',
  },
  TRN: {
    getRecordsAddedText: defaultGetText,
    classificationName: 'Trenes',
    listTitle: 'Proveedores de Trenes',
  },
  AER: {
    getRecordsAddedText: defaultGetText,
    classificationName: 'Aerolineas',
    listTitle: 'Proveedores de Aerolineas',
  },
};
