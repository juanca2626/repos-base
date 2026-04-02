<template>
  <div class="box-search">
    <div class="box-search-header pt-5 pb-5" v-if="showHeader">
      <div class="title mt-5">
        <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="text-dark-gray" />
        {{ t('files.label.search_hotel') }}
      </div>
    </div>

    <template v-if="showFilters">
      <div class="spin-white" id="quotes-layout">
        <a-spin :spinning="filesStore.isLoadingAsync" size="large">
          <template #indicator>
            <LoadingMaca v-if="filesStore.isLoadingAsync" />
          </template>
          <div class="box-search-filters">
            <div class="mt-3 mb-3 mx-2">
              <a-row type="flex" class="d-flex">
                <a-col flex="auto">
                  <a-radio-group v-model:value="filter.country">
                    <a-radio value="PE">Perú</a-radio>
                  </a-radio-group>
                </a-col>
                <a-col flex="auto">
                  <a-row type="flex" align="middle" justify="end">
                    <a-col class="d-flex ant-row-middle">
                      <font-awesome-icon
                        :icon="['fas', 'building']"
                        size="lg"
                        class="text-dark-gray"
                      />
                    </a-col>
                    <a-col
                      v-bind:class="['d-flex ant-row-middle ms-2', !flag_SGL ? 'text-dark' : '']"
                    >
                      <span
                        @click="flag_SGL = !flag_SGL"
                        v-bind:class="[
                          'd-flex cursor-pointer',
                          filesStore.isLoadingAsync ? 'events-none' : '',
                        ]"
                      >
                        <template v-if="flag_SGL">
                          <i
                            class="bi bi-check-square-fill text-danger"
                            style="font-size: 1.5rem"
                          ></i>
                        </template>
                        <template v-else>
                          <i class="bi bi-square text-dark-light" style="font-size: 1.5rem"></i>
                        </template>
                      </span>
                      <span
                        @click="flag_SGL = !flag_SGL"
                        v-bind:class="[
                          'mx-1 cursor-pointer',
                          filesStore.isLoadingAsync ? 'events-none' : '',
                        ]"
                        >{{ t('files.label.single') }}</span
                      >
                      <a-input
                        type="number"
                        v-bind:readonly="filesStore.isLoadingAsync"
                        v-model:value="quantity_sgl"
                        style="width: 80px"
                        v-if="flag_SGL"
                        min="0"
                      />
                    </a-col>
                    <a-col
                      v-bind:class="[
                        'd-flex ant-row-middle ms-2',
                        !flag_DBL ? 'svg-dark-light' : '',
                      ]"
                    >
                      <span
                        @click="flag_DBL = !flag_DBL"
                        v-bind:class="[
                          'd-flex cursor-pointer',
                          filesStore.isLoadingAsync ? 'events-none' : '',
                        ]"
                      >
                        <template v-if="flag_DBL">
                          <i
                            class="bi bi-check-square-fill text-danger"
                            style="font-size: 1.5rem"
                          ></i>
                        </template>
                        <template v-else>
                          <i class="bi bi-square text-dark-light" style="font-size: 1.5rem"></i>
                        </template>
                      </span>
                      <span
                        @click="flag_DBL = !flag_DBL"
                        v-bind:class="[
                          'mx-1 cursor-pointer',
                          filesStore.isLoadingAsync ? 'events-none' : '',
                        ]"
                        >{{ t('files.label.double') }}</span
                      >
                      <a-input
                        type="number"
                        v-bind:readonly="filesStore.isLoadingAsync"
                        v-model:value="quantity_dbl"
                        style="width: 80px"
                        v-if="flag_DBL"
                        min="0"
                      />
                    </a-col>
                    <a-col
                      v-bind:class="[
                        'd-flex ant-row-middle ms-2',
                        !flag_TPL ? 'svg-dark-light' : '',
                      ]"
                    >
                      <span
                        @click="flag_TPL = !flag_TPL"
                        v-bind:class="[
                          'd-flex cursor-pointer',
                          filesStore.isLoadingAsync ? 'events-none' : '',
                        ]"
                      >
                        <template v-if="flag_TPL">
                          <i
                            class="bi bi-check-square-fill text-danger"
                            style="font-size: 1.5rem"
                          ></i>
                        </template>
                        <template v-else>
                          <i class="bi bi-square text-dark-light" style="font-size: 1.5rem"></i>
                        </template>
                      </span>
                      <span
                        @click="flag_TPL = !flag_TPL"
                        v-bind:class="[
                          'mx-1 cursor-pointer',
                          filesStore.isLoadingAsync ? 'events-none' : '',
                        ]"
                        >{{ t('files.label.triple') }}</span
                      >
                      <a-input
                        type="number"
                        v-bind:readonly="filesStore.isLoadingAsync"
                        v-model:value="quantity_tpl"
                        style="width: 80px"
                        v-if="flag_TPL"
                        min="0"
                      />
                    </a-col>
                  </a-row>
                </a-col>
              </a-row>
            </div>
          </div>

          <div class="box-search-content" id="quotes-add-service d-block">
            <a-card title="Buscar hotel">
              <div>
                <a-form class="ant-row-space-between" layout="inline">
                  <a-form-item>
                    <label for="destiny" class="d-block mb-1"
                      >{{ t('files.label.destiny') }} <b class="text-danger">*</b></label
                    >
                    <a-select
                      style="width: 200px"
                      :allowClear="true"
                      id="destiny"
                      v-model:value="filter.destiny"
                      :showSearch="true"
                      size="large"
                      :disabled="filesStore.isLoadingAsync"
                      placeholder="Selecciona"
                      optionFilterProp="label"
                      :fieldNames="{ label: 'label', value: 'code' }"
                      :options="destinies"
                    >
                    </a-select>
                  </a-form-item>
                  <a-form-item>
                    <label for="category" class="d-block mb-1">{{
                      t('files.label.category')
                    }}</label>
                    <a-select
                      style="width: 150px"
                      :allowClear="false"
                      id="category"
                      size="large"
                      :disabled="filesStore.isLoadingAsync"
                      placeholder="Selecciona"
                      v-model:value="filter.category"
                      :fieldNames="{ label: 'class_name', value: 'class_id' }"
                      :options="filesStore.getCategories"
                    >
                    </a-select>
                  </a-form-item>
                  <a-form-item>
                    <label for="date_range" class="d-block mb-1"
                      >{{ t('files.label.check_in') }} - {{ t('files.label.check_out') }}
                      <b class="text-danger">*</b></label
                    >
                    <a-range-picker
                      :disabledDate="disabledDate"
                      :defaultPickerValue="[
                        dayjs(filesStore.getFile.dateIn),
                        dayjs(filesStore.getFile.dateIn),
                      ]"
                      size="large"
                      class="p-2"
                      :clear="false"
                      :disabled="filesStore.isLoadingAsync"
                      v-model:value="filter.date_range"
                      id="date_range"
                    />
                  </a-form-item>
                  <a-form-item>
                    <a-row type="flex" justify="space-between" align="middle" class="mb-1">
                      <a-col>
                        <label for="passengers" class="d-block text-capitalize"
                          >{{ t('global.label.passengers') }} <b class="text-danger">*</b></label
                        >
                      </a-col>
                      <a-col>
                        <span class="cursor-pointer me-2" @click="togglePassengers">
                          <font-awesome-icon :icon="['fas', 'user-check']" />
                        </span>
                      </a-col>
                    </a-row>
                    <a-select
                      mode="tags"
                      id="passengers"
                      size="large"
                      :disabled="filesStore.isLoadingAsync"
                      v-model:value="filter.passengers"
                      :fieldNames="{ label: 'label', value: 'id' }"
                      style="width: 180px"
                      placeholder="Selecciona"
                      max-tag-count="responsive"
                      v-on:change="
                        all_passengers =
                          filter.passengers.length == filesStore.getFilePassengers.length
                      "
                      :options="filesStore.getFilePassengers"
                    >
                    </a-select>
                  </a-form-item>
                  <a-form-item :span="4">
                    <label for="price_range" class="d-block mb-1">{{
                      t('files.label.price_range')
                    }}</label>
                    <div class="d-flex ant-row-middle">
                      <b class="text-danger pt-2 me-2">
                        <i class="bi bi-currency-dollar"></i>
                      </b>
                      <a-slider
                        range
                        id="price_range"
                        :disabled="filesStore.isLoadingAsync"
                        v-model:value="filter.price"
                        :marks="marks"
                        :min="0"
                        :max="950"
                        style="width: 200px"
                      />
                      <b class="text-danger pt-2 ms-2">
                        <i class="bi bi-currency-dollar"></i>
                      </b>
                    </div>
                  </a-form-item>
                </a-form>

                <a-form class="ant-row-space-between ant-row-bottom mt-2" layout="inline">
                  <a-form-item style="width: 740px">
                    <label for="search" class="d-block mb-1">{{
                      t('files.label.filter_by_hotel_name')
                    }}</label>
                    <a-input
                      :readonly="filesStore.isLoadingAsync"
                      v-model:value="filter.search"
                      placeholder="Escribe aquí..."
                      autocomplete="off"
                      class="w-100"
                      size="large"
                      id="search"
                    ></a-input>
                  </a-form-item>
                  <a-form-item>
                    <a-button
                      type="link"
                      danger
                      @click="onReset"
                      size="large"
                      class="d-flex ant-row-middle text-600"
                      :disabled="filesStore.isLoadingAsync"
                    >
                      <font-awesome-icon :icon="['fas', 'wand-sparkles']" />
                      <span class="mx-2">{{ t('global.button.clear_filters') }}</span>
                    </a-button>
                  </a-form-item>
                  <a-form-item>
                    <a-button
                      type="primary"
                      default
                      @click="onSubmit"
                      size="large"
                      class="text-600"
                      style="height: auto !important; padding: 16px 39px"
                      :loading="filesStore.isLoadingAsync"
                    >
                      <span class="d-flex ant-row-middle" style="font-size: 18px">
                        <font-awesome-icon
                          :icon="['fas', 'magnifying-glass']"
                          v-if="!filesStore.isLoadingAsync"
                        />
                        <span class="ms-2">{{ t('global.button.search') }}</span>
                      </span>
                    </a-button>
                  </a-form-item>
                </a-form>
              </div>
            </a-card>
          </div>
        </a-spin>
      </div>
    </template>
  </div>

  <template v-if="flag_SGL || flag_DBL || flag_TPL">
    <div class="box-selected my-4 mx-2" v-if="filesStore.getFileItinerariesReplace.length > 0">
      <a-row type="flex" justify="space-between" align="middle">
        <a-col>
          <b class="text-uppercase">{{ t('global.label.selected') }}</b>
        </a-col>
      </a-row>

      <a-row class="my-3">
        <template v-for="(item, i) in filesStore.getFileItinerariesReplace">
          <template template v-for="(room, r) in item.rooms" :key="r">
            <template v-for="(rate, rt) in room.rates" :key="'rate-' + rt">
              <a-row
                align="middle"
                justify="start"
                style="gap: 7px"
                v-bind:class="[
                  'item-selected ant-row-middle',
                  item.top ? 'item-selected-success' : 'item-selected-default',
                ]"
              >
                <a-col>
                  <span>
                    <font-awesome-icon :icon="['fas', 'building-circle-check']" />
                  </span>
                </a-col>
                <a-col>
                  <span class="text-danger text-700">|</span>
                </a-col>
                <a-col>
                  <span>{{ item.quantity }}</span>
                  <span class="ms-1">{{ showTextOccupation(room.occupation) }}</span>
                </a-col>
                <a-col><span class="text-danger text-700">|</span></a-col>
                <a-col
                  ><small class="text-uppercase text-600">{{ item.name }}</small></a-col
                >
                <a-col class="ms-1">
                  <a-tag color="gold">
                    <small class="text-uppercase">{{ rate.name_commercial }}</small>
                  </a-tag>
                </a-col>
                <a-col>
                  <span class="text-dark-gray cursor-pointer" v-on:click="removeItem(r + i)">
                    <font-awesome-icon :icon="['fas', 'xmark']" />
                  </span>
                </a-col>
              </a-row>
            </template>
          </template>
        </template>
      </a-row>

      <a-row type="flex" justify="end" align="middle">
        <a-col>
          <a-tooltip>
            <template #title v-if="flag_locked">
              No coinciden las habitaciones seleccionadas con las habitaciones buscadas. Por favor,
              intente nuevamente.
            </template>
            <a-button
              v-on:click="nextStep()"
              type="primary"
              class="px-4 text-600"
              default
              :disabled="filesStore.isLoading || filesStore.isLoadingAsync || flag_locked"
              size="large"
            >
              {{ t('global.button.continue') }}
            </a-button>
          </a-tooltip>
        </a-col>
      </a-row>
    </div>

    <template v-if="!filesStore.isLoadingAsync && filesStore.getFlagSearchHotels">
      <template v-if="filesStore.getHotelsTop.length > 0 || filesStore.getHotels.length > 0">
        <div
          v-bind:class="['box-top-hotels', showFilters ? 'p-4 mt-3' : 'm-0 p-5']"
          v-if="filesStore.getHotelsTop.length > 0"
        >
          <a-row type="flex" justify="space-between" align="middle">
            <a-col>
              <div class="subtitle p-0 d-flex ant-row-middle">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 512 512"
                  width="16"
                  height="16"
                  class="svg-danger mx-1 d-flex"
                >
                  <path
                    d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM228 104c0-11-9-20-20-20s-20 9-20 20v14c-7.6 1.7-15.2 4.4-22.2 8.5c-13.9 8.3-25.9 22.8-25.8 43.9c.1 20.3 12 33.1 24.7 40.7c11 6.6 24.7 10.8 35.6 14l1.7 .5c12.6 3.8 21.8 6.8 28 10.7c5.1 3.2 5.8 5.4 5.9 8.2c.1 5-1.8 8-5.9 10.5c-5 3.1-12.9 5-21.4 4.7c-11.1-.4-21.5-3.9-35.1-8.5c-2.3-.8-4.7-1.6-7.2-2.4c-10.5-3.5-21.8 2.2-25.3 12.6s2.2 21.8 12.6 25.3c1.9 .6 4 1.3 6.1 2.1l0 0 0 0c8.3 2.9 17.9 6.2 28.2 8.4V312c0 11 9 20 20 20s20-9 20-20V298.2c8-1.7 16-4.5 23.2-9c14.3-8.9 25.1-24.1 24.8-45c-.3-20.3-11.7-33.4-24.6-41.6c-11.5-7.2-25.9-11.6-37.1-15l-.7-.2c-12.8-3.9-21.9-6.7-28.3-10.5c-5.2-3.1-5.3-4.9-5.3-6.7c0-3.7 1.4-6.5 6.2-9.3c5.4-3.2 13.6-5.1 21.5-5c9.6 .1 20.2 2.2 31.2 5.2c10.7 2.8 21.6-3.5 24.5-14.2s-3.5-21.6-14.2-24.5c-6.5-1.7-13.7-3.4-21.1-4.7V104z"
                  />
                </svg>
                <span>{{ t('files.label.option_hotels_top') }}</span>
              </div>
            </a-col>
            <a-col>
              <span class="cursor-pointer" @click="flagTopHotels = !flagTopHotels">
                <font-awesome-icon
                  v-if="flagTopHotels"
                  icon="fa-solid fa-chevron-up"
                  size="lg"
                  class="svg-danger"
                />
                <font-awesome-icon
                  v-if="!flagTopHotels"
                  icon="fa-solid fa-chevron-down"
                  size="lg"
                  class="svg-danger"
                />
              </span>
            </a-col>
          </a-row>
          <div v-if="flagTopHotels">
            <template v-for="(_hotel, h) in filesStore.getHotelsTop" :key="h">
              <div
                class="hotel-top-item bg-light px-4 py-3 mt-3"
                v-if="_hotel.rooms.length > 0 && _hotel.price > 0"
              >
                <template v-for="(_room, r) in _hotel.rooms" :key="'item-' + h + '-' + r">
                  <template v-if="_room.best_price > 0">
                    <a-row
                      type="flex"
                      justify="start"
                      align="middle"
                      v-if="r == 0"
                      style="gap: 7px"
                    >
                      <a-col>
                        <h6 class="text-700 m-0">
                          <i class="bi bi-building-fill" style="font-size: 1.3rem"></i>
                          {{ _hotel.name }}
                        </h6>
                      </a-col>
                      <a-col>
                        <a-tag
                          class="text-white"
                          v-if="_hotel.flag_show || typeof _hotel.flag_show == 'undefined'"
                          :color="_hotel.color_class"
                        >
                          {{ _hotel.class }}
                        </a-tag>
                      </a-col>
                    </a-row>
                    <a-row :span="24" type="flex" justify="space-between" align="middle">
                      <template v-for="(_rate, r) in _room.rates" :key="'rate' + h + '-' + r">
                        <a-col v-if="_rate.total > 0" class="py-2">
                          <a-row type="flex" justify="space-between" align="top">
                            <a-col>
                              <span class="text-dark-gray me-2">
                                {{ moment(_rate.rate[0].amount_days[0].date).format('DD/MM/YYYY') }}
                                <template v-if="_rate.rate[0].amount_days.length > 1">
                                  |
                                  {{
                                    moment(
                                      _rate.rate[0].amount_days[
                                        _rate.rate[0].amount_days.length - 1
                                      ].date
                                    ).format('DD/MM/YYYY')
                                  }}
                                </template>
                              </span>
                            </a-col>
                            <a-col>
                              <span class="text-danger text-700 me-2">
                                {{ _rate.rate[0].amount_days.length }}
                                {{ t('global.label.nights') }}
                              </span>
                            </a-col>
                            <a-col>
                              <span class="me-3">
                                <b>{{ t('global.label.room') }}:</b> {{ _room.name }}
                              </span>
                            </a-col>
                            <a-col>
                              <div class="d-flex">
                                <span class="mx-1">
                                  <i
                                    class="d-flex bi bi-check2-circle text-success"
                                    style="font-size: 1.3rem"
                                    v-if="_rate.onRequest == 1"
                                  ></i>
                                  <i
                                    class="d-flex bi bi-exclamation-triangle text-warning"
                                    style="font-size: 1.3rem"
                                    v-else
                                  ></i>
                                </span>
                                <span
                                  class="mx-1 cursor-pointer"
                                  v-on:click="showInformation(_hotel, _room, _rate)"
                                >
                                  <i class="d-flex bi bi-info-circle" style="font-size: 1.3rem"></i>
                                </span>
                                <b class="mx-2">{{ _rate.name_commercial }}</b>
                                <b class="text-danger">$ {{ _rate.total }}</b>
                              </div>
                            </a-col>
                          </a-row>
                        </a-col>
                        <a-col class="d-flex" style="gap: 5px">
                          <template v-if="_rate.total > 0">
                            <a-input
                              type="number"
                              size="small"
                              v-model:value="_rate.quantity_room"
                              style="width: 80px; border: 1px solid #ddd"
                              v-if="
                                items.indexOf(generateKey(_rate)) > -1 && _rate.quantity_room > 0
                              "
                              min="1"
                              v-on:change="toggleRate(_rate, _room, _hotel, true, false)"
                            />
                            <span
                              class="cursor-pointer"
                              v-on:click="toggleRate(_rate, _room, _hotel, true)"
                            >
                              <template v-if="items.indexOf(generateKey(_rate)) > -1">
                                <i
                                  class="bi bi-check-square-fill text-danger"
                                  style="font-size: 1.5rem"
                                ></i>
                              </template>
                              <template v-else>
                                <i
                                  class="bi bi-square text-danger text-dark-light"
                                  style="font-size: 1.5rem"
                                ></i>
                              </template>
                            </span>
                          </template>
                        </a-col>
                      </template>
                    </a-row>
                  </template>
                </template>
              </div>
            </template>
          </div>
        </div>

        <template v-if="filesStore.getHotels.length > 0">
          <div class="box-hotels p-4 mt-3" v-for="(_hotel, h) in filesStore.getHotels" :key="h">
            <a-row type="flex" justify="space-between" align="top">
              <a-col>
                <div class="h6 text-700 p-0">
                  <i class="bi bi-building-fill" style="font-size: 1.3rem"></i>
                  {{ _hotel.name }}
                </div>
                <a-tag
                  class="text-white mt-2"
                  v-if="_hotel.flag_show || (h == 0 && typeof _hotel.flag_show == 'undefined')"
                  :color="_hotel.color_class"
                >
                  {{ _hotel.class }}
                </a-tag>
              </a-col>
              <a-col>
                <span class="cursor-pointer" @click="toggleViewHotelDetails(_hotel, h)">
                  <font-awesome-icon
                    v-if="_hotel.flag_show || (h == 0 && typeof _hotel.flag_show == 'undefined')"
                    icon="fa-solid fa-chevron-up"
                    size="lg"
                    class="svg-danger"
                  />
                  <font-awesome-icon
                    v-else
                    icon="fa-solid fa-chevron-down"
                    size="lg"
                    class="svg-danger"
                  />
                </span>
              </a-col>
            </a-row>

            <template v-if="_hotel.flag_show || (h == 0 && typeof _hotel.flag_show == 'undefined')">
              <template v-if="_hotel.rooms.length > 0 && _hotel.price > 0">
                <div class="px-2 mt-2" v-for="(_room, r) in _hotel.rooms" :key="r">
                  <a-row
                    type="flex"
                    justify="space-between"
                    align="top"
                    class="hotel-item pt-2"
                    v-if="_room.best_price > 0"
                  >
                    <a-col :span="12">
                      <div class="d-flex me-2" style="gap: 5px">
                        <b>{{ t('global.label.name') }}:</b>
                        <span class="text-dark-gray"> {{ _room.name }}</span>
                      </div>
                      <div class="d-flex me-2" style="gap: 5px">
                        <b>{{ t('global.label.description') }}:</b>
                        <span class="text-dark-gray"> {{ _room.room_type }}</span>
                      </div>
                    </a-col>
                    <a-col :span="12">
                      <template v-for="(_rate, r) in _room.rates" :key="r">
                        <a-row align="top" justify="space-between">
                          <a-col>
                            <a-row align="top" justify="space-between">
                              <a-col class="mx-2 pt-2 d-flex ant-row-middle">
                                <span class="mx-1">
                                  <i
                                    class="d-flex bi bi-check2-circle text-success"
                                    style="font-size: 1.3rem"
                                    v-if="_rate.onRequest == 1"
                                  ></i>
                                  <i
                                    class="d-flex bi bi-exclamation-triangle text-warning"
                                    style="font-size: 1.3rem"
                                    v-else
                                  ></i>
                                </span>
                                <span
                                  class="mx-1 cursor-pointer"
                                  v-on:click="showInformation(_hotel, _room, _rate)"
                                >
                                  <icon-circle-information />
                                  <!-- i class="d-flex bi bi-info-circle" style="font-size: 1.3rem"></i -->
                                </span>
                              </a-col>
                              <a-col>
                                <div class="d-block">
                                  <b>{{ _rate.name_commercial }}</b>
                                </div>
                                <div class="d-block my-1">
                                  <small class="text-600 text-dark-gray me-2">
                                    {{
                                      moment(_rate.rate[0].amount_days[0].date).format('DD/MM/YYYY')
                                    }}
                                    <template v-if="_rate.rate[0].amount_days.length > 1">
                                      -
                                      {{
                                        moment(
                                          _rate.rate[0].amount_days[
                                            _rate.rate[0].amount_days.length - 1
                                          ].date
                                        ).format('DD/MM/YYYY')
                                      }}
                                    </template>
                                  </small>
                                </div>
                                <div class="d-block">
                                  <b class="text-danger">$ {{ _rate.total }}</b>
                                </div>
                              </a-col>
                            </a-row>
                          </a-col>
                          <a-col class="d-flex pt-2" style="gap: 5px">
                            <template v-if="_rate.total > 0">
                              <a-input
                                type="number"
                                size="small"
                                v-model:value="_rate.quantity_room"
                                style="width: 80px; border: 1px solid #ddd"
                                v-if="items.indexOf(generateKey(_rate)) > -1"
                                min="1"
                                v-on:change="toggleRate(_rate, _room, _hotel, false, false)"
                              />
                              <span
                                class="cursor-pointer"
                                v-on:click="toggleRate(_rate, _room, _hotel, false)"
                              >
                                <template v-if="items.indexOf(generateKey(_rate)) > -1">
                                  <i
                                    class="bi bi-check-square-fill text-danger"
                                    style="font-size: 1.5rem"
                                  ></i>
                                </template>
                                <template v-else>
                                  <i
                                    class="bi bi-square text-danger text-dark-light"
                                    style="font-size: 1.5rem"
                                  ></i>
                                </template>
                              </span>
                            </template>
                          </a-col>
                        </a-row>
                      </template>
                    </a-col>
                  </a-row>
                </div>
              </template>
            </template>
          </div>
        </template>

        <div class="my-3">
          <a-row type="flex" justify="end" align="middle">
            <a-col>
              <a-button
                type="default"
                class="mx-2 px-4 text-600"
                v-on:click="returnToProgram()"
                default
                :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
                size="large"
              >
                {{ t('global.button.back') }}
              </a-button>
              <a-tooltip>
                <template #title v-if="flag_locked">
                  No coinciden las habitaciones seleccionadas con las habitaciones buscadas. Por
                  favor, intente nuevamente.
                </template>
                <a-button
                  v-on:click="nextStep()"
                  type="primary"
                  class="px-4 text-600"
                  default
                  :disabled="filesStore.isLoading || filesStore.isLoadingAsync || flag_locked"
                  size="large"
                >
                  {{ t('global.button.continue') }}
                </a-button>
              </a-tooltip>
            </a-col>
          </a-row>
        </div>
      </template>

      <template v-else>
        <a-alert type="warning" class="my-3">
          <template #message>
            <div class="text-warning">
              No se encontraron hoteles disponibles con los filtros seleccionados.
            </div>
          </template>
        </a-alert>
      </template>
    </template>
  </template>

  <a-modal v-model:visible="modalInformation" :width="800">
    <template #title>
      <div id="files-layout">
        <div class="text-left px-4 pt-4">
          <h6
            class="mb-0"
            style="font-size: 36px !important; line-height: 43px !important; font-weight: 400"
          >
            {{ hotel.name }}
          </h6>
          <a-tag class="text-white mt-2" :color="hotel.color_class">
            {{ hotel.class }}
          </a-tag>
          <font-awesome-icon icon="fa-regular fa-star" v-for="star in hotel.category" />
        </div>
      </div>
    </template>
    <div id="files-layout">
      <div class="px-2">
        <a-row :gutter="24" type="flex" justify="space-between" align="top">
          <a-col :span="15">
            <div class="bg-light" v-if="hotel.galleries.length > 0">
              <a-carousel arrows :autoplay="true" class="mb-3">
                <template #prevArrow>
                  <div class="custom-slick-arrow" style="left: 10px; z-index: 1">
                    <left-circle-outlined />
                  </div>
                </template>
                <template #nextArrow>
                  <div class="custom-slick-arrow" style="right: 10px">
                    <right-circle-outlined />
                  </div>
                </template>
                <div
                  v-for="(image, i) in hotel.galleries.slice(0, 4)"
                  class="w-100 object-cover aspect-square"
                  :key="i"
                >
                  <img :src="image" class="w-100 object-cover h-full" />
                </div>
              </a-carousel>
            </div>
            <div class="mb-3 text-justify" v-html="hotel.description"></div>
            <template v-if="hotel.rate.political">
              <p><b>Políticas de cancelación</b></p>
              <ul style="margin-left: -0.5rem">
                <li
                  class="text-danger text-400 mb-0"
                  v-if="hotel.rate.political_first != '' && hotel.rate.political_first != null"
                >
                  {{ hotel.rate.political_first }}.
                </li>
                <li
                  class="text-danger text-400 mb-0"
                  v-if="hotel.rate.political_second != '' && hotel.rate.political_second != null"
                >
                  {{ hotel.rate.political_second }}.
                </li>
                <li
                  class="text-danger text-400 mb-0"
                  v-if="hotel.rate.no_show != '' && hotel.rate.no_show != null"
                >
                  {{ hotel.rate.no_show }}
                </li>
              </ul>
            </template>
          </a-col>
          <a-col :span="9">
            <p><i class="bi bi-geo-alt"></i> {{ hotel.address }}</p>
            <p><i class="bi bi-building"></i> S - DBL - TP - C</p>
            <p>
              <i class="bi bi-clock"></i> <b>In:</b>
              <span class="text-danger text-400 mx-1">{{ hotel.checkIn }}</span> <b>Out:</b>
              <span class="text-danger text-400 mx-1">{{ hotel.checkOut }}</span>
            </p>
            <template v-if="hotel.amenities.length > 0">
              <p class="mb-0">
                <b>Incluye</b>
              </p>
              <p>
                <template v-for="item in hotel.amenities">
                  <a-tooltip>
                    <template #title>{{ item.name }}</template>
                    <img
                      class="d-inline-block mb-1 me-1"
                      v-bind:src="item.image"
                      v-bind:alt="item.name"
                      v-if="item.image != '' && item.image != null"
                    />
                  </a-tooltip>
                </template>
              </p>
            </template>
            <template v-if="hotel.rate.meal_id > 0">
              <p class="mb-0">
                <b>Comidas</b>
              </p>
              <p>{{ hotel.rate.meal_name }}</p>
            </template>
          </a-col>
        </a-row>
      </div>
    </div>
    <template #footer></template>
  </a-modal>
