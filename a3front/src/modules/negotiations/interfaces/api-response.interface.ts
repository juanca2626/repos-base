type ResponseData<T> = T extends any[] ? T : T extends object ? T : T | null;

export interface ApiResponse<T> {
  success: boolean;
  data: ResponseData<T>; // Solo permite null si no es un array/objeto
  code: number;
  error?: string;
}

interface Pagination {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

export interface ApiListResponse<T> extends ApiResponse<T> {
  pagination: Pagination;
}

export interface FieldMessageError {
  field: string;
  message: string;
}

export interface HttpFieldMessageError {
  response: {
    status: number;
    statusText: string;
    data: {
      message: string[] | FieldMessageError[];
      path: string;
      stack: string;
      statusCode: number;
      success: boolean;
      timestamp: string;
    };
  };
  message: string;
}
