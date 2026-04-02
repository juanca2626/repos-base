<template>
  <a-form-item :name="name" :label="label" required>
    <template #help v-if="required">
      <span class="err-message">{{ errMessage }}</span>
    </template>
    <a-input
      :name="name"
      :readonly="readonly"
      :value="modelValue"
      :placeholder="placeholder"
      @input="handleInput"
    />
  </a-form-item>
</template>

<script setup>
  import { mask as maska } from 'maska';

  import { defineProps, ref, watch } from 'vue';

  const props = defineProps([
    'modelValue',
    'mask',
    'required',
    'label',
    'readonly',
    'placeholder',
    'name',
  ]);
  const emit = defineEmits(['update:modelValue']);
  const auxVal = ref();
  const errMessage = ref('Campo obligatorio');

  const handleInput = (event) => {
    auxVal.value = event.target.value;
  };
  const validate = (val) => {
    if (val.length <= props.mask.length) {
      return props.required && val.length === props.mask.length;
    } else {
      return true;
    }
  };
  watch(auxVal, (val) => {
    errMessage.value = validate(val) ? '' : 'Campo obligatorio';
    const maskedVal = maska(val, props.mask);
    emit('update:modelValue', maskedVal);
  });
</script>

<style lang="sass" scoped>
  .err-message
    color: red
    font-size: 12px
    display: block
</style>
