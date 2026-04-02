<template>
  <div class="detail-footer-component">
    <div class="options">
      <!-- <CheckBoxComponent label="Quiero donar a Huilloc" />
      <CheckBoxComponent label="Quiero pagar mi Huella de Carbono" /> -->
      <a :href="url + 'cancellation-policies'" target="_blank">
        <LinkIconComponent :text="t('quote.label.view_poli')" />
      </a>
    </div>
    <div class="detail">
      <template
        v-if="operation == 'passengers' && priceCategorySelected.data.type_report == 'summarized'"
      >
        <h3>{{ t('quote.label.detail_prices') }}</h3>

        <span class="text">
          <span class="tag tag-off">
            {{ categoryText }}
          </span>
        </span>

        <div class="label big">
          {{ t('quote.label.rooms') }}
          <span v-if="single > 0">{{ singleF }} SGL</span>
          <span v-if="double > 0">{{ doubleF }} DBL</span>
          <span v-if="triple > 0">{{ tripleF }} TPL</span>
        </div>
        <hr />
        <div class="data">
          <template v-if="priceCategorySelected">
            <template v-for="(price, index) in priceCategorySelected.data.headers" :key="index">
              <div class="label">x {{ price }}</div>
              <div class="text">
                $ {{ displayPrice(priceCategorySelected.data.services_totals[index]) }}
              </div>
            </template>
          </template>
          <hr />
          <template v-if="priceCategorySelected">
            <div class="label total">{{ t('quote.label.total') }}</div>
            <div class="text total">$ {{ displayPrice(priceCategorySelected.data.sum_total) }}</div>
            <span v-if="showCommissionBadge" class="badge-warning ml-2">
              {{ t('global.label.with_commission') }}
            </span>
          </template>
        </div>
      </template>

      <!-- <div class="data" v-else>                 
        <template v-if="priceCategorySelected">
          <div class="label total">{{ t("quote.label.total") }}</div>
          <div class="text total">$ {{ priceCategorySelected.data.sum_total }}</div> 
        </template>
      </div> -->
    </div>
    <div class="buttons">
      <div style="position: relative">
        <a-badge-ribbon
          class="cursor-pointer"
          style="position: absolute; z-index: 999; top: -12px; padding: 3px 5px; right: -8px"
          v-if="
            (reservationErrors.hotels && reservationErrors.hotels.length > 0) ||
            (reservationErrors.services && reservationErrors.services.length > 0)
          "
        >
          <template #text>
            <span>
              <a-popover placement="topRight">
                <template #title v-if="reservationErrors.hotels.some((item: any) => item.name)">
                  <a-row type="flex" style="gap: 7px" align="middle" justify="start">
                    <a-col>
                      <span>{{ t('global.label.founded_errors') }}</span>
                    </a-col>
                  </a-row>
                </template>
                <template #content>
                  <div class="pe-2" style="max-height: 220px; overflow-y: auto">
                    <template v-for="(item, i) in reservationErrors.hotels">
                      <b :class="['d-block', i > 0 ? 'mt-2' : '']">{{ item?.name }}</b>
                      <p class="mb-0" v-for="error in item.errors">
                        <font-awesome-icon :icon="['far', 'thumbs-down']" :class="'text-danger'" />
                        {{ error.error }}
                      </p>
                    </template>

                    <template v-for="(item, i) in reservationErrors.services">
                      <b :class="['d-block', i > 0 ? 'mt-2' : '']">{{ item?.name }}</b>
                      <p class="mb-0" v-for="error in item.errors">
                        <font-awesome-icon :icon="['far', 'thumbs-down']" :class="'text-danger'" />
                        {{ error.error }}
                      </p>
                    </template>
                  </div>
                </template>
                <font-awesome-icon :icon="['fas', 'circle-exclamation']" fade />
              </a-popover>
            </span>
          </template>
        </a-badge-ribbon>
        <ButtonComponent
          @click="pre_reservations"
          style="position: relative"
          :class="[loadingBooking || !validBooking || processing ? 'disabled' : '']"
          :loading="loadingBooking"
          :disabled="loadingBooking || !validBooking || processing"
          v-if="!quote.file.file_code"
          >{{ t('quote.label.reserve') }}
        </ButtonComponent>
      </div>
      <ButtonComponent type="outline" @click="pageReport('reports')" :disabled="processing">
        {{ t('quote.label.my_quotes') }}
      </ButtonComponent>
      <DowloadButton :items="downloadItems" @selected="selectDownload" :disabled="processing" />
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
      <div class="footer m-0 p-0">
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
    :modal-active="state.showModalSkeletonDetalle"
    class="modal-Skeletondetalle"
    @close="toggleModalSkeleton"
  >
    <template #body>
      <DownloadSkeleton />
    </template>
    <template #footer>
      <div class="footer m-0 p-0">
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
    :modal-active="state.showModalReservations"
    class="modal-reservations"
    @close="toggleModalReservations"
  >
    <template #body>
      <ModalReservation
        :people="people"
        :quoteChildAges="quoteChildAges"
        :accommodation_p="accommodation_p"
      />
    </template>
    <template #footer>
      <div class="footer p-0 m-0">
        <button :disabled="false" class="cancel" @click="toggleModalReservations">
          {{ t('quote.label.return') }}
        </button>
        <button :disabled="false" class="ok" @click="changeQuote">
          {{ t('quote.label.yes_continue') }}
        </button>
      </div>
    </template>
  </ModalComponent>
