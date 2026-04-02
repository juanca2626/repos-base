<template>
  <div v-if="show" class="hotel-service">
    <DropDownSelectComponent :items="servicesTypes" :multi="false" @selected="selected" />
  </div>
</template>
<script lang="ts" setup>
  import { toRef } from 'vue';
  import DropDownSelectComponent from '../../global/DropDownSelectComponent.vue';
  import type { ServicesType } from '@/quotes/interfaces/services';

  interface Props {
    show: boolean;
    servicesTypes: ServicesType[];
  }

  const props = defineProps<Props>();

  const show = toRef(props, 'show');
  const servicesTypes = toRef(props, 'servicesTypes');

  const emit = defineEmits(['selected']);

  const selected = (args: number[]) => {
    emit(
      'selected',
      servicesTypes.value?.filter((service) => args.includes(service.id))
    );
  };
</script>

<style lang="scss" scoped>
  .hotel-service {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    border-radius: 0 0 6px 6px;
    background: #fff;
    box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
    position: absolute;
    top: 35px;
  }
</style>
