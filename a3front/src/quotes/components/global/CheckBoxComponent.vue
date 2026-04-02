<template>
  <div v-if="showCheckbox" :class="{ selected: isChecked }" class="checkbox" @click="selectItem">
    <div class="icon">
      <font-awesome-icon v-if="isChecked" class="active" icon="check" />
    </div>
    <label v-if="props.label">{{ props.label }}</label>
  </div>
</template>
<script setup>
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { defineEmits, defineProps, onMounted, toRef } from 'vue';

  const props = defineProps({
    modelValue: { type: Boolean },
    label: String,
    showCheckbox: { type: Boolean, default: false }, // Nueva prop para determinar la visibilidad
  });

  // const state = reactive({
  //   isChecked: false,
  // });
  const isChecked = toRef(props, 'modelValue');

  const emit = defineEmits(['checked']);

  onMounted(() => {
    if (props.modelValue) {
      isChecked.value = props.modelValue;
    }
  });

  const selectItem = () => {
    emit('checked', !isChecked.value);
    isChecked.value = !isChecked.value;
  };
</script>

<style lang="sass" scoped>
  .checkbox
    display: flex
    align-items: flex-start
    gap: 15px
    height: 24px

    &.selected
      .icon
        background-color: #eb5757
        border: 1px solid #eb5757

    & > *
      font-size: 16px

    & .active
      color: #ffffff

    .icon
      display: flex
      width: 24px !important
      height: 24px !important
      border: 1px solid #c4c4c4
      border-radius: 2px
      cursor: pointer
      justify-content: center
      align-items: center

    .label
      color: #979797
      font-size: 16px
      font-style: normal
      font-weight: 500
      line-height: 23px
      letter-spacing: -0.24px
</style>
