<script lang="ts" setup>
  import AmountComponent from '@/quotes/components/global/AmountComponent.vue';
  import { ref, toRef, computed, watchEffect } from 'vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';
  import { storeToRefs } from 'pinia';
  import { useQuoteStore } from '@/quotes/store/quote.store';

  const { t } = useI18n();

  interface Emits {
    (e: 'change', value: number): void;
  }

  const emits = defineEmits<Emits>();

  interface Props {
    show: boolean;
  }

  const props = withDefaults(defineProps<Props>(), {
    show: false,
  });

  const show = toRef(props, 'show');

  // const show_occupations = inject('show-occupations');

  const { people, quoteChildAges } = useQuote();

  const quoteStore = useQuoteStore();
  const { showPassengersForm } = storeToRefs(quoteStore);

  const adults = ref<number>(people.value.adults);
  const child = ref<number>(people.value.child);
  const childAges = ref<[]>([...quoteChildAges.value]);

  const setPassenger = async (type: string, value: number) => {
    switch (type) {
      case 'adults':
        adults.value = value;
        //  await setQuotePassengers('adults', value)
        break;
      case 'child':
        child.value = value;

        if (childAges.value.length < child.value) {
          for (var i = 0; i < child.value; i++) {
            childAges.value.push({
              age: '1',
              id: null,
              quote_id: null,
            });
          }
        }

        if (child.value < childAges.value.length) {
          childAges.value.splice(child.value, childAges.value.length);
        }

        //  await setQuotePassengers('child', value)
        break;
    }
  };

  const setChildAge = async (ageIndex: number, value: number) => {
    childAges.value[ageIndex].age = value;
  };

  const showAccommodation = async () => {
    let year_chlilds = true;
    childAges.value.forEach((element) => {
      if (parseInt(element.age) < 1) {
        year_chlilds = false;
      }
    });

    if (year_chlilds == false) {
      return false;
    }

    // show_occupations.value = true
    emits('change', true, adults.value, child.value, childAges.value);
  };

  watchEffect(() => {
    if (show.value == false) {
      adults.value = people.value.adults;
      child.value = people.value.child;
      childAges.value = [...quoteChildAges.value];
      quoteStore.showPassengersForm = false;
    }
  });

  const computedShow = computed(() => show.value !== showPassengersForm.value);
</script>

<template>
  <div v-if="computedShow" class="box-passengers">
    <div class="top">
      <div class="block">
        <div class="input">
          <label>{{ t('quote.label.adults') }}</label>
          <div class="box">
            <AmountComponent
              :amount="adults"
              :min="1"
              :max="30"
              @change="(value) => setPassenger('adults', value)"
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
              :amount="child"
              :min="0"
              :max="10"
              @change="(value) => setPassenger('child', value)"
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
              :max="40"
              @change="(value) => setPassenger('infant', value)"
            />
          </div>
          <input name="babies" type="number" />
        </div>
      </div>
    </div>
    <template v-for="(age, ind) of childAges" :key="ind">
      <div class="bottom" :class="{ child_error: childAges[ind].age == 0 }">
        <span>{{ t('quote.label.child_age') }} {{ ind + 1 }}</span>
        <div class="block">
          <div class="input">
            <div class="box">
              <AmountComponent
                :amount="age.age"
                :min="0"
                :max="17"
                @change="(value) => setChildAge(ind, value)"
              />
            </div>
            <input name="babies-1" type="number" />
          </div>
        </div>
      </div>
    </template>

    <div class="bottom widthFull">
      <button class="ok normal button-component" @click="showAccommodation()">
        {{ t('quote.label.save') }}
      </button>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .child_error {
    .box {
      border: 1px solid red !important;

      :deep(.amountN) {
        color: red !important;
      }
    }
  }

  .box-passengers {
    display: inline-flex;
    padding: 12px 16px;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    border-radius: 0 0 6px 6px;
    background: #ffffff;
    box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
    width: fit-content;
    position: absolute;
    top: 35px;

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

      input {
        border: none;
        width: 100%;
      }

      :deep(.ant-input-number) {
        input {
          text-align: center;
          padding: 0;
        }
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
      gap: 0;

      span {
        color: #4f4b4b;
        font-size: 14px;
        font-style: normal;
        font-weight: 500;
        line-height: 21px;
        letter-spacing: 0.21px;
        text-align: right;
        width: 100px;
        margin-top: -7px;
      }

      .block {
        display: flex;
        height: 45px;
        padding: 5px 10px;
        align-items: center;
        gap: 16px;
        border-radius: 4px;
        background: #ffffff;
        width: 75px;
        padding-right: 0;
      }

      .ok {
        min-width: 117px;
        width: 117px;
        margin: 0 auto;
      }
    }

    .block {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 65px;

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
