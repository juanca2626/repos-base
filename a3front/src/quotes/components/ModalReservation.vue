<script lang="ts" setup>
  import { toRef, watchEffect } from 'vue';
  import AmountComponent from '@/quotes/components/global/AmountComponent.vue';
  import type { PassengerAgeChild, Person } from '@/quotes/interfaces';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  interface Props {
    people: Person;
    quoteChildAges: PassengerAgeChild[];
    accommodation_p: '';
  }

  const props = defineProps<Props>();

  const people = toRef(props, 'people');
  const quoteChildAges = toRef(props, 'quoteChildAges');
  const accommodation_p = toRef(props, 'accommodation_p');

  const setQuotePassenger = (type: string, value: number) => {
    switch (type) {
      case 'adults':
        if (value > 0) {
          people.value.adults = value;
        }
        break;
      case 'child':
        if (value > -1) {
          people.value.child = value;
        }
        break;
      case 'infant':
        people.value.infant = value;
        break;
    }
  };

  const setQuotePassengerChild = (ageIndex: number, value: number) => {
    if (value > 0) {
      quoteChildAges.value[ageIndex].age = value;
    }
  };

  watchEffect(() => {
    if (people.value.child > quoteChildAges.value.length) {
      for (let i = people.value.child - quoteChildAges.value.length; i > 0; i--) {
        quoteChildAges.value.push({
          age: 1,
          quote_id: people.value.id,
        });
      }
    } else if (people.value.child < quoteChildAges.value.length) {
      for (let i = quoteChildAges.value.length - people.value.child; i > 0; i--) {
        quoteChildAges.value.pop();
      }
    }
  });
