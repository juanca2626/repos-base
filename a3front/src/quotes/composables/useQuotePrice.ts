import { storeToRefs } from 'pinia';

// import quotesApi from "@/quotes/api/quotesApi";
import quotesA3Api from '@/quotes/api/quotesA3Api';
import type {
  QuotePricePassenger,
  QuotePricePassengersResponse,
  QuotePriceRange,
  QuotePriceRangesResponse,
} from '@/quotes/interfaces';
import { getUserId, getUserType } from '@/utils/auth';
// import { getLang } from "@/quotes/helpers/get-lang";
import useLoader from '@/quotes/composables/useLoader';
import { useQuoteStore } from '@/quotes/store/quote.store';
import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';

const getQuotePricePassenger = async (
  quote_id: number,
  lang: string
): Promise<QuotePricePassenger> => {
  const { data } = await quotesA3Api.get<QuotePricePassengersResponse>(
    `/api/quote/${quote_id}/price-by-passengers?user_type_id=${getUserType()}&user_id=${getUserId()}&lang=${lang}`
  );

  return data.data;
};

const getQuotePriceRange = async (quote_id: number, lang: string): Promise<QuotePriceRange> => {
  const { data } = await quotesA3Api.get<QuotePriceRangesResponse>(
    `/api/quote/${quote_id}/price-by-ranges?user_type_id=${getUserType()}&user_id=${getUserId()}&lang=${lang}`
  );

  return data.data;
};

const useQuotePrice = () => {
  const store = useQuoteStore();
  const { quote, quotePricePassenger, quotePriceRanger } = storeToRefs(store);
  const { showIsLoading, closeIsLoading } = useLoader();
  const { getLang } = useQuoteTranslations();

  return {
    // Properties
    quotePricePassenger,
    quotePriceRanger,
    // Methods
    getQuotePricePassenger: async (silent: boolean = false) => {
      if (!silent) showIsLoading();
      console.log('descarga');
      try {
        quotePricePassenger.value = await getQuotePricePassenger(quote.value.id, getLang());
        if (!silent) closeIsLoading();
      } catch (e) {
        console.log(e);
        if (!silent) closeIsLoading();
      }
    },
    getQuotePriceRanger: async (silent: boolean = false) => {
      if (!silent) showIsLoading();
      try {
        quotePriceRanger.value = await getQuotePriceRange(quote.value.id, getLang());
        if (!silent) closeIsLoading();
      } catch (e) {
        console.log(e);
        if (!silent) closeIsLoading();
      }
    },

    // Getters
  };
};

export default useQuotePrice;
