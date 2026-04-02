<template>
  <div>
    <div class="box-content">
      <!-- Encabezado de programación -->
      <a-row :gutter="16" align="middle" justify="space-between">
        <a-col :span="24">
          <div class="header-programacion">
            <div class="info-date">
              <IconCalendar />
              <span>17/05/2021</span>
            </div>
            <div class="info-service">
              <font-awesome-icon :icon="['fas', 'bus']" />
              <span>Tour gastronómico privado Barranco privado Barranco privado...</span>
            </div>
            <div class="info-time">
              <font-awesome-icon :icon="['far', 'clock']" />
              <span>12:08</span>
            </div>
            <div class="info-passengers">
              <span>02 ADL 01 CHD</span>
            </div>
            <div class="info-status">
              <div class="status-tag">Compartido</div>
              <IconCircleExclamation color="#5C5AB4" width="1.6em" height="1.6em" />
            </div>
          </div>
        </a-col>
      </a-row>
      <!-- Detalles del servicio -->
      <a-row :gutter="16" align="middle" justify="space-between" class="mt-2">
        <a-col :span="checked ? 17 : 24">
          <div class="box-service-container" :class="{ 'add-margin-right': !checked }">
            <div class="box-service">
              <a-row :gutter="16" align="middle" justify="space-between">
                <a-col :span="3">
                  <div class="box-service-title">Guía:</div>
                </a-col>
                <a-col :span="8">
                  <div class="box-service-info">EDSON JARA GUZMAN</div>
                </a-col>
                <a-col :span="7">
                  <div class="box-service-number-phone">
                    <IconPhone color="#28A745" />
                    974 726 654
                  </div>
                </a-col>
                <a-col :span="6" justify="flex-end">
                  <div class="box-service-time-line">
                    <a-switch v-model:checked="checked" />
                    Linea de tiempo
                  </div>
                </a-col>
              </a-row>
              <a-row :gutter="16" align="top" justify="space-between" class="mt-2">
                <a-col :span="3">
                  <div class="box-service-title">Descripción:</div>
                </a-col>
                <a-col :span="21">
                  <div class="box-service-description">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse tristique
                    orci sapien, nec consequat risus tempus et. Ut porttitor auctor nisi sed
                    hendrerit. Integer posuere, mi vitae dapibus rutrum, nisl mauris viverra nulla,
                    quis porttitor libero tortor faucibus neque. Ut viverra fringilla elit, et
                    eleifend tortor. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    Suspendisse tristique orci sapien, nec consequat risus tempus et. Ut porttitor
                    auctor nisi sed hendrerit. Integer posuere, mi vitae dapibus rutrum, nisl mauris
                    viverra nulla, quis porttitor libero tortor faucibus neque. Ut viverra fringilla
                    elit, et eleifend tortor. Lorem ipsum dolor sit amet, consectetur adipiscing
                    elit. Suspendisse tristique orci sapien, nec consequat risus tempus et. Ut
                    porttitor auctor nisi sed hendrerit. Integer posuere, mi vitae dapibus rutrum,
                    nisl mauris viverra nulla, quis porttitor libero tortor faucibus neque. Ut
                    viverra fringilla elit, et eleifend tortor.
                  </div>
                </a-col>
              </a-row>
            </div>
          </div>
        </a-col>
        <a-col :span="7" v-if="checked">
          <a-timeline class="timeline-incidents">
            <a-timeline-item color="blue">
              <template #dot>
                <font-awesome-icon
                  :icon="['far', 'circle']"
                  size="xs"
                  style="color: #55a3ff; border: 1px"
                />
              </template>
              <div>Creada el 2015-09-01</div>
              <small class="text-muted"> a las 10:12 h. </small>
            </a-timeline-item>
            <a-timeline-item>
              <template #dot>
                <font-awesome-icon :icon="['far', 'clock']" size="xm" style="color: #000000" />
              </template>
              <div>Problema inicial en proceso el 2015-09-01</div>
              <small class="text-muted"> a las 10:12 h. </small>
            </a-timeline-item>
            <a-timeline-item>
              <template #dot>
                <font-awesome-icon
                  :icon="['far', 'circle']"
                  size="xs"
                  style="color: #ff3b3b; border: 1px"
                />
              </template>
              <div>Queja creada el 2015-09-01</div>
              <small class="text-muted"> a las 10:12 h. </small>
            </a-timeline-item>
            <a-timeline-item>
              <template #dot>
                <font-awesome-icon :icon="['far', 'face-smile']" size="xm" style="color: #000000" />
              </template>
              <div>Problema resuelto el 2015-09-01</div>
              <small class="text-muted"> a las 10:12 h. </small>
            </a-timeline-item>
          </a-timeline>
        </a-col>
      </a-row>
    </div>
  </div>
