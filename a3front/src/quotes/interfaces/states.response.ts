export interface StatesResponse {
  success: boolean;
  data: State[];
}

export interface State {
  iso: string;
  name: string;
}