</template>

<script setup>
  import { onBeforeMount, ref, watch } from 'vue';
  import { useFilesStore } from '@store/files';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import moment from 'moment';
  import { notification } from 'ant-design-vue';
  import { useI18n } from 'vue-i18n';
  import dayjs from 'dayjs';
  import IconCircleInformation from '@/components/icons/IconCircleInformation.vue';
  import { LeftCircleOutlined, RightCircleOutlined } from '@ant-design/icons-vue';
  import LoadingMaca from '@/components/global/LoadingMaca.vue';

  const { t } = useI18n({
    useScope: 'global',
  });

  const emit = defineEmits(['onReturnToPogram', 'onNextStep']);

  const filesStore = useFilesStore();

  const filter = ref({
    country: 'PE',
    price: [0, 950],
    passengers: [],
  });

  const flagTopHotels = ref(true);
  const flag_locked = ref(false);
  const modalInformation = ref(false);
  const hotel = ref({});
  const marks = ref({
    0: '0',
    150: '150',
    500: '500',
    950: '950',
  });
  const flag_SGL = ref(false);
  const flag_DBL = ref(false);
  const flag_TPL = ref(false);
  const items = ref([]);
  const quantity_sgl = ref(0);
  const quantity_dbl = ref(0);
  const quantity_tpl = ref(0);
  const destinies = ref([]);
  const all_passengers = ref(false);

  watch(
    () => flag_SGL.value,
    (newValue) => {
      if (!newValue) {
        quantity_sgl.value = 0;
      }
    }
  );

  watch(
    () => flag_DBL.value,
    (newValue) => {
      if (!newValue) {
        quantity_dbl.value = 0;
      }
    }
  );

  watch(
    () => flag_TPL.value,
    (newValue) => {
      if (!newValue) {
        quantity_tpl.value = 0;
      }
    }
  );

  const returnToProgram = () => {
    emit('onReturnToProgram');
  };

  const showInformation = (_hotel, _room, _rate) => {
    let chunks = ['', ''];

    if (_rate.political) {
      let _political = _rate.political.cancellation.name;
      chunks = _political.split('. ');
    }

    _rate.political_first = chunks[0];
    _rate.political_second = chunks[1];

    hotel.value = _hotel;
    hotel.value.rate = _rate;
    hotel.value.room = _room;

    setTimeout(() => {
      modalInformation.value = true;
    }, 100);
  };

  const props = defineProps({
    showHeader: {
      type: Boolean,
      default: () => true,
    },
    showFilters: {
      type: Boolean,
      default: () => true,
    },
    type: {
      type: String,
      default: () => 'new',
    },
    items: {
      type: Array,
      default: () => [],
    },
  });

  onBeforeMount(async () => {
    let client_id = localStorage.getItem('client_id');
    let lang = localStorage.getItem('lang');

    filesStore.clearSearchHotels();
    filesStore.clearFileItineraryHotelsReplace();

    await filesStore.fetchDestiniesByClient({ client_id });
    await filesStore.fetchCategoriesHotel({ lang, client_id });

    destinies.value = filesStore.getDestinies;

    if (props.type == 'new') {
      if (filesStore.getFile.suggested_accommodation_dbl > 0) {
        flag_DBL.value = true;
        quantity_dbl.value = filesStore.getFile.suggested_accommodation_dbl;
      }

      if (filesStore.getFile.suggested_accommodation_sgl > 0) {
        flag_SGL.value = true;
        quantity_sgl.value = filesStore.getFile.suggested_accommodation_sgl;
      }

      if (filesStore.getFile.suggested_accommodation_tpl > 0) {
        flag_TPL.value = true;
        quantity_tpl.value = filesStore.getFile.suggested_accommodation_tpl;
      }

      const dateIn = dayjs(filesStore.getFile.dateIn);
      const dateOut = dayjs(filesStore.getFile.dateIn).add(1, 'days');

      filter.value.date_range = [dateIn, dateOut];

      // console.log(filesStore.get);
    }

    if (props.type == 'modification') {
      const dateIn = dayjs(filesStore.getFileItinerary.date_in);
      const dateOut = dayjs(filesStore.getFileItinerary.date_out);

      filter.value.date_range = [dateIn, dateOut];

      destinies.value = filesStore.getDestinies.filter((destiny) => {
        const destinyCode = destiny.code.toLowerCase();
        const destinyIso = filesStore.getFileItinerary.city_in_iso.toLowerCase();
        return destinyCode.indexOf(`,${destinyIso}`) > -1;
      });

      if (destinies.value.length == 0) {
        destinies.value = filesStore.getDestinies;
      } else {
        filter.value.destiny = destinies.value[0].code;
      }

      const rooms = filesStore.getFileItinerary.rooms
        .filter((room) => room.units.some((unit) => props.items.includes(unit.id)))
        .map((room) => ({
          ...room,
          units: room.units.filter((unit) => props.items.includes(unit.id)),
        }));

      for (const room of rooms) {
        for (const unit of room.units) {
          const accommodations = unit.accommodations ?? [];

          filter.value.passengers.push(
            ...filesStore.getFilePassengers
              .filter((passenger) =>
                accommodations.some(
                  (accommodation) => accommodation.file_passenger_id === passenger.id
                )
              )
              .map((passenger) => passenger.id)
          );

          const typeRoom = showTypeRoom(
            (room.total_adults + room.total_children) / room.total_rooms
          );

          if (typeRoom == 'SGL') {
            flag_SGL.value = true;
            quantity_sgl.value = room.total_rooms;
          }

          if (typeRoom == 'DBL') {
            flag_DBL.value = true;
            quantity_dbl.value = room.total_rooms;
          }

          if (typeRoom == 'TPL') {
            flag_TPL.value = true;
            quantity_tpl.value = room.total_rooms;
          }
        }
      }

      filter.value.price = [0, 950];
    }
  });

  const validateQuantityRooms = () => {
    let maxSGL = 0,
      maxDBL = 0,
      maxTPL = 0;
    let quantitySGL = 0,
      quantityDBL = 0,
      quantityTPL = 0;

    if (flag_SGL.value) {
      maxSGL = parseInt(quantity_sgl.value, 10);
    }

    if (flag_DBL.value) {
      maxDBL = parseInt(quantity_dbl.value, 10);
    }

    if (flag_TPL.value) {
      maxTPL = parseInt(quantity_tpl.value, 10);
    }

    filesStore.getFileItinerariesReplace.map((item) => {
      const itemQuantity = parseInt(item.quantity, 10) || 0;

      item.rooms.map((room) => {
        const occupation = typeof room.occupation === 'number' ? parseInt(room.occupation) : 0;

        if (occupation === 1) {
          quantitySGL += parseInt(itemQuantity, 10);
        }

        if (occupation === 2) {
          quantityDBL += parseInt(itemQuantity, 10);
        }

        if (occupation === 3) {
          quantityTPL += parseInt(itemQuantity, 10);
        }
      });
    });

    console.log(quantitySGL, quantityDBL, quantityTPL);
    console.log(maxSGL, maxDBL, maxTPL);

    flag_locked.value = !(
      quantitySGL === maxSGL &&
      quantityDBL === maxDBL &&
      quantityTPL === maxTPL
    );

    /*
    if (quantitySGL > maxSGL || quantityDBL > maxDBL || quantityTPL > maxTPL) {
      notification.warning({
        message: `Selección de Habitaciones`,
        description:
          'No coinciden las habitaciones seleccionadas con las habitaciones buscadas. Por favor, intente nuevamente.',
        duration: 5,
      });

      return false;
    }
    */
  };

  const generateKey = (rate) => {
    console.log('RATE: ', rate);
    return `${rate.rateId}-${rate.rate[0].amount_days[0].date}`;
  };

  const toggleRate = (rate, room, hotel, top, update) => {
    const key = generateKey(rate);

    if (typeof rate.quantity_room === 'undefined') {
      rate.quantity_room = 1; // Seteando el valor del rate..
    }

    if (rate.quantity_room < 1) {
      rate.quantity_room = 0;
      update = false;
    }

    if (rate.quantity_room >= 0) {
      if (update === false) {
        let index = items.value.indexOf(key);

        if (index > -1) {
          items.value.splice(index, 1);
          filesStore.removeFileItineraryReplace(index);
        }
      }

      let index = items.value.indexOf(key);

      if (index > -1) {
        items.value.splice(index, 1);
        filesStore.removeFileItineraryReplace(index);
      } else {
        let quantity = rate.quantity_room > 0 ? rate.quantity_room : 1;

        let params = {
          token_search: filesStore.getTokenSearchHotels,
          search_parameters: filesStore.getSearchParametersHotels,
          hotel: hotel,
          room: room,
          rate: rate,
          hotel_name: hotel.name,
          occupation: room.occupation,
          top: top,
          quantity: quantity,
          passengers: filter.value.passengers,
        };

        items.value.push(key);
        filesStore.putFileItinerariesReplace(params);
      }

      setTimeout(() => {
        validateQuantityRooms();
      }, 350);
    }
  };

  const showTypeRoom = (_type) => {
    _type = parseFloat(Math.round(_type)).toFixed(0);

    let types = ['', 'SGL', 'DBL', 'TPL'];
    return types[_type];
  };

  const toggleViewHotelDetails = (hotel, h) => {
    if (typeof hotel.flag_show == 'undefined') {
      hotel.flag_show = h == 0 ? false : true;
    } else {
      hotel.flag_show = !hotel.flag_show;
    }
  };

  const removeItem = (_index) => {
    items.value.splice(_index, 1);
    filesStore.removeFileItineraryReplace(_index);
    validateQuantityRooms();
  };

  const showTextOccupation = (occupation) => {
    let response = '';

    if (occupation >= 1 || occupation <= 3) {
      response = 'SGL';

      if (occupation > 1) {
        response = 'DBL';
      }

      if (occupation > 2) {
        response = 'TPL';
      }
    }

    return response;
  };

  const nextStep = () => {
    emit('onNextStep');
  };

  const disabledDate = (current) => {
    const currentDate = dayjs(current).format('YYYY-MM-DD');
    const today = dayjs(new Date()).format('YYYY-MM-DD');
    const lastDate = dayjs(filesStore.getFile.dateIn, 'YYYY-MM-DD').format('YYYY-MM-DD');

    return currentDate && (currentDate < lastDate || currentDate <= today);
  };

  const onSubmit = async () => {
    let client_id = localStorage.getItem('client_id');
    let lang = localStorage.getItem('lang');
    let quantity_temp = 0;

    if (client_id == '' || client_id == null) {
      client_id = 15766;
    }

    if (
      (!flag_SGL.value || quantity_sgl.value == 0) &&
      (!flag_DBL.value || quantity_dbl.value == 0) &&
      (!flag_TPL.value || quantity_tpl.value == 0)
    ) {
      notification.warning({
        message: t('global.message.searching_hotels'),
        description: t('global.message.room_quantity_selected_error'),
        duration: 5,
      });

      return false;
    }

    if (typeof filter.value.destiny == 'undefined') {
      notification.warning({
        message: t('global.message.searching_hotels'),
        description: t('files.message.destiny_error'),
        duration: 5,
      });

      return false;
    }

    if (typeof filter.value.date_range == 'undefined') {
      notification.warning({
        message: t('global.message.searching_hotels'),
        description: t('global.message.date_range_error'),
        duration: 5,
      });

      return false;
    }

    if (typeof filter.value.passengers == 'undefined' || filter.value.passengers.length == 0) {
      notification.warning({
        message: t('global.message.searching_hotels'),
        description: t('global.message.passengers_selected_error'),
        duration: 5,
      });

      return false;
    }

    let rooms = [];

    if (quantity_sgl.value > 0) {
      rooms.push({
        adults: 1,
        child: 0,
        ages_child: [],
        room: quantity_sgl.value,
      });

      quantity_temp += parseFloat(quantity_sgl.value);
    }

    if (quantity_dbl.value > 0) {
      rooms.push({
        adults: 2,
        child: 0,
        ages_child: [],
        room: quantity_dbl.value,
      });

      quantity_temp += parseFloat(quantity_dbl.value * 2);
    }

    if (quantity_tpl.value > 0) {
      rooms.push({
        adults: 3,
        child: 0,
        ages_child: [],
        room: quantity_tpl.value,
      });

      quantity_temp += parseFloat(quantity_tpl.value * 3);
    }

    let quantity_paxs = 0;

    filter.value.passengers.forEach((pax) => {
      if (pax.type == 'ADL') {
        quantity_paxs++;
      }
    });

    console.log(quantity_paxs, quantity_temp);

    if (filter.value.passengers.length != quantity_temp) {
      notification.warning({
        message: `Búsqueda de Hoteles`,
        description: t('global.message.accommodation_selected_error'),
        duration: 5,
      });

      return false;
    }

    let date_from = moment(filter.value.date_range[0].toDate()).format('YYYY-MM-DD');
    let date_to = moment(filter.value.date_range[1].toDate()).format('YYYY-MM-DD');

    if (date_from == date_to) {
      date_to = dayjs(date_to, 'YYYY-MM-DD').add(1, 'days').format('YYYY-MM-DD');
    }

    let params = {
      client_id: client_id,
      date_from: date_from,
      date_to: date_to,
      quantity_persons_rooms: rooms,
      destiny: {
        code: filter.value.destiny,
        label: '',
      },
      price_range: {
        min: filter.value.price ? filter.value.price[0] : '',
        max: filter.value.price ? filter.value.price[1] : '',
      },
      hotels_search_code: filter.value.search,
      lang: lang,
      quantity_rooms: rooms.length,
      typeclass_id: filter.value.category,
      zero_rates: true,
    };

    await filesStore.fetchHotels(params);
  };

  const onReset = () => {
    filter.value = {
      country: 'PE',
      price: [0, 950],
    };
    quantity_sgl.value = 0;
    quantity_dbl.value = 0;
    quantity_tpl.value = 0;
    items.value = [];
  };

  const togglePassengers = () => {
    all_passengers.value = !all_passengers.value;
    filter.value.passengers = [];

    if (all_passengers.value) {
      filter.value.passengers = filesStore.getFilePassengers.map((passenger) => passenger.id);
    }
  };
</script>

<style>
  .slick-slide {
    height: auto !important;
  }
</style>

<style scoped>
  /* For demo */
  :deep(.slick-slide) {
    text-align: center;
    height: 160px;
    line-height: 160px;
    background: #364d79;
    overflow: hidden;
  }

  :deep(.slick-arrow.custom-slick-arrow) {
    width: 25px;
    height: 25px;
    font-size: 25px;
    color: #fff;
    background-color: rgba(31, 45, 61, 0.11);
    transition: ease all 0.3s;
    opacity: 0.3;
    z-index: 1;
  }
  :deep(.slick-arrow.custom-slick-arrow:before) {
    display: none;
  }
  :deep(.slick-arrow.custom-slick-arrow:hover) {
    color: #fff;
    opacity: 0.5;
  }

  :deep(.slick-slide h3) {
    color: #fff;
  }
</style>
