<script lang="ts" setup>
  import type { UnwrapRef } from 'vue';
  import { onMounted, reactive, ref, watch } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { storeToRefs } from 'pinia';

  import { useSiderBarStore } from '../../store/sidebar';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import IconClear from '@/quotes/components/icons/IconClear.vue';
  import IconSearch from '@/quotes/components/icons/IconSearch.vue';

  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import type { Rule } from 'ant-design-vue/es/form';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import { useFilesStore } from '@/stores/files';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();
  const { getLang } = useQuoteTranslations();
  // store
  // TODO: refactor to use composable instead
  const storeSidebar = useSiderBarStore();
  const quoteStore = useQuoteStore();
  const { isLoading } = storeToRefs(quoteStore);

  const myButton = ref(null);

  // Composable
  const { quote, deleteServiceSelected } = useQuote();
  const { getMiselaniosAvailable } = useQuoteServices();
  const filesStore = useFilesStore();

  const props = defineProps({
    isFile: { type: Boolean, default: false },
    items: { type: Object, default: null },
    itinerary: { type: Object, default: null },
    modalMode: { type: Boolean, default: false },
  });

  // Form
  const searchFormRef = ref();

  interface SearchForm {
    date_from: Dayjs | undefined;
    service_quantity: number;
    service_name: string;
    include_temporary: boolean;
    passengers: [];
  }

  const searchFormState: UnwrapRef<SearchForm> = reactive({
    date_from: dayjs(quote.value.date_in),
    service_name: '',
    service_quantity: 1,
    include_temporary: false,
    passengers: [],
  });

  const searchFormRules: Record<string, Rule[]> = {
    date_from: [
      {
        required: true,
        message: t('quote.label.select_start_day'),
        trigger: 'change',
        type: 'object',
      },
    ],
    passengers: [
      {
        required: true,
        message: t('quote.label.select_passengers'),
        trigger: 'change',
        type: 'array',
      },
    ],
  };

  const dateFormat = 'DD/MM/YYYY';

  const search = () => {
    storeSidebar.setStatus(false, '', '');
    deleteServiceSelected();
    searchFormRef.value
      .validate()
      .then(async () => {
        if (props.isFile) {
          filesStore.initedAsync();
          let adults_ = filesStore.getQuantityAdults(searchFormState.passengers);
          let children_ = filesStore.getQuantityChildren(searchFormState.passengers);
          console.log('adults_', adults_);
          console.log('children_', children_);
          filesStore.putSearchPassengers(searchFormState.passengers);

          await filesStore.fetchServices({
            quantity_persons: {
              adults: adults_,
              child: children_,
              age_childs: [
                {
                  age: 1,
                },
              ],
            },
            date: searchFormState.date_from!.format('YYYY-MM-DD'),
            destiny: '', //destiny
            lang: getLang(),
            client_id: localStorage.getItem('client_id'),
            origin: '',
            filter: searchFormState.service_name,
            type: 'all',
            experience: 'all',
            classification: 'all',
            category: [24],
            service_mask: true,
            limit: 10,
            page: 1,
          });
          filesStore.finished();
        } else {
          await getMiselaniosAvailable(
            {
              adults: quote.value.people[0].adults | 1,
              allWords: 1, // true
              children: quote.value.people[0].child,
              date_from: searchFormState.date_from!.format('YYYY-MM-DD'),
              destiny: '', //destiny
              lang: getLang(),
              origin: '',
              service_name: searchFormState.service_name,
            },
            props.modalMode
          );

          if (!props.modalMode) {
            storeSidebar.setStatus(true, 'service', 'search');
          }
        }
      })
      .catch((error: string) => {
        console.log('error', error);
      });
  };

  const clearForm = () => {
    searchFormRef.value.resetFields();
  };

  const includeTemporary = () => {
    searchFormState.include_temporary = !searchFormState.include_temporary;
    filesStore.setIncludeTemporaryInSearch(searchFormState.include_temporary);
  };

  onMounted(() => {
    if (
      props.items &&
      props.items.service &&
      props.items.service.service_origin &&
      props.items.service.service_type
    ) {
      searchFormState.date_from = dayjs(props.items.date_in_format);
      myButton.value.click();
    }

    updateFormSearch();
  });

  watch(
    () => filesStore.getFile,
    () => {
      updateFormSearch();
    },
    { deep: true }
  );

  watch(filesStore.getFilePassengers, () => {
    updateFormSearch();
  });

  const all_passengers = ref(false);

  const selectAllPassengers = () => {
    searchFormState.passengers = [];
    all_passengers.value = !all_passengers.value;

    if (all_passengers.value) {
      searchFormState.passengers = filesStore.getFilePassengers.map(
        (passenger: any) => passenger.id
      );
    }
  };

  const updateFormSearch = () => {
    if (props.isFile) {
      if (typeof props.itinerary.id !== 'undefined') {
        searchFormState.date_from = dayjs(props.itinerary.date_in, 'YYYY-MM-DD');

        const accommodations = props.itinerary.accommodations || [];
        searchFormState.passengers = filesStore.getFilePassengers
          .filter((passenger: any) => {
            return accommodations.some(
              (accommodation: any) => accommodation.file_passenger_id === passenger.id
            );
          })
          .map((passenger: any) => passenger.id);
      } else {
        searchFormState.date_from = dayjs(filesStore.getFile.dateIn, 'YYYY-MM-DD');
        selectAllPassengers();
      }
    }
  };

  const disabledDate = (current: any) => {
    return current && current < dayjs().startOf('day');
  };
