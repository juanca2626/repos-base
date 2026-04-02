export interface CatalogItem {
  code: string;
  name: string;
}

export interface SupplierCategory extends CatalogItem {}

export interface Profile extends CatalogItem {}

export interface PointType extends CatalogItem {}

export interface TrainType extends CatalogItem {}

export interface Activity extends CatalogItem {}

export interface Requirement extends CatalogItem {}

export interface Inclusion extends CatalogItem {}

export interface TextType extends CatalogItem {
  contentLength: {
    type: string;
    max: number;
  };
}

export interface ProgramDuration extends CatalogItem {}

export interface OperationalSeason extends CatalogItem {}
export interface SupportResource {
  supplierCategories?: SupplierCategory[];
  profiles?: Profile[];
  pointTypes?: PointType[];
  trainTypes?: TrainType[];
  programDurations?: ProgramDuration[];
  operationalSeasons?: OperationalSeason[];
  activities?: Activity[];
  requirements?: Requirement[];
  inclusions?: Inclusion[];
  textTypes?: TextType[];
}
