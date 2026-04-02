export interface Provider {
  _id: string;
  code: string;
  contract: Contract;
  createdAt: Date;
  fullname: string;
  status: boolean;
  type: Type;
  updatedAt: Date;
}

export enum Contract {
  F = 'F',
  P = 'P',
}

export enum Type {
  GUI = 'GUI',
}
