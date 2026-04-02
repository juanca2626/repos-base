<script lang="ts" setup>
  import { computed, onMounted, reactive, ref, toRef, watch } from 'vue';
  import BoxComponent from './info/BoxComponent.vue';
  import IconSearch from '@/quotes/components/icons/IconSearch.vue';
  import IconDoblueArrow from '@/quotes/components/icons/IconDoblueArrow.vue';
  import HotelsComponent from './search/HotelsComponent.vue';
  import ServicesComponent from './search/ServicesComponent.vue';
  import ExtensionsComponent from './search/ExtensionsComponent.vue';
  import IconArrowDown from '@/quotes/components/icons/IconArrowDown.vue';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import DropDownSelectComponent from '@/quotes/components/global/DropDownSelectComponent.vue';
  import type { Hotel } from '@/quotes/interfaces';
  import { usePopup } from '@/quotes/composables/usePopup';
  import SearchSidebar from '@/quotes/components/SearchSidebar.vue';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import type { Service, ServiceExtensionsResponse } from '@/quotes/interfaces/services';
  import IconClose from '@/quotes/components/icons/IconClose.vue';
  import { useSiderBarStore } from '../store/sidebar';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import dayjs from 'dayjs';
  import ErrorView from '@/views/ErrorView.vue';

  const {
    getToursAvailable,
    getMealsAvailable,
    getTransferAvailable,
    getMiselaniosAvailable,
    getServiceByCategory,
  } = useQuoteServices();
  const { serviceSelected } = useQuote();
  const { getHotels } = useQuoteHotels();

  const { t } = useI18n();
  const { getLang } = useQuoteTranslations();
  const searchSide = ref<string>('');

  const { showForm, toggleForm, showForm2, toggleForm2 } = usePopup();

  const storeSidebar = useSiderBarStore();

  interface Props {
    page: string;
    isFile: { type: Boolean; default: false };
  }

  const props = defineProps<Props>();

  const page = toRef(props, 'page');

  const state = reactive({
    formPrice: {
      isOpen: false,
    },
    modalHotelDetail: {
      isOpen: false,
    },
    pagination: {
      currentPage: 1,
      perPage: 10,
    },
    viewTooltip: true,
  });

  const { hotels } = useQuoteHotels();
  const { deleteServiceSelected } = useQuote();

  const orderBy = ref<string>(t('quote.label.price'));
  const setSorting = (v: string) => {
    orderBy.value = v;
    state.pagination.currentPage = 1;
  };

  const orders = [
    {
      label: t('quote.label.offers'),
      value: 'ofertas',
      selected: false,
    },
    {
      label: t('quote.label.price'),
      value: 'precio',
      selected: false,
    },
    {
      label: t('quote.label.name'),
      value: 'nombre',
      selected: false,
    },
  ];

  onMounted(() => {
    watch(orderBy, sortItems, { immediate: true });

    sortHotelInitials();
    //scrollTooltipInitials();

    scrollTop();

    scrollTooltip();

    console.log('entra a buscar');
  });

  const scrollTooltip = () => {
    if (state.viewTooltip === true) {
      setTimeout(function () {
        state.viewTooltip = false;
      }, 3000);
    }
  };

  const scrollTop = () => {
    let pageYOffset = window.pageYOffset;

    let viewBtn = document.getElementById('viewBtn');
    let topviewBtn = viewBtn.offsetTop;

    if (pageYOffset > topviewBtn) {
      topviewBtn = pageYOffset - 1;
    }

    window.scrollTo({
      top: topviewBtn,
      behavior: 'smooth',
    });
  };

  // Hotels
  const sortHotels = (val: string) => {
    const h = [...hotels.value];

    if (val === 'nombre') {
      h.sort(function (a: Hotel, b: Hotel) {
        if (a.name < b.name) {
          return -1;
        }
        if (a.name > b.name) {
          return 1;
        }
        return 0;
      });
    }
    if (val === 'precio') {
      h.sort(function (a: Hotel, b: Hotel) {
        return Number(a.price) - Number(b.price);
      });
    }
    if (val === 'ofertas') {
      h.sort(function (a: Hotel, b: Hotel) {
        let ap = 0;
        let bp = 0;
        for (const room of a.rooms) {
          for (const rate of room.rates) {
            if (rate.promotions_data.length === 0) {
              ap = 1;
            }
          }
        }
        for (const room of b.rooms) {
          for (const rate of room.rates) {
            if (rate.promotions_data.length === 0) {
              bp = 1;
            }
          }
        }

        if (ap < bp) {
          return -1;
        }
        if (ap > bp) {
          return 1;
        }
        return 0;
      });
    }

    sortedHotels.value = h;
  };

  const sortHotelInitials = () => {
    const h = [...hotels.value];

    let hotel_new_favorite = h.filter(function (hotel) {
      if (hotel.favorite == 1) {
        return true;
      }
    });

    let hotel_new_populars = h.filter(function (hotel) {
      if (hotel.popularity != 0 && hotel.favorite == 0) {
        return true;
      }
    });

    let hotel_new_no_favorite_no_popularity = h.filter(function (hotel) {
      if (hotel.favorite == 0 && hotel.popularity == 0) {
        return true;
      }
    });

    let hotels_new_filtrado;

    if (hotel_new_favorite.length == 0 && hotel_new_populars.length == 0) {
      hotels_new_filtrado = hotel_new_no_favorite_no_popularity.sort(
        (hotela, hotelb) => hotela.price - hotelb.price
      );
    } else {
      let hotel_new_favorite_sort = hotel_new_favorite.sort(
        (hotela, hotelb) => hotela.price - hotelb.price
      );

      let hotel_new_populars_sort = hotel_new_populars.sort(
        (hotela, hotelb) => hotela.price - hotelb.price
      );

      let hotel_new_no_favorite_no_popularity_sort = hotel_new_no_favorite_no_popularity.sort(
        (hotela, hotelb) => hotela.price - hotelb.price
      );

      hotels_new_filtrado = hotel_new_favorite_sort.concat(
        hotel_new_populars_sort,
        hotel_new_no_favorite_no_popularity_sort
      );
    }

    sortedHotels.value = hotels_new_filtrado;
  };

  const sortedHotels = ref<Hotel[]>([]);
  const showHotelsSearch = ref<Hotel[]>([]);

  const showHotels = computed(() => {
    return sortedHotels.value.slice(
      (state.pagination.currentPage - 1) * state.pagination.perPage,
      state.pagination.currentPage * state.pagination.perPage
    );
  });

  // Services
  const { services, extensions } = useQuoteServices();
  const servicesSearch = computed(() => services.value);

  const sortServices = (val: string) => {
    const s = [...servicesSearch.value];

    if (val === 'nombre') {
      s.sort(function (a: Service, b: Service) {
        const nameA = a.service_translations[0].name;
        const nameB = b.service_translations[0].name;

        if (nameA < nameB) {
          return -1;
        }
        if (nameA > nameB) {
          return 1;
        }
        return 0;
      });
    }
    if (val === 'precio') {
      s.sort(function (a: Service, b: Service) {
        const priceA = a.service_rate[0].service_rate_plans[0].price_adult;
        const priceB = b.service_rate[0].service_rate_plans[0].price_adult;

        return Number(priceA) - Number(priceB);
      });
    }
    if (val === 'ofertas') {
      // h.sort(function (a: Service, b: Service) {
      //   let ap = 0;
      //   let bp = 0;
      //   for (const room of a.rooms) {
      //     for (const rate of room.rates) {
      //       if (rate.promotions_data.length === 0) {
      //         ap = 1;
      //       }
      //     }
      //   }
      //   for (const room of b.rooms) {
      //     for (const rate of room.rates) {
      //       if (rate.promotions_data.length === 0) {
      //         bp = 1;
      //       }
      //     }
      //   }
      //
      //   if (ap < bp) {
      //     return -1;
      //   }
      //   if (ap > bp) {
      //     return 1;
      //   }
      //   return 0;
      // });
    }

    sortedServices.value = s;
  };

  const sortExtension = () => {
    const s = [...extensions.value];
    s.sort(function (a: ServiceExtensionsResponse, b: ServiceExtensionsResponse) {
      const nameA = a.translations[0].name;
      const nameB = b.translations[0].name;

      if (nameA < nameB) {
        return -1;
      }
      if (nameA > nameB) {
        return 1;
      }
      return 0;
    });

    sortedExtensions.value = s;
  };

  const sortedServices = ref<Service[]>([]);
  const showServicesSearch = ref<Service[]>([]);

  const showServices = computed(() => {
    return sortedServices.value.slice(
      (state.pagination.currentPage - 1) * state.pagination.perPage,
      state.pagination.currentPage * state.pagination.perPage
    );
  });

  const sortedExtensions = ref<ServiceExtensionsResponse[]>([]);
  const showExtensionsSearch = ref<ServiceExtensionsResponse[]>([]);

  const showExtensions = computed(() => {
    return sortedExtensions.value.slice(
      (state.pagination.currentPage - 1) * state.pagination.perPage,
      state.pagination.currentPage * state.pagination.perPage
    );
  });

  // Pagination

  const sortItems = (val: string) => {
    //console.log(val);
    if (page.value == 'hotel') {
      sortHotels(val);
    } else if (page.value == 'service') {
      sortServices(val);
    } else if (page.value == 'extension') {
      sortExtension();
    }
  };

  const items = computed(() => {
    let items: Hotel[] | Service[] | ServiceExtensionsResponse[] = [];

    if (page.value == 'hotel') {
      if (sortedHotels.value.length === 0) return [];

      items = sortedHotels.value;
    } else if (page.value == 'service') {
      if (sortedServices.value.length === 0) return [];

      items = sortedServices.value;
    } else if (page.value == 'extension') {
      if (sortedExtensions.value.length === 0) return [];

      items = sortedExtensions.value;
    }

    window.dataLayer.push({
      event: 'search',
      results: items.length > 0 ? 'true' : 'false',
    });

    return items;
  });

  // const paginationPages = computed(() => {
  //   if (items.value.length === 0) return [];
  //
  //   let startNumber = 1;
  //
  //   let numbers = [];
  //
  //   for (let i = 0; i < Math.ceil(items.value.length / state.pagination.perPage); i++) {
  //     numbers.push(startNumber + i);
  //   }
  //
  //   return numbers;
  // });

  const changePage = (page: number | string) => {
    if (
      typeof page === 'number' &&
      page > 0 &&
      page <= Math.ceil(items.value.length / state.pagination.perPage)
    )
      state.pagination.currentPage = page;

    let myDiv = document.getElementById('bodyPage');
    myDiv.scrollTop = 0;
  };

  const onChange = (pageNumber: number) => {
    changePage(pageNumber);
  };

  const closeSidebar = () => {
    storeSidebar.setStatus(false, '', '');
    deleteServiceSelected();
  };

  const setShowPopup = async (
    value: boolean,
    country: string,
    state: string,
    service_type: string,
    service_name: string
  ) => {
    if (page.value == 'hotel') {
      await searchHotelsToReplace(value, country, state, service_type, service_name);
    }

    if (page.value == 'service') {
      await searchServicesToReplace(value, country, state, service_type, service_name);
    }
  };

  const searchServicesToReplace = async (
    value: boolean,
    country: string,
    state: string,
    service_type: string,
    service_name: string
  ) => {
    // Destin

    // Origin
    const originState = {
      code: state.value,
      label: state.label,
    };

    let originCity = null;

    const origin = {
      code: country!.code + ',' + (originState?.code ?? '') + ',' + (originCity?.code ?? ''),
      label: country!.label + ',' + (originState?.label ?? '') + ',' + (originCity?.label ?? ''),
    };

    switch (serviceSelected.value.service.service!.service_sub_category!.service_category_id) {
      case 9:
        await getToursAvailable({
          adults: serviceSelected.value.service.adult > 0 ? serviceSelected.value.service.adult : 1,
          allWords: 1, // true
          children: serviceSelected.value.service.child,
          date_from: dayjs(serviceSelected.value.service.date_in, 'DD/MM/YYYY').format(
            'YYYY-MM-DD'
          ),
          destiny: '',
          lang: getLang(),
          origin: origin,
          service_name: service_name,
          service_type: service_type ? service_type.value : '',
          experience_type: '',
          service_sub_category: '',
        });
        break;

      case 10:
        await getMealsAvailable({
          adults: serviceSelected.value.service.adult > 0 ? serviceSelected.value.service.adult : 1,
          allWords: 1, // true
          children: serviceSelected.value.service.child,
          date_from: dayjs(serviceSelected.value.service.date_in, 'DD/MM/YYYY').format(
            'YYYY-MM-DD'
          ),
          destiny: '',
          lang: getLang(),
          origin: origin,
          service_name: service_name,
          service_type: service_type ? service_type.value : '',
          service_sub_category: '',
          // price_range:
        });
        break;

      case 1:
        await getTransferAvailable({
          adults: serviceSelected.value.service.adult > 0 ? serviceSelected.value.service.adult : 1,
          allWords: 1, // true
          children: serviceSelected.value.service.child,
          date_from: dayjs(serviceSelected.value.service.date_in, 'DD/MM/YYYY').format(
            'YYYY-MM-DD'
          ),
          destiny: '',
          lang: getLang(),
          origin: origin,
          service_name: service_name,
          service_type: service_type ? service_type.value : '',
          service_premium: '',
          include_transfer_driver: '',
        });
        break;

      case 14:
        await getMiselaniosAvailable({
          adults: serviceSelected.value.service.adult > 0 ? serviceSelected.value.service.adult : 1,
          allWords: 1, // true
          children: serviceSelected.value.service.child,
          date_from: dayjs(serviceSelected.value.service.date_in, 'DD/MM/YYYY').format(
            'YYYY-MM-DD'
          ),
          destiny: '',
          lang: getLang(),
          origin: '',
          service_name: service_name,
        });
        break;

      default:
        await getServiceByCategory({
          service_category:
            serviceSelected.value.service?.service_sub_category?.service_category_id,
          adults: serviceSelected.value.service.adult > 0 ? serviceSelected.value.service.adult : 1,
          allWords: 1, // true
          children: serviceSelected.value.service.child,
          date_from: dayjs(serviceSelected.value.service.date_in, 'DD/MM/YYYY').format(
            'YYYY-MM-DD'
          ),
          destiny: '',
          lang: getLang(),
          origin: origin,
          service_name: service_name,
        });
        break;
    }

    storeSidebar.setStatus(true, 'service', 'search');
    showForm2.value = value;
    sortServices('');
  };

  const searchHotelsToReplace = async (
    value: boolean,
    country: string,
    state: string,
    service_type: string,
    service_name: string
  ) => {
    await getHotels({
      // We look for the available hotels to be able to add the room
      date_from: dayjs(serviceSelected.value.service.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
      date_to: dayjs(serviceSelected.value.service.date_out, 'DD/MM/YYYY').format('YYYY-MM-DD'),
      destiny: {
        code: country.iso + ',' + state.value,
        label: country.label + ',' + state.label,
      },
      hotels_id: [],
      lang: getLang(),
      quantity_persons_rooms: [],
      quantity_rooms: 1,
      set_markup: 0,
      typeclass_id: service_type ? service_type.value : '',
      hotels_search_code: service_name,
      zero_rates: true,
    });

    storeSidebar.setStatus(true, 'hotel', 'search');
    showForm2.value = value;
    sortHotels('');
  };

  const viewSearch = () => {
    let headerSearch = document.getElementById('headerSearch');
    let pagination = document.getElementById('pagination');
    if (headerSearch.classList.contains('selectedSearch')) {
      headerSearch.classList.remove('selectedSearch');
      headerSearch.classList.add('dismissSearch');

      if (page.value == 'hotel') {
        showHotelsSearch.value = [];
      } else if (page.value == 'service') {
        showServicesSearch.value = [];
      } else if (page.value == 'extension') {
        showExtensionsSearch.value = [];
      }

      searchSide.value = null;
      //this.$refs.searchSide.value=null;
      pagination.classList.remove('hide');
    } else {
      searchSide.value = '';
      searchSide.value = null;
      headerSearch.classList.remove('dismissSearch');
      headerSearch.classList.add('selectedSearch');
    }
  };

  const onSearch = (searchValue) => {
    let textValue = searchValue.target.value;
    /*console.log(searchValue);
  console.log(textValue);*/

    let pagination = document.getElementById('pagination');
    if (!textValue) {
      pagination.classList.remove('hide');

      if (page.value == 'hotel') {
        showHotelsSearch.value = [];
      } else if (page.value == 'service') {
        showServicesSearch.value = [];
      } else if (page.value == 'extension') {
        showExtensionsSearch.value = [];
      }
    } else {
      let items = [];
      let tempRecipes = [];

      if (page.value == 'hotel') {
        items = sortedHotels.value;
      } else if (page.value == 'service') {
        items = sortedServices.value;
      } else if (page.value == 'extension') {
        items = sortedExtensions.value;
      }

      tempRecipes = items.filter((item) => {
        let val = '';
        if (page.value == 'hotel') {
          val = item.name;
        } else if (page.value == 'service') {
          val = item.service_translations[0].name;
        } else if (page.value == 'extension') {
          val = item.translations[0].name;
        }

        return val.toUpperCase().includes(textValue.toUpperCase());
      });

      //
      if (tempRecipes.length > 0) {
        if (page.value == 'hotel') {
          showHotelsSearch.value = tempRecipes;
        } else if (page.value == 'service') {
          /*console.log('Entraa');
          console.log(tempRecipes)*/
          showServicesSearch.value = tempRecipes;
          //console.log(showServicesSearch.value)
        } else if (page.value == 'extension') {
          showExtensionsSearch.value = tempRecipes;
        }
      }

      pagination.classList.add('hide');
    }
  };
</script>

<template>
  <div class="sidebar">
    <div class="header">
      <div id="headerSearch" class="contentHeader">
        <div class="contentSlideHeader">
          <h2>{{ t('quote.label.results') }}</h2>
          <template class="data-right">
            <template v-if="page != 'extension'">
              <div class="ordeby" @click="toggleForm()">
                <span>{{ orderBy }}</span>

                <icon-arrow-down />

                <BoxComponent :showEdit="false">
                  <template #form>
                    <div v-if="showForm" class="price-order">
                      <DropDownSelectComponent
                        :items="orders"
                        :multi="false"
                        @selected="(val) => setSorting(val[0])"
                      />
                    </div>
                  </template>
                </BoxComponent>
              </div>
            </template>

            <template v-if="page != 'extension' && Object.keys(serviceSelected).length > 0">
              <div class="ordeby">
                <a-tooltip placement="topRight" visible="true" v-if="state.viewTooltip">
                  <template #title>
                    <!--<span> {{ t('quote.label.information') }}</span>-->
                    <span v-if="page.value == 'hotel'">{{
                      t('quote.label.categoriesTooltip')
                    }}</span>
                    <span v-else>{{ t('quote.label.servicesTooltip') }}</span>
                  </template>
                  <icon-search color="#3D3D3D" @click="toggleForm2()" />
                </a-tooltip>
                <a-tooltip placement="topRight" v-else>
                  <template #title>
                    <!--<span> {{ t('quote.label.information') }}</span>-->
                    <span v-if="page.value == 'hotel'">{{
                      t('quote.label.categoriesTooltip')
                    }}</span>
                    <span v-else>{{ t('quote.label.servicesTooltip') }}</span>
                  </template>
                  <icon-search color="#3D3D3D" @click="toggleForm2()" />
                </a-tooltip>

                <BoxComponent :showEdit="false">
                  <template #form>
                    <SearchSidebar
                      :show="showForm2"
                      :page="page"
                      @change="
                        (
                          value: boolean,
                          country: string,
                          state: string,
                          service_type: string,
                          service_name: string
                        ) => setShowPopup(value, country, state, service_type, service_name)
                      "
                    />
                  </template>
                </BoxComponent>
              </div>
            </template>

            <div class="cursorPointer" v-if="Object.keys(serviceSelected).length == 0">
              <a-tooltip placement="bottomRight" visible="true" v-if="state.viewTooltip">
                <template #title>
                  <!--<span> {{ t('quote.label.information') }}</span>-->
                  <span v-if="page.value == 'hotel'">{{ t('quote.label.categoriesTooltip') }}</span>
                  <span v-else>{{ t('quote.label.servicesTooltip') }}</span>
                </template>
                <icon-search color="#3D3D3D" @click="viewSearch()" />
              </a-tooltip>
              <a-tooltip placement="bottomRight" v-else>
                <template #title>
                  <!--<span> {{ t('quote.label.information') }}</span>-->
                  <span v-if="page.value == 'hotel'">{{ t('quote.label.categoriesTooltip') }}</span>
                  <span v-else>{{ t('quote.label.servicesTooltip') }}</span>
                </template>
                <icon-search color="#3D3D3D" @click="viewSearch()" />
              </a-tooltip>
            </div>

            <div class="icon-close-modal">
              <icon-close @click="closeSidebar()" />
            </div>
          </template>
        </div>

        <div class="contentSlideHeader d-none">
          <div>
            <icon-search color="#979797" class="icon-search" />
            <a-input-search
              :placeholder="t('quote.label.search')"
              v-model:searchSide="asd"
              @keyup="onSearch"
              size="large"
            />
          </div>
          <div class="cursorPointer">
            <icon-doblue-arrow color="#3D3D3D" @click="viewSearch()" />
          </div>
        </div>
      </div>
    </div>

    <div id="bodyPage" class="body">
      <template
        v-if="
          showHotelsSearch.length ||
          showHotels.length ||
          showServicesSearch.length ||
          showServices.length ||
          showExtensionsSearch.length ||
          showExtensions.length
        "
      >
        <template v-if="page == 'hotel' && sortedHotels.length">
          <HotelsComponent
            v-if="showHotelsSearch.length"
            v-for="hotel of showHotelsSearch"
            :key="hotel.id.toString()"
            :hotel="hotel"
          />
          <HotelsComponent
            v-for="hotel of showHotels"
            :key="hotel.id.toString()"
            :hotel="hotel"
            v-else
          />
        </template>
        <template v-if="page == 'service' && sortedServices.length">
          <ServicesComponent
            v-if="showServicesSearch.length"
            v-for="service of showServicesSearch"
            :key="service.id.toString()"
            :service="service"
          />

          <ServicesComponent
            v-for="service of showServices"
            :key="service.id.toString()"
            :service="service"
            v-else
          />
        </template>
        <template v-if="page == 'extension'">
          <ExtensionsComponent
            v-if="showExtensionsSearch.length"
            v-for="extension of showExtensionsSearch"
            :key="extension.id.toString()"
            :service="extension"
          />
          <ExtensionsComponent
            v-for="extension of showExtensions"
            :key="extension.id.toString()"
            :service="extension"
            v-else
          />
        </template>
      </template>
      <template v-else>
        <ErrorView
          status="404"
          :title="t('quote.error.no_results.title')"
          :subtitle="t('quote.error.no_results.subtitle')"
        />
      </template>
    </div>

    <div class="footer" id="pagination" v-if="items.length > 1">
      <a-pagination :total="items.length" show-less-items @change="onChange" />
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .ordeby {
    cursor: pointer;
    color: #eb5758;

    span {
      text-decoration: underline;
      display: inline-block;
      vertical-align: middle;
      margin-right: 5px;
    }

    svg {
      display: inline-block;
      vertical-align: middle;
    }
  }

  .data-right {
    display: flex;
    gap: 12px;
    text-transform: capitalize;
    align-items: center;
  }

  .price-order {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    border-radius: 0 0 6px 6px;
    background: #fff;
    box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
    z-index: 3;

    .dropdown-select {
      width: 120px;
    }
  }

  .scrollDownMaxBottom {
    .scrollDown {
      .body {
        height: 50vh;
        max-height: 50vh;
      }
    }
  }

  .hide {
    ul {
      visibility: hidden;
      opacity: 0;
    }
  }
</style>
