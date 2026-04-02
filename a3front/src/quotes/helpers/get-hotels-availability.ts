import quotesApi from '@/quotes/api/quotesApi';
import type { QuoteHotelsResponse, QuoteHotelsSearchRequest } from '@/quotes/interfaces';

export const getHotelsAvailability = async (
  request: QuoteHotelsSearchRequest
): Promise<QuoteHotelsResponse> => {
  const { data } = await quotesApi.post<QuoteHotelsResponse>(
    `/services/hotels/available/quote`,
    request
  );

  return data;
};
