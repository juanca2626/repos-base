import { storeToRefs } from 'pinia';
import { useLanguagesStore } from '@/stores/global';

const useQuoteTranslations = () => {
  const store = useLanguagesStore();
  const { currentLanguage } = storeToRefs(store);

  const getLang = () => {
    return currentLanguage.value;
  };
  return {
    // Properties
    currentLanguage,
    // Methods
    getLang,
    // Getters
  };
};

export default useQuoteTranslations;
