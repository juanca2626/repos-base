<template>
  <transition name="modal-animation">
    <div v-show="props.modalActive" class="modal" id="quotes-layout">
      <div class="modal-close-container" @click="close"></div>
      <transition name="modal-animation-inner">
        <div v-show="props.modalActive" class="modal-inner">
          <div class="modal-close" @click="close">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="31"
              height="30"
              viewBox="0 0 31 30"
              fill="none"
            >
              <path
                d="M23 7.5L8 22.5"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M8 7.5L23 22.5"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <!--<font-awesome-icon icon="xmark" />-->
          </div>
          <!-- Modal Header -->
          <div v-if="slots.header" class="modal-header">
            <slot name="header"></slot>
          </div>
          <!-- Modal Content -->
          <div v-if="slots.body" class="modal-body">
            <slot name="body"></slot>
          </div>
          <!-- Modal Footer -->
          <div v-if="slots.footer" class="modal-footer">
            <slot name="footer"></slot>
          </div>
        </div>
      </transition>
    </div>
  </transition>
</template>

<script lang="ts" setup>
  import { useSlots } from 'vue';

  interface Props {
    modalActive: boolean;
  }

  const props = defineProps<Props>();

  const emit = defineEmits(['close']);

  const slots = useSlots();

  const close = () => {
    emit('close');
  };
</script>

<style lang="scss">
  .modal-close-container {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    cursor: pointer;
  }

  .modal-animation-enter-active,
  .modal-animation-leave-active {
    transition: opacity 0.3s cubic-bezier(0.52, 0.02, 0.19, 1.02);
  }

  .modal-animation-enter-from,
  .modal-animation-leave-to {
    opacity: 0;
  }

  .modal-animation-inner-enter-active {
    transition: all 0.3s cubic-bezier(0.52, 0.02, 0.19, 1.02) 0.15s;
  }

  .modal-animation-inner-leave-active {
    transition: all 0.3s cubic-bezier(0.52, 0.02, 0.19, 1.02);
  }

  .modal-animation-inner-enter-from {
    opacity: 0;
    transform: scale(0.8);
  }

  .modal-animation-inner-leave-to {
    transform: scale(0.8);
  }

  .modal {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    width: 100vw;
    position: fixed;
    top: 0;
    left: 0;
    background-color: rgba(0, 0, 0, 0.5) !important;
    z-index: 25;
    overflow: auto;

    .modal-inner {
      position: relative;
      max-width: 640px;
      width: 80%;
      box-shadow:
        0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
      background-color: #fff;
      border-radius: 0.375em;
      padding: 20px 24px;

      .modal-close {
        position: absolute;
        font-size: 30px;
        color: #eb5757;
        top: 32px;
        right: 32px;
        cursor: pointer;
        z-index: 1000;
      }

      .modal-body {
        padding: 0 !important;

        .title {
          color: #4f4b4b;
          text-align: center;
          font-size: 24px !important;
          font-style: normal;
          font-weight: 700;
          line-height: 31px;
          letter-spacing: -0.24px;
          // margin: 20px 0 45px 0;
        }

        .description {
          color: #4f4b4b;
          text-align: center;
          font-size: 16px;
          font-style: normal;
          font-weight: 500;
          line-height: 18px;
          margin-bottom: 10px;
          padding: 0;
        }

        span {
          font-family: 'Montserrat', sans-serif;
        }
      }

      .modal-footer {
        padding: 20px 0 0 0;
        background: none !important;
      }
    }
  }
</style>
