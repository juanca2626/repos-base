<template>
  <div v-if="show" class="language-service">
    <DropDownSelectComponent :items="languages" :multi="false" @selected="selected" />
  </div>
</template>
<script lang="ts" setup>
  import { toRef } from 'vue';
  import DropDownSelectComponent from '@/quotes/components/global/DropDownSelectComponent.vue';
  import type { Language } from '@/quotes/interfaces';

  interface Props {
    show: boolean;
    languages: Language[];
  }

  const props = defineProps<Props>();
  const show = toRef(props, 'show');
  const languages = toRef(props, 'languages');

  const emit = defineEmits(['selected']);

  const selected = (args: string[]) => {
    console.log(args, languages.value);
    emit(
      'selected',
      languages.value?.filter((language) => args.includes(language.value))
    );
  };
</script>

<style lang="scss" scoped>
  .language-service {
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
