interface Data {
  id: number;
  code: string;
  name: string;
}

export interface TypeUnitTransportsResponseInterface {
  success: boolean;
  data: Data[];
  code: number;
}

export interface TypeUnitTransportInterface {
  id: number;
  name: string;
}
