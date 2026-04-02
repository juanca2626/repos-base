import { ref } from 'vue';

import type { QuoteHotelCategory, QuoteHotelCategoryListResponse } from '@/quotes/interfaces';
import quotesApi from '@/quotes/api/quotesApi';
// import { getLang } from "@/quotes/helpers/get-lang";
import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';

// import useQuoteTranslations  from "@/quotes/composables/useQuoteTranslations";

// const { getLang } = useQuoteTranslations()

const quoteHotelCategories = ref<QuoteHotelCategory[]>([]);
const isLoading = ref(true);
const getQuoteHotelCategories = async (lang: string): Promise<QuoteHotelCategory[]> => {
  try {
    const { data } = await quotesApi.get<QuoteHotelCategoryListResponse>(
      `/api/typeclass/quotes/selectbox?lang=${lang}&type=2`
    );

    if (data.success && data.data) {
      return Object.values(data.data).map((item) => ({
        value: item.id.toString(),
        // Agregamos un encadenamiento opcional (?.) por si translations viene vacío
        label: item.translations[0]?.value || '',
        selected: !!item.checked, // Casting más limpio a booleano
      }));
    }

    // Si success es false, devolvemos un array vacío
    return [];
  } catch (error) {
    console.error('Error fetching hotel categories:', error);
    // Siempre debemos devolver algo que cumpla con Promise<QuoteHotelCategory[]>
    return [];
  }
};

export const useQuoteHotelCategories = () => {
  const { getLang } = useQuoteTranslations();
  return {
    // Props
    quoteHotelCategories,
    isLoading,
    // Methods
    getQuoteHotelCategories: async () => {
      isLoading.value = true;
      quoteHotelCategories.value = await getQuoteHotelCategories(getLang());
      isLoading.value = false;
    },

    // Getters
  };
};
