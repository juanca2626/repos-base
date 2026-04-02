export interface Response {
  success: boolean;
  data: Datum[];
  code: number;
}

export interface Datum {
  id: number;
  code: string;
  name: string;
  sub_classifications: SubClassification[];
}

export interface SubClassification {
  id: number;
  name: string;
}
