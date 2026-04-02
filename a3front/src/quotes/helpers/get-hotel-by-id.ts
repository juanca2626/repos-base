import quotesApi from '@/quotes/api/quotesApi';
import type { Hotel } from '@/quotes/interfaces';
// import { getLang } from '@/quotes/helpers/get-lang';
import type { HotelByIdResponse } from '@/quotes/interfaces/quote-hotels-by-id.response';

export const getHotelById = async (hotelId: number, lang: string): Promise<Hotel> => {
  const { data } = await quotesApi.get<HotelByIdResponse>(
    `/services/hotel/${hotelId}?lang=${lang}`
  );

  return data.data;
};
