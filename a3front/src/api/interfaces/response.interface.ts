import type { Meta } from './meta.response';

export interface ApiResponse<T> {
  data: T[];
  meta: Meta;
}
