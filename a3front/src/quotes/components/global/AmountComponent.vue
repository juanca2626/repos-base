<script lang="ts" setup>
  import { toRef } from 'vue';

  interface Props {
    amount: number;
    max: number | null;
    min: number | null;
  }

  const props = withDefaults(defineProps<Props>(), {
    amount: 0,
    max: null,
    min: null,
  });

  const amount = toRef(props, 'amount');
  const max = toRef(props, 'max');
  const min = toRef(props, 'min');

  // const amountPrint = computed(() => amount.value.toString().padStart(2, '0'));

  interface Emits {
    (e: 'change', value: number): void;

    (e: 'update:amount', value: number): void;
  }

  const emits = defineEmits<Emits>();

  const onClick = (value: number) => {
    if (amount.value < value && max.value !== null && max.value < value) {
      return;
    }
    if (amount.value > value && min.value !== null && min.value > value) {
      return;
    }

    emits('change', value);
    emits('update:amount', value);
  };

  // const onEnter = (val) => {
  //   let id = parseInt(val.target.value);
  //   onClick(id);
  // };

  const onNumber = (searchValue) => {
    let textValue = parseInt(searchValue.target.value);
    let keyValue = parseInt(searchValue.key);
    let charCode = searchValue.which ? searchValue.which : searchValue.keyCode;

    if (charCode == 189 || charCode == 190) {
      amount.value = parseInt(min.value);
      textValue = parseInt(min.value);
    } else if (textValue <= 0 || textValue > max.value || !Number.isInteger(textValue)) {
      amount.value = min.value;
      textValue = min.value;
    } else {
      if (
        Number.isInteger(keyValue) &&
        max.value !== null &&
        textValue <= max.value &&
        textValue >= min.value &&
        max.value !== null
      ) {
        amount.value = textValue;
      } else {
        if (charCode == 46 || charCode == 8) {
          amount.value = textValue;
        } else {
          textValue = amount.value;
        }
      }
    }

    emits('change', textValue);
    emits('update:amount', textValue);
  };

  const onNumberNaN = (evt) => {
    let textValue = parseInt(evt.target.value);
    if (!Number.isInteger(textValue)) {
      evt.target.value = amount.value;
    }
  };
</script>

<template>
  <!--<span class="amount">{{ amountPrint }}</span>-->
  <!--<a-input-number :value="amountPrint" class="amountN" @pressEnter="onEnter" />-->
  <!--<a-input-number :value="amount" id="edValue" class="amountN" @keypress="onNumber" />-->
  <!--<a-input-number class="amountN" @blur="onBlur" v-model:value="amount" />-->

  <!--<a-input-number :value="amount" id="test" class="amountN" @keydown="onNumberSea" @keyup="onNumber" />-->
  <!--<a-input-number :value="amount" id="test" class="amountN" @keypress="changeNumber" />-->
  <!--<a-input id="test" class="amountN" @onkeypress="return changeNumber(event);" />-->

  <input
    type="number"
    class="amountN"
    @keyup="onNumber"
    @focusout="onNumberNaN"
    :value="amount"
    :min="min"
  />
  <div class="buttons">
    <font-awesome-icon icon="chevron-up" @click="onClick(parseInt(amount) + 1)" />
    <font-awesome-icon icon="chevron-down" @click="onClick(parseInt(amount) - 1)" />
  </div>
</template>

<style lang="scss" scoped>
  .amountN {
    color: #575757;
    font-size: 14px;
    border: 0;

    visibility: visible !important;
    height: 100% !important;
    width: 100% !important;
    padding: 0 2px !important;
    text-align: center;

    :deep(.ant-input-number-handler-wrap) {
      display: none;
    }

    :deep(.ant-input-number-input) {
      padding: 0;
      text-align: center;
    }

    :deep(.ant-input-number-input-wrap) {
      input.amountN,
      input.ant-input-number-input {
        visibility: visible !important;
        height: 100% !important;
        width: 100% !important;
      }
    }
  }

  span {
    color: #575757;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 21px;
    letter-spacing: 0.21px;

    &.amount {
      font-weight: 600;
    }
  }

  input {
    border: none;
    width: 100%;
  }

  .buttons {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
    padding-left: 2px;
    width: 32px;

    svg {
      display: flex;
      /*width: 15px;
    height: 8px;*/
      cursor: pointer;
      justify-content: center;
      align-items: center;
      color: #eb5757;
    }
  }

  input[type='number']::-webkit-inner-spin-button,
  input[type='number']::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  input[type='number'] {
    -moz-appearance: textfield;
  }
</style>
