<script lang="ts" setup>
  import { storeToRefs } from 'pinia';
  import { computed, onMounted, reactive, ref, watch } from 'vue';
  import moment from 'moment';
  import IconAlert from '@/quotes/components/icons/IconAlert.vue';
  import IconAlertRe from '@/quotes/components/icons/IconAlert.vue';
  import IconHistory from '@/quotes/components/icons/IconHistory.vue';
  import IconShare from '@/quotes/components/icons/IconShare.vue';
  import { useReports } from '@/quotes/composables/useReports';
  import IconConfirmed from '@/quotes/components/icons/IconConfirmed.vue';
  import CheckBoxComponent from '@/quotes/components/global/CheckBoxComponent.vue';
  import IconOnRequest from '@/quotes/components/icons/IconOnRequest.vue';
  import DowloadButtonReport from '@/quotes/components/global/DowloadButtonReport.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import useLoader from '@/quotes/composables/useLoader';
  import { useQuote } from '@/quotes/composables/useQuote';
  import ButtonOutlineContainer from '@/quotes/components/global/ButtonOutlineContainer.vue';
  import DownloadItinerary from '@/quotes/components/DownloadItinerary.vue';
  import DownloadSkeleton from '@/quotes/components/DownloadSkeleton.vue';
  import DowloadButton from '@/quotes/components/global/DowloadButton.vue';
  import useQuoteLanguages from '@/quotes/composables/useQuoteLanguages';
  import { useLanguagesStore } from '@/stores/global';
  // import { useLanguagesStore } from "@/stores/global";
  import IconClear from '@/quotes/components/icons/IconClear.vue';
  import IconEditDark from '@/quotes/components/icons/IconEditDark.vue';
  import IconSearch from '@/quotes/components/icons/IconSearch.vue';
  import IconTrashDark from '@/quotes/components/icons/IconTrashDark.vue';
  import { useI18n } from 'vue-i18n';
  import QuoteDetailsPackages from '@/quotes/pages/QuoteDetailsPackages.vue';

  const { t } = useI18n({
    useScope: 'global',
  });

  // const { showErrorNotification } = useNotification();

  const languageStore = useLanguagesStore();
  const { showIsLoading, closeIsLoading } = useLoader();
  const {
    deleteQuote,
    deleteQuoteReport,
    exportar,
    downloadQuoteItinerary,
    downloadQuoteSkeleton,
    downloadItinerary,
    downloadSkeletonUse,
    getQuote,
    saveQuote,
    currentReportQuote,
  } = useQuote();
  const {
    listReport,
    reportsList,
    reportsDestinity,
    itemReport,
    duplicateQuoteReport,
    historyQuoteReport,
    searchDestinations,
    checkEditing,
    putQuote,
    existByUserStatus,
    replaceQuote,
    markets,
    marketList,
    sellers,
    sellersList,
    sharedSend,
  } = useReports();

  const reportSelectedServices = ref();
  const history_logs = ref();
  const shared = ref();
  const reportSelectedDownload = ref();
  const rowSelect = ref();
  const checkedItem = ref();
  // Form
  const searchFormRef = ref();
  const sharedQuoteRef = ref();
  //const listSellers = ref();
  const sharedQuoteId = ref();
  const quote_pages = ref();
  const pageActive = ref();
  const inputValue = ref<string>();

  interface SearchForm {
    status: string;
    destiny: string;
    search: string;
  }

  interface SearchFormQuote {
    nameClient: string;
    userAssocie: string;
    modo: string;
  }

  const searchFormState: UnwrapRef<SearchForm> = reactive({
    status: undefined,
    destiny: undefined,
    search: undefined,
  });

  const sharedQuoteState: UnwrapRef<SearchFormQuote> = reactive({
    nameClient: undefined,
    userAssocie: undefined,
    modo: 'view_permission',
  });

  const { getLanguages } = useQuoteLanguages();

  const state = reactive({
    openedRow: false,
    openedList: false,
    showModalItinerarioDetalle: false,
    showModalSkeletonDetalle: false,
    showModalEliminar: false,
    openDownload: false,
    rowSelectHistory: false,
    rowSelectInfo: false,
    buttonsAction: false,
    rowSelectShare: false,
    showModalEdit: false,
    showModalEditId: false,
    quoteRow: '',
    countpage: 10,
    filter: 'all',
    colorActive: '#3D3D3D',
  });

  onMounted(async () => {
    await getLanguages();

    await listReport('all');
    await searchDestinations();
    await markets();
    await sellers();

    return reportsList.value.data;
  });

  const { currentLanguage } = storeToRefs(languageStore);
  watch(currentLanguage, async () => {
    handleUpdateLanguage();
  });

  const handleUpdateLanguage = () => {
    showIsLoading();
    closeIsLoading();
  };

  /*onMounted(async () => {
	await searchDestinations();
	return reportsDestinity.value
})*/

  const footerItems = [
    {
      img: 'img01.png',
      name: 'quote.label.hacienda_concepcion',
      days: '3D/2N',
      place: 'quote.label.puerto_maldonado',
      description: 'quote.label.biodiversity_peruvian',
      price: '280',
    },
    {
      img: 'img02.png',
      name: 'quote.label.posada_amazonas',
      days: '3D/2N',
      place: 'quote.label.puerto_maldonado',
      description: 'quote.label.biodiversity_peruvian',
      price: '280',
    },
    {
      img: 'img03.png',
      name: 'quote.label.amazon_reserve',
      days: '3D/2N',
      place: 'quote.label.puerto_maldonado',
      description: 'quote.label.biodiversity_peruvian',
      price: '440',
    },
  ];
  console.log(footerItems);
  const items = computed(() => reportsList.value.data);
  const totales = computed(() => reportsList.value.totals);
  const listDestinity = computed(() => reportsDestinity.value.data);
  const listMarkets = computed(() => marketList.value.data);
  const listSellers = computed(() => sellersList.value.data);

  //const listDestinity = computed(() => reportsDestinity);

  let itemPage = computed(() => {
    if (itemPage.value > 0) {
      return itemPage;
    }
    return 1;
  });
  quote_pages.value = computed(() => {
    let pages = [];
    for (let i = 0; i < reportsList.value.count / state.countpage; i++) {
      pages.push(i + 1);
    }
    return pages;
  });

  const selectItem = (e: boolean) => {
    //checkedItem.value = e
    if (typeof checkedItem.value === 'undefined') {
      checkedItem.value = [];
    }
    if (!Array.isArray(checkedItem.value)) {
      checkedItem.value = [];
    }

    if (checkedItem.value.includes(e)) {
      checkedItem.value.splice(checkedItem.value.indexOf(e), 1);
    } else {
      checkedItem.value.push(e);
    }

    /*if(!checkedItem.value.id){
		checkedItem.value.id = array();
	}
	
	checkedItem.value.id.push(e); */
  };

  const reformatDate = (_date) => {
    if (_date == undefined) {
      return;
    }
    _date = moment(_date).format('ddd D MMM YYYY');
    return _date;
  };

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const formatDate = (_date) => {
    if (_date == undefined) {
      return;
    }
    let secondPartDate = '';

    if (_date.length > 10) {
      secondPartDate = _date.substr(10, _date.length);
      _date = _date.substr(0, 10);
    }

    _date = _date.split('-');
    _date = _date[2] + '/' + _date[1] + '/' + _date[0];
    return _date + secondPartDate;
  };

  const reformatDateTime = (_date) => {
    if (_date == undefined) {
      return;
    }
    _date = moment(_date).format('ddd D MMM YYYY');
    return _date;
  };

  const reformatDateTimeH = (_date) => {
    if (_date == undefined) {
      return;
    }
    _date = moment(_date).format('hh:mm:ss');
    return _date;
  };

  const viewServices = async (item) => {
    showIsLoading();
    const listReportSelected = await itemReport(item);
    reportSelectedServices.value = listReportSelected.data.data[0].services;
    rowSelect.value = item.id;
    state.openedList = true;
    state.rowSelectInfo = true;
    closeIsLoading();
  };

  const toggleModalDelete = (quote) => {
    if (quote) {
      if (quote === 'all') {
        state.buttonsAction = true;
      } else {
        reportSelectedDownload.value = quote;
      }
    }
    state.showModalEliminar = !state.showModalEliminar;
  };

  const duplicate = async (quote) => {
    await duplicateQuoteReport(quote);
    await listReport('all');
  };

  const history = async (quote) => {
    const listhistoryReportSelected = await historyQuoteReport(quote);
    rowSelect.value = quote.id;
    state.openedList = true;
    history_logs.value = listhistoryReportSelected.data.data;
    state.rowSelectHistory = true;
  };

  const close = () => {
    state.openedList = false;
    rowSelect.value = '';
    history_logs.value = '';
    reportSelectedServices.value = '';
    state.rowSelectHistory = false;
    state.rowSelectInfo = false;
    state.rowSelectShare = false;
    shared.value = false;
    sharedQuoteId.value = '';
  };

  const modalDelete = async () => {
    if (state.buttonsAction) {
      await Promise.all(
        checkedItem.value.map(async (item) => {
          let data = [];
          data.id = item;
          await deleteQuoteReport(data);
        })
      );

      //checkedItem.value = 'undefined';
      //state.buttonsAction = false;

      await listReport('all');
      toggleModalDelete();
    } else {
      await deleteQuoteReport(reportSelectedDownload.value);
      await listReport('all');
      toggleModalDelete();
    }
  };

  const toggleModalIntinerario = () => {
    state.showModalItinerarioDetalle = !state.showModalItinerarioDetalle;
  };

  const toggleModalSkeleton = () => {
    state.showModalSkeletonDetalle = !state.showModalSkeletonDetalle;
  };

  const toggleDownload = () => {
    state.openDownload = !state.openDownload;
  };
  const downloadftSkeleton = async () => {
    if (state.buttonsAction) {
      await Promise.all(
        checkedItem.value.map(async (item) => {
          let result = items.value.find((ele) => ele.id === item);
          const response = await downloadQuoteSkeleton(result);

          //checkedItem.value = 'undefined';
          //state.buttonsAction = false;

          let fileURL = window.URL.createObjectURL(new Blob([response.data]));
          let fileLink = document.createElement('a');
          fileLink.href = fileURL;
          fileLink.setAttribute(
            'download',
            'Skeleton - ' + downloadSkeletonUse.value.nameService + '.docx'
          );
          document.body.appendChild(fileLink);

          fileLink.click();
        })
      );
      state.showModalSkeletonDetalle = !state.showModalSkeletonDetalle;
    } else {
      const response = await downloadQuoteSkeleton(reportSelectedDownload.value);
      let fileURL = window.URL.createObjectURL(new Blob([response.data]));
      let fileLink = document.createElement('a');
      fileLink.href = fileURL;
      fileLink.setAttribute(
        'download',
        'Skeleton - ' + downloadSkeletonUse.value.nameService + '.docx'
      );
      document.body.appendChild(fileLink);

      fileLink.click();
      state.showModalSkeletonDetalle = !state.showModalSkeletonDetalle;
    }
  };

  const donwloadIntinerario = async () => {
    if (state.buttonsAction) {
      await Promise.all(
        checkedItem.value.map(async (item) => {
          let result = items.value.find((ele) => ele.id === item);

          const response = await downloadQuoteItinerary(result);

          //checkedItem.value = 'undefined';
          //state.buttonsAction = false;
          let nameXport;
          if (downloadItinerary.value.nameServicioItem) {
            nameXport = downloadItinerary.value.nameServicioItem;
          } else {
            nameXport = result.name;
          }

          var fileURL = window.URL.createObjectURL(new Blob([response.data]));
          var fileLink = document.createElement('a');
          fileLink.href = fileURL;
          fileLink.setAttribute('download', 'Itinerary - ' + nameXport + '.docx');
          document.body.appendChild(fileLink);

          fileLink.click();
        })
      );
      state.showModalItinerarioDetalle = !state.showModalItinerarioDetalle;
    } else {
      const response = await downloadQuoteItinerary(reportSelectedDownload.value);

      let nameXport;
      if (downloadItinerary.value.nameServicioItem) {
        nameXport = downloadItinerary.value.nameServicioItem;
      } else {
        nameXport = result.name;
      }
      console.log(nameXport);
      var fileURL = window.URL.createObjectURL(new Blob([response.data]));
      var fileLink = document.createElement('a');
      fileLink.href = fileURL;
      fileLink.setAttribute(
        'download',
        'Itinerary - ' + downloadItinerary.value.nameServicioItem + '.docx'
      );
      document.body.appendChild(fileLink);

      fileLink.click();

      state.showModalItinerarioDetalle = !state.showModalItinerarioDetalle;
    }
  };

  const downloadItems = [
    {
      label: 'Excel',
      value: 'excel',
    },
    {
      label: t('quote.label.itinerary'),
      value: 'itinerario',
    },
    {
      label: t('quote.label.day_by_day_program_select'),
      value: 'programa-dia-dia',
    },
  ];

  const download = (quote) => {
    if (quote === 'all') {
      state.buttonsAction = true;
    } else {
      reportSelectedDownload.value = quote;
    }
  };

  const selectDownload = (item: string[] | null) => {
    if (!state.buttonsAction) {
      currentReportQuote.value = reportSelectedDownload.value;
    }

    if (item && item.includes('excel')) {
      if (state.buttonsAction) {
        checkedItem.value.forEach(async (row) => {
          let result = items.value.find((ele) => ele.id === row);
          let data = [];
          data.id = row;
          data.operation = result.operation;
          exportar(data);
        });
        //checkedItem.value = 'undefined';
        //state.buttonsAction = false;
      } else {
        exportar(reportSelectedDownload.value);
      }
    }

    if (item && item.includes('itinerario')) {
      toggleModalIntinerario();
    }

    if (item && item.includes('programa-dia-dia')) {
      toggleModalSkeleton();
    }
    toggleDownload();
  };

  const filterStatus = async (item) => {
    state.openedList = false;
    rowSelect.value = '';
    history_logs.value = '';
    reportSelectedServices.value = '';
    state.rowSelectHistory = false;
    state.rowSelectInfo = false;
    state.rowSelectShare = false;
    shared.value = false;
    sharedQuoteId.value = '';

    state.filter = item;
    let destiny, search, page;
    await listReport(item, destiny, search, page, state.countpage);
  };

  const filterChange = async (value) => {
    console.log(value);
    let destiny,
      search,
      status = 'all';

    if (typeof searchFormState.destiny != 'undefined') {
      let destiny = [];

      searchFormState.destiny.forEach((d) => {
        destiny.push(d.id);
      });
    }

    if (searchFormState.search != 'undefined') {
      search = searchFormState.search;
    }

    if (typeof searchFormState.status != 'undefined') {
      status = searchFormState.status.option.code;
    }

    let divsToHide = document.getElementsByClassName('ant-select-dropdown');
    for (var i = 0; i < divsToHide.length; i++) {
      divsToHide[i].style.display = 'none';
    }

    await listReport(status, destiny, search);
  };

  const all_pagina = [
    { value: '5', label: '5' },
    { value: '10', label: '10' },
    { value: '20', label: '20' },
    { value: '30', label: '30' },
    { value: '40', label: '40' },
    { value: '50', label: '50' },
    { value: '100', label: '100' },
  ];

  /*const all_status = [
	{ code: 'all', label: t(state.txt_view_all) },
	{ code: 'received', label: 'quote.label.received' },
	{ code: 'sent', label: 'quote.label.sent' }
];*/

  const clearForm = async () => {
    searchFormRef.value.resetFields();
    await listReport('all');
  };

  const edit = async (quote) => {
    //state.showModalEdit = !state.showModalEdit;
    const result_check = await checkEditing(quote);

    if (result_check.editing == true) {
      window.location.href = '/quotes';
    } else {
      const result_exist = await existByUserStatus(quote);

      if (result_exist.success == true) {
        state.showModalEdit = true;
        state.showModalEditId = quote.id;
        state.quoteRow = quote;
      } else {
        await putQuote(quote);
        window.location.href = '/quotes';
      }
    }
  };

  const saveCoti = async () => {
    await getQuote();
    await saveQuote();
    await deleteQuote(false);

    await putQuote(state.quoteRow);
    // window.location.href = '/quotes';
  };

  const toggleModalEdit = () => {
    state.showModalEdit = !state.showModalEdit;
  };

  const replaceCoti = async () => {
    let result = await replaceQuote(state.showModalEditId);
    if (result.success) {
      window.location.href = '/quotes';
    }
  };

  const sharedQuote = async (item) => {
    shared.value = true;
    rowSelect.value = item.id;
    state.openedList = true;
    sharedQuoteId.value = item;
    state.rowSelectShare = true;
  };

  const filterOption = (input: string, option: any) => {
    return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
  };

  // const handleChange = async (value: string) => {
  //   //listSellers.value = await sellers(value);
  // };

  const setPage = async (page) => {
    if (page === 'press') {
      page = inputValue.value;
    }

    let destiny,
      search,
      status = 'all';

    if (typeof searchFormState.destiny != 'undefined') {
      destiny = searchFormState.destiny.option.id;
    }

    if (searchFormState.search != 'undefined') {
      search = searchFormState.search;
    }

    if (typeof searchFormState.status != 'undefined') {
      status = searchFormState.status.option.code;
    } else {
      if (state.filter) {
        status = state.filter;
      }
    }

    itemPage.value = page;

    await listReport(status, destiny, search, page, state.countpage);
  };

  const setPagePagina = async (page) => {
    //return false;

    state.countpage = page;

    let destiny,
      search,
      status = 'all';

    if (typeof searchFormState.destiny != 'undefined') {
      destiny = searchFormState.destiny.option.id;
    }

    if (searchFormState.search != 'undefined') {
      search = searchFormState.search;
    }

    if (typeof searchFormState.status != 'undefined') {
      status = searchFormState.status.option.code;
    } else {
      if (state.filter) {
        status = state.filter;
      }
    }

    itemPage.value = 1;

    await listReport(status, destiny, search, itemPage, page);
  };

  const submitSharedQuote = async () => {
    if (typeof sharedQuoteState.nameClient === 'undefined') {
      return false;
    }

    let clientSellerSelected = listSellers.value.find(
      (ele) => ele.id === sharedQuoteState.nameClient
    );
    let name = '(' + listMarkets.value.code + ') ' + listMarkets.value.name;
    let clientsSelected = {
      code: listMarkets.value.id,
      label: name,
    };

    let modo = sharedQuoteState.modo === 1 ? 'view_permission' : 'edit_permission';

    let data = {
      clients_selected: clientsSelected,
      client_seller_selected: clientSellerSelected,
      send_notification: 1,
      permission_selected: modo,
    };

    await sharedSend(sharedQuoteId.value.id, data);

    // if(results === false){
    // 	showErrorNotification("Error Server")
    // 	return false;
    // }

    await listReport('all');
  };

  const contact = () => {
    document.location.href = window.url_front_a2 + 'contact-us';
  };
