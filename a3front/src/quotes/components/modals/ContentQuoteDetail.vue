<script lang="ts" setup>
  import { computed, onMounted, ref, toRef } from 'vue';
  import type { Service, ServiceDetailResponse } from '@/quotes/interfaces/services';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import { storeToRefs } from 'pinia';
  import { getHours } from '@/quotes/helpers/get-hours';
  import { useI18n } from 'vue-i18n';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';
  import defaultImage from '@/images/quotes/1.png';

  const { t } = useI18n();

  // Props
  interface Props {
    service: Service;
    serviceDate: Date | string;
    categoryName: string;
    flagRemarks?: boolean;
    section?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    section: 'default',
    flagRemarks: true,
  });

  // Composable
  const { getServiceDetails } = useQuoteServices();
  const quoteStore = useQuoteStore();
  const { processing } = storeToRefs(quoteStore);

  // Service
  const service = toRef(props, 'service');
  const serviceDate = toRef(props, 'serviceDate');
  const categoryName = toRef(props, 'categoryName');
  const flagRemarks = toRef(props, 'flagRemarks');

  const name = computed(() => service.value.service_translations[0].name);
  const notes = computed(() => service.value.service_translations[0].summary);

  const galleryImages = computed(() => {
    const galleries = service.value.galleries;
    console.log('Images: ', galleries);

    if (galleries && galleries.length > 0) {
      return galleries.map((img: any) => {
        if (typeof img === 'string') return { url: img };
        return { url: img.url || img.path || '' };
      });
    }
    return [{ url: defaultImage }];
  });

  const serviceDetail = ref<ServiceDetailResponse>();

  onMounted(async () => {
    try {
      serviceDetail.value = await getServiceDetails(
        service.value.id,
        serviceDate.value,
        service.value.adult ?? 1,
        service.value.child ?? 0
      );

      console.log(service.value, serviceDetail.value);
      // Hack to force carousel resize calculation
      setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 500);
    } catch (e) {
      console.error('Error fetching service details', e);
    } finally {
      quoteStore.setProcessing(false);
    }
  });

  //const type = computed(() => serviceDetail.value?.service_type.name);
  //const type_id = computed(() => serviceDetail.value?.service_type.id);
  const reservationTime = computed(() => serviceDetail.value?.reservation_time);

  const duration = computed(() => {
    if (!service.value.duration) return '';
    return (
      service.value.duration + ' ' + (service.value.unit_durations?.translations?.[0]?.value || '')
    );
  });

  const itinerary = computed(() => {
    const daysI: {
      detail: string;
      end_time: string;
      start_time: string;
      itinerary: string;
    }[][] = [];

    if (serviceDetail.value?.operations?.turns) {
      serviceDetail.value.operations.turns.forEach((a) => {
        a.forEach((b: any) => {
          const dayIndex = b.day - 1;
          if (!daysI[dayIndex]) {
            daysI[dayIndex] = [];
          }

          if (b.detail.length > 0) {
            b.detail.forEach((c: any) => {
              c.itinerary = serviceDetail.value?.descriptions?.itinerary[dayIndex].description;
              daysI[dayIndex].push(c);
            });
          } else {
            daysI[dayIndex].push({
              detail: '',
              itinerary: serviceDetail.value?.descriptions?.itinerary[dayIndex].description ?? '',
              start_time: '',
              end_time: '',
            });
          }
        });
      });
    }

    return daysI;
  });

  const inclusions = computed(() => serviceDetail.value?.inclusions?.[0]?.include || []);
  const notIncluded = computed(() => serviceDetail.value?.inclusions?.[0]?.no_include || []);
  const remarks = computed(() => serviceDetail.value?.descriptions?.remarks);
  const restrictions = computed(() => serviceDetail.value?.restrictions || []);
  const experiences = computed(() => serviceDetail.value?.experiences || []);

  const availability = computed<
    {
      time: string;
      days: string;
    }[]
  >(() => {
    const schedule: {
      [key: string]: {
        time: string;
        days: string[];
      };
    } = {};

    if (serviceDetail.value?.operations?.schedule) {
      type dayKey = keyof typeof serviceDetail.value.operations.days;

      serviceDetail.value.operations.schedule.forEach((a) => {
        Object.entries(a).forEach((entry) => {
          const [day, time] = entry;
          // Verify if the day is enabled in operations.days
          if (
            serviceDetail.value?.operations.days &&
            serviceDetail.value.operations.days[day as dayKey]
          ) {
            if (!schedule[time]) {
              schedule[time] = {
                time: time,
                days: [],
              };
            }
            schedule[time].days.push(day);
          }
        });
      });
    }

    return Object.values(schedule).map((i) => {
      return {
        time: i.time,
        days: i.days.length == 7 ? t('quote.label.every_day') : i.days.join(', '),
      };
    });
  });

  // Helper to determine if we should show a specific block
  const showBlock = (blockName: string) => {
    return props.section === 'default' || props.section === blockName;
  };
