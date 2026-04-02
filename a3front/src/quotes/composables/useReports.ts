import { storeToRefs } from 'pinia';

import quotesApi from '@/quotes/api/quotesApi';
import quotesA3Api from '@/quotes/api/quotesA3Api';
import { useQuoteStore } from '@/quotes/store/quote.store';

import useLoader from '@/quotes/composables/useLoader';
import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';

import { getUserClientId, getUserId } from '@/utils/auth';

// Quote Service add
/*const list = async (quoteId: number, request: QuoteServiceAddRequest): Promise<void> => {

    await.get(`/api/quotes?lang=en&page=1&limit=3&filterBy=all&queryCustom=&filterUserType=E&destinations=&market=&client=&executive=`, request)


}*/

// const list = async (lang: string): Promise => {
//   const { data } = await quotesA3Api.get(
//     `/api/quotes?lang=${lang}&page=1&limit=3&filterBy=all&queryCustom=&filterUserType=E&destinations=&market=&client=&executive=`
//   );
//   return data;
// };

export const useReports = () => {
  const store = useQuoteStore();
  const { reportsList, reportsDestinity, marketList, sellersList } = storeToRefs(store);

  const { showIsLoading, closeIsLoading } = useLoader();
  const { getLang } = useQuoteTranslations();

  return {
    reportsList,
    reportsDestinity,
    marketList,
    sellersList,
    listReport: async (status, destiny = '', search = '', page, countPage) => {
      try {
        page = page > 0 ? page : '1';
        countPage = countPage > 0 ? countPage : '10';

        showIsLoading();

        const data = await quotesA3Api.get(
          '/api/quotes?lang=' +
            getLang() +
            '&page=' +
            page +
            '&limit=' +
            countPage +
            '&filterBy=' +
            status +
            '&queryCustom=' +
            search +
            '&filterUserType=E&destinations=' +
            destiny +
            '&market=&client=&executive=' +
            getUserId()
        );

        reportsList.value = data.data;

        closeIsLoading();

        return data.data;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },

    itemReport: async (quote) => {
      try {
        showIsLoading();

        const result = await quotesA3Api.get(
          'api/quote/' + quote.id + '/categories/services?lang=' + getLang()
        );

        closeIsLoading();

        return result;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },

    duplicateQuoteReport: async (quote) => {
      try {
        showIsLoading();
        const result = await quotesA3Api.post('api/quote/' + quote.id + '/copy/quote');

        closeIsLoading();

        return result;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },

    historyQuoteReport: async (quote) => {
      try {
        showIsLoading();

        const result = await quotesA3Api.get(
          'api/quotes/' +
            quote.id +
            '/history_logs?query=&page=1&limit=100&filter_by=&lang=' +
            getLang()
        );

        closeIsLoading();

        return result;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },

    searchDestinations: async () => {
      try {
        showIsLoading();

        const result = await quotesA3Api.get('api/quote/ubigeo/selectbox/destinations');

        reportsDestinity.value = result.data;

        closeIsLoading();

        return result.data;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },

    checkEditing: async (quote) => {
      try {
        showIsLoading();

        const result = await quotesA3Api.get('api/quote/check_editing/' + quote.id);

        // closeIsLoading();

        return result.data;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },

    existByUserStatus: async (quote) => {
      try {
        showIsLoading();
        console.log(quote);
        const result = await quotesA3Api.get('api/quote/existByUserStatus/2');

        closeIsLoading();

        return result.data;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },

    replaceQuote: async (id) => {
      showIsLoading();

      const result = await quotesA3Api.post('api/quote/' + id + '/replaceQuoteInFront', {
        client_id: getUserClientId(),
      });

      return result.data;
      closeIsLoading();
    },

    putQuote: async (quote) => {
      showIsLoading();

      const result = await quotesA3Api.post('api/quote/' + quote.id + '/copy/quote', {
        status: 2,
        client_id: getUserClientId(),
      });
      console.log(result);
      window.location.href = '/quotes';
    },

    markets: async (quote) => {
      showIsLoading();
      console.log('test client api' + quote);
      /*const result = await quotesA3Api.post('api/get/clients/market', {
                        market_id: 9
                    });*/

      const result = await quotesApi.get('api/clients/' + getUserClientId());

      marketList.value = result.data;

      closeIsLoading();
      return result.data;
    },

    sellers: async (client_id) => {
      showIsLoading();
      console.log(client_id);
      const result = await quotesApi.get(
        'api/sellers?lang=' + getLang() + '&client_id=' + getUserClientId() + '&status=1'
      );

      sellersList.value = result.data;

      closeIsLoading();
      return result.data;
    },

    sharedSend: async (id, data) => {
      try {
        showIsLoading();

        const result = await quotesA3Api.post('api/quote/' + id + '/share/quote', data);

        closeIsLoading();

        return result.data;
      } catch (e) {
        console.log(e);
        closeIsLoading();
        return false;
      }
    },

    /*translations : async () => {
            
            try {

                             
                let data = quotesA3Api.get('translation/' + getLang() + '/slug/quote').then((data) => {
                    return data.data;

                    all_status = [
                        { code: 'all', label: translations.label.show_all },
                        { code: 'activated', label: translations.label.only + ' ' + translations.label.active },
                        { code: 'expired', label: translations.label.only + ' ' + translations.label.expired },
                        { code: 'comingExpired', label: translations.label.next_to_expire },
                        { code: 'received', label: translations.label.received },
                        { code: 'sent', label: translations.label.sent }
                    ]
                });
                return data;
                

            } catch (e) {
                closeIsLoading()
            }
        }, */
  };
};
