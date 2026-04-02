export interface ProductQueryParams {
  page: number;
  pageSize: number;
  searchTerm?: string;
}

export interface ProductFilterInputs {
  searchTerm: string | null;
}
