<script lang="ts" setup>
  import { computed, onMounted, ref, toRef } from 'vue';
  import type { Service, ServiceDetailResponse } from '@/quotes/interfaces/services';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { getHours } from '@/quotes/helpers/get-hours';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();
  // const { quote } = useQuote();
  // Props
  interface Props {
    service: Service;
    serviceDate: Date | string;
    categoryName: string;
    flagRemarks: {
      type: Boolean;
      default: false;
    };
    section?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    section: 'default',
  });

  // Composable
  const { getServiceDetails } = useQuoteServices();

  // Service
  const service = toRef(props, 'service');
  const serviceDate = toRef(props, 'serviceDate');
  const categoryName = toRef(props, 'categoryName');
  const flagRemarks = toRef(props, 'flagRemarks');

  const name = computed(() => service.value.service_translations[0].name);
  const notes = computed(() => service.value.service_translations[0].summary);

  const serviceDetail = ref<ServiceDetailResponse>();
  onMounted(async () => {
    serviceDetail.value = await getServiceDetails(
      service.value.id,
      serviceDate.value,
      service.value.adult ?? 1,
      service.value.child ?? 0
    );
  });

  const type = computed(() => serviceDetail.value?.service_type.name);
  const type_id = computed(() => serviceDetail.value?.service_type.id);
  const reservationTime = computed(() => serviceDetail.value?.reservation_time);
  // const duration = computed(() => itinerary.value.length)
  const duration = computed(() => {
    return service.value.duration + ' ' + service.value.unit_durations.translations[0].value;
  });
  const itinerary = computed(() => {
    const daysI: {
      detail: string;
      end_time: string;
      start_time: string;
    }[][] = [];

    if (serviceDetail.value) {
      serviceDetail.value?.operations.turns.forEach((a) => {
        a.forEach((b) => {
          const dayIndex = b.day - 1;
          if (!daysI[dayIndex]) {
            daysI[dayIndex] = [];
          }

          b.detail.forEach((c) => {
            daysI[dayIndex].push(c);
          });
        });
      });
    }

    return daysI;
  });
  // const inclusions = computed(() => {
  //   const daysInc: {
  //     date: Date | string
  //     name: string
  //   }[][] = []

  //   if (serviceDetail.value) {
  //     serviceDetail.value?.inclusions.forEach((dayInc) => {
  //       daysInc.push(dayInc.include)
  //     })
  //   }

  //   return daysInc
  // })

  // })

  const inclusions = computed(() => serviceDetail.value?.inclusions[0]?.include || []);
  const notIncluded = computed(() => serviceDetail.value?.inclusions[0]?.no_include || []);
  const remarks = computed(() => serviceDetail.value?.descriptions.remarks);
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

    if (serviceDetail.value) {
      type dayKey = keyof typeof serviceDetail.value.operations.days;

      serviceDetail.value?.operations.schedule.forEach((a) => {
        Object.entries(a).forEach((entry) => {
          const [day, time] = entry;
          if (serviceDetail.value?.operations.days[day as dayKey]) {
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
</script>

<template>
  <div class="container">
    <div class="type-botton">
      <span
        v-bind:class="{
          'bg-private': type_id == 2,
          'bg-shared': type_id == 1,
          'bg-none': type_id == 3,
        }"
        >{{ type }}
      </span>
      <!-- <span class="bg-2">{{ type }}</span> -->
    </div>

    <div class="titlePopup">
      <h4>{{ name }}</h4>
      <div class="clases">
        <div class="categoria">{{ categoryName }}</div>
      </div>
    </div>
    <div class="politics-modal" v-if="(section === 'default' || section === 'notes') && notes">
      <div class="title-politics">{{ t('quote.label.notes') }}:</div>
      <ul>
        <li v-html="notes"></li>
      </ul>
    </div>
    <div
      class="politics-modal"
      v-if="(section === 'default' || section === 'remarks') && flagRemarks && remarks"
    >
      <div class="title-politics">Remarks:</div>
      <div v-html="remarks" style="font-size: 13px"></div>
    </div>
    <div class="container-flex" v-if="serviceDetail">
      <!-- Section: Itinerary -->
      <div class="item-flex-left" v-if="section === 'default' || section === 'itinerary'">
        <div v-if="section === 'default'">
          <div class="subtitle">{{ t('quote.label.operability') }}</div>

          <div class="horary">{{ t('quote.label.hour_time_system') }}</div>
          <h5>
            {{ t('quote.label.departure_time') }}:
            <span>{{ getHours(reservationTime) }}</span>
          </h5>
        </div>

        <div v-for="(day, ind) of itinerary" :key="`itinerary-day-${ind}`" class="list-transfer">
          <div class="day">{{ t('quote.label.day') }} {{ ind + 1 }}</div>
          <div
            class="item"
            v-for="(itemI, indItem) of day"
            :key="`itinerary-day-${ind}-item-${indItem}`"
          >
            <div class="item-hora">{{ getHours(itemI.start_time) }}</div>
            <div class="item-hora">
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
            </div>
            <div class="item-hora">{{ getHours(itemI.end_time) }}</div>
            <div class="item-bold">|</div>
            <div>{{ itemI.detail }}</div>
          </div>
        </div>

        <!-- Experiences & Restrictions (Only in Itinerary view) -->
        <div v-if="section === 'itinerary'" class="extra-info-section">
          <div v-if="experiences.length > 0" class="mb-4">
            <div class="subtitle mb-2">Experiences</div>
            <div class="icons">
              <span
                v-for="exp in experiences"
                :key="exp.id"
                class="experience-pill"
                :style="{ backgroundColor: exp.color, color: '#fff' }"
              >
                {{ exp.name }}
              </span>
            </div>
          </div>

          <div v-if="restrictions.length > 0">
            <div class="subtitle mb-2">Restrictions</div>
            <ul class="restrictions-list">
              <li v-for="(res, idx) in restrictions" :key="idx">{{ res.description || res }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="item-flex-right" v-if="section !== 'itinerary'">
        <div class="item" v-if="section === 'default'">
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

        <!-- Section: Inclusions -->
        <template v-if="section === 'default' || section === 'inclusions'">
          <div class="item title">
            <p>{{ t('quote.label.include') }}</p>

            <div class="icons">
              <span
                v-for="(item, ind) of inclusions"
                :key="`inclusions-day-${ind}-item-${ind}`"
                class="pill-dark"
              >
                {{ item.name }}
              </span>
            </div>
          </div>

          <div class="item title" v-if="notIncluded.length > 0">
            <p>{{ t('quote.label.not_include') }}</p>

            <div class="icons">
              <span
                v-for="(item, ind) of notIncluded"
                :key="`not-inclusions-day-${ind}-item-${ind}`"
                class="pill-dark"
              >
                {{ item.name }}
              </span>
            </div>
          </div>
        </template>

        <!-- Section: Schedule -->
        <div class="item title" v-if="section === 'default' || section === 'schedule'">
          <div v-if="section === 'schedule'" class="mb-4">
            <div class="subtitle">{{ t('quote.label.operability') }}</div>
            <div class="horary">{{ t('quote.label.hour_time_system') }}</div>
            <h5>
              {{ t('quote.label.departure_time') }}:
              <span>{{ getHours(reservationTime) }}</span>
            </h5>
          </div>

          <p class="p-title">{{ t('quote.label.availability') }}</p>
          <ul class="availability">
            <li>
              <div>
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

              <div>{{ t('quote.label.days') }}:</div>

              <div>{{ t('quote.label.schedule') }}:</div>
            </li>

            <li v-for="(avail, availInd) of availability">
              <span>{{ availInd + 1 }}</span>
              <span>{{ avail.days }}</span>
              <span>{{ getHours(avail.time) }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .container {
    display: flex;
    flex-direction: column;
    padding: 0 20px;
    gap: 45px;

    .type-botton {
      position: absolute;
      right: 120px;
      top: 0;
      display: flex;
      justify-content: flex-end;
      align-items: flex-end;
      gap: 40px;

      span {
        border-radius: 0px 0px 6px 6px;
        padding: 13px 18px;
        color: #fff;
      }
    }

    p {
      margin: 0;
    }
  }

  .titlePopup {
    display: flex;
    flex-direction: column;
    padding: 31px 0 0 0;
  }

  h4 {
    font-size: 36px;
    font-style: normal;
    font-weight: 400;
    line-height: 43px; /* 119.444% */
    letter-spacing: -0.36px;
    color: #212529;
    margin: 0;
  }

  .clases {
    display: flex;
    align-items: center;
    padding: 15px 0 0 0;
    gap: 10px;

    .categoria {
      display: flex;
      height: 27px;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background: #4ba3b2;
      border-radius: 6px;
      color: #fff;
      padding: 10px;
      width: 160px;
    }

    .estrellas {
      display: flex;
      gap: 10px;

      .item {
        display: flex;
        width: 21px;
        height: 21px;
        justify-content: center;
        align-items: center;
      }
    }
  }

  .item-flex-left {
    gap: 15px;
  }

  .list-transfer {
    display: flex;
    flex-direction: column;
    align-self: stretch;

    .day {
      color: #eb5757;
      font-weight: 700;
      font-size: 18px;
      margin-bottom: 5px;
    }

    .item {
      display: flex;
      gap: 10px;
      padding: 0 0 0 12px;
      font-size: 18px;
      font-style: normal;
      font-weight: 400;
      line-height: 25px; /* 138.889% */
      letter-spacing: -0.18px;
      margin-bottom: 10px;

      .item-hora {
        color: #eb5757;
      }

      .item-bold {
        color: #0d0d0d;
        font-weight: 400;
      }
    }
  }

  .horary {
    color: #979797;
    font-size: 12px;
    font-style: normal;
    font-weight: 400;
    line-height: 19px; /* 158.333% */
    letter-spacing: 0.18px;
  }

  .subtitle {
    color: #212529;
    font-size: 24px;
    font-style: normal;
    font-weight: 700;
    margin-bottom: 0;
    line-height: 31px;
    letter-spacing: -0.24px;
  }

  h5 {
    color: #0d0d0d;
    font-size: 16px;
    font-style: normal;
    font-weight: 600;
    line-height: 23px; /* 143.75% */
    letter-spacing: -0.24px;
    margin: 0;

    span {
      font-weight: 400;
      margin-left: 4px;
    }
  }

  .type-botton {
    position: absolute;
    right: 120px;
    top: 0;
    display: flex;
    justify-content: flex-end;
    align-items: flex-end;
    gap: 40px;

    span {
      border-radius: 0px 0px 6px 6px;
      padding: 13px 18px;
      color: #fff;
    }
  }

  .bg-1 {
    background: #eb5757;
  }

  .bg-2 {
    background: #ffb001;
  }

  .container-flex {
    a {
      color: #eb5757;
      font-size: 16px;
      font-style: normal;
      font-weight: 500;
      line-height: 23px; /* 143.75% */
      letter-spacing: -0.24px;

      svg {
        vertical-align: middle;
      }

      span {
        position: relative;

        &:before {
          content: '';
          position: absolute;
          left: 0;
          right: 0;
          bottom: -3px;
          height: 1.5px;
          background: #eb5757;
          border-radius: 2px;
        }
      }
    }

    .item-flex-right {
      div {
        display: flex;
        align-items: center;
        gap: 10px;
        align-self: stretch;
        font-weight: 400;
        font-size: 18px;
        color: #0d0d0d;

        &.title {
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          gap: 6px;
          align-self: stretch;
          padding: 0;
        }

        p {
          font-weight: 700;
          margin-bottom: 5px;
          color: #eb5757;
          font-size: 18px;
        }

        .p-title {
          color: #212529;
          font-size: 18px;
        }

        span {
          color: #eb5757;
        }
      }

      .icons {
        display: flex;
        align-items: flex-start;
        gap: 9px 15px;
        flex-wrap: wrap;

        span {
          border-radius: 6px;
          background: #e9e9e9;
          color: #0d0d0d;
          text-align: center;
          font-size: 14px;
          font-style: normal;
          font-weight: 400;
          line-height: 21px; /* 150% */
          letter-spacing: 0.21px;
          padding: 6px 11px;
        }
      }

      ul {
        list-style: none;
        margin: 0;
        padding: 0;
        font-weight: 400;

        &.availability {
          display: flex;
          gap: 8px;
          flex-direction: column;
          width: 100%;

          li {
            display: flex;
            /*flex-direction: column;*/
            align-items: flex-start;
            gap: 5px;
            color: #737373;
            font-size: 18px;
            font-style: normal;
            font-weight: 700;
            line-height: 25px; /* 138.889% */
            letter-spacing: -0.18px;

            div {
              width: 63%;
              font-weight: 700;
              color: #737373;

              &:first-child {
                width: 7%;
              }

              &:last-child {
                width: 30%;
              }
            }

            &.txt-left {
              align-items: flex-start;
            }

            &.txt-left-margin {
              margin-right: 14px;
            }

            span {
              color: #0d0d0d;
              width: 63%;
              font-size: 14px;
              font-style: normal;
              font-weight: 400;
              line-height: 21px; /* 150% */
              letter-spacing: -0.21px;
              text-align: left;

              &:first-child {
                width: 7%;
                text-align: center;
                padding-top: 1px;
              }

              &:last-child {
                width: 30%;
              }
            }
          }
        }
      }
    }
  }

  .bold {
    font-weight: 600 !important;
  }

  .pill-dark {
    background: #0d0d0d !important;
    color: #fff !important;
  }

  .experience-pill {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
    margin-right: 8px;
    margin-bottom: 8px;
  }

  .extra-info-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
  }

  .restrictions-list {
    list-style: disc;
    padding-left: 20px;

    li {
      margin-bottom: 5px;
      color: #666;
    }
  }

  .mb-4 {
    margin-bottom: 1.5rem;
  }
  .mb-2 {
    margin-bottom: 0.5rem;
  }
</style>
