<script lang="ts" setup>
  import { computed, h, reactive, watch, watchEffect, onMounted } from 'vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import dayjs from 'dayjs';
  import { useI18n } from 'vue-i18n';
  import { MinusOutlined, PlusOutlined } from '@ant-design/icons-vue';

  import 'dayjs/locale/es-mx';
  import 'dayjs/locale/pt-br';
  import 'dayjs/locale/en-au';
  import { useLanguagesStore } from '@/stores/global';
  // import { useLanguagesStore } from "@/stores/global";
  import { getPriceWithCommission, hasCommission } from '@/utils/price';
  import { getUserType } from '@/utils/auth';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import { storeToRefs } from 'pinia';
  const quoteStore = useQuoteStore();
  const { marketList } = storeToRefs(quoteStore);
  const user_type_id = parseInt(getUserType(), 10);

  // ✅ usar directamente el cliente guardado en el store
  const client = computed(() => marketList?.value.data || null);

  // ✅ Función para mostrar el precio con comisión
  const displayPrice = (price: number) => getPriceWithCommission(price, client.value, user_type_id);
  const showCommissionBadge = computed(() => hasCommission(client.value, user_type_id));
  const { t } = useI18n();
  const languageStore = useLanguagesStore();

  const { page, quote, accommodation, save_reservation_reminders, delete_reservation_reminders } =
    useQuote();

  onMounted(() => {
    // DATALAYER para detalle de quotes..
    /*
    window.dataLayer.push({
      "content-name": "/quotes",
      "content-view-name": "quotes-confirmation",
      "event": "content-view"
    });
    */

    console.log('QUOTE: ', quote);

    if (quote.value.reservation.file_code) {
      const services = quote.value.categories[0].services;
      let total_amount = 0;

      const items = services
        .filter((service) => service.type === 'service' || service.type === 'group_header')
        .map((service) => {
          let item = {};

          if (service.type === 'service') {
            item = {
              item_id: service.service.service.id,
              item_sku: service.service.service.aurora_code,
              item_name: service.service.service.name.toUpperCase(),
              price: service.service.import.total_amount,
              item_brand: '',
              item_category: 'service',
              item_category2: 'quote_product',
              item_list_id: '',
              item_list_name: '',
            };

            total_amount += parseFloat(service.service.import.total_amount);
          }

          if (service.type === 'group_header') {
            item = {
              item_id: service.service.hotel.id,
              item_sku: service.service.hotel.channel[0].code,
              item_name: service.service.hotel.name.toUpperCase(),
              price: service.service.import,
              item_brand: '',
              item_category: 'hotel',
              item_category2: 'quote_product',
              item_list_id: '',
              item_list_name: '',
            };

            total_amount += parseFloat(service.service.import);
          }

          return item;
        });

      dataLayer.push({
        event: 'purchase',
        funnel_type: 'quotes-funnel',
        transaction_id: quote.value.reservation.file_code,
        currency: 'USD',
        value: total_amount,
        package_id: quote.value.id,
        package_code: quote.value.id,
        package_name: quote.value.reservation.customer_name,
        items: items,
      });
    }
  });

  const single = computed(() => accommodation.value.single);
  const double = computed(() => accommodation.value.double);
  const triple = computed(() => accommodation.value.triple);

  const singleF = computed(() => accommodation.value.single.toString().padStart(2, '0'));
  const doubleF = computed(() => accommodation.value.double.toString().padStart(2, '0'));
  const tripleF = computed(() => accommodation.value.triple.toString().padStart(2, '0'));

  const accommodation_selected = computed(() => {
    let data: Array<string> = [];

    if (single.value > 0) {
      data.push(singleF.value + ' SGL');
    }
    if (double.value > 0) {
      data.push(doubleF.value + ' DBL');
    }
    if (triple.value > 0) {
      data.push(tripleF.value + ' TPL');
    }

    return data;
  });

  const quote_nigth = computed(() => {
    // return 3;
    const date_in = dayjs(quote.value.statement.quote.date_in);
    const date_out = dayjs(quote.value.statement.quote.date_out_format);
    const diff = date_out.diff(date_in, 'day');
    return diff;
  });

  const dateIn = computed(() => {
    // return 3;
    return dayjs(quote.value.statement.quote.date_in).format('MMM D, YYYY');
  });

  const dateOut = computed(() => {
    // return 3;
    return dayjs(quote.value.statement.quote.date_out_format).format('MMM D, YYYY');
  });

  const cancellationDate = computed(() => {
    // return 3;
    return quote.value.statement.policies.penalties &&
      quote.value.statement.policies.penalties.length > 0
      ? dayjs(quote.value.statement.policies.penalties[0].apply_date, 'DD/MM/YYYY').format(
          'MMM D, YYYY'
        )
      : '';
  });

  computed(() => {
    return '';
  });

  const state: State = reactive({
    checked1: false,
    checked2: true,
    checked3: false,
    diasprevios: 1,
    email: '',
    email_alt: '',
    btnSave: true,
    inputEmail: true,
  });

  const removeDays = async () => {
    if (state.diasprevios > 1) {
      state.diasprevios = state.diasprevios - 1;
      state.btnSave = false;
    }
  };

  const onCheckChange = async () => {
    state.btnSave = false;
  };

  const onCheckChange2 = async () => {
    state.inputEmail = state.checked3 ? false : true;
    state.email_alt = '';
    state.btnSave = false;
  };

  const addDays = async () => {
    state.diasprevios = state.diasprevios + 1;
    state.btnSave = false;
  };

  const saveReminder = async () => {
    if (state.checked1 == true) {
      await save_reservation_reminders(
        state.diasprevios,
        state.email,
        quote.value.statement.min_date_cancellation
      );
    } else {
      await delete_reservation_reminders();
    }

    state.btnSave = true;
  };

  const pageReport = async (newView: string) => {
    //await searchDestinations();
    if (newView === 'reports') {
      window.open('/quotes/reports', '_blank');
    } else {
      page.value = newView;
      window.scrollTo(0, 0);
    }
  };

  watchEffect(() => {
    if (languageStore.currentLanguage == 'es') {
      dayjs.locale('es-mx');
    }

    if (languageStore.currentLanguage == 'en') {
      dayjs.locale('en-br');
    }

    if (languageStore.currentLanguage == 'pt') {
      dayjs.locale('pt-br');
    }
  });

  watch(
    () => state.email_alt,
    (value) => {
      console.log(value);
      state.btnSave = true;
      if (state.checked3) {
        state.btnSave = false;
      }
    }
  );
