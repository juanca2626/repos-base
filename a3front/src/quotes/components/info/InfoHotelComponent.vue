<template>
  <div class="quotes-details">
    <div class="quotes-tabs">
      <div class="left">
        <svg
          fill="none"
          height="21"
          viewBox="0 0 20 21"
          width="20"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M12.5 15.5L7.5 10.5L12.5 5.5"
            stroke="#C4C4C4"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
          />
        </svg>
      </div>
      <div class="center">
        <!-- <div
          :class="{ active: state.currentTab === 'instalaciones' }"
          class="tab"
          @click="changeTab('instalaciones')"
        >
          Instalaciones
        </div> -->
        <div
          :class="{ active: state.currentTab === 'servicios' }"
          class="tab"
          @click="changeTab('servicios')"
        >
          {{ t('quote.label.services') }}
        </div>
        <!-- <div
          :class="{ active: state.currentTab === 'eventos' }"
          class="tab"
          @click="changeTab('eventos')"
        >
          Eventos
        </div> -->
      </div>
    </div>
    <div class="quotes-tabs-body">
      <!-- <InstalacionTabComponent v-if="state.currentTab === 'instalaciones'" /> -->
      <InstalacionTabComponent
        v-if="state.currentTab === 'servicios'"
        :amenities="props.amenities"
      />

      <!-- <div class="eventos" v-if="state.currentTab === 'eventos'">
        <div class="infoGeneralContent">
          <div>Nombre del salón</div>

          <div class="icon">
            <div>
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                <g clip-path="url(#clip0_8822_44898)">
                  <path
                      d="M10.625 13.125V11.875C10.625 11.212 10.3616 10.5761 9.89277 10.1072C9.42393 9.63839 8.78804 9.375 8.125 9.375H3.125C2.46196 9.375 1.82607 9.63839 1.35723 10.1072C0.888392 10.5761 0.625 11.212 0.625 11.875V13.125"
                      stroke="#3D3D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path
                      d="M5.625 6.875C7.00571 6.875 8.125 5.75571 8.125 4.375C8.125 2.99429 7.00571 1.875 5.625 1.875C4.24429 1.875 3.125 2.99429 3.125 4.375C3.125 5.75571 4.24429 6.875 5.625 6.875Z"
                      stroke="#3D3D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path
                      d="M14.375 13.1248V11.8748C14.3746 11.3209 14.1902 10.7828 13.8509 10.345C13.5115 9.90722 13.0363 9.59453 12.5 9.45605"
                      stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path
                      d="M10 1.95605C10.5378 2.09374 11.0144 2.40649 11.3548 2.845C11.6952 3.2835 11.8799 3.82282 11.8799 4.37793C11.8799 4.93304 11.6952 5.47235 11.3548 5.91086C11.0144 6.34937 10.5378 6.66212 10 6.7998"
                      stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <defs>
                  <clipPath id="clip0_8822_44898">
                    <rect width="15" height="15" fill="white"/>
                  </clipPath>
                </defs>
              </svg>
            </div>
            <div><span class="bold">Aforo:</span> 24 personas</div>
          </div>

          <div class="schedules">
            <div>
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                <g clip-path="url(#clip0_8822_31736)"><path d="M7.5 13.75C10.9518 13.75 13.75 10.9518 13.75 7.5C13.75 4.04822 10.9518 1.25 7.5 1.25C4.04822 1.25 1.25 4.04822 1.25 7.5C1.25 10.9518 4.04822 13.75 7.5 13.75Z" stroke="#3D3D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.5 3.75V7.5L10 8.75" stroke="#3D3D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></g><defs><clipPath id="clip0_8822_31736"><rect width="15" height="15" fill="white"></rect></clipPath></defs>
              </svg>
            </div>
            <div>
              <span class="bold">Horario de atención:</span> <div>06:00 - 22:00</div></div>
          </div>
        </div>

        <div class="infoGeneralContent">
          <div>Nombre del salón</div>

          <div class="icon">
            <div>
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                <g clip-path="url(#clip0_8822_44898)">
                  <path
                      d="M10.625 13.125V11.875C10.625 11.212 10.3616 10.5761 9.89277 10.1072C9.42393 9.63839 8.78804 9.375 8.125 9.375H3.125C2.46196 9.375 1.82607 9.63839 1.35723 10.1072C0.888392 10.5761 0.625 11.212 0.625 11.875V13.125"
                      stroke="#3D3D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path
                      d="M5.625 6.875C7.00571 6.875 8.125 5.75571 8.125 4.375C8.125 2.99429 7.00571 1.875 5.625 1.875C4.24429 1.875 3.125 2.99429 3.125 4.375C3.125 5.75571 4.24429 6.875 5.625 6.875Z"
                      stroke="#3D3D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path
                      d="M14.375 13.1248V11.8748C14.3746 11.3209 14.1902 10.7828 13.8509 10.345C13.5115 9.90722 13.0363 9.59453 12.5 9.45605"
                      stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path
                      d="M10 1.95605C10.5378 2.09374 11.0144 2.40649 11.3548 2.845C11.6952 3.2835 11.8799 3.82282 11.8799 4.37793C11.8799 4.93304 11.6952 5.47235 11.3548 5.91086C11.0144 6.34937 10.5378 6.66212 10 6.7998"
                      stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <defs>
                  <clipPath id="clip0_8822_44898">
                    <rect width="15" height="15" fill="white"/>
                  </clipPath>
                </defs>
              </svg>
            </div>
            <div><span class="bold">Aforo:</span> 24 personas</div>
          </div>

          <div class="schedules">
            <div>
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                <g clip-path="url(#clip0_8822_31736)"><path d="M7.5 13.75C10.9518 13.75 13.75 10.9518 13.75 7.5C13.75 4.04822 10.9518 1.25 7.5 1.25C4.04822 1.25 1.25 4.04822 1.25 7.5C1.25 10.9518 4.04822 13.75 7.5 13.75Z" stroke="#3D3D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.5 3.75V7.5L10 8.75" stroke="#3D3D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></g><defs><clipPath id="clip0_8822_31736"><rect width="15" height="15" fill="white"></rect></clipPath></defs>
              </svg>
            </div>
            <div>
              <span class="bold">Horario de atención:</span> <div>06:00 - 22:00</div></div>
          </div>
        </div>
      </div> -->
    </div>
  </div>
