<template>
  <a-tooltip :title="errorMessage">
    <slot />
  </a-tooltip>
</template>

<script setup lang="ts">
  import { computed, type PropType } from 'vue';
  import type { FormFieldError } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

  const props = defineProps({
    formError: {
      required: true,
      type: Object as PropType<FormFieldError>,
    },
  });

  const errorMessage = computed(() => {
    if (!props.formError.isInvalid) return '';

    return props.formError.message;
  });
</script>