</script>

<template>
  <div class="banner">
    <img src="../../images/quotes/banner-reservations.jpg" />
  </div>

  <div class="container-hotel-info data-reserva">
    <div class="left">
      <div class="title-page">
        <h1>3. {{ t('quote.label.reservation_created') }}</h1>
      </div>

      <div class="num-reserve">
        {{ t('quote.label.num_reserve') }}:
        <b>{{ quote.reservation.file_code }}</b>
      </div>
      <div class="name-reserve">
        {{ t('quote.label.name_reserve') }}:
        <b>{{ quote.reservation.customer_name }}</b>
      </div>

      <div class="title-page sub">
        <h2>{{ t('quote.label.data_reserve') }}:</h2>
      </div>

      <div class="lista-reserva">
        <div class="image">
          <img src="../../images/quotes/reserva1.jpg" class="w-100" />
        </div>
        <div class="datos">
          <h3>
            {{ quote.reservation.customer_name }}
            <span
              >{{ quote_nigth + 1 }} {{ t('quote.label.days') }} / {{ quote_nigth }}
              {{ t('quote.label.nights') }}
            </span>
          </h3>
          <ul>
            <li>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="30"
                height="29"
                viewBox="0 0 30 29"
                fill="none"
              >
                <path
                  d="M26.0476 21.7497H3.02879C2.69411 21.7497 2.42303 22.0201 2.42303 22.3539V23.5622C2.42303 23.896 2.69411 24.1664 3.02879 24.1664H26.0476C26.3823 24.1664 26.6534 23.896 26.6534 23.5622V22.3539C26.6534 22.0201 26.3823 21.7497 26.0476 21.7497ZM4.11954 12.5988L7.47923 15.6197C7.7549 15.8679 8.08521 16.0481 8.44352 16.1457L19.332 19.1042C20.3346 19.3765 21.3977 19.4335 22.3987 19.1556C23.522 18.8433 24.0433 18.3547 24.1876 17.8071C24.3326 17.2596 24.1221 16.572 23.3028 15.7318C22.5728 14.9834 21.6237 14.4937 20.6212 14.2214L16.9294 13.2185L13.1298 5.97416C13.0726 5.75477 12.9046 5.58334 12.6884 5.52443L10.2241 4.85494C9.82427 4.74619 9.4328 5.05582 9.43999 5.47572L11.2542 11.6764L7.38495 10.6251L6.3404 8.06193C6.26733 7.87728 6.11286 7.73832 5.92318 7.68697L4.41901 7.27802C4.02754 7.17154 3.64175 7.46683 3.63455 7.87766L3.64326 11.7209C3.65045 12.0574 3.87155 12.3757 4.11954 12.5988Z"
                  fill="#5C5AB4"
                />
              </svg>
              {{ t('quote.label.date_arrival') }}: <span>{{ dateIn }}</span>
            </li>
            <li>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="30"
                height="29"
                viewBox="0 0 30 29"
                fill="none"
              >
                <path
                  d="M26.0473 21.1458H3.02889C2.69421 21.1458 2.42314 21.4162 2.42314 21.75V22.9583C2.42314 23.2921 2.69421 23.5624 3.02889 23.5624H26.0473C26.382 23.5624 26.653 23.2921 26.653 22.9583V21.75C26.653 21.4162 26.382 21.1458 26.0473 21.1458ZM5.4727 17.1157C5.71046 17.374 6.04437 17.5205 6.39381 17.5201L11.336 17.5133C11.726 17.5128 12.1104 17.4207 12.4581 17.2445L23.4736 11.6693C24.486 11.1569 25.3935 10.4255 26.0109 9.46749C26.7041 8.39209 26.7795 7.61386 26.5058 7.06408C26.2328 6.51392 25.5691 6.10989 24.3005 6.02758C23.1704 5.95432 22.0463 6.25112 21.034 6.76314L17.3045 8.65074L9.02465 5.55218C8.9251 5.4853 8.80944 5.44614 8.68963 5.43877C8.56983 5.43141 8.45022 5.45609 8.34318 5.51027L5.85394 6.77031C5.44999 6.97459 5.35231 7.51229 5.65821 7.8476L11.5726 11.5518L7.66513 13.5297L4.92601 12.1526C4.83164 12.1051 4.72741 12.0805 4.62172 12.0806C4.51604 12.0807 4.41187 12.1056 4.31762 12.1533L2.79833 12.9225C2.40308 13.1226 2.29896 13.6445 2.58745 13.9828L5.4727 17.1157Z"
                  fill="#5C5AB4"
                />
              </svg>
              {{ t('quote.label.date_departure') }}: <span>{{ dateOut }}</span>
            </li>
            <li>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="29"
                height="29"
                viewBox="0 0 29 29"
                fill="none"
              >
                <path
                  d="M9.06254 14.5C10.7282 14.5 12.0834 13.1448 12.0834 11.4792C12.0834 9.81355 10.7282 8.45833 9.06254 8.45833C7.39692 8.45833 6.0417 9.81355 6.0417 11.4792C6.0417 13.1448 7.39692 14.5 9.06254 14.5ZM22.3542 9.66667H13.8959C13.5621 9.66667 13.2917 9.93703 13.2917 10.2708V15.7083H4.83336V7.85417C4.83336 7.52036 4.563 7.25 4.22919 7.25H3.02086C2.68705 7.25 2.41669 7.52036 2.41669 7.85417V21.1458C2.41669 21.4796 2.68705 21.75 3.02086 21.75H4.22919C4.563 21.75 4.83336 21.4796 4.83336 21.1458V19.3333H24.1668V21.1458C24.1668 21.4796 24.4371 21.75 24.7709 21.75H25.9793C26.3131 21.75 26.5834 21.4796 26.5834 21.1458V13.8958C26.5834 11.56 24.6901 9.66667 22.3542 9.66667Z"
                  fill="#5C5AB4"
                />
              </svg>
              {{ t('quote.label.total_stay') }}:
              <span>{{ quote_nigth }} {{ t('quote.label.nights') }}</span>
            </li>
            <li>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="29"
                height="29"
                viewBox="0 0 29 29"
                fill="none"
              >
                <path
                  d="M9.66671 14.5003C12.0041 14.5003 13.8959 12.6085 13.8959 10.2712C13.8959 7.93379 12.0041 6.04199 9.66671 6.04199C7.32933 6.04199 5.43753 7.93379 5.43753 10.2712C5.43753 12.6085 7.32933 14.5003 9.66671 14.5003ZM12.5667 15.7087H12.2533C11.4679 16.0863 10.5956 16.3128 9.66671 16.3128C8.7378 16.3128 7.86931 16.0863 7.08011 15.7087H6.7667C4.36513 15.7087 2.41669 17.6571 2.41669 20.0587V21.1462C2.41669 22.1468 3.22854 22.9587 4.22919 22.9587H15.1042C16.1049 22.9587 16.9167 22.1468 16.9167 21.1462V20.0587C16.9167 17.6571 14.9683 15.7087 12.5667 15.7087ZM20.5417 14.5003C22.5431 14.5003 24.1668 12.8766 24.1668 10.8753C24.1668 8.87402 22.5431 7.25033 20.5417 7.25033C18.5404 7.25033 16.9167 8.87402 16.9167 10.8753C16.9167 12.8766 18.5404 14.5003 20.5417 14.5003ZM22.3542 15.7087H22.2108C21.6859 15.8899 21.1308 16.0107 20.5417 16.0107C19.9527 16.0107 19.3976 15.8899 18.8727 15.7087H18.7292C17.9589 15.7087 17.249 15.9314 16.626 16.2902C17.5473 17.2833 18.1251 18.6011 18.1251 20.0587V21.5087C18.1251 21.5917 18.1062 21.671 18.1024 21.7503H24.7709C25.7716 21.7503 26.5834 20.9385 26.5834 19.9378C26.5834 17.6005 24.6916 15.7087 22.3542 15.7087Z"
                  fill="#5C5AB4"
                />
              </svg>
              {{ t('quote.label.number_passagers_reserve') }}:
              <span
                >{{ quote.statement.quote.people[0].adults }}
                {{ t('quote.label.adults') }}
                {{ quote.statement.quote.people[0].child }}
                {{ t('quote.label.child') }}(s)</span
              >
            </li>
            <li>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="29"
                height="29"
                viewBox="0 0 29 29"
                fill="none"
              >
                <path
                  d="M24.5066 25.0732H23.5626V3.5498C23.5626 2.92416 23.0554 2.41699 22.4298 2.41699H6.57036C5.94472 2.41699 5.43755 2.92416 5.43755 3.5498V25.0732H4.49353C4.18073 25.0732 3.92712 25.3269 3.92712 25.6396V26.5837H25.073V25.6396C25.073 25.3269 24.8194 25.0732 24.5066 25.0732ZM9.96881 6.00423C9.96881 5.69143 10.2224 5.43783 10.5352 5.43783H12.4232C12.736 5.43783 12.9897 5.69143 12.9897 6.00423V7.89225C12.9897 8.20505 12.736 8.45866 12.4232 8.45866H10.5352C10.2224 8.45866 9.96881 8.20505 9.96881 7.89225V6.00423ZM9.96881 10.5355C9.96881 10.2227 10.2224 9.96908 10.5352 9.96908H12.4232C12.736 9.96908 12.9897 10.2227 12.9897 10.5355V12.4235C12.9897 12.7363 12.736 12.9899 12.4232 12.9899H10.5352C10.2224 12.9899 9.96881 12.7363 9.96881 12.4235V10.5355ZM12.4232 17.5212H10.5352C10.2224 17.5212 9.96881 17.2676 9.96881 16.9548V15.0667C9.96881 14.7539 10.2224 14.5003 10.5352 14.5003H12.4232C12.736 14.5003 12.9897 14.7539 12.9897 15.0667V16.9548C12.9897 17.2676 12.736 17.5212 12.4232 17.5212ZM16.0105 25.0732H12.9897V21.1084C12.9897 20.7956 13.2433 20.542 13.5561 20.542H15.4441C15.7569 20.542 16.0105 20.7956 16.0105 21.1084V25.0732ZM19.0313 16.9548C19.0313 17.2676 18.7777 17.5212 18.4649 17.5212H16.5769C16.2641 17.5212 16.0105 17.2676 16.0105 16.9548V15.0667C16.0105 14.7539 16.2641 14.5003 16.5769 14.5003H18.4649C18.7777 14.5003 19.0313 14.7539 19.0313 15.0667V16.9548ZM19.0313 12.4235C19.0313 12.7363 18.7777 12.9899 18.4649 12.9899H16.5769C16.2641 12.9899 16.0105 12.7363 16.0105 12.4235V10.5355C16.0105 10.2227 16.2641 9.96908 16.5769 9.96908H18.4649C18.7777 9.96908 19.0313 10.2227 19.0313 10.5355V12.4235ZM19.0313 7.89225C19.0313 8.20505 18.7777 8.45866 18.4649 8.45866H16.5769C16.2641 8.45866 16.0105 8.20505 16.0105 7.89225V6.00423C16.0105 5.69143 16.2641 5.43783 16.5769 5.43783H18.4649C18.7777 5.43783 19.0313 5.69143 19.0313 6.00423V7.89225Z"
                  fill="#5C5AB4"
                />
              </svg>
              {{ t('quote.label.arrangement') }}:
              <span>{{ accommodation_selected.join(' / ') }}</span>
            </li>
            <li>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="29"
                height="29"
                viewBox="0 0 29 29"
                fill="none"
              >
                <path
                  d="M26.2299 18.6126L22.8199 15.2064C22.3667 14.7533 21.7512 14.5002 21.1092 14.5002H18.1147C17.4463 14.5002 16.9063 15.0402 16.9063 15.7086V18.6994C16.9063 19.3414 17.1593 19.9532 17.6124 20.4063L21.0224 23.8125C21.4944 24.2845 22.261 24.2845 22.733 23.8125L26.2261 20.3195C26.7019 19.8474 26.7019 19.0846 26.2299 18.6126ZM19.3268 17.8196C18.8246 17.8196 18.4205 17.4155 18.4205 16.9133C18.4205 16.411 18.8246 16.007 19.3268 16.007C19.8291 16.007 20.2332 16.411 20.2332 16.9133C20.2332 17.4117 19.8291 17.8196 19.3268 17.8196ZM10.8755 14.4965C13.5454 14.4965 15.7092 12.3327 15.7092 9.66285C15.7092 6.99681 13.5454 4.83301 10.8755 4.83301C8.20572 4.83301 6.04191 6.99681 6.04191 9.66662C6.04191 12.3327 8.20572 14.4965 10.8755 14.4965ZM15.7016 18.6957V15.9352C15.2409 15.7993 14.7613 15.7011 14.2591 15.7011H13.6284C12.7901 16.0863 11.8574 16.3053 10.8755 16.3053C9.89371 16.3053 8.96475 16.0863 8.12264 15.7011H7.492C4.69 15.7049 2.41669 17.9782 2.41669 20.7802V22.3511C2.41669 23.3518 3.22859 24.1637 4.2293 24.1637H17.5218C18.1071 24.1637 18.6207 23.8805 18.953 23.45L16.7628 21.2598C16.0792 20.5762 15.7016 19.6662 15.7016 18.6957Z"
                  fill="#5C5AB4"
                />
              </svg>
              {{ t('quote.label.type_services') }}: <span>{{ t('quote.label.private') }}</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- <div class="lista-reserva">
	  		<div class="image">
	  			<img src="../../images/quotes/reserva2.jpg" class="w-100">
	  		</div>
	  		<div class="datos">
	  			<h3>Huella de carbono</h3>
	  			<ul>
	  				<li>
	  					<svg xmlns="http://www.w3.org/2000/svg" width="30" height="29" viewBox="0 0 30 29" fill="none">
							<path d="M7.34482 12.9899H3.55883C2.93154 12.9899 2.42303 13.4971 2.42303 14.1227V25.4508C2.42303 26.0765 2.93154 26.5837 3.55883 26.5837H7.34482C7.97211 26.5837 8.48062 26.0765 8.48062 25.4508V14.1227C8.48062 13.4971 7.97211 12.9899 7.34482 12.9899ZM5.45183 24.6956C4.82454 24.6956 4.31603 24.1885 4.31603 23.5628C4.31603 22.9372 4.82454 22.43 5.45183 22.43C6.07912 22.43 6.58763 22.9372 6.58763 23.5628C6.58763 24.1885 6.07912 24.6956 5.45183 24.6956ZM20.5958 6.26157C20.5958 8.26363 19.3668 9.38662 19.021 10.7243H23.835C25.4155 10.7243 26.6459 12.0339 26.6533 13.4665C26.6573 14.3132 26.2962 15.2247 25.7334 15.7887L25.7282 15.7939C26.1937 16.8954 26.118 18.4388 25.2877 19.5448C25.6985 20.7671 25.2844 22.2685 24.5124 23.0734C24.7158 23.904 24.6186 24.611 24.2214 25.1801C23.2555 26.5642 20.8616 26.5837 18.8372 26.5837L18.7025 26.5836C16.4173 26.5828 14.5471 25.753 13.0443 25.0862C12.2892 24.7511 11.3018 24.3363 10.5526 24.3226C10.2431 24.3169 9.99502 24.065 9.99502 23.7563V13.6662C9.99502 13.5152 10.0557 13.3702 10.1634 13.264C12.0381 11.4164 12.8443 9.46025 14.3809 7.9251C15.0814 7.22503 15.3362 6.16755 15.5826 5.1449C15.793 4.27164 16.2332 2.41699 17.1884 2.41699C18.3242 2.41699 20.5958 2.7946 20.5958 6.26157Z" fill="#5C5AB4"/>
						</svg> Contribución realizada
					</li>
	  			</ul>
	  			<div class="outline button-component">Ver certificado</div>
	  		</div>
	  	</div> -->

      <div class="cancelaciones">
        <div class="first">
          <div>
            {{ t('quote.label.cancellation_penalty') }}:
            <span>{{ cancellationDate }}</span>
          </div>

          <!--<svg width="27" height="26" viewBox="0 0 27 26" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M20.2529 16.25L13.7529 9.75L7.25293 16.25" stroke="#737373" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>-->
        </div>
        <!--<div class="toggle">
		      <div class="container">
		        <a-switch v-model:checked="state.checked1" />
		        <span>{{ t('quote.label.remendir')  }}</span> Recordatorio
		      </div>
		    </div> -->
        <!-- <hr /> -->
        <div class="email-envio">
          <a-checkbox v-model:checked="state.checked1" @change="onCheckChange"
            >{{ t('quote.label.send_email_remendir') }}
          </a-checkbox>
          <!-- Enviar recordatorio via email -->

          <div>
            <a-button
              type="primary"
              shape="circle"
              :icon="h(MinusOutlined)"
              @click="removeDays()"
            />
            <span>{{ state.diasprevios }}</span>
            <a-button type="primary" shape="circle" :icon="h(PlusOutlined)" @click="addDays()" />
            {{ t('quote.label.days_before') }}
          </div>
        </div>

        <div class="email-envio-confir" v-if="state.checked1">
          <a-checkbox v-model:checked="state.checked2" disabled>
            {{ quote.statement.client.email }}
          </a-checkbox>
          <a-checkbox v-model:checked="state.checked3" style="width: 100%" @change="onCheckChange2">
            <a-input
              class="ant-input"
              v-model:value="state.email_alt"
              :placeholder="t('quote.label.email')"
              :disabled="state.inputEmail"
            />
          </a-checkbox>
        </div>

        <div class="btns guardar_reserva">
          <a-button @click="saveReminder()" :disabled="state.btnSave">
            {{ t('quote.label.save') }}
          </a-button>
        </div>
      </div>
      <div class="btns" @click="pageReport('reports')">
        <div class="guardar">{{ t('quote.label.view_reserve') }}</div>
      </div>
    </div>

    <div class="left">
      <div class="table-price">
        <!-- <div>Perú especial <span>$ 1400</span></div>

  			<div>02 Adulto(s) x persona <span>$ 700</span></div>

  			<div>00 Niño(s) con cama x persona <span>$ 0</span></div>

  			<div>00 Niño(s) sin cama x persona <span>$ 0</span></div>

  			<div>Comunidad Huilloc <span>$ 10</span></div>

  			<div>Total <span>$ 1410</span></div> -->
        <div>
          {{ t('quote.label.total_pay') }}:
          <span
            v-if="showCommissionBadge"
            class="badge-warning ml-2"
            style="font-size: 10px; padding: 1px 2px"
            >{{ t('global.label.with_commission') }}</span
          >
          <span>USD $ {{ displayPrice(quote.reservation.total) }}</span>
        </div>
      </div>

      <p>{{ t('quote.label.sending_reservation') }}</p>

      <!-- <p class="red">Si deseas enviarlo a otro correo ingrésalo aquí:</p>

  		<div class="form"> 
			<a-input class="ant-input" v-model:value="state.email" :placeholder="t('quote.label.email')" />
  			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
				<path d="M16.5 2L8.25 10.25" stroke="#979797" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M16.5 2L11.25 17L8.25 10.25L1.5 7.25L16.5 2Z" stroke="#979797" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
  		</div>  -->

      <div class="alert">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="25"
          height="24"
          viewBox="0 0 25 24"
          fill="none"
        >
          <path
            d="M11.0428 3.8602L2.57283 18.0002C2.3982 18.3026 2.3058 18.6455 2.30482 18.9947C2.30384 19.3439 2.39432 19.6873 2.56725 19.9907C2.74019 20.2941 2.98955 20.547 3.29054 20.7241C3.59152 20.9012 3.93363 20.9964 4.28283 21.0002H21.2228C21.572 20.9964 21.9141 20.9012 22.2151 20.7241C22.5161 20.547 22.7655 20.2941 22.9384 19.9907C23.1113 19.6873 23.2018 19.3439 23.2008 18.9947C23.1999 18.6455 23.1075 18.3026 22.9328 18.0002L14.4628 3.8602C14.2846 3.56631 14.0336 3.32332 13.734 3.15469C13.4345 2.98605 13.0966 2.89746 12.7528 2.89746C12.4091 2.89746 12.0712 2.98605 11.7716 3.15469C11.4721 3.32332 11.2211 3.56631 11.0428 3.8602V3.8602Z"
            stroke="#FFCC00"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M12.7528 17H12.7628"
            stroke="#FFCC00"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M12.7528 9V13"
            stroke="#FFCC00"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        {{ t('quote.label.sending_reservation_no') }}: IT-5262E7
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 20 20"
          fill="none"
        >
          <path
            d="M15 5L5 15"
            stroke="#C4C4C4"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M5 5L15 15"
            stroke="#C4C4C4"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </div>
    </div>
  </div>

  <div class="container-hotel-info">
    <div class="content-extra">
      <div class="title-content">{{ t('quote.label.active_masi') }}</div>
      <div class="content-extra-item">
        <div class="item">
          <img src="../../images/quotes/masi.png" />
        </div>

        <div class="item">
          <h3>{{ t('quote.label.with_masi') }}</h3>

          <p>1. {{ t('quote.label.with_masi_1') }}</p>
          <p>2. {{ t('quote.label.with_masi_2') }}</p>
          <p>3. {{ t('quote.label.with_masi_3') }}</p>
          <p>4. {{ t('quote.label.with_masi_4') }}</p>
          <p>5. {{ t('quote.label.with_masi_5') }}</p>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-hotel-info">
    <div class="container-hotel-info">
      <div class="content-extra">
        <div class="title-content">
          {{ t('quote.label.travel_recommendations') }}
        </div>
        <div class="content-extra-item">
          <div class="item">
            <h3>{{ t('quote.label.it_recommended') }}</h3>
            <p>{{ t('quote.label.it_recommended_descri') }}</p>
          </div>

          <div class="item">
            <h3>{{ t('quote.label.it_recommended_no') }}</h3>

            <p>{{ t('quote.label.it_recommended_descri_no') }}</p>
          </div>
        </div>
      </div>

      <div class="content-extra">
        <h4>{{ t('quote.label.recommendations_transfer') }}</h4>
        <div class="content-extra-item">
          <div class="item">
            <h3>{{ t('quote.label.train_machu') }}</h3>
            <p>{{ t('quote.label.train_machu_des') }}</p>

            <div class="table">
              <div class="head">{{ t('quote.label.count') }}</div>
              <div class="head">{{ t('quote.label.weight') }}</div>
              <div class="head">{{ t('quote.label.size') }}</div>
              <div>1 {{ t('quote.label.bag_backpack') }}</div>
              <div>5kg / 11lb</div>
              <div>62’’ / 157 cm</div>
            </div>
          </div>

          <div class="item">
            <h3>{{ t('quote.label.bus_country') }}</h3>

            <p>{{ t('quote.label.bus_country_des') }}</p>

            <div class="table">
              <div class="head">{{ t('quote.label.count') }}</div>
              <div class="head">{{ t('quote.label.weight') }}</div>
              <div class="head">{{ t('quote.label.size') }}</div>
              <div>{{ t('quote.label.baggage') }}</div>
              <div>20 kg</div>
              <div>-</div>
              <div>{{ t('quote.label.hand_bag') }}</div>
              <div>5 kg</div>
              <div>-</div>
            </div>
          </div>
        </div>
      </div>

      <div class="content-extra txt-center">
        <div class="content-extra-item">
          <div class="item">
            <div class="title-content">{{ t('quote.label.entry_peru') }}</div>
            <p>{{ t('quote.label.entry_peru_des') }}</p>
          </div>

          <div class="item">
            <img src="../../images/quotes/ingreso-peru.png" class="w-100" />
          </div>
        </div>
      </div>

      <div class="content-extra txt-center">
        <div class="content-extra-item">
          <div class="item">
            <img src="../../images/quotes/guia-esencial.jpg" class="w-100" />
          </div>

          <div class="item">
            <div class="title-content">{{ t('quote.label.guide_peru') }}</div>
            <p>{{ t('quote.label.guide_peru_des') }}</p>
            <p class="red">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
              >
                <path
                  d="M12.0001 18.2597C12.4898 18.2597 12.8867 17.8627 12.8867 17.373C12.8867 16.8833 12.4898 16.4863 12.0001 16.4863C11.5104 16.4863 11.1134 16.8833 11.1134 17.373C11.1134 17.8627 11.5104 18.2597 12.0001 18.2597Z"
                  fill="#FF3B3B"
                />
                <path
                  d="M12 15.0736C11.8232 15.0736 11.6537 15.0033 11.5286 14.8783C11.4036 14.7533 11.3334 14.5837 11.3334 14.4069V6.4069C11.3334 6.23009 11.4036 6.06052 11.5286 5.9355C11.6537 5.81047 11.8232 5.74023 12 5.74023C12.1769 5.74023 12.3464 5.81047 12.4714 5.9355C12.5965 6.06052 12.6667 6.23009 12.6667 6.4069V14.4069C12.6667 14.5837 12.5965 14.7533 12.4714 14.8783C12.3464 15.0033 12.1769 15.0736 12 15.0736Z"
                  fill="#FF3B3B"
                />
                <path
                  d="M12 22.6663C9.89038 22.6663 7.82809 22.0408 6.07396 20.8687C4.31984 19.6966 2.95267 18.0307 2.14533 16.0816C1.338 14.1326 1.12676 11.9878 1.53834 9.91872C1.94991 7.84959 2.96581 5.94897 4.45757 4.45721C5.94933 2.96545 7.84995 1.94955 9.91908 1.53797C11.9882 1.12639 14.1329 1.33763 16.082 2.14496C18.0311 2.9523 19.697 4.31947 20.8691 6.0736C22.0411 7.82772 22.6667 9.89001 22.6667 11.9997C22.6667 14.8287 21.5429 17.5418 19.5425 19.5422C17.5421 21.5425 14.829 22.6663 12 22.6663ZM12 2.66635C10.1541 2.66635 8.34958 3.21374 6.81472 4.2393C5.27986 5.26486 4.08359 6.72252 3.37717 8.42797C2.67075 10.1334 2.48592 12.01 2.84605 13.8205C3.20618 15.631 4.09509 17.2941 5.40038 18.5993C6.70567 19.9046 8.36871 20.7935 10.1792 21.1537C11.9897 21.5138 13.8663 21.329 15.5718 20.6226C17.2772 19.9161 18.7349 18.7199 19.7604 17.185C20.786 15.6501 21.3334 13.8456 21.3334 11.9997C21.3334 9.52433 20.35 7.15035 18.5997 5.40002C16.8494 3.64968 14.4754 2.66635 12 2.66635Z"
                  fill="#FF3B3B"
                />
              </svg>
              {{ t('quote.label.review_protocols') }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="footerHotel">
    <div class="container-hotel-info">
      <div>{{ t('quote.label.tell_something') }}</div>
      <div class="btn">{{ t('quote.label.write_us') }}</div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .data-reserva {
    display: flex;
    gap: 32px;

    .left {
      width: 70%;

      &:nth-child(2) {
        padding-top: 130px;
        width: 30%;

        .red {
          font-weight: 400;
        }
      }
    }
  }

  .alert {
    display: flex;
    gap: 10px;
    margin-top: 28px;
    padding: 16px;
    background: #fffbdb;
    border-radius: 4px;
    border: 0.5px solid #fc0;
    font-size: 14px;
    align-items: flex-start;
    color: #e4b804;

    svg {
      width: 60px;
    }
  }

  .form {
    position: relative;

    input {
      height: 45px;
    }

    svg {
      cursor: pointer;
      position: absolute;
      right: 10px;
      top: 14px;
    }
  }

  .table-price {
    display: flex;
    padding: 32px 15px 60px;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;

    div {
      display: flex;
      justify-content: space-between;
      width: 100%;
      font-size: 13px;
      font-style: normal;
      font-weight: 400;
      color: #3d3d3d;
      line-height: 22px;

      &:nth-child(1),
      &:nth-last-child(3),
      &:nth-last-child(2) {
        font-size: 16px;

        span {
          font-weight: 800;
        }
      }

      &:nth-last-child(1) {
        font-size: 20px;
        font-weight: 800;
      }

      &:nth-last-child(2) {
        padding: 16px 0 0 0;
        border-top: 1px solid #c4c4c4;
      }
    }
  }

  .red {
    color: #eb5757;
    font-size: 14px;
    font-weight: 700;

    svg {
      display: inline-block;
      vertical-align: top;
      width: 24px;
    }
  }

  .table {
    display: flex;
    flex-wrap: wrap;
    border-radius: 5px;
    overflow: hidden;
    border: 1px solid #eb5757;

    div {
      text-align: center;
      border-right: 1px solid #eb5757;
      border-bottom: 1px solid #eb5757;
      width: 33.3333%;
      padding: 12px 0;
      align-items: center;
      display: flex;
      justify-content: center;

      &.head {
        background: #eb5757;
        border-color: #fff;
        color: #fff;

        &:nth-child(3n) {
          border-right: 0;
        }
      }

      &:last-child {
        border-right: 0;
        border-bottom: 0;
      }

      &:nth-child(3n) {
        border-right: 0;
      }

      &:nth-last-child(3),
      &:nth-last-child(2) {
        border-bottom: 0;
      }
    }
  }

  .content-extra {
    padding: 32px 0;

    &.txt-center {
      border-top: 1px solid #c4c4c4;
      padding: 90px 0;
      margin-top: 47px;

      .content-extra-item {
        align-items: center;
      }
    }

    .title-content {
      margin-bottom: 30px;
    }

    &-item {
      display: flex;
      gap: 24px;
    }

    h3 {
      font-size: 18px;
      font-style: normal;
      font-weight: 700;
      line-height: 28px;
      letter-spacing: -0.27px;
      margin-bottom: 24px;
    }

    h4 {
      font-size: 16px;
      font-style: normal;
      font-weight: 700;
      line-height: 24px;
      letter-spacing: -0.24px;
      margin-bottom: 40px;
    }
  }

  .bg-hotel-info {
    background: #f5f5f5;
    margin-top: 50px;
    padding-top: 40px;

    .content-extra {
      .title-content {
        margin-bottom: 45px;
      }

      .item {
        width: 50%;
      }

      p {
        text-align: justify;
      }
    }
  }

  .title-content {
    color: #000;
    font-family: Montserrat;
    font-size: 32px;
    font-style: normal;
    font-weight: 400;
    line-height: 48px;
    letter-spacing: -0.48px;
  }

  .cancelaciones {
    color: #5c5ab4;
    flex-direction: column;
    /* Text 16, 18, 24/Body P Medium */
    font-family: Montserrat;
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
    display: flex;
    gap: 16px;
    line-height: 19px;

    span {
      color: #3d3d3d;
    }

    .first {
      border-bottom: 1px solid #c4c4c4;
      padding: 0 0 10px 0;
      display: flex;
      gap: 20px;
      justify-content: space-between;

      div {
        display: flex;
        gap: 15px;
      }
    }

    .email-envio {
      display: flex;
      align-items: center;
      gap: 20px;

      .ant-btn-primary {
        border-color: #737373;
        background: none;
        width: 30px;
        height: 30px;
        padding: 0;
        min-width: 30px;
        color: #737373;

        :deep(svg) {
          fill: #737373;
        }
      }

      & > div {
        display: flex;
        gap: 14px;
        align-items: center;
        color: #737373;

        span {
          color: #737373;
        }
      }
    }

    .email-envio-confir {
      display: flex;
      align-items: center;
      gap: 20px;
    }
  }

  .toggle {
    display: flex;
    width: 256px;
    height: 21px;
    padding-left: 0;
    justify-content: center;
    align-items: center;

    .container {
      display: flex;
      height: 21px;
      justify-content: center;
      align-items: center;
      gap: 8px;

      .ant-switch {
        background-color: #fff;
        transition: background-color 700ms linear;
        border: 1px solid #eb5757;
        width: 36px;

        &-checked {
          background-color: #eb5757;
          transition: background-color 700ms linear;

          :deep(.ant-switch-handle:before) {
            background-color: #fff;
          }
        }
      }

      .ant-switch-handle {
        top: 1px;
      }

      .ant-switch-handle:before {
        background-color: #eb5757;
      }

      :deep(.ant-switch-handle) {
        bottom: 1px;
        height: auto;
        top: 1px;
        width: 16px;
      }

      :deep(.ant-switch-handle:before) {
        background-color: #eb5757;
      }

      span {
        color: #eb5757;
        font-size: 14px;
        font-style: normal;
        font-weight: 500;
        letter-spacing: 0.21px;
        width: 210px;
      }
    }
  }

  .lista-reserva {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    align-self: stretch;
    margin-bottom: 32px;

    .button-component {
      width: 180px;
    }

    h3 {
      color: #eb5757;
      /* Body Text CTA Btn */
      font-family: Montserrat;
      font-size: 16px;
      font-style: normal;
      font-weight: 700;
      line-height: 24px;
      letter-spacing: -0.27px;
      margin-bottom: 12px;
    }

    ul {
      margin: 0;
      padding: 0;
      list-style: none;
    }

    li {
      color: #5c5ab4;
      font-family: Montserrat;
      font-size: 14px;
      font-style: normal;
      margin-bottom: 11px;
      font-weight: 500;
      line-height: 22px;
      letter-spacing: -0.27px;
      display: flex;
      align-items: flex-start;
      gap: 7px;

      span {
        color: #3d3d3d;
      }
    }
  }

  .num-reserve {
    color: #3d3d3d;
    /* BODY 36/Body P Large Medium */
    font-family: Montserrat;
    font-size: 28px;
    font-style: normal;
    font-weight: 500;
    line-height: 36px;
    letter-spacing: -0.28px;
  }

  .name-reserve {
    color: #979797;
    /* Text 16, 18, 24/Body H4 Medium */
    font-family: Montserrat;
    font-size: 18px;
    font-style: normal;
    font-weight: 500;
    line-height: 26px;
    letter-spacing: -0.18px;
  }

  .container-hotel-info {
    width: 80vw;
    margin: 0 auto;
  }

  .btns {
    display: flex;
    align-items: flex-end;
    gap: 36px;
    align-self: stretch;
    justify-content: flex-start;
    margin-top: 60px;
    margin-bottom: 60px;

    .guardar {
      margin-top: 0;
      margin-bottom: 60px;
      display: flex;
      padding: 14px 32px;
      align-items: center;
      border-radius: 6px;
      color: #fff;
      cursor: pointer;
      background: #eb5757;
      width: 248px;
      justify-content: center;
    }

    &.guardar_reserva {
      margin-top: 0;
      justify-content: flex-end;

      button {
        border-color: #eb5757;
        color: #eb5757;
        min-width: 130px;

        &:hover {
          background: rgba(255, 225, 225, 0.4);
        }
      }
    }
  }

  .footerHotel {
    background:
      url(/images/limaFooter.jpg),
      lightgray 50% / cover no-repeat;
    background-size: cover;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
    color: #fff;
    /* Body P Large */
    font-family: Montserrat;
    font-size: 28px;
    font-style: normal;
    font-weight: 400;
    line-height: 40px;
    letter-spacing: -0.42px;
    height: 500px;

    .container-hotel-info {
      display: flex;
      align-items: center;
      gap: 34px 20px;
      flex-wrap: wrap;
    }

    div {
      align-items: center;
      padding: 80px 0 80px 0;
      justify-content: space-between;

      & > div:first-child {
      }
    }

    .btn {
      padding: 0;
      align-items: center;
      font-size: 18px;
      border-radius: 6px;
      background: #eb5757;
      text-align: center;
      cursor: pointer;
      width: 192px;
      height: 52px;
    }
  }

  .title-page {
    display: flex;
    justify-content: space-between;
    align-items: center;
    align-self: stretch;
    padding: 120px 0 32px;

    &.sub {
      padding: 32px 0 32px;
    }

    h1 {
      color: #eb5757;
      font-family: Montserrat;
      font-size: 36px !important;
      font-style: normal;
      font-weight: 700;
      line-height: 44px;
      letter-spacing: -0.36px;
      margin-bottom: 0;
    }

    .actions {
      display: flex;
      gap: 15px;
      align-items: center;

      div {
        display: flex;
        align-items: center;
      }
    }

    h2 {
      color: #3d3d3d;
      font-family: Montserrat;
      font-size: 20px !important;
      font-style: normal;
      font-weight: 700;
      line-height: 28px;
      letter-spacing: -0.2px;
    }
  }

  @media only screen and (max-width: 1800px) {
    .container-hotel-info {
      width: 85vw;
    }
  }

  @media only screen and (max-width: 1600px) {
    .container-hotel-info {
      width: 90vw;
    }
  }

  @media only screen and (max-width: 1450px) {
    .container-hotel-info {
      width: 95vw;
    }
  }
</style>