</script>
<template>
  <div id="quote-reports">
    <div class="banner">
      <img src="../../images/quotes/reports.jpg" />
      <div class="txt-banner">
        <h1>{{ t('quote.label.find_quotes') }}</h1>
      </div>
    </div>

    <div class="container-hotel-info">
      <div class="actions-reports" v-if="totales">
        <div class="totals">
          <p>
            <span
              ><svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="14"
                viewBox="0 0 15 14"
                fill="none"
              >
                <path
                  d="M1.375 13C1.375 13.5531 1.76602 14 2.25 14H12.75C13.234 14 13.625 13.5531 13.625 13V4H1.375V13ZM5.75 6.375C5.75 6.16875 5.89766 6 6.07812 6H8.92188C9.10234 6 9.25 6.16875 9.25 6.375V6.625C9.25 6.83125 9.10234 7 8.92188 7H6.07812C5.89766 7 5.75 6.83125 5.75 6.625V6.375ZM13.625 0H1.375C0.891016 0 0.5 0.446875 0.5 1V2.5C0.5 2.775 0.696875 3 0.9375 3H14.0625C14.3031 3 14.5 2.775 14.5 2.5V1C14.5 0.446875 14.109 0 13.625 0Z"
                  fill="#4F4B4B"
                /></svg
            ></span>
            {{ t('quote.label.total_quotes') }}
          </p>
          <h2>{{ totales.total }}</h2>
          <div class="link w-100" @click="filterStatus('all')">
            {{ t('quote.label.see_all') }}
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="17"
              height="16"
              viewBox="0 0 17 16"
              fill="none"
            >
              <path
                d="M3.83337 8H13.1667"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M8.5 3.33325L13.1667 7.99992L8.5 12.6666"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>

        <div class="new">
          <h2>
            <span>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="19"
                height="18"
                viewBox="0 0 19 18"
                fill="none"
              >
                <path
                  d="M11 1.5H5C4.60218 1.5 4.22064 1.65804 3.93934 1.93934C3.65804 2.22064 3.5 2.60218 3.5 3V15C3.5 15.3978 3.65804 15.7794 3.93934 16.0607C4.22064 16.342 4.60218 16.5 5 16.5H14C14.3978 16.5 14.7794 16.342 15.0607 16.0607C15.342 15.7794 15.5 15.3978 15.5 15V6L11 1.5Z"
                  stroke="#2E2B9E"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M11 1.5V6H15.5"
                  stroke="#2E2B9E"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M9.5 13.5V9"
                  stroke="#2E2B9E"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M7.25 11.25H11.75"
                  stroke="#2E2B9E"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            <div>
              {{ t('quote.label.news') }}
              <span class="text-last">{{ t('quote.label.lastDay') }}</span>
            </div>
          </h2>
          <div class="link w-100" @click="filterStatus('news')">
            {{ totales.news }} {{ t('quote.label.quotes') }}
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="17"
              height="16"
              viewBox="0 0 17 16"
              fill="none"
            >
              <path
                d="M3.83337 8H13.1667"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M8.5 3.33325L13.1667 7.99992L8.5 12.6666"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>

        <div class="actives">
          <h2>
            <span>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="19"
                height="18"
                viewBox="0 0 19 18"
                fill="none"
              >
                <path
                  d="M17 8.3099V8.9999C16.9991 10.6172 16.4754 12.1909 15.507 13.4863C14.5386 14.7816 13.1775 15.7293 11.6265 16.1878C10.0756 16.6464 8.41794 16.5913 6.90085 16.0308C5.38376 15.4703 4.08849 14.4345 3.20822 13.0777C2.32795 11.7209 1.90984 10.1159 2.01626 8.50213C2.12267 6.88832 2.74791 5.35214 3.79871 4.1227C4.84951 2.89326 6.26959 2.03644 7.84714 1.68001C9.42469 1.32358 11.0752 1.48665 12.5525 2.1449"
                  stroke="#3D3D3D"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M17 3L9.5 10.5075L7.25 8.2575"
                  stroke="#3D3D3D"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            {{ t('quote.label.active') }}
          </h2>
          <div class="link w-100" @click="filterStatus('activated')">
            {{ totales.activated }} {{ t('quote.label.quotes') }}
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="17"
              height="16"
              viewBox="0 0 17 16"
              fill="none"
            >
              <path
                d="M3.83337 8H13.1667"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M8.5 3.33325L13.1667 7.99992L8.5 12.6666"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>

        <div class="expires">
          <h2>
            <span>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="19"
                height="18"
                viewBox="0 0 19 18"
                fill="none"
              >
                <path
                  d="M9.5 16.5C13.6421 16.5 17 13.1421 17 9C17 4.85786 13.6421 1.5 9.5 1.5C5.35786 1.5 2 4.85786 2 9C2 13.1421 5.35786 16.5 9.5 16.5Z"
                  stroke="#D80404"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M9.5 4.5V9L12.5 10.5"
                  stroke="#D80404"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            {{ t('quote.label.expired') }}
          </h2>
          <div class="link w-100" @click="filterStatus('expired')">
            {{ totales.expired }} {{ t('quote.label.quotes') }}
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="17"
              height="16"
              viewBox="0 0 17 16"
              fill="none"
            >
              <path
                d="M3.83337 8H13.1667"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M8.5 3.33325L13.1667 7.99992L8.5 12.6666"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>

        <div class="files">
          <h2>
            <span>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="19"
                height="18"
                viewBox="0 0 19 18"
                fill="none"
              >
                <path
                  d="M11 1.5H5C4.60218 1.5 4.22064 1.65804 3.93934 1.93934C3.65804 2.22064 3.5 2.60218 3.5 3V15C3.5 15.3978 3.65804 15.7794 3.93934 16.0607C4.22064 16.342 4.60218 16.5 5 16.5H14C14.3978 16.5 14.7794 16.342 15.0607 16.0607C15.342 15.7794 15.5 15.3978 15.5 15V6L11 1.5Z"
                  stroke="#E4B804"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M11 1.5V6H15.5"
                  stroke="#E4B804"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M12.5 9.75H6.5"
                  stroke="#E4B804"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M12.5 12.75H6.5"
                  stroke="#E4B804"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M8 6.75H7.25H6.5"
                  stroke="#E4B804"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            Files
          </h2>
          <div class="link w-100" @click="filterStatus('files')">
            {{ totales.files }} {{ t('local.quotes') }}
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="17"
              height="16"
              viewBox="0 0 17 16"
              fill="none"
            >
              <path
                d="M3.83337 8H13.1667"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M8.5 3.33325L13.1667 7.99992L8.5 12.6666"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>
      </div>

      <div class="form">
        <a-form id="searchResporte" class="container" ref="searchFormRef" :model="searchFormState">
          <div>
            <div class="select-box">
              <a-form-item name="status">
                <a-select
                  v-model:value="searchFormState.status"
                  @change="filterChange"
                  :placeholder="t('quote.label.status_file')"
                  label-in-value
                >
                  <a-select-option value="all">{{ t('quote.label.view_all') }}</a-select-option>
                  <a-select-option value="received"
                    >{{ t('quote.label.received') }}
                  </a-select-option>
                  <a-select-option value="sent">{{ t('quote.label.sent') }}</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="select-box">
              <a-form-item name="destiny">
                <a-select
                  showSearch
                  v-model:value="searchFormState.destiny"
                  :options="listDestinity"
                  :field-names="{ label: 'name', value: 'id' }"
                  label-in-value
                  :placeholder="t('quote.label.destinations')"
                  mode="multiple"
                  @change="filterChange"
                ></a-select>
              </a-form-item>
            </div>

            <div>
              <a-form-item name="search">
                <a-input
                  :placeholder="t('quote.label.search_here')"
                  type="text"
                  v-model:value="searchFormState.search"
                />
              </a-form-item>

              <div class="search_button_container">
                <icon-search color="#979797" />
              </div>
            </div>
          </div>

          <!--<div class="actionButtons">-->
          <div class="text" @click="clearForm">
            <icon-clear />
            <span>{{ t('quote.label.clean_filters') }}</span>
          </div>

          <div class="text searchBtn" @click="filterChange">
            <icon-search color="#fff" />
            <span>{{ t('quote.label.search') }}</span>
          </div>
          <!--</div>-->
        </a-form>
      </div>

      <div class="quotes-actions" v-if="checkedItem">
        <ButtonOutlineContainer
          icon="trash-can"
          :text="t('quote.label.detele_quote')"
          @click="toggleModalDelete('all')"
          v-if="checkedItem.length > 0"
        />
        <DowloadButton
          @click="download('all')"
          :items="downloadItems"
          @selected="selectDownload"
          v-if="checkedItem.length > 0"
        />
      </div>

      <div class="tabs">
        <ul>
          <li class="active">
            <svg
              width="18"
              height="21"
              viewBox="0 0 18 21"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M8.00007 10.4999C10.2744 10.4999 12.1177 8.65665 12.1177 6.3823C12.1177 4.10794 10.2744 2.26465 8.00007 2.26465C5.72572 2.26465 3.88242 4.10794 3.88242 6.3823C3.88242 8.65665 5.72572 10.4999 8.00007 10.4999ZM10.8824 11.5294H10.3452C9.63105 11.8575 8.83647 12.0441 8.00007 12.0441C7.16367 12.0441 6.37231 11.8575 5.65494 11.5294H5.11772C2.73077 11.5294 0.794189 13.4659 0.794189 15.8529V17.1911C0.794189 18.0436 1.48583 18.7352 2.33831 18.7352H13.6618C14.5143 18.7352 15.206 18.0436 15.206 17.1911V15.8529C15.206 13.4659 13.2694 11.5294 10.8824 11.5294Z"
                fill="#EB5757"
              />
            </svg>
            {{ t('quote.label.my_quotes') }}
          </li>
        </ul>
      </div>

      <div class="table">
        <div class="row header">
          <div class="check">
            <CheckBoxComponent />
          </div>
          <!--<div class="pin">{{ t('quote.label.pin') }}</div>-->
          <div class="cotizacion">{{ t('quote.label.quote_num') }}</div>
          <div class="nombre">{{ t('quote.label.name_detail') }}</div>
          <div class="inicio">{{ t('quote.label.date_start') }}</div>
          <div class="ciudades">{{ t('quote.label.city') }}</div>
          <div class="duracion">{{ t('quote.label.duration') }}</div>
          <div class="tipo">{{ t('quote.label.type') }}</div>
          <div class="acciones">{{ t('quote.label.acctions') }}</div>
        </div>

        <div
          class="row"
          :class="
            (quote.backRow,
            { file: quote.reservation != null },
            { closeList: state.openedList },
            { opened: rowSelect === quote.id })
          "
          v-for="(quote, indexService) in items"
        >
          <div class="check">
            <CheckBoxComponent @checked="selectItem(quote.id)" />
          </div>
          <!--<div class="pin">
						<svg width="13" height="18" viewBox="0 0 13 18" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M9.95142 7.64158L9.55315 3.79174H10.9271C11.3585 3.79174 11.7083 3.44197 11.7083 3.0105V1.448C11.7083 1.01652 11.3585 0.666748 10.9271 0.666748H2.07291C1.64144 0.666748 1.29167 1.01652 1.29167 1.448V3.0105C1.29167 3.44197 1.64144 3.79174 2.07291 3.79174H3.44684L3.04856 7.64158C1.46956 8.37572 0.25 9.69216 0.25 11.3438C0.25 11.7753 0.599772 12.1251 1.03125 12.1251H5.45833V15.5107C5.45833 15.5511 5.46773 15.591 5.48583 15.6272L6.26708 17.1897C6.36282 17.3812 6.637 17.3815 6.73294 17.1897L7.51418 15.6272C7.53226 15.591 7.54168 15.5511 7.54169 15.5107V12.1251H11.9688C12.4002 12.1251 12.75 11.7753 12.75 11.3438C12.75 9.67771 11.5136 8.36791 9.95142 7.64158V7.64158Z" fill="#5C5AB4"/>
						</svg>
					</div>-->
          <div class="cotizacion">{{ quote.id }}</div>
          <div class="nombre">
            {{ quote.name }} <span>{{ quote.when_it_starts }}</span>
            <span v-if="quote.reservation">File #: {{ quote.reservation.file_code }}</span>
          </div>
          <div class="inicio">{{ reformatDate(quote.date_in) }}</div>
          <div class="ciudades">
            <span class="tag" v-for="destiny in quote.destinations">{{ destiny.state.iso }}</span>
            <span v-if="quote.destinations.length == 0">-</span>
          </div>
          <div class="duracion">
            {{ quote.nights }} <span>{{ t('quote.label.nights') }}</span>
          </div>
          <div class="tipo">
            <span
              class="tag-button"
              v-bind:class="{
                'bg-private': quote.service_type.id == 2,
                'bg-shared': quote.service_type.id == 1,
                'bg-none': quote.service_type.id == 3,
              }"
              >{{ quote.service_type.code }}</span
            >
          </div>
          <div class="acciones">
            <div class="botones">
              <div class="viewServices" @click="viewServices(quote)">
                <a-tooltip placement="top">
                  <template #title>
                    <span> {{ t('quote.label.information') }}</span>
                  </template>

                  <icon-alert-re
                    v-if="rowSelect === quote.id && state.rowSelectInfo"
                    color="#D80404"
                    :height="21"
                    :width="20"
                  />
                  <icon-alert-re v-else :height="21" :color="state.colorActive" :width="20" />
                </a-tooltip>
              </div>

              <a-tooltip placement="top">
                <template #title>
                  <span> {{ t('quote.label.download') }}</span>
                </template>
                <DowloadButtonReport
                  :items="downloadItems"
                  @click="download(quote)"
                  @selected="selectDownload"
                />
              </a-tooltip>

              <div @click="edit(quote)">
                <a-tooltip placement="top">
                  <template #title>
                    <span> {{ t('quote.label.edit') }}</span>
                  </template>
                  <icon-edit-dark />
                </a-tooltip>
              </div>

              <div @click="toggleModalDelete(quote)">
                <a-tooltip placement="top">
                  <template #title>
                    <span> {{ t('quote.label.delete_btn') }}</span>
                  </template>
                  <icon-trash-dark />
                </a-tooltip>
              </div>

              <div @click="duplicate(quote)">
                <a-tooltip placement="top">
                  <template #title>
                    <span> {{ t('quote.label.double') }}</span>
                  </template>
                  <svg
                    width="21"
                    height="20"
                    viewBox="0 0 21 20"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <g clip-path="url(#clip0_10770_2242)">
                      <path
                        d="M16.9167 7.5H9.41667C8.49619 7.5 7.75 8.24619 7.75 9.16667V16.6667C7.75 17.5871 8.49619 18.3333 9.41667 18.3333H16.9167C17.8371 18.3333 18.5833 17.5871 18.5833 16.6667V9.16667C18.5833 8.24619 17.8371 7.5 16.9167 7.5Z"
                        stroke="#3D3D3D"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M4.4165 12.5001H3.58317C3.14114 12.5001 2.71722 12.3245 2.40466 12.0119C2.0921 11.6994 1.9165 11.2754 1.9165 10.8334V3.33341C1.9165 2.89139 2.0921 2.46746 2.40466 2.1549C2.71722 1.84234 3.14114 1.66675 3.58317 1.66675H11.0832C11.5252 1.66675 11.9491 1.84234 12.2617 2.1549C12.5742 2.46746 12.7498 2.89139 12.7498 3.33341V4.16675"
                        stroke="#3D3D3D"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </g>
                    <defs>
                      <clipPath id="clip0_10770_2242">
                        <rect width="20" height="20" fill="white" transform="translate(0.25)" />
                      </clipPath>
                    </defs>
                  </svg>
                </a-tooltip>
              </div>

              <div @click="history(quote)" v-if="quote.history_logs_count > 0">
                <a-tooltip placement="top">
                  <template #title>
                    <span> {{ t('quote.label.history') }}</span>
                  </template>

                  <icon-history
                    v-if="rowSelect === quote.id && state.rowSelectHistory"
                    :height="21"
                    color="#D80404"
                    :width="20"
                  />

                  <icon-history v-else :height="21" :color="state.colorActive" :width="20" />
                </a-tooltip>
              </div>
              <div class="cursor_none" style="opacity: 0.5" v-else>
                <a-tooltip placement="top">
                  <template #title>
                    <span> {{ t('quote.label.history') }}</span>
                  </template>
                  <icon-history :height="21" :color="state.colorActive" :width="20" />
                </a-tooltip>
              </div>

              <div @click="sharedQuote(quote)">
                <a-tooltip placement="top">
                  <template #title>
                    <span> {{ t('quote.label.text_shared') }}</span>
                  </template>

                  <icon-share
                    v-if="rowSelect === quote.id && state.rowSelectShare"
                    :height="21"
                    color="#D80404"
                    :width="20"
                  />

                  <icon-share v-else :height="21" :color="state.colorActive" :width="20" />
                </a-tooltip>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="list-services" v-if="reportSelectedServices">
        <div class="close" @click="close">
          <svg
            width="18"
            height="10"
            viewBox="0 0 18 10"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M16 8.5L9 1.5L2 8.5"
              stroke="#EB5757"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>

        <a-tooltip placement="top">
          <template #title>
            <span> {{ t('quote.label.information') }}</span>
          </template>
          <icon-alert class="svg" color="#212529" :height="19" :width="19" />
          <h4>{{ t('quote.label.information_quote') }}</h4>
        </a-tooltip>

        <div class="bodyServices">
          <div class="item-services" v-for="(serv, indexReport) in reportSelectedServices">
            <div class="title">
              {{ indexReport + 1 }}.
              <b v-if="serv.type == 'service'">
                {{ serv.service.name }}
              </b>
              <b v-if="serv.type == 'hotel'">
                {{ serv.hotel.name }}
                <icon-confirmed v-if="serv.on_request === 0" />
                <icon-on-request v-else />
              </b>
              <b v-if="serv.type == 'flight'">
                <span v-if="serv.code_flight == 'AEC' || serv.code_flight == 'AECFLT'">
                  {{ t('quote.label.national') }}
                </span>
                <span v-if="serv.code_flight == 'AEI' || serv.code_flight == 'AEIFLT'">
                  {{ t('quote.label.international') }}
                </span>
              </b>
            </div>
            <div class="descripcion">
              {{ serv.date_in }} -
              <span v-if="serv.type == 'service'">
                {{ serv.adult }} ADL / {{ serv.child }} CHD / {{ serv.infant }} INF
              </span>
              <span v-if="serv.type == 'hotel'">
                {{ serv.single }} SGL / {{ serv.double }} DBL / {{ serv.triple }} TPL <br />
                {{ serv.nights }}
                {{ t('quote.label.nights') }}
              </span>
              <span v-if="serv.type == 'flight'">
                {{ serv.origin }} <i class="fas fa-long-arrow-alt-right"></i> {{ serv.destiny }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="list-services history" v-if="history_logs">
        <div class="close" @click="close">
          <svg
            width="18"
            height="10"
            viewBox="0 0 18 10"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M16 8.5L9 1.5L2 8.5"
              stroke="#EB5757"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>

        <svg
          class="svg"
          width="19"
          height="19"
          viewBox="0 0 19 19"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M5.67111 5.1309L5.29558 5.48411L5.66013 5.84866L7.36557 7.5541C7.37195 7.56048 7.37448 7.56484 7.37543 7.56668C7.37645 7.56865 7.37699 7.57034 7.37731 7.5722C7.37804 7.57644 7.37801 7.58481 7.37374 7.59513C7.36946 7.60545 7.36357 7.61139 7.36005 7.61387C7.35851 7.61495 7.35694 7.61577 7.35483 7.61644C7.35286 7.61707 7.348 7.61836 7.33898 7.61836H2.45414C2.43334 7.61836 2.4165 7.60152 2.4165 7.58072V2.69588C2.4165 2.68686 2.41779 2.682 2.41842 2.68003C2.41909 2.67792 2.41991 2.67635 2.42099 2.6748C2.42347 2.67129 2.42942 2.66539 2.43974 2.66112C2.45006 2.65684 2.45844 2.65681 2.46268 2.65754C2.46454 2.65786 2.46623 2.6584 2.4682 2.65942C2.47004 2.66037 2.47439 2.66289 2.48076 2.66926C2.48076 2.66927 2.48077 2.66927 2.48078 2.66928L4.13976 4.32826L4.48539 4.67389L4.83877 4.33619C6.24554 2.99178 8.15081 2.16675 10.2498 2.16675C14.5711 2.16675 18.0752 5.66585 18.0832 9.98524C18.0911 14.3003 14.5705 17.8303 10.2555 17.8334C8.39099 17.8347 6.6796 17.1852 5.33395 16.0992L5.01993 16.4882L5.33395 16.0992C5.19463 15.9867 5.18205 15.775 5.31169 15.6453L5.69028 15.2667C5.80419 15.1528 5.98071 15.1455 6.09758 15.2382C7.23755 16.1428 8.68093 16.6829 10.2498 16.6829C13.9435 16.6829 16.9326 13.6932 16.9326 10.0001C16.9326 6.3064 13.9429 3.31728 10.2498 3.31728C8.4774 3.31728 6.86639 4.00664 5.67111 5.1309ZM12.4395 11.9003L12.4395 11.9003L12.1095 12.3247C12.1095 12.3247 12.1095 12.3247 12.1094 12.3247C12.0055 12.4582 11.813 12.4823 11.6794 12.3784C11.6794 12.3784 11.6794 12.3784 11.6794 12.3784L9.67456 10.8191V6.50545C9.67456 6.3362 9.81176 6.199 9.98101 6.199H10.5186C10.6879 6.199 10.8251 6.3362 10.8251 6.50545V10.0118V10.2563L11.0181 10.4065L12.3858 11.4702L12.3858 11.4702C12.5194 11.5741 12.5435 11.7666 12.4395 11.9003Z"
            fill="#3D3D3D"
            stroke="#3D3D3D"
          />
        </svg>
        <h4>{{ t('quote.label.history_quote') }}</h4>

        <div class="item-services header historial" v-if="history_logs.length > 0">
          <div class="type">{{ t('quote.label.type') }}</div>
          <div class="user">{{ t('quote.label.user') }}</div>
          <div class="date">{{ t('quote.label.date') }}</div>
          <div class="detail">{{ t('quote.label.detail') }}</div>
        </div>

        <div class="bodyServices">
          <div
            class="item-services"
            v-if="history_logs.length > 0"
            v-for="(history, indexReport) in history_logs"
          >
            <div class="type">
              <svg
                v-if="history.type === 'update'"
                width="15"
                height="16"
                viewBox="0 0 15 16"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M12.5 9.6625V13C12.5 13.3315 12.3683 13.6495 12.1339 13.8839C11.8995 14.1183 11.5815 14.25 11.25 14.25H2.5C2.16848 14.25 1.85054 14.1183 1.61612 13.8839C1.3817 13.6495 1.25 13.3315 1.25 13V4.25C1.25 3.91848 1.3817 3.60054 1.61612 3.36612C1.85054 3.1317 2.16848 3 2.5 3H5.8375"
                  stroke="#BBBDBF"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M11.25 1.75L13.75 4.25L7.5 10.5H5V8L11.25 1.75Z"
                  stroke="#BBBDBF"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>

              <svg
                v-if="history.type === 'store'"
                width="16"
                height="16"
                viewBox="0 0 16 16"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M12.375 13.625H3.625C3.29348 13.625 2.97554 13.4933 2.74112 13.2589C2.5067 13.0245 2.375 12.7065 2.375 12.375V3.625C2.375 3.29348 2.5067 2.97554 2.74112 2.74112C2.97554 2.5067 3.29348 2.375 3.625 2.375H10.5L13.625 5.5V12.375C13.625 12.7065 13.4933 13.0245 13.2589 13.2589C13.0245 13.4933 12.7065 13.625 12.375 13.625Z"
                  stroke="#BBBDBF"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M11.125 13.625V8.625H4.875V13.625"
                  stroke="#BBBDBF"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M4.875 2.375V5.5H9.875"
                  stroke="#BBBDBF"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>

              <a-tooltip placement="bottom">
                <template #title>
                  <span> {{ t('quote.label.delete') }}</span>
                </template>
                <icon-trash-dark
                  v-if="history.type === 'destroy'"
                  :width="14"
                  :height="14"
                  color="#BBBDBF"
                />
              </a-tooltip>

              {{ history.type }}
            </div>
            <div class="user">{{ history.user.name }}</div>
            <div class="date">
              {{ reformatDateTime(history.created_at) }}
              <span>{{ reformatDateTimeH(history.created_at) }}</span>
            </div>
            <div class="detail">
              {{ t('quote.messages.' + history.slug) }}
              <b
                v-if="
                  history.slug === 'update_accommodation' ||
                  history.slug === 'update_markup' ||
                  history.slug === 'update_name' ||
                  history.slug === 'update_service_type_general' ||
                  history.slug === 'update_general_adults' ||
                  history.slug === 'update_type_pax' ||
                  history.slug === 'update_date_general' ||
                  history.slug === 'update_general_childs' ||
                  history.slug === 'update_date_estimated' ||
                  history.slug === 'copy_category' ||
                  history.slug === 'store_general_adults'
                "
              >
                {{ history.previous_data }} {{ t('quote.label.to') }} {{ history.current_data }}
              </b>
              <b v-if="history.slug === 'destroy_range'"> "{{ history.previous_data }}" </b>
              <b v-if="history.slug === 'destroy_category' || history.slug === 'store_category'">
                "{{ history.current_data }}"
              </b>
              <b
                v-if="
                  history.slug === 'destroy_service' ||
                  history.slug === 'store_service' ||
                  history.slug === 'store_extension'
                "
              >
                {{ t('quote.label.category') }}:
                {{ history.current_data_json.quote_category_name }}, <i class="fa fa-calendar"></i>
                {{ history.current_data_json.date_in }}
                {{ t('quote.label.' + history.current_data_json.type_service) }}:
                {{ history.current_data_json.service_code }}
              </b>
              <b id="5" v-if="history.slug === 'store_flight'">
                {{ t('quote.label.category') }}:
                {{ history.current_data_json.quote_category_name }}, <i class="fa fa-calendar"></i>
                {{ history.current_data_json.date_in }}
                {{ t('quote.label.' + history.current_data_json.type_service) }}:
                {{ history.current_data_json.origin }} -> {{ history.current_data_json.destiny }}
              </b>
              <b v-if="history.slug === 'replace_service'">
                {{ t('quote.label.category') }}:
                {{ history.current_data_json.quote_category_name }}, <i class="fa fa-calendar"></i>
                {{ history.current_data_json.date_in }}
                {{ t('quote.label.' + history.current_data_json.type_service) }}:
                {{ history.previous_data_json.service_code }} {{ t('quote.label.to') }}
                {{ history.current_data_json.service_code }}
              </b>
              <b v-if="history.slug === 'update_date'">
                {{ t('quote.label.category') }}:
                {{ history.current_data_json.quote_category_name }},
                {{ t('quote.label.' + history.current_data_json.type_service) }}:
                {{ history.current_data_json.service_code }}
                <i class="fa fa-calendar"></i>
                {{ history.previous_data }} {{ t('quote.label.to') }}
                {{ history.current_data_json.date_in }}
              </b>
              <b v-if="history.slug === 'update_service_paxs'">
                {{ t('quote.label.category') }}:
                {{ history.current_data_json.quote_category_name }},
                {{ t('quote.label.' + history.current_data_json.type_service) }}:
                {{ history.current_data_json.service_code }}
                <i class="fa fa-calendar"></i>
                (ADL:{{ history.previous_data_json.adult }}, CHD:{{
                  history.previous_data_json.child
                }}) {{ t('quote.label.to') }} (ADL:{{ history.current_data_json.adult }}, CHD:{{
                  history.current_data_json.child
                }})
              </b>
              <b v-if="history.slug === 'update_occupation'">
                {{ t('quote.label.category') }}:
                {{ history.current_data_json.quote_category_name }},
                {{ t('quote.label.' + history.current_data_json.type_service) }}:
                {{ history.current_data_json.service_code }}
                <i class="fa fa-calendar"></i>
                (SGL:{{ history.previous_data_json.single }}, DBL:{{
                  history.previous_data_json.double
                }}, TPL:{{ history.previous_data_json.triple }}) {{ t('quote.label.to') }} (SGL:{{
                  history.current_data_json.single
                }}, DBL:{{ history.current_data_json.double }}, TPL:{{
                  history.current_data_json.triple
                }})
              </b>
            </div>
          </div>
          <div v-else>
            <p>{{ t('quote.label.history_quote_none') }}</p>
          </div>
        </div>
      </div>

      <div class="list-services share" v-if="shared">
        <div class="close" @click="close">
          <svg
            width="18"
            height="10"
            viewBox="0 0 18 10"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M16 8.5L9 1.5L2 8.5"
              stroke="#EB5757"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>

        <svg
          width="18"
          height="18"
          viewBox="0 0 18 18"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M13.5 6C14.7426 6 15.75 4.99264 15.75 3.75C15.75 2.50736 14.7426 1.5 13.5 1.5C12.2574 1.5 11.25 2.50736 11.25 3.75C11.25 4.99264 12.2574 6 13.5 6Z"
            stroke="#212529"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M4.5 11.25C5.74264 11.25 6.75 10.2426 6.75 9C6.75 7.75736 5.74264 6.75 4.5 6.75C3.25736 6.75 2.25 7.75736 2.25 9C2.25 10.2426 3.25736 11.25 4.5 11.25Z"
            stroke="#212529"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M13.5 16.5C14.7426 16.5 15.75 15.4926 15.75 14.25C15.75 13.0074 14.7426 12 13.5 12C12.2574 12 11.25 13.0074 11.25 14.25C11.25 15.4926 12.2574 16.5 13.5 16.5Z"
            stroke="black"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M6.44238 10.1326L11.5649 13.1176"
            stroke="black"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M11.5574 4.88257L6.44238 7.86757"
            stroke="black"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        <h4>{{ t('quote.label.share_quote') }}</h4>

        <div class="bodyServices shared">
          <div class="item-services" v-if="sharedQuoteId.shared == 1">
            <h3>
              {{ t('quote.label.share_post') }}: ({{ sharedQuoteId.permission.client.code }})
              {{ sharedQuoteId.permission.seller.name }} {{ sharedQuoteId.permission.created_at }}
            </h3>
          </div>

          <div class="item-services" v-if="sharedQuoteId.shared == 0">
            <a-form
              id="sharedQuote"
              class="container"
              ref="sharedQuoteRef"
              :model="sharedQuoteState"
            >
              <a-form-item name="nameClient">
                <a-select
                  v-model:value="sharedQuoteState.nameClient"
                  show-search
                  :placeholder="t('quote.label.name_client')"
                  :options="listSellers"
                  :field-names="{ label: 'name', value: 'id' }"
                  :filter-option="filterOption"
                ></a-select>
              </a-form-item>

              <!--<a-form-item name="userAssocie" >
								<a-select
									v-model:value="sharedQuoteState.userAssocie"
									:options="listSellers"
									:field-names="{ label: 'name', value: 'id' }"
									:placeholder="t('quote.destinations')"
									@change="filterChange"
									label-in-value></a-select>
							</a-form-item>-->

              <a-radio-group v-model:value="sharedQuoteState.modo">
                <a-radio :value="1">{{ t('quote.label.display_only') }}</a-radio>
                <a-radio :value="2">{{ t('quote.label.edit_mode') }}</a-radio>
              </a-radio-group>

              <div class="submitSharedQuote" @click="submitSharedQuote">
                <svg
                  width="18"
                  height="18"
                  viewBox="0 0 18 18"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M13.5 6C14.7426 6 15.75 4.99264 15.75 3.75C15.75 2.50736 14.7426 1.5 13.5 1.5C12.2574 1.5 11.25 2.50736 11.25 3.75C11.25 4.99264 12.2574 6 13.5 6Z"
                    stroke="#ffffff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M4.5 11.25C5.74264 11.25 6.75 10.2426 6.75 9C6.75 7.75736 5.74264 6.75 4.5 6.75C3.25736 6.75 2.25 7.75736 2.25 9C2.25 10.2426 3.25736 11.25 4.5 11.25Z"
                    stroke="#ffffff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M13.5 16.5C14.7426 16.5 15.75 15.4926 15.75 14.25C15.75 13.0074 14.7426 12 13.5 12C12.2574 12 11.25 13.0074 11.25 14.25C11.25 15.4926 12.2574 16.5 13.5 16.5Z"
                    stroke="#ffffff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M6.44238 10.1326L11.5649 13.1176"
                    stroke="#ffffff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M11.5574 4.88257L6.44238 7.86757"
                    stroke="#ffffff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <span>{{ t('quote.label.shared') }}</span>
              </div>
            </a-form>
          </div>
        </div>
      </div>

      <div class="list-paginate" v-if="checkedItem">
        <nav aria-label="page navigation">
          <div class="text-center">
            <ul class="pagination">
              <li
                :class="{ 'page-item': true, disabled: itemPage == 1 }"
                @click="setPage(pageActive - 1)"
              >
                <a class="page-link" href="javascript:;">
                  <svg
                    width="25"
                    height="25"
                    viewBox="0 0 25 25"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M15.5 18.5L9.5 12.5L15.5 6.5"
                      stroke="#EB5757"
                      stroke-width="2.5"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </a>
              </li>

              <li
                v-for="page in quote_pages.value"
                @click="setPage(page)"
                :class="{ 'page-item': true, active: page == itemPage }"
              >
                <a class="page-link" href="javascript:;">
                  {{ page }}
                </a>
              </li>

              <li
                :class="{ 'page-item': true, disabled: itemPage == quote_pages.length }"
                @click="setPage(itemPage + 1)"
              >
                <a class="page-link" href="javascript:;">
                  <svg
                    width="25"
                    height="25"
                    viewBox="0 0 25 25"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M9.5 18.5L15.5 12.5L9.5 6.5"
                      stroke="#EB5757"
                      stroke-width="2.5"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </a>
              </li>
            </ul>
          </div>
        </nav>

        <a-select
          v-model:value="state.countpage"
          :options="all_pagina"
          @change="setPagePagina"
        ></a-select>

        <div class="searchPage">
          {{ t('quote.label.go_page') }}
          <a-input type="number" @pressEnter="setPage('press')" v-model:value="inputValue" />
        </div>
      </div>
    </div>

    <div class="footer">
      <div class="centerFooter">
        <QuoteDetailsPackages />
      </div>
    </div>

    <div class="footerHotel">
      <div class="container-hotel-info">
        <div>{{ t('quote.label.tell_something') }}</div>
        <div class="btn" @click="contact()">{{ t('quote.label.write_us') }}</div>
      </div>
    </div>

    <ModalComponent
      v-if="state.showModalItinerarioDetalle"
      :modal-active="state.showModalItinerarioDetalle"
      class="modal-itinerariodetalle"
      @close="toggleModalIntinerario"
    >
      <template #body>
        <DownloadItinerary />
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="false" class="cancel" @click="toggleModalIntinerario">
            {{ t('quote.label.return') }}
          </button>
          <button :disabled="false" class="ok" @click="donwloadIntinerario">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      v-if="state.showModalSkeletonDetalle"
      :modal-active="state.showModalSkeletonDetalle"
      class="modal-Skeletondetalle"
      @close="toggleModalSkeleton"
    >
      <template #body>
        <DownloadSkeleton :quoteReport="reportSelectedDownload" />
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="false" class="cancel" @click="toggleModalSkeleton">
            {{ t('quote.label.return') }}
          </button>
          <button :disabled="false" class="ok" @click="downloadftSkeleton">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      v-if="state.showModalEliminar"
      :modal-active="state.showModalEliminar"
      class="modal-eliminar"
      @close="toggleModalDelete"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.detele_quote') }}</h3>
        <div class="description">
          {{ t('quote.label.eliminating_quote') }} {{ t('quote.label.youre_sure') }}
        </div>
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="false" class="cancel" @click="toggleModalDelete">
            {{ t('quote.label.cancel') }}
          </button>
          <button :disabled="false" class="ok" @click="modalDelete">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      v-if="state.showModalEdit"
      :modal-active="state.showModalEdit"
      class="modal-eliminar"
      @close="toggleModalEdit"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.replace_before_continuing') }}</h3>
        <div class="description">
          {{ t('quote.label.save_before_continuing') }}
        </div>
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="false" class="cancel" @click="replaceCoti">
            {{ t('quote.label.replace') }}
          </button>
          <button :disabled="false" class="ok" @click="saveCoti">
            {{ t('quote.label.save_firts') }}
          </button>
        </div>
      </template>
    </ModalComponent>
  </div>
</template>

<style lang="scss" scoped>
  #quote-reports {
    .footer {
      background-color: #f5f5f5;
      margin-top: 80px;
      padding: 90px 0 110px;

      .centerFooter {
        width: 80vw;
        margin: 0 auto;

        h2 {
          color: #000;
          font-size: 48px;
          font-style: normal;
          font-weight: 400;
          line-height: 72px;
          letter-spacing: -0.72px;
          margin-bottom: 30px;
        }

        .items {
          display: flex;
          flex-direction: row;
          justify-content: space-between;
          gap: 70px;

          .item {
            max-width: 410px;
            display: flex;
            flex-direction: column;
            gap: 10px;

            img {
              width: 100%;
              height: 280px;
              margin-bottom: 15px;
            }

            .top {
              display: flex;
              color: #000;
              font-size: 24px;
              font-style: normal;
              font-weight: 700;
              line-height: 36px;
              letter-spacing: -0.36px;
              justify-content: space-between;
            }

            .place {
              color: #333;
              font-size: 18px;
              font-style: normal;
              font-weight: 700;
              line-height: 30px;
              letter-spacing: -0.27px;
              display: flex;
              align-items: center;
            }

            .description {
              font-size: 18px;
              color: #2e2e2e;
              margin-bottom: 40px;
            }

            .buttons {
              display: flex;
              flex-direction: row;
              justify-content: space-between;

              .button-component.btn-md {
                height: 40px;
                line-height: 40px;
                min-width: 148px;
                padding: 0;
              }
            }

            .price {
              span {
                font-size: 18px;
              }
            }
          }
        }
      }
    }
  }

  .actionButtons {
    display: flex;
    gap: 10px;
  }

  .list-paginate {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 30px;

    .searchPage {
      display: flex;
      align-items: center;
      gap: 10px;

      input {
        width: 70px;
      }
    }

    :deep(.ant-select-selector) {
      font-size: 14px;
    }
  }

  ul.pagination {
    list-style: none;
    align-items: center;
    display: flex !important;
    margin: 0;

    li {
      width: 30px;
      height: 30px;
      text-align: center;
      line-height: 30px;

      a {
        display: block;
        line-height: 28px;

        svg {
          vertical-align: middle;
        }
      }

      &.active {
        border: 1px solid #eb5757;
        color: #eb5757;
      }
    }
  }

  :deep(.button-download-container) {
    div {
      display: flex;
    }
  }

  :deep(.ant-radio-group-outline) {
    .ant-radio-checked::after {
      border: #eb5757;
    }

    .ant-radio-wrapper:hover .ant-radio,
    .ant-radio:hover .ant-radio-inner,
    .ant-radio-input:focus + .ant-radio-inner,
    .ant-radio-checked .ant-radio-inner {
      border-color: #eb5757;
    }

    .ant-radio-inner::after {
      background-color: #eb5757;
      height: 24px;
      width: 24px;
      margin-top: -12px;
      margin-left: -12px;
    }

    .ant-radio-inner {
      height: 24px;
      width: 24px;
      color: #979797;
    }

    .ant-form label {
      font-size: 14px;
      color: #979797;
      display: flex !important;
      margin-bottom: 4px;
      align-items: center;
    }

    .ant-radio {
      top: 0;
    }
  }

  #sharedQuote {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 55px;

    .ant-radio-group {
      label {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        gap: 5px;

        &:last-child {
          margin-bottom: 0;
        }
      }
    }

    .ant-form-item {
      margin-bottom: 0;
      width: 28%;
    }

    .submitSharedQuote {
      background: #eb5757;
      color: #fff;
      width: 160px;
      height: 48px;
      line-height: 48px;
      border-radius: 6px;
      cursor: pointer;
      text-align: center;
      display: flex;
      gap: 8px;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 500;
    }
  }

  .quotes-actions {
    display: inline-flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 27px;
    border: none;
    background: transparent;
  }

  #searchResporte {
    display: flex;
    justify-content: space-between;

    & > div:first-child {
      display: flex;
      width: 74%;
      gap: 22px;

      & > div {
        width: 25%;
        line-height: 45px;

        &:last-child {
          position: relative;
          width: 45%;
        }
      }
    }

    .search_button_container {
      position: absolute;
      top: 0;
      left: 8px;
      line-height: 40px;
      height: 45px;

      svg {
        display: inline-block;
        vertical-align: middle;
      }
    }

    .text {
      background: #fff;
      color: #eb5757;
      border: 1px solid #eb5757;
      display: flex;
      gap: 10px;
      cursor: pointer;
      border-radius: 6px;
      justify-content: center;
      align-items: center;
      padding: 0 20px;
      height: 45px;

      &:hover {
        background: rgba(255, 225, 225, 0.4);
      }

      &.searchBtn {
        background: #eb5757;
        color: #fff;
      }
    }

    .ant-input {
      padding-left: 40px;
    }
  }

  #sharedQuote,
  #searchResporte {
    .ant-select-multiple {
      :deep(.ant-select-selector) {
        background: url(../images/quotes/arrow.svg) no-repeat 96.5% center #fff !important;
      }
    }

    :deep(.ant-select-selector) {
      height: 45px;

      .ant-select-selection-search-input {
        height: 45px;
        line-height: 45px;
      }

      .ant-select-selection-placeholder {
        line-height: 45px;
      }

      span.ant-select-selection-item {
        height: 35px;
        line-height: 35px;
        margin-top: 4px;
      }

      .ant-select-selection-overflow-item {
        span.ant-select-selection-item {
          margin-top: -3px;
        }
      }
    }

    .ant-input,
    :deep(.ant-select-selector),
    :deep(.ant-select-selection-item) {
      height: 45px;
      line-height: 45px;
    }
  }

  .select-box {
    width: 20%;

    & > div {
      width: 100%;
    }
  }

  .cursor_none {
    cursor: default !important;
  }

  .svg {
    display: inline-block;
    vertical-align: top;
    margin-top: 2px;
  }

  .list-history {
    position: relative;
    padding-left: 8%;
    padding-top: 10px;
    max-height: 300px;
    overflow: auto;
    position: relative;
  }

  .container-hotel-info .close {
    position: absolute;
    right: 20px;
    top: 8px;
    cursor: pointer;
  }

  h4 {
    color: #212529;
    text-decoration: underline;
    font-size: 16px;
    font-weight: 600;
    display: inline-block;
    margin-left: 5px;
  }

  .table {
    padding: 30px 0;
    display: flex;
    flex-direction: column;
    gap: 15px;

    .row {
      display: flex;
      gap: 30px;
      padding: 10px 10px;
      align-items: center;
      border-radius: 8px;
      background: #fafafa;

      &.new {
        background: #ededff;
      }

      &.warning {
        background: #ffe1e1;
      }

      &.file {
        background: #fffbdb;
      }

      &.closeList {
        display: none;
      }

      &.closeList.opened,
      &.header {
        display: flex;
        font-weight: 700;
        color: #3d3d3d;

        .check {
          visibility: hidden;
        }
      }

      &.header {
        background: none;
      }

      div {
        flex-grow: 1;
        text-align: center;
        font-size: 14px;
      }

      .pin {
        width: 4%;
      }

      .cotizacion {
        width: 15%;
      }

      .nombre {
        width: 22%;

        span {
          font-size: 12px;
          display: block;
          color: #909090;
        }
      }

      .inicio {
        width: 15%;
      }

      .ciudades {
        width: 15%;

        span {
          background: #c4c4c4;
          font-size: 12px;
          font-weight: 500;
          border-radius: 6px;
          color: #fff;
          margin: 0 3px;
          display: inline-block;
          padding: 2px 6px;
        }
      }

      .duracion {
        width: 10%;
        font-weight: 600;

        span {
          font-weight: 400;
          display: block;
          text-transform: lowercase;
        }
      }

      .tipo {
        width: 5%;

        span {
          padding: 3px 6px;
          color: #fff;
          color: rgba(0, 0, 0, 0.85);
          border-radius: 6px;
          font-size: 12px;
        }
      }

      .acciones {
        width: 20%;

        div {
          &.botones {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            & > div {
              flex-grow: initial;
              outline: none;
              cursor: pointer;
              margin-right: 0;
              display: inline-block;
              vertical-align: middle;
              display: flex;

              div {
                display: flex;
              }

              svg {
                display: flex;
                align-items: center;
                outline: none;
              }

              &:last-child {
                margin-right: 0;
              }
            }

            & div {
              display: flex;
              outline: none;
            }
          }
        }
      }

      &.closeList.opened {
        .acciones {
          width: 20%;
        }
      }
    }
  }

  .list-services {
    padding-left: 2%;
    padding-top: 10px;
    position: relative;
    margin-bottom: 40px;

    .bodyServices {
      width: 98%;
      margin-top: 20px;
      padding-right: 3%;
      overflow: auto;
      max-height: 460px;
      padding-left: 4%;

      /* width */
      &::-webkit-scrollbar {
        width: 8px;
        margin-right: 4px;
        padding-right: 2px;
      }

      /* Track */
      &::-webkit-scrollbar-track {
        border-radius: 10px;
      }

      /* Handle */
      &::-webkit-scrollbar-thumb {
        border-radius: 4px;
        background: #c4c4c4;
        margin-right: 4px;
      }

      /* Handle on hover */
      &::-webkit-scrollbar-thumb:hover {
        background: #eb5757;
      }

      &.shared {
        padding-left: 0;
      }
    }

    .item-services {
      padding: 10px 10px 20px 10px;
      margin-bottom: 15px;
      background: #fafafa;
      border-radius: 6px;

      &.header {
        background: none;
        padding-top: 30px;
        font-weight: 700;
      }

      &.historial {
        padding-left: 3.5%;
      }

      &:last-child {
        border-bottom: 0;
      }
    }

    &.history {
      .item-services {
        display: flex;
        align-items: center;
        text-align: center;
        gap: 10px;
      }

      .type {
        width: 14%;
        display: flex;
        align-items: center;
        gap: 4px;
      }

      .user {
        width: 28%;
      }

      .date {
        width: 28%;

        span {
          display: block;
          color: #979797;
        }
      }

      .detail {
        width: 30%;
      }
    }

    .title {
      font-size: 16px;
      display: flex;
      align-items: center;
      font-weight: 500;

      b {
        font-weight: 500;
        margin-left: 20px;
        display: flex;
        align-items: center;
        gap: 6px;
      }
    }

    .descripcion {
      padding: 4px 0 0 8px;
      font-size: 14px;
      color: #909090;
    }
  }

  .tabs {
    border-bottom: 1px solid #c4c4c4;
    padding: 0 10px;

    ul {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      gap: 20px;
    }

    li {
      font-size: 16px;
      font-weight: bold;
      color: #979797;
      border-bottom: 2px solid #fff;
      display: flex;
      align-items: center;
      padding: 14px 2px;
      cursor: pointer;
      gap: 6px;

      &.active {
        color: #eb5757;
        border-bottom: 2px solid #eb5757;
      }
    }
  }

  .container-hotel-info {
    width: 80vw;
    margin: 0 auto;
  }

  .form {
    border-top: 1px solid #e9e9e9;
    margin-top: 20px;
    padding: 40px 0 32px;
  }

  .actions-reports {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    padding: 80px 0 20px;

    & > div {
      flex: auto;
      text-align: center;
      border-radius: 6px;
      border: 2px solid #bbbdbf;
      padding: 14px;
      min-height: 138px;

      h2 {
        font-size: 20px;
        font-style: normal;
        font-weight: 700;
        display: flex;
        margin: 0 0 10px 0;
        gap: 8px;
      }

      &:first-child {
        h2 {
          color: #4f4b4b;
          font-size: 56px;
          justify-content: center;
          margin: 0;
        }

        .link {
          font-size: 12px;
          line-height: 22px;
          background: #bbbdbf;
          color: #fff;
          padding: 3px 0;
        }
      }

      &.totals {
        p {
          span {
            background: none;
          }
        }

        &:hover {
          background: #e9e9e9;

          p {
            span {
              background: #4f4b4b;

              path {
                fill: #fff;
              }
            }
          }

          .link {
            background: #737373;
            color: #fff;

            svg,
            path {
              stroke: #fff;
              fill: #fff;
            }
          }
        }
      }

      &.new {
        h2 {
          color: #2e2b9e;

          div {
            text-align: left;

            span {
              font-size: 12px;
              display: block;
              width: 100%;
              text-align: left;
              height: auto;
              font-weight: 400;
              background: none !important;
              margin-top: -8px;
            }
          }
        }

        .link {
          background: #ededff;

          svg,
          path {
            stroke: #2e2b9e;
            fill: #2e2b9e;
          }
        }

        &:hover {
          border-color: #2e2b9e;
          background: #ededff;

          h2 {
            span {
              background: #2e2b9e;

              path {
                fill: #fff;
              }
            }
          }

          .link {
            background: rgba(149, 116, 175, 0.5);
            color: #fff;

            svg,
            path {
              stroke: #2e2b9e;
              fill: #2e2b9e;
            }
          }
        }
      }

      &.actives {
        h2 {
          color: #3d3d3d;
        }

        .link {
          background: #fafafa;

          svg,
          path {
            stroke: #3d3d3d;
            fill: #3d3d3d;
          }
        }

        &:hover {
          border-color: #3d3d3d;
          background: #fafafa;

          h2 {
            span {
              background: #3d3d3d;

              path {
                fill: #fff;
              }
            }
          }

          .link {
            background: rgba(233, 233, 233, 1);
            color: #575757;

            svg,
            path {
              stroke: #3d3d3d;
              fill: #3d3d3d;
            }
          }
        }
      }

      &.expires {
        h2 {
          color: #d80404;
        }

        .link {
          background: #ffe1e1;

          svg,
          path {
            stroke: #d80404;
            fill: #d80404;
          }
        }

        &:hover {
          border-color: #d80404;
          background: #fff6f6;

          h2 {
            span {
              background: #d80404;

              path {
                fill: #fff;
              }
            }
          }

          .link {
            background: #ffe1e1;
            color: #575757;

            svg,
            path {
              stroke: #d80404;
              fill: #d80404;
            }
          }
        }
      }

      &.files {
        h2 {
          color: #e4b804;
        }

        .link {
          background: #fffbdb;

          svg,
          path {
            stroke: #e4b804;
            fill: #e4b804;
          }
        }

        &:hover {
          border-color: #ffcc00;
          background: #fffbdb;

          h2 {
            span {
              background: #e4b804;

              path {
                fill: #fff;
              }
            }
          }

          .link {
            background: rgba(255, 218, 11, 0.3);
            color: #575757;

            svg,
            path {
              stroke: #ffcc00;
              fill: #ffcc00;
            }
          }
        }
      }
    }

    p {
      display: flex;
      align-items: center;
      flex: 1 0 0;
      color: #4f4b4b;
      margin: 0;
      font-size: 12px;
      font-weight: 600;
      line-height: 19px;
      letter-spacing: 0.18px;
      gap: 10px;
    }

    span {
      background: #ededff;
      height: 28px;
      width: 28px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .link {
      font-size: 14px;
      font-style: normal;
      font-weight: 600;
      cursor: pointer;
      border-radius: 6px;
      justify-content: center;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #575757;
      width: 165px;
      padding: 16px 0;
    }
  }

  .banner {
    position: relative;
  }

  .txt-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;

    h1 {
      color: #fff;
      font-size: 56px;
      font-style: normal;
      font-weight: 700;
      line-height: 63px;
      letter-spacing: -0.56px;
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
    font-size: 36px;
    font-style: normal;
    font-weight: 400;
    line-height: 50px; /* 138.889% */
    letter-spacing: -0.54px;
    height: 500px;

    div {
      align-items: center;
      justify-content: space-between;
      display: flex;
      align-items: center;

      & > div:first-child {
      }
    }

    .btn {
      padding: 0;
      align-items: center;
      font-size: 18px;
      text-align: center;
      border-radius: 6px;
      background: #eb5757;
      text-align: center;
      cursor: pointer;
      width: 192px;
      height: 52px;
      justify-content: center;
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
