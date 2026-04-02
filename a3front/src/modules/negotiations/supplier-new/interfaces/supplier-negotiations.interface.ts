/** Clasificación normalizada (aplanada desde la estructura anidada de la API) */
export interface SupplierClassificationInterface {
  typeCode: string;
  name: string;
  group: string;
}

/** Subclasificación normalizada */
export interface SupplierSubClassificationInterface {
  subtypeCode: string;
  name: string;
  parentTypeCode: string;
}

/** Estructura anidada que devuelve la API */
export interface SupplierClassificationApiSubtype {
  name: string;
  subtypeCode: string;
}

export interface SupplierClassificationApiType {
  _id?: string;
  type: string;
  typeCode: string;
  subtypes: SupplierClassificationApiSubtype[];
}

export interface SupplierClassificationApiGroup {
  _id?: string;
  group: string;
  types: SupplierClassificationApiType[];
}