</template>

<script lang="ts" setup>
  import InstalacionTabComponent from '@/quotes/components/details/tabs/InstalacionTabComponent.vue';
  import { useI18n } from 'vue-i18n';
  import { reactive } from 'vue';

  const { t } = useI18n();
  const props = defineProps<{ amenities?: [] }>();
  // const { quote } = useQuote();

  const state = reactive({
    currentTab: 'servicios',
  });

  const changeTab = (tab: string) => {
    state.currentTab = tab;
  };
</script>

<style lang="scss">
  .bold {
    font-weight: 600;
  }

  .eventos {
    margin-top: 16px;
    padding: 0 10px;

    .infoGeneralContent {
      flex: none;
      display: flex;
      margin-bottom: 5px;
      padding: 0;
      justify-content: center;
      align-items: center;
      font-size: 14px;

      & > div {
        flex-grow: 0;
        flex-shrink: 0;
        padding: 16px 0;
        letter-spacing: 0.21px;
        line-height: normal;

        &:first-child {
          flex-basis: 44%;
          color: #4f4b4b;
        }
      }

      .icon {
        align-items: center;
        gap: 7px;
        display: flex;
        flex-basis: 28%;
        color: #3d3d3d;
      }

      svg {
        margin-top: 2px;
      }

      .schedules {
        gap: 4px;
        display: flex;
        flex: 1 0 0;
        flex-basis: 28%;
        color: #3d3d3d;
      }
    }
  }

  .quotes-details {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0;
    width: 100%;
    margin-bottom: 24px;

    .quotes-tabs {
      display: flex;
      align-items: center;
      gap: 10px;
      align-self: stretch;

      .left {
        display: flex;
        padding: 4px;
        align-items: flex-start;
        gap: 10px;
        border-radius: 6px;
        background: #fafafa;

        svg {
          width: 20px;
          height: 20px;
        }
      }

      .center {
        display: flex;
        gap: 10px;
        justify-content: space-between;
        flex-direction: row;
        align-items: flex-end;

        .tab {
          display: flex;
          width: 180px;
          padding: 4px 16px;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          border-radius: 6px 6px 0 0;
          background: #e9e9e9;
          color: #979797;
          text-align: center;
          font-size: 14px;
          font-style: normal;
          font-weight: 600;
          line-height: 21px;
          letter-spacing: 0.21px;
          cursor: pointer;

          &.active {
            background: #737373;
            color: #fefefe;
            padding: 6px 16px;
          }
        }
      }
    }

    .quotes-tabs-body {
      width: 100%;
    }
  }
</style>