</template>
<script setup lang="ts">
  import IconCalendar from '@/components/icons/IconCalendar.vue';
  import IconCircleExclamation from '@/components/icons/IconCircleExclamation.vue';
  import IconPhone from '@/components/icons/IconPhone.vue';
  import { ref } from 'vue';

  const checked = ref<boolean>(false);
</script>

<style scoped lang="scss">
  /* Estilos para el encabezado */
  .header-programacion {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 30px;
    gap: 10px;
    margin-bottom: 20px;
  }

  .timeline-incidents {
    :deep(.ant-timeline-item-head-custom) {
      background-color: #fafafa;
    }
  }

  .info-date,
  .info-service,
  .info-time,
  .info-passengers,
  .box-service-time-line,
  .info-status {
    display: flex;
    align-items: center;
  }

  .info-date {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 10px;
    font-family: Montserrat, serif;
    font-weight: 700;
    font-size: 12px;

    svg {
      color: #eb5757;
      font-size: 15px;
    }
  }

  .info-status {
    gap: 16px;
  }

  .box-service-time-line {
    gap: 10px;
    justify-content: flex-end;
  }

  .status-tag {
    font-family: Montserrat, serif;
    padding: 2px 10px;
    background-color: #c63838;
    color: #ffffff;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
  }

  .info-passengers {
    font-weight: 500 !important;
  }

  .info-service,
  .info-passengers {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    font-family: Montserrat, serif;
    font-weight: 400;
    font-size: 14px;

    svg {
      font-size: 18px;
    }
  }

  .info-time {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    font-family: Montserrat, serif;
    font-weight: 400;
    font-size: 14px;
    background-color: #fff;
    padding: 0 12px;
    border-radius: 6px;

    svg {
      color: #c4c4c4;
      font-size: 18px;
    }
  }

  .icon-calendar,
  .icon-bus,
  .icon-clock {
    font-size: 20px;
    color: #575757;
  }

  .info-status .ant-tag {
    font-size: 14px;
    padding: 0 10px;
  }

  .add-margin-right {
    margin-right: 56px;
  }

  /* Detalles del servicio */
  .box-service-container {
    background: #fafafa;
    border-radius: 6px;
    margin-left: 56px;

    .box-service {
      background: #fff;
      padding: 15px 20px;
      margin-bottom: 30px;

      &-title {
        font-family: Montserrat, serif;
        font-weight: 600;
        font-size: 12px;
      }

      &-info {
        font-family: Montserrat, serif;
        font-weight: 500;
        font-size: 16px;
        color: #979797;
      }

      &-description {
        font-family: Montserrat, serif;
        font-weight: 400;
        font-size: 12px;
        color: #3d3d3d;
        text-align: justify;
      }

      &-profile {
        font-family: Montserrat, serif;
        font-weight: 600;
        color: #55a3ff;
        font-size: 12px;
        display: flex;
        gap: 8px;
        align-items: flex-start;
        flex-direction: row;
        flex-wrap: nowrap;
        cursor: pointer;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;

        svg {
          color: #55a3ff;
          font-size: 15px;
        }
      }

      &-view-report {
        font-family: Montserrat, serif;
        font-weight: 600;
        color: #eb5757;
        font-size: 12px;
        display: flex;
        gap: 8px;
        align-items: flex-start;
        flex-direction: row;
        flex-wrap: nowrap;
        cursor: pointer;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;

        svg {
          color: #55a3ff;
          font-size: 15px;
        }
      }

      &-number-phone {
        font-family: Montserrat, serif;
        font-weight: 600;
        color: #52c41a;
        font-size: 12px;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-direction: row;
        flex-wrap: nowrap;
        cursor: pointer;
        justify-content: flex-end;

        svg {
          color: #55a3ff;
          font-size: 15px;
        }
      }
    }
  }

  /* Estilo para contenido de tab */
  .box-content {
    padding: 0;
    background-color: #fafafa;
    border-radius: 6px;
    border: 2px solid #e9e9e9;
    margin-bottom: 10px;
  }
</style>
