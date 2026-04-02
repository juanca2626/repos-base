import { useQuotesStore } from '@/stores/quotes-store';
import { useQuoteStore } from '@/quotes/store/quote.store';
import { ref } from 'vue';
import useNotification from '@/quotes/composables/useNotification';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';

export const usePopup = () => {
  // TODO move to it own module Packages folder
  const store = useQuotesStore();
  const showForm = ref<boolean>(false);
  const showForm2 = ref<boolean>(false);
  const quoteStore = useQuoteStore();
  const { quote } = storeToRefs(quoteStore);
  const { showErrorNotification } = useNotification();
  const { t } = useI18n();

  store.$onAction(({ after }) => {
    after(() => {
      showForm.value = false;
      showForm2.value = false;
    });
  });

  return {
    // Properties
    showForm,
    showForm2,
    // Methods

    toggleForm: () => {
      if (quote?.value?.passengers) {
        const hasInvalidAge = quote.value?.age_child?.some((child) => parseInt(child.age) === 0);
        if (hasInvalidAge) {
          showErrorNotification(t('quote.label.error_child_age_zero'));
          store.showPassengersForm = true;
          window.scrollTo(0, 0);
        }
      }

      if (showForm.value) {
        store.closeModals();
        showForm.value = false;
      } else {
        store.openModals();
        showForm.value = true;
      }
    },
    toggleForm2: () => {
      if (showForm2.value) {
        store.closeModals();
        showForm2.value = false;
      } else {
        store.openModals();
        showForm2.value = true;
      }
    },

    // Getters
  };
};
