<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :keyboard="!isSaving"
      :maskClosable="!isSaving"
      :focusTriggerAfterClose="false"
      width="920px"
    >
      <template #title>
        <div class="service-tags">
          <div class="tag-modal tag-type-service">Tipo de servicio</div>
          <div class="tag-modal tag-category-service">Premium</div>
        </div>
        <div class="title-service-master">Nombre del servicio a prestar en traslados</div>
      </template>

      <!-- Contenido del modal -->
      <div class="modal-content">
        <!-- Fila principal con detalles -->
        <a-row :gutter="16">
          <!-- Columna izquierda: detalles del servicio -->
          <a-col :span="14">
            <div class="operation-details">
              <div class="operation-title">Operatividad</div>
              <p class="time-system">Sistema horario de 24 horas</p>
              <p class="check-out-time"><strong>Hora de salida:</strong> 05:00:00</p>
              <div class="time-ranges">
                <div class="time-range">
                  <div class="time">05:00:00 → 05:40:00</div>
                  <a-divider
                    type="vertical"
                    style="height: 20px; width: 2px; background-color: #212529"
                  />
                  <div class="operation-name">Briefing y revisión de itinerarios</div>
                </div>
              </div>
              <div class="time-ranges">
                <div class="time-range">
                  <div class="time">05:00:00 → 05:40:00</div>
                  <a-divider
                    type="vertical"
                    style="height: 20px; width: 2px; background-color: #212529"
                  />
                  <div class="operation-name">
                    Traslado del aeropuerto al hotel (Sujeto a horario de vuelo)
                  </div>
                </div>
              </div>
            </div>
          </a-col>

          <!-- Columna derecha: otros detalles -->
          <a-col :span="10">
            <div class="service-master-details">
              <div class="service-duration">
                <font-awesome-icon :icon="['far', 'clock']" />
                <strong>Duración:</strong> 20 minutos
              </div>
              <div class="title-include">Incluye</div>
              <a-tag class="tag-include">Traslados</a-tag>
              <a-tag class="tag-include">Chofer trasladista</a-tag>

              <div class="title-availability">Disponibilidad</div>

              <div class="availability">
                <div class="box-availability">
                  <font-awesome-icon :icon="['far', 'circle-check']" size="xl" />
                </div>
                <div class="box-availability">
                  <div class="title">Días:</div>
                  <div class="description">Todos los días</div>
                </div>
                <div class="box-availability">
                  <div class="title">Horario:</div>
                  <div class="description">13:00:00 - 18:45:00</div>
                </div>
              </div>
            </div>
          </a-col>
        </a-row>
        <a-row :gutter="16">
          <a-col :span="24">
            <div class="notes mt-4">
              <h4>Notas:</h4>
              <p>• El conductor no podrá bajar para asistir al pasajero en el check-in</p>
            </div>
          </a-col>
        </a-row>
      </div>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
  defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const emit = defineEmits(['update:isOpen', 'submit']);

  const handleCancel = () => {
    emit('update:isOpen', false);
  };
</script>

<style scoped lang="scss">
  .file-modal {
    .title-service-master {
      font-family: 'Montserrat', sans-serif;
      font-weight: 400;
      font-size: 36px;
      letter-spacing: -0.01em;
      color: #212529;
    }

    .service-tags {
      display: flex;
      gap: 35px;
      top: -20px;
      position: relative;
      justify-content: flex-end;
      margin-right: 45px;

      .tag-modal {
        color: #fff;
        padding: 10px 18px;
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 5px;
      }

      .tag-type-service {
        background-color: #ff4d4f;
      }

      .tag-category-service {
        background-color: #ffb001;
      }
    }

    .modal-content {
      padding: 20px;
    }

    .operation-details {
      font-family: 'Montserrat', sans-serif;

      .operation-title {
        font-size: 24px;
        font-weight: bold;
      }

      .time-system {
        font-size: 12px;
        color: #979797;
        margin-bottom: 10px;
      }

      .check-out-time {
        font-size: 16px;
        color: #0d0d0d;
        margin-bottom: 20px;
      }
    }

    .time-ranges {
      font-size: 18px;

      .time-range {
        display: flex;
        align-items: center;
        flex-direction: row;
        gap: 10px;
        margin-bottom: 10px;
      }

      .time {
        color: #ff4d4f;
        width: 40%;
      }

      .operation-name {
        color: #0d0d0d;
        width: 48%;
      }
    }

    .notes h4 {
      font-size: 16px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .notes p {
      font-family: 'Montserrat', sans-serif;
      color: #ff4d4f;
      font-size: 18px;
    }

    .service-master-details {
      font-weight: 400;
      font-size: 18px;

      .service-duration {
        margin-bottom: 30px;

        strong {
          font-size: 18px;
          font-weight: 600;
        }

        svg {
          margin-right: 10px;
        }
      }

      .title-include,
      .title-availability {
        font-family: 'Montserrat', sans-serif;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 10px;
      }

      .title-availability {
        margin-top: 25px;
      }

      .tag-include {
        font-family: 'Montserrat', sans-serif;
        margin-right: 10px;
        padding: 5px 12px 5px 12px;
        background: #e9e9e9;
        border-radius: 6px;
        color: #0d0d0d;
        font-size: 14px;
      }

      .availability {
        display: flex;
        gap: 16px;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: center;

        .box-availability {
          display: flex;
          flex-direction: column;
          gap: 5px;

          svg {
            color: #1ed790;
          }

          .title {
            font-size: 18px;
            font-weight: 700;
            color: #737373;
          }

          .description {
            font-size: 14px;
            font-weight: 400;
            color: #0d0d0d;
          }
        }
      }
    }

    .service-details i {
      margin-right: 5px;
    }

    .a-tag {
      margin-right: 10px;
    }
  }
</style>
