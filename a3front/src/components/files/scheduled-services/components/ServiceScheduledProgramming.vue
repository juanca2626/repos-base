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
      <div class="box-service-container">
        <div class="box-service">
          <a-row :gutter="16" align="middle" justify="space-between">
            <a-col :span="2">
              <div class="box-service-title">Transporte:</div>
            </a-col>
            <a-col :span="6">
              <div class="box-service-info">Bus Hyundai</div>
            </a-col>
            <a-col :span="2">
              <div class="box-service-title">Cantidad:</div>
            </a-col>
            <a-col :span="4">
              <div class="box-service-info">Bus Hyundai</div>
            </a-col>
            <a-col :span="8"></a-col>
          </a-row>
          <a-row :gutter="16" align="middle" justify="space-between" class="mt-2">
            <a-col :span="2">
              <div class="box-service-title"></div>
            </a-col>
            <a-col :span="6">
              <div class="box-service-info">Bus Hyundai</div>
            </a-col>
            <a-col :span="2">
              <div class="box-service-title">Cantidad:</div>
            </a-col>
            <a-col :span="4">
              <div class="box-service-info">Bus Hyundai</div>
            </a-col>
            <a-col :span="8"></a-col>
          </a-row>
        </div>
        <div class="box-service">
          <a-row :gutter="16" align="middle" justify="space-between">
            <a-col :span="2">
              <div class="box-service-title">Guia:</div>
            </a-col>
            <a-col :span="6">
              <div class="box-service-info">EDSON JARA GUZMAN</div>
            </a-col>
            <a-col :span="3">
              <div class="box-service-number-phone">
                <IconPhone color="#28A745" />
                974 726 654
              </div>
            </a-col>
            <a-col :span="3">
              <div class="box-service-profile">
                <IconUser color="#55A3FF" />
                Ver perfil
              </div>
            </a-col>
            <a-col :span="5">
              <div class="box-service-view-report">Ver reporte de servicio</div>
            </a-col>
            <a-col :span="5"></a-col>
          </a-row>
        </div>
        <div class="box-service">
          <a-row :gutter="16" align="middle" justify="space-between">
            <a-col :span="2">
              <div class="box-service-title">Escort:</div>
            </a-col>
            <a-col :span="6">
              <div class="box-service-info">EDSON JARA GUZMAN</div>
            </a-col>
            <a-col :span="3">
              <div class="box-service-number-phone">
                <IconPhone color="#28A745" />
                974 726 654
              </div>
            </a-col>
            <a-col :span="3">
              <div class="box-service-profile">
                <IconUser color="#55A3FF" />
                Ver perfil
              </div>
            </a-col>
            <a-col :span="5">
              <div class="box-service-view-report">Ver reporte de servicio</div>
            </a-col>
            <a-col :span="5"></a-col>
          </a-row>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
  import IconCalendar from '@/components/icons/IconCalendar.vue';
  import IconCircleExclamation from '@/components/icons/IconCircleExclamation.vue';
  import IconPhone from '@/components/icons/IconPhone.vue';
  import IconUser from '@/components/icons/IconUser.vue';
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

  .info-date,
  .info-service,
  .info-time,
  .info-passengers,
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

  /* Detalles del servicio */
  .box-service-container {
    background: #fafafa;
    border-radius: 6px;
    margin-left: 56px;
    margin-right: 56px;

    .box-service {
      background: #fff;
      padding: 15px 20px;
      margin-bottom: 30px;

      &-title {
        font-family: Montserrat, serif;
        font-weight: 600;
        font-size: 14px;
      }

      &-info {
        font-family: Montserrat, serif;
        font-weight: 500;
        font-size: 16px;
        color: #979797;
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
