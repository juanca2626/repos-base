export interface OriginResponse {
  success: boolean;
  data: Origin[];
  params: Params;
}

export interface Origin {
  codciu: string;
  ciudad: string;
  codpais: string;
  pais: string;
}

export interface Params {
  type: string;
  term: string;
}
