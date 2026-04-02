<script lang="ts" setup>
  import { toRef } from 'vue';

  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import IconClose from '@/quotes/components/icons/IconClose.vue';

  // import type { Service } from '@/quotes/interfaces/services';

  interface Props {
    show: boolean;
    type: string;
    title: string;
    description: string;
  }

  const props = defineProps<Props>();

  const show = toRef(props, 'show');
  const title = toRef(props, 'title');
  const description = toRef(props, 'description');

  // Emits
  interface Emits {
    (e: 'close'): void;
  }

  const emits = defineEmits<Emits>();

  const onClose = () => {
    emits('close');
  };
</script>

<template>
  <ModalComponent v-if="show" :modalActive="show" class="modal-itinerario-detail" @close="onClose">
    <template #body>
      <div class="icon-close-modal" @click="onClose">
        <icon-close />
      </div>

      <template>
        <div class="container">
          <div class="titlePopup">
            <h4>{{ title }}</h4>
          </div>

          <div class="container-flex">
            <div class="item-flex-left">
              <p>{{ description }}</p>
            </div>
          </div>
        </div>
      </template>
    </template>
  </ModalComponent>
</template>

<style lang="scss" scoped></style>