</script>
<template>
  <h3 class="title">{{ t('quote.label.book_quote') }}</h3>
  <div class="body">
    <a-alert
      message=""
      :description="t('quote.label.reserve_rangos_have')"
      type="warning"
      show-icon
    />
    <p class="text-adicion">
      {{ t('quote.label.with_reservation_passengers') }}
    </p>

    <div class="box-passengers">
      <div class="top">
        <div class="block">
          <div class="input">
            <label>{{ t('quote.label.adults') }}</label>
            <div class="box">
              <AmountComponent
                :amount="people?.adults"
                :min="1"
                :max="30"
                @change="(value) => setQuotePassenger('adults', value)"
              />
            </div>
            <input name="adulto" type="number" />
          </div>
        </div>
        <div class="block">
          <div class="input">
            <label>{{ t('quote.label.child') }}</label>
            <div class="box">
              <AmountComponent
                :amount="people?.child"
                :min="0"
                :max="40"
                @change="(value) => setQuotePassenger('child', value)"
              />
            </div>
            <input name="kids" type="number" />
          </div>
        </div>
        <div class="block" v-if="false">
          <div class="input">
            <label>{{ t('quote.label.infants') }}</label>
            <div class="box">
              <AmountComponent
                :amount="people?.infant"
                :min="0"
                :max="17"
                @change="(value) => setQuotePassenger('infant', value)"
              />
            </div>
            <input name="babies" type="number" />
          </div>
        </div>
      </div>
      <div class="bottom" v-for="(age, ind) of quoteChildAges" :key="ind">
        <span>{{ t('quote.label.child_age') }} {{ ind + 1 }}</span>
        <div class="block">
          <div class="input">
            <div class="box">
              <AmountComponent
                :amount="age.age"
                @change="(value) => setQuotePassengerChild(ind, value)"
              />
            </div>
            <input name="babies-1" type="number" />
          </div>
        </div>
      </div>
    </div>

    <div class="rooms-form" style="position: relative !important">
      <div class="input">
        <label>{{ t('quote.label.rooms') }}</label>

        <div class="boxes">
          <div class="box">
            <span>SGL</span>
            <AmountComponent
              v-model:amount="accommodation_p.single"
              @change="(value) => (accommodation_p.single = value)"
            />
            <input class="inputBox" name="box-1" type="text" />
          </div>

          <div class="box">
            <span>DBL</span>
            <AmountComponent
              v-model:amount="accommodation_p.double"
              @change="(value) => (accommodation_p.double = value)"
            />
            <input class="inputBox" name="box-1" type="text" />
          </div>

          <div class="box">
            <span>TRL</span>
            <AmountComponent
              v-model:amount="accommodation_p.triple"
              @change="(value) => (accommodation_p.triple = value)"
            />
            <input class="inputBox" name="box-1" type="text" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
  .modal-body {
    .body {
      max-height: 60vh;
      overflow: auto;
      /* width */
      &::-webkit-scrollbar {
        width: 5px;
        margin-right: -14px;
        right: 10px;
        padding-right: 2px;
        position: absolute;
      }

      /* Track */
      &::-webkit-scrollbar-track {
        border-radius: 10px;
        width: 5px;
      }

      /* Handle */
      &::-webkit-scrollbar-thumb {
        border-radius: 4px;
        background: #c4c4c4;
        margin-right: 4px;
        width: 5px;
      }

      /* Handle on hover */
      &::-webkit-scrollbar-thumb:hover {
        background: #eb5757;
      }
    }
  }

  .text-adicion {
    width: 90%;
    margin: 0 auto;
    text-align: center;
    color: #4f4b4b;
    line-height: 21px;
    font-weight: 500;
    font-size: 18px;
    margin-bottom: 10px;
  }

  h3.title {
    margin: 10px 30px 10px 0 !important;
  }

  .rooms-form {
    width: 100%;
    display: block;
    top: 0;
    box-shadow: none;
    margin-bottom: 30px;
    padding: 12px 0 0 0;

    .box {
      width: 33.333%;

      &:nth-child(2n) {
        justify-content: center;
      }

      &:last-child {
        justify-content: flex-end;
      }

      input.inputBox {
        display: none !important;
      }

      .amountN {
        width: 25px;
      }
    }

    input {
      display: none;
    }

    label {
      color: #575757;
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 21px;
      letter-spacing: 0.21px;
      align-self: stretch;
      text-transform: capitalize;
    }
  }

  .box-passengers {
    display: block;
    padding: 12px 0;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    /*border-radius: 0 0 6px 6px;*/
    background: #ffffff;
    /*box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);*/
    width: 100%;
    // position: absolute;
    // top: 35px;

    .box {
      display: flex;
      align-items: center;
      border: 1px solid #c4c4c4;
      border-radius: 4px;
      gap: 8px;
      width: 100%;
      justify-content: center;
      align-items: center;

      span {
        color: #575757;
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 21px;
        letter-spacing: 0.21px;
      }

      .ant-input-number {
        width: 80%;

        input {
          text-align: left;
          padding: 0 15px;
          box-sizing: border-box;
        }
      }

      input {
        border: none;
        width: 100%;
      }
    }

    .top {
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    .bottom {
      display: flex;
      align-items: center;
      gap: 10px;

      span {
        color: #4f4b4b;
        font-size: 13px;
        font-style: normal;
        font-weight: 500;
        line-height: 21px;
        letter-spacing: 0.21px;
        text-align: right;
        width: 50%;
      }

      .block {
        display: flex;
        height: 45px;
        padding: 5px 0;
        align-items: center;
        gap: 16px;
        border-radius: 4px;
        background: #ffffff;
        width: 50%;
        padding-right: 0;
      }
    }

    .block {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 50%;

      .input {
        display: flex;
        padding: 0 1px;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        width: 100%;

        label {
          color: #575757;
          font-size: 14px;
          font-style: normal;
          font-weight: 500;
          line-height: 21px;
          letter-spacing: 0.21px;
          align-self: stretch;
        }

        input {
          visibility: hidden;
          height: 0;
          width: 0;
        }
      }
    }
  }
</style>