</script>

<template>
  <a-form
    id="tours"
    class="container"
    ref="searchFormRef"
    :model="searchFormState"
    :rules="searchFormRules"
  >
    <div id="meals" class="container">
      <div class="row-box">
        <div class="input-box miscellaneous-input">
          <label for="start-date">{{ t('quote.label.date') }}: *</label>
          <a-form-item name="date_from">
            <a-date-picker
              v-model:value="searchFormState.date_from"
              id="start-date"
              :disabledDate="disabledDate"
              :format="dateFormat"
            />
          </a-form-item>
        </div>
        <!-- <div class="input-box miscellaneous-cantidad">
        <label for="country">Cantidad: *</label>
        <a-form-item name="service_quantity">
          <a-input  type="number" v-model:value="searchFormState.service_quantity"  />
        </a-form-item>
      </div> -->
        <div class="input-box search miscellaneous">
          <label for="country">{{ t('quote.label.filter_by_words') }}: </label>
          <a-form-item name="service_name" class="d-block w-100">
            <a-input
              v-model:value="searchFormState.service_name"
              :placeholder="t('quote.label.write_here')"
            />
          </a-form-item>
        </div>
        <div class="input-box" v-if="props.isFile">
          <label for="passengers">
            <a-row type="flex" justify="space-between" align="middle">
              <a-col>
                <label for="passengers" class="d-block text-capitalize"
                  >{{ t('global.label.passengers') }} <b class="text-danger">*</b></label
                >
              </a-col>
              <a-col>
                <span class="cursor-pointer me-2" @click="selectAllPassengers">
                  <font-awesome-icon :icon="['fas', 'user-check']" />
                </span>
              </a-col>
            </a-row>
          </label>
          <a-form-item name="passengers">
            <a-select
              mode="tags"
              id="passengers"
              v-model:value="searchFormState.passengers"
              :fieldNames="{ label: 'label', value: 'id' }"
              max-tag-count="responsive"
              :options="filesStore.getFilePassengers"
            >
            </a-select>
          </a-form-item>
        </div>
      </div>
      <div class="row-box">
        <div class="input-box search"></div>
        <div class="actions_buttons">
          <div class="text" @click="includeTemporary" v-if="props.isFile">
            <template v-if="filesStore.getIsIncludeTemporaryInSearch">
              <i class="bi bi-check-square-fill text-danger" style="font-size: 1.5rem"></i>
              <font-awesome-icon :icon="['fas', 'stopwatch']" />
              Incluir servicios temporales
            </template>
            <template v-else>
              <i class="bi bi-square text-danger text-dark-light" style="font-size: 1.5rem"></i>
              <font-awesome-icon :icon="['fas', 'stopwatch']" />
              Incluir servicios temporales
            </template>
          </div>
          <div class="text" :class="{ disabled: isLoading }" @click="!isLoading && clearForm()">
            <icon-clear />
            <span>{{ t('quote.label.clean_filters') }}</span>
          </div>
          <div
            class="search_button_container"
            :class="{ disabled: isLoading }"
            ref="myButton"
            @click="!isLoading && search()"
          >
            <div class="search-button">
              <div class="content">
                <div class="icon">
                  <icon-search />
                </div>
                <div class="text">{{ t('quote.label.search') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </a-form>
</template>

<style lang="scss" scoped>
  form#tours.container {
    .input-box.search {
      .ant-row.ant-form-item {
        width: 59% !important;
      }
    }
  }
</style>
