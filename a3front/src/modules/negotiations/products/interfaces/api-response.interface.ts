type ResponseData<T> = T extends any[] ? T : T extends object ? T : T | null;

export interface ApiResponse<T> {
  success: boolean;
  statusCode?: number;
  timestamp?: string;
  path?: string;
  message?: any[];
  stack?: string;
  data: ResponseData<T>;
}

interface Pagination {
  page: number;
  limit: number;
  total: number;
}

export interface ApiListResponse<T> extends ApiResponse<T> {
  pagination: Pagination;
}
