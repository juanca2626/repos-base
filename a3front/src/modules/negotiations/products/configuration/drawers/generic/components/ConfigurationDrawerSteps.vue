<template>
  <div class="steps-container">
    <div class="step">
      <div
        class="icon-wrapper icon-wrapper-active"
        :class="{
          'icon-wrapper-active-step-two': stepNumber > 1,
        }"
      >
        <template v-if="stepNumber > 1">
          <font-awesome-icon icon="fa-solid fa-circle-check" class="circle-check-icon" />
        </template>
        <template v-else>
          <svg
            width="23px"
            height="24px"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            fill="#575B5F"
          >
            <g>
              <g>
                <path fill="none" d="M0 0h24v24H0z"></path>
                <path
                  d="M18.364 17.364L12 23.728l-6.364-6.364a9 9 0 1 1 12.728 0zM12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0-2a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"
                ></path>
              </g>
            </g>
          </svg>
        </template>
      </div>
    </div>
    <div
      class="line"
      :class="{
        'line-active-step-two': stepNumber > 1,
      }"
    ></div>
    <div class="step">
      <div
        class="icon-wrapper"
        :class="[stepNumber > 1 ? 'icon-wrapper-active' : 'icon-wrapper-inactive']"
      >
        <svg
          width="24px"
          height="24px"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 640 512"
          :fill="stepNumber > 1 ? '#575B5F' : '#BABCBD'"
        >
          <path
            d="M224 0a128 128 0 1 1 0 256A128 128 0 1 1 224 0zM178.3 304l91.4 0c11.8 0 23.4 1.2 34.5 3.3c-2.1 18.5 7.4 35.6 21.8 44.8c-16.6 10.6-26.7 31.6-20 53.3c4 12.9 9.4 25.5 16.4 37.6s15.2 23.1 24.4 33c15.7 16.9 39.6 18.4 57.2 8.7l0 .9c0 9.2 2.7 18.5 7.9 26.3L29.7 512C13.3 512 0 498.7 0 482.3C0 383.8 79.8 304 178.3 304zM436 218.2c0-7 4.5-13.3 11.3-14.8c10.5-2.4 21.5-3.7 32.7-3.7s22.2 1.3 32.7 3.7c6.8 1.5 11.3 7.8 11.3 14.8l0 30.6c7.9 3.4 15.4 7.7 22.3 12.8l24.9-14.3c6.1-3.5 13.7-2.7 18.5 2.4c7.6 8.1 14.3 17.2 20.1 27.2s10.3 20.4 13.5 31c2.1 6.7-1.1 13.7-7.2 17.2l-25 14.4c.4 4 .7 8.1 .7 12.3s-.2 8.2-.7 12.3l25 14.4c6.1 3.5 9.2 10.5 7.2 17.2c-3.3 10.6-7.8 21-13.5 31s-12.5 19.1-20.1 27.2c-4.8 5.1-12.5 5.9-18.5 2.4l-24.9-14.3c-6.9 5.1-14.3 9.4-22.3 12.8l0 30.6c0 7-4.5 13.3-11.3 14.8c-10.5 2.4-21.5 3.7-32.7 3.7s-22.2-1.3-32.7-3.7c-6.8-1.5-11.3-7.8-11.3-14.8l0-30.5c-8-3.4-15.6-7.7-22.5-12.9l-24.7 14.3c-6.1 3.5-13.7 2.7-18.5-2.4c-7.6-8.1-14.3-17.2-20.1-27.2s-10.3-20.4-13.5-31c-2.1-6.7 1.1-13.7 7.2-17.2l24.8-14.3c-.4-4.1-.7-8.2-.7-12.4s.2-8.3 .7-12.4L343.8 325c-6.1-3.5-9.2-10.5-7.2-17.2c3.3-10.6 7.7-21 13.5-31s12.5-19.1 20.1-27.2c4.8-5.1 12.4-5.9 18.5-2.4l24.8 14.3c6.9-5.1 14.5-9.4 22.5-12.9l0-30.5zm92.1 133.5a48.1 48.1 0 1 0 -96.1 0 48.1 48.1 0 1 0 96.1 0z"
          ></path>
        </svg>
      </div>
    </div>
  </div>
  <div class="steps-container-text mb-2">
    <div class="city-container">
      <p class="step-text city-title">Locación</p>
      <p class="step-text city-subtitle">Operación del servicio</p>
    </div>
    <div class="config-container">
      <p
        class="step-text config-title"
        :class="{
          'config-title-active': stepNumber > 1,
        }"
      >
        Configuración
      </p>
      <p
        class="step-text config-subtitle"
        :class="{
          'config-subtitle-active': stepNumber > 1,
        }"
      >
        Comportamiento del servicio
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
  defineProps<{
    stepNumber: number;
  }>();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_productComponentScoped.scss';

  .steps-container-text {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 24px;

    .city-container {
      padding-left: 90px;

      .city-title {
        font-size: 14px;
        font-weight: 500;
        color: $color-black-2;
      }

      .city-subtitle {
        font-size: 14px;
        font-weight: 400;
        color: $color-black-3;
      }
    }

    .config-container {
      padding-right: 75px;

      .config-title {
        font-size: 14px;
        font-weight: 500;
        color: $color-black-4;

        &-active {
          color: $color-black-2;
        }
      }

      .config-subtitle {
        font-size: 14px;
        font-weight: 400;
        color: $color-gray-ligth-4;

        &-active {
          color: $color-black-3;
        }
      }
    }

    .step-text {
      text-align: center;
    }
  }

  .steps-container {
    padding-top: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0px;
  }

  .line {
    margin: 0;
    flex: 1;
    height: 3px;
    background-color: $color-black-4;
    max-width: 160px;

    &-active-step-two {
      background-color: $color-blue;
    }
  }

  .icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    padding: 0;
    margin: 0;

    &-active {
      border: 2px solid $color-black-2;
    }

    &-active-step-two {
      border: 2px solid $color-blue;
    }

    &-config-step-two {
      border: 2px solid $color-black-2;
    }

    &-inactive {
      border: 2px solid $color-black-4;
    }
  }

  .circle-check-icon {
    color: $color-blue;
    width: 24px;
    height: 24px;
  }
</style>