</script>

<template>
  <div class="content-quote-detail" id="quotes-layout">
    <!-- Title and Category Header (Only show in default or if meaningful for the section) -->
    <div class="header-section">
      <!-- div
        class="type-badge"
        :class="{
          'bg-private': type_id == 2,
          'bg-shared': type_id == 1,
          'bg-none': type_id == 3,
        }"
      >
        {{ type }}
      </div -->

      <div class="title-container">
        <h4>{{ name }}</h4>
        <div class="category-badge">{{ categoryName }}</div>
      </div>
    </div>

    <div class="content-body" v-if="processing">
      <LoadingSkeleton rows="3" />
    </div>

    <div v-else class="content-body">
      <!-- MAIN CONTENT FLEX -->
      <div
        class="gallery-container w-100 mb-4"
        v-if="(showBlock('itinerary') || showBlock('schedule')) && galleryImages.length > 2"
      >
        <a-row :gutter="[12, 12]">
          <a-col
            v-for="(image, index) in galleryImages"
            :key="index"
            :span="galleryImages.length === 1 ? 24 : galleryImages.length === 2 ? 12 : 8"
          >
            <div class="image-wrapper grid-image">
              <img :src="image.url" class="w-100" />
            </div>
          </a-col>
        </a-row>
      </div>
      <div
        class="detail-flex-container"
        v-if="showBlock('itinerary') || showBlock('schedule') || showBlock('default')"
      >
        <div class="left-column" v-if="showBlock('itinerary') || showBlock('schedule')">
          <!-- Itinerary List -->
          <div
            class="gallery-container w-100 mb-4"
            v-if="(showBlock('itinerary') || showBlock('schedule')) && galleryImages.length <= 2"
          >
            <div class="image-wrapper">
              <img :src="galleryImages[0].url" class="w-100" />
            </div>
          </div>
          <div v-if="showBlock('schedule') || showBlock('itinerary')">
            <div
              v-if="itinerary"
              v-for="(day, ind) of itinerary"
              :key="`itinerary-day-${ind}`"
              class="itinerary-day"
            >
              <div
                class="day-label text-uppercase"
                v-if="
                  (day.some((d: any) => d.detail) && showBlock('schedule')) || itinerary.length > 1
                "
              >
                {{ t('quote.label.day') }} {{ ind + 1 }}
              </div>
              <div
                v-html="day[ind].itinerary"
                class="clear-text"
                v-if="showBlock('itinerary')"
              ></div>
              <template
                v-for="(itemI, indItem) of day"
                :key="`itinerary-day-${ind}-item-${indItem}`"
              >
                <template v-if="itemI.detail">
                  <div v-if="showBlock('schedule')" class="itinerary-item">
                    <div class="time-range">
                      <span class="text-danger">{{ getHours(itemI.start_time) }}</span>
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="13"
                        height="12"
                        viewBox="0 0 13 12"
                        fill="none"
                      >
                        <path
                          d="M8.5076 5.10711H1.76786C1.61993 5.10711 1.5 5.22704 1.5 5.37497V6.62496C1.5 6.77289 1.61993 6.89282 1.76786 6.89282H8.5076V7.92092C8.5076 8.3982 9.08463 8.63722 9.42213 8.29974L11.3431 6.37878C11.5523 6.16956 11.5523 5.83037 11.3431 5.62117L9.42213 3.70021C9.08465 3.36274 8.5076 3.60175 8.5076 4.07903V5.10711Z"
                          fill="#EB5757"
                        />
                      </svg>
                      <span class="text-danger">{{ getHours(itemI.end_time) }}</span>
                    </div>
                    <div class="separator">|</div>
                    <div class="description m-0">{{ itemI.detail }}</div>
                  </div>
                </template>
              </template>
            </div>
          </div>

          <!-- Departure Time (Default/Schedule view) -->
          <div v-if="reservationTime && showBlock('schedule')" class="mb-4">
            <div class="subtitle">{{ t('quote.label.operability') }}</div>
            <div class="horary-label">{{ t('quote.label.hour_time_system') }}</div>
            <div class="departure-time">
              {{ t('quote.label.departure_time') }}: <span>{{ getHours(reservationTime) }}</span>
            </div>
          </div>

          <!-- Experiences & Restrictions -->
          <div v-if="showBlock('itinerary')" class="extra-info-section">
            <div v-if="experiences.length > 0" class="mb-4">
              <div class="subtitle mb-2">{{ t('quote.label.experiences') }}</div>
              <div class="pill-container">
                <span
                  v-for="exp in experiences"
                  :key="exp.id"
                  class="experience-pill"
                  :style="{ backgroundColor: exp.color ?? '#EB5757', color: '#fff' }"
                >
                  {{ exp.name }}
                </span>
              </div>
            </div>

            <div v-if="restrictions.length > 0">
              <div class="subtitle mb-2">{{ t('quote.label.restrictions') }}</div>
              <ul class="restrictions-list">
                <li v-for="(res, idx) in restrictions" :key="idx">
                  {{ res.description || res.name }}
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div
          class="right-column"
          v-if="
            showBlock('default') ||
            showBlock('inclusions') ||
            showBlock('schedule') ||
            (showBlock('itinerary') && galleryImages.length === 2)
          "
        >
          <!-- Duration -->
          <div class="info-item" v-if="showBlock('default') && duration">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="25"
              viewBox="0 0 24 25"
              fill="none"
            >
              <path
                d="M12 22.5C17.5228 22.5 22 18.0228 22 12.5C22 6.97715 17.5228 2.5 12 2.5C6.47715 2.5 2 6.97715 2 12.5C2 18.0228 6.47715 22.5 12 22.5Z"
                stroke="#212529"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M12 6.5V12.5L16 14.5"
                stroke="black"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <b>{{ t('quote.label.duration') }}:</b> {{ duration }}
          </div>

          <!-- Inclusions / Exclusions -->
          <template v-if="showBlock('inclusions')">
            <div class="info-item-block" v-if="inclusions.length">
              <p class="section-label">{{ t('quote.label.include') }}</p>
              <div class="pill-container">
                <span v-for="(item, ind) of inclusions" :key="`inc-${ind}`" class="pill-dark">
                  {{ item.name }}
                </span>
              </div>
            </div>

            <div class="info-item-block" v-if="notIncluded.length > 0">
              <p class="section-label">{{ t('quote.label.not_include') }}</p>
              <div class="pill-container">
                <span v-for="(item, ind) of notIncluded" :key="`not-inc-${ind}`" class="pill-dark">
                  {{ item.name }}
                </span>
              </div>
            </div>
          </template>

          <!-- Availability / Schedule -->
          <div class="info-item-block" v-if="showBlock('schedule')">
            <p class="section-label">{{ t('quote.label.availability') }}</p>
            <ul class="availability-list">
              <li class="header-row">
                <div class="icon-col">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="21"
                    viewBox="0 0 20 21"
                    fill="none"
                  >
                    <path
                      d="M18.3346 9.7333V10.5C18.3336 12.297 17.7517 14.0455 16.6757 15.4848C15.5998 16.9241 14.0874 17.977 12.3641 18.4866C10.6408 18.9961 8.79902 18.9349 7.11336 18.3121C5.4277 17.6894 3.98851 16.5384 3.01044 15.0309C2.03236 13.5233 1.56779 11.74 1.68603 9.9469C1.80427 8.15377 2.49897 6.44691 3.66654 5.08086C4.8341 3.71482 6.41196 2.76279 8.16479 2.36676C9.91763 1.97073 11.7515 2.15192 13.393 2.8833"
                      stroke="#1ED790"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M18.3333 3.83325L10 12.1749L7.5 9.67492"
                      stroke="#1ED790"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </div>
                <div class="days-col">{{ t('quote.label.days') }}:</div>
                <div class="time-col">{{ t('quote.label.schedule') }}:</div>
              </li>
              <li v-for="(avail, availInd) of availability" :key="availInd" class="data-row">
                <span class="index-col">{{ availInd + 1 }}</span>
                <span class="days-val">{{ avail.days }}</span>
                <span class="time-val">{{ getHours(avail.time) }}</span>
              </li>
            </ul>
          </div>

          <div
            class="gallery-container w-100 mb-4"
            v-if="showBlock('itinerary') && galleryImages.length === 2"
          >
            <div class="image-wrapper">
              <img :src="galleryImages[1].url" class="w-100" />
            </div>
          </div>
        </div>
      </div>

      <a-tabs>
        <a-tab-pane
          key="notes"
          :tab="t('quote.label.notes')"
          v-if="showBlock('notes') && notes"
          style="height: 200px"
        >
          <div class="info-block m-0">
            <div class="block-content" v-html="notes"></div>
          </div>
        </a-tab-pane>
        <a-tab-pane
          key="remarks"
          tab="Remarks"
          v-if="showBlock('remarks') && flagRemarks && remarks"
        >
          <div class="info-block m-0">
            <div class="block-content" v-html="remarks"></div>
          </div>
        </a-tab-pane>
      </a-tabs>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  :deep(.ant-tabs-tabpane) {
    height: auto !important;
  }

  :deep(.ant-tabs-tab-btn) {
    font-weight: 700;
    text-transform: uppercase;
  }

  .content-quote-detail {
    display: flex;
    flex-direction: column;
    gap: 20px;
    color: #212529;
  }

  .header-section {
    position: relative;
    margin-right: 40px;

    .type-badge {
      position: absolute;
      right: 0;
      top: -20px;
      padding: 8px 16px;
      border-radius: 0 0 6px 6px;
      color: #fff;
      font-size: 14px;
      font-weight: 500;
    }
  }

  .bg-private {
    background: #4ba3b2;
  } /* Example color, adjust if needed */
  .bg-shared {
    background: #eb5757;
  }
  .bg-none {
    background: #999;
  }

  .title-container {
    display: flex;
    flex-direction: column;
    gap: 10px;

    h4 {
      font-size: 28px;
      font-weight: 600;
      margin: 0;
      color: #212529;
    }

    .category-badge {
      background: #4ba3b2;
      color: #fff;
      padding: 6px 16px;
      border-radius: 6px;
      display: inline-block;
      width: fit-content;
      font-size: 14px;
    }
  }

  .content-body {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .clear-text {
    margin-bottom: 1em;

    * {
      font-size: 14px !important;
      background-color: transparent !important;
      line-height: 1.3em !important;
      // color: #555;
    }

    :deep(p) {
      margin: 0 !important;
    }
  }

  .info-block {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 2px dashed #cccc;
    border-left: 4px solid #4ba3b2;

    .block-title {
      font-weight: 700;
      font-size: 16px;
      margin-bottom: 8px;
      color: #4ba3b2;
      text-transform: uppercase;
    }

    .block-content {
      * {
        font-size: 13px !important;
        background-color: transparent !important;
        // color: #555;
      }

      :deep(p) {
        margin: 0 !important;
      }
    }
  }

  .detail-flex-container {
    display: flex;
    gap: 30px;

    .left-column,
    .right-column {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .right-column {
      position: sticky;
      top: 10px;
      height: fit-content;
    }
  }

  .subtitle {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 5px;
    color: #212529;
  }

  .horary-label {
    font-size: 12px;
    color: #999;
    margin-bottom: 5px;
  }

  .departure-time {
    font-weight: 600;
    span {
      font-weight: 400;
      margin-left: 5px;
    }
  }

  .itinerary-day {
    margin-bottom: 20px;

    .day-label {
      color: #eb5757;
      font-weight: 700;
      font-size: 16px;
      margin-bottom: 8px;
    }

    .itinerary-item {
      display: flex;
      gap: 10px;
      align-items: flex-start;
      margin-bottom: 8px;
      font-size: 14px;

      .time-range {
        display: flex;
        align-items: center;
        gap: 3px;

        > span {
          display: block;
          width: 48px;
          text-align: center;
        }
      }

      .text-danger {
        color: #eb5757;
      }

      .separator {
        color: #ccc;
      }

      .description {
        font-size: 14px;
        color: #333;
        text-align: left !important;
      }
    }
  }

  .carousel-img {
    width: 100%;
    height: 350px;
    object-fit: cover;
    display: block;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .image-wrapper {
    display: block;
    width: 100%;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

    &.grid-image {
      height: 220px;
    }

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;

      &:hover {
        transform: scale(1.05);
      }
    }
  }

  /* Custom arrows for the carousel */
  :deep(.slick-arrow.custom-slick-arrow) {
    width: 30px;
    height: 30px;
    font-size: 30px;
    color: #fff;
    background-color: rgba(31, 45, 61, 0.11);
    transition: ease all 0.3s;
    opacity: 0.3;
    z-index: 10;
    display: flex !important;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
  }

  :deep(.slick-arrow.custom-slick-arrow:before) {
    display: none;
  }

  :deep(.slick-arrow.custom-slick-arrow:hover) {
    color: #fff;
    opacity: 0.8;
    background-color: rgba(31, 45, 61, 0.5);
  }

  .gallery-static {
    width: 100%;
    margin-bottom: 20px;
  }

  .info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
  }

  .section-label {
    font-weight: 700;
    color: #eb5757;
    margin-bottom: 10px;
    font-size: 16px;
  }

  .pill-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .pill-dark {
    background: #333;
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 13px;
  }

  .experience-pill {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
  }

  .restrictions-list {
    padding-left: 20px;
    color: #666;
    font-size: 14px;
  }

  .availability-list {
    list-style: none;
    padding: 0;
    margin: 0;
    width: 100%;

    li {
      display: flex;
      align-items: center;
      padding: 8px 0;
      border-bottom: 1px solid #eee;

      &.header-row {
        font-weight: 700;
        color: #777;
        border-bottom: 2px solid #ddd;
      }

      .icon-col,
      .index-col {
        width: 40px;
        text-align: center;
      }

      .days-col,
      .days-val {
        flex: 2;
      }

      .time-col,
      .time-val {
        flex: 1;
      }
    }
  }

  .mb-2 {
    margin-bottom: 8px;
  }
  .mb-4 {
    margin-bottom: 16px;
  }
</style>