</template>

<script lang="ts" setup>
  // import CheckBoxComponent from '@/quotes/components/global/CheckBoxComponent.vue';
  import LinkIconComponent from '@/quotes/components/global/LinkIconComponent.vue';
  import ButtonComponent from '@/quotes/components/global/ButtonComponent.vue';
  import DowloadButton from '@/quotes/components/global/DowloadButton.vue';
  import DownloadItinerary from '@/quotes/components/DownloadItinerary.vue';
  import DownloadSkeleton from '@/quotes/components/DownloadSkeleton.vue';
  import ModalReservation from '@/quotes/components/ModalReservation.vue';
  import { computed, reactive, ref, toRef, onMounted, watch } from 'vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import type { PassengerAgeChild, Person } from '@/quotes/interfaces';
  import { useI18n } from 'vue-i18n';
  import Cookies from 'js-cookie';
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

  // ✅ Mostrar badge solo si aplica
  const showCommissionBadge = computed(() => hasCommission(client.value, user_type_id));

  const { t, locale } = useI18n({
    useScope: 'global',
  });

  const {
    quote,
    operation,
    quoteCategories,
    accommodation,
    quotePricePassenger,
    quotePriceRanger,
    exportar,
    downloadQuoteItinerary,
    downloadQuoteSkeleton,
    downloadItinerary,
    downloadSkeletonUse,
    page,
    cancelQuoteRanges,
    getQuote,
    setQuoteChildAgeChangeQuote,
    setUpdatePeople,
    validate_reservation,
    reservationErrors,
    processing,
  } = useQuote();

  const loadingBooking = ref<boolean>(false);
  const validBooking = ref<boolean>(true);

  watch(locale, async () => {
    await validateQuote();
  });

  const validateQuote = async () => {
    const disableReservation = (Cookies.get(window.USER_DISABLE_RESERVATION) ?? 0) == 1;

    if (disableReservation) {
      // Se desactiva la reserva para este cliente..
      if (!reservationErrors.value.hotels) {
        reservationErrors.value.hotels = [];
      }

      reservationErrors.value.hotels.push({
        name: '',
        errors: [{ error: t('files.message.reservations_deactivated') }],
      });
      validBooking.value = false;
    } else {
      loadingBooking.value = true;
      const result = await validate_reservation({ loading: false });
      loadingBooking.value = false;

      if (result == false) {
        validBooking.value = false;
      }
    }
  };

  onMounted(async () => {
    await validateQuote();
  });

  const people = ref<Person>({
    id: 0,
    adults: 1,
    child: 0,
    infant: 0,
    quote_id: 0,
  });
  const quoteChildAges = ref<PassengerAgeChild[]>([]);
  const accommodation_p = ref({ ...accommodation.value });

  const url = ref<string>(window.url_front_a2);

  interface Props {
    category: number;
  }

  const props = defineProps<Props>();
  const category = toRef(props, 'category');

  const single = computed(() => accommodation.value.single);
  const double = computed(() => accommodation.value.double);
  const triple = computed(() => accommodation.value.triple);

  const singleF = computed(() => accommodation.value.single.toString().padStart(2, '0'));
  const doubleF = computed(() => accommodation.value.double.toString().padStart(2, '0'));
  const tripleF = computed(() => accommodation.value.triple.toString().padStart(2, '0'));

  const categoryText = computed(() => {
    console.log(category.value);
    return quoteCategories?.value.find((c) => c.type_class_id === category.value).type_class
      .translations[0].value;
  });

  const priceCategorySelected = computed(() => {
    if (operation.value == 'passengers') {
      return quotePricePassenger?.value.find((c) => c.category_id === category.value);
    } else {
      return quotePriceRanger?.value.find((c) => c.category_id === category.value);
    }
  });

  // const changePage = async(newView: string) => {
  //    page.value = newView
  // };

  const pre_reservations = () => {
    if (operation.value == 'passengers') {
      page.value = 'reservations';
      window.scrollTo(0, 0);
    } else {
      toggleModalReservations();
    }
  };

  const changeQuote = async () => {
    await cancelQuoteRanges(
      people.value.adults,
      people.value.child,
      accommodation_p.value.single,
      accommodation_p.value.double,
      accommodation_p.value.triple
    );

    if (quoteChildAges.value.length > 0) {
      toggleModalReservations();
      page.value = 'details';

      await getQuote(null, false);
      await setUpdatePeople(people.value.adults, people.value.child);
      let i = 0;
      for (const row of quoteChildAges.value) {
        await setQuoteChildAgeChangeQuote(i, row.age);
        i++;
      }
      await getQuote();
    } else {
      toggleModalReservations();
      page.value = 'details';
      await getQuote();
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

  const state = reactive({
    isOpen: false,
    showModalItinerarioDetalle: false,
    showModalSkeletonDetalle: false,
    showModalReservations: false,
    openDownload: false,
  });

  const toggleModalIntinerario = () => {
    state.showModalItinerarioDetalle = !state.showModalItinerarioDetalle;
  };

  const toggleModalSkeleton = () => {
    state.showModalSkeletonDetalle = !state.showModalSkeletonDetalle;
  };

  const toggleModalReservations = () => {
    state.showModalReservations = !state.showModalReservations;
  };

  const toggleDownload = () => {
    state.openDownload = !state.openDownload;
  };
  const downloadftSkeleton = async () => {
    const response = await downloadQuoteSkeleton();
    var fileURL = window.URL.createObjectURL(new Blob([response.data]));
    var fileLink = document.createElement('a');
    fileLink.href = fileURL;
    fileLink.setAttribute(
      'download',
      'Skeleton - ' + downloadSkeletonUse.value.nameService + '.docx'
    );
    document.body.appendChild(fileLink);

    fileLink.click();
    state.showModalSkeletonDetalle = !state.showModalSkeletonDetalle;
  };

  const donwloadIntinerario = async () => {
    const response = await downloadQuoteItinerary();

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
  };

  const selectDownload = (item: string[] | null) => {
    if (item && item.includes('excel')) {
      exportar();
    }

    if (item && item.includes('itinerario')) {
      toggleModalIntinerario();
    }

    if (item && item.includes('programa-dia-dia')) {
      toggleModalSkeleton();
    }
    toggleDownload();
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
</script>

<style lang="sass">

  .modal-reservations
    .modal-inner
      width: 490px
      max-height: 490vh
      overflow: auto


  .detail-footer-component
    display: flex
    margin-top: 40px

    .options
      flex: 1 1 50%
      display: inline-flex
      flex-direction: column
      align-items: flex-start
      gap: 16px

    .detail
      flex: 1 1 35%
      display: flex
      margin-right: 100px
      flex-direction: column

      h3
        flex: 1 1 100%
        color: #2E2E2E
        font-size: 18px
        font-style: normal
        font-weight: 700
        line-height: 30px
        letter-spacing: -0.27px
        margin-bottom: 10px

      .big
        span
          margin: 0 6px
          border: 1px solid #E9E9E9
          border-radius: 6px
          font-size: 12px
          font-weight: normal !important
          padding: 3px 5px

      .data
        display: flex
        flex-wrap: wrap
        flex: 1 1 50%

        .label
          flex: 1 1 50%
          color: #000
          font-size: 14px
          font-style: normal
          font-weight: 400
          line-height: 22px
          letter-spacing: -0.21px

          &.big
            font-size: 18px
            line-height: 30px
            letter-spacing: -0.27px
            margin-top: 10px

            span
              margin: 0 6px
              border: 1px solid #E9E9E9
              border-radius: 6px
              font-size: 12px
              font-weight: normal !important
              padding: 3px 5px

          &.total
            color: #EB5757
            font-size: 24px
            font-weight: 700
            line-height: 36px
            letter-spacing: -0.36px

          &.extra
            color: #2E2E2E
            font-size: 18px
            font-style: normal
            font-weight: 400
            line-height: 30px
            letter-spacing: -0.27px

        .text
          flex: 1 1 50%
          text-align: right

          &.big
            font-size: 18px
            line-height: 30px
            letter-spacing: -0.27px
            margin-top: 10px

          &.total
            color: #EB5757
            font-size: 36px
            font-style: normal
            font-weight: 700
            line-height: 50px
            letter-spacing: -0.54px

          &.extra
            color: #EB5757
            font-size: 18px
            font-style: normal
            font-weight: 400
            line-height: 30px
            letter-spacing: -0.27px

        hr
          width: 100%
          stroke-width: 1px
          stroke: #C4C4C4

    .buttons
      display: flex
      align-items: center
      flex-direction: column
      gap: 10px
</style>

<style lang="sass" scoped>
  .body
    .tag
      display: inline-block
      width: 114px
      height: 24px
      flex-shrink: 0
      border-radius: 6px
      color: #FEFEFE
      text-align: center
      font-size: 14px
      line-height: 22px
      letter-spacing: -0.21px
      margin-right: 16px
      position: relative
      cursor: default

      a
        position: absolute
        left: -5px
        top: -5px
        border-radius: 10px
        background: #575757
        width: 20px
        height: 20px
        display: none
        line-height: 20px

      &.tag-on
        background: #EB5757

        &:hover
          background: #C63838

      &.tag-off
        background: #CFCFCF

        &:hover
          background: #BBBDBF

      &:hover a
        display: block

      &.left-tag
        margin-left: 0

    .top-bg
      img
        width: 100%
        height: 355px

    .body
      width: 80vw
      margin: 0 auto

      h1
        color: #EB5757
        font-size: 48px
        font-style: normal
        font-weight: 400
        line-height: 72px
        letter-spacing: -0.72px

      .title
        color: #000
        font-size: 36px
        font-style: normal
        font-weight: 400
        line-height: 50px
        letter-spacing: -0.54px
        display: flex
        gap: 30px
        align-items: center
        margin-bottom: 30px
        padding: 0

        :deep(.button-outline-container)
          height: auto
          padding: 14px 16px

          .text
            font-size: 16px


      .dates
        font-size: 24px
        font-weight: 700
        line-height: 36px

    .quote-number
      color: #EB5757
      font-size: 18px
      margin-bottom: 30px


    .header
      font-size: 18px
      font-style: normal
      font-weight: 400
      line-height: 30px
      letter-spacing: -0.27px


      .route
        color: #000
        margin-bottom: 40px

      .detail
        display: grid
        grid-template-columns: 430px 1fr

        .label
          text-transform: uppercase
          flex: 1 1 300px
          color: #000
          font-weight: 700
          margin-bottom: 20px

        .text
          flex: 1 1 0

    hr
      stroke-width: 1px
      stroke: #C4C4C4
      margin-bottom: 38px

    .footer
      background-color: #FFF

      .centerFooter
        width: 80vw
        margin: 0 auto

        h2
          color: #000
          font-size: 48px
          font-style: normal
          font-weight: 400
          line-height: 72px
          letter-spacing: -0.72px
          margin-bottom: 30px

        .items
          display: flex
          flex-direction: row
          justify-content: space-between
          gap: 70px

          .item
            max-width: 410px
            display: flex
            flex-direction: column
            gap: 10px

            img
              width: 100%
              height: 280px
              margin-bottom: 15px

            .top
              display: flex
              color: #000
              font-size: 24px
              font-style: normal
              font-weight: 700
              line-height: 36px
              letter-spacing: -0.36px
              justify-content: space-between

            .place
              color: #333
              font-size: 18px
              font-style: normal
              font-weight: 700
              line-height: 30px
              letter-spacing: -0.27px
              display: flex
              align-items: center

            .description
              font-size: 18px
              color: #2E2E2E
              margin-bottom: 40px


            .buttons
              display: flex
              flex-direction: row
              justify-content: space-between

              .button-component.btn-md
                height: 40px
                line-height: 40px
                min-width: 148px
                padding: 0

            .price
              span
                font-size: 18px
</style>
