export interface Provider {
  _id: string;
  code: string;
  createdAt: Date;
  fullname: string;
  status: boolean;
  type: Type;
  updatedAt: Date;
}

export enum Type {
  GUI = 'GUI',
}
