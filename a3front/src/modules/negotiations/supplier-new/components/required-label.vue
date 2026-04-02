<template>
  <span> {{ label }}<span class="required-asterisk">*</span> </span>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';

  export default defineComponent({
    name: 'RequiredLabel',
    props: {
      label: {
        type: String,
        required: true,
      },
    },
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .required-asterisk {
    color: $color-primary-strong;
    margin-left: 4px;
    font-weight: bold;
  }
</style>
