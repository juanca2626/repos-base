<template>
  <div class="box-content" v-for="(room, r) in rooms" :key="`room-${r}`">
    <div class="box-header-hotel">
      <a-row :gutter="16" align="middle" justify="space-between">
        <a-col :span="12">
          <div class="box-header-hotel-room">
            <IconLink2 color="#4F4B4B" width="1.2em" height="1.2em" />
            {{ room.room }}
          </div>
        </a-col>
        <a-col :span="12">
          <div class="box-header-hotel-room-confirmation">
            Código de confirmación: <span class="number">{{ room.confirmation_code }}</span>
          </div>
        </a-col>
      </a-row>
    </div>
    <div class="box-body-hotel">
      <table class="custom-table">
        <!-- Encabezado de la tabla -->
        <thead>
          <tr>
            <th width="15%">Apellido, nombre</th>
            <th width="4%">Sexo</th>
            <th width="4%">Tipo</th>
            <th width="4%">Na</th>
            <th width="4%">Doc</th>
            <th width="4%">Número</th>
            <th width="4%">F. Nac.</th>
            <th width="30%">Restricciones Médicas</th>
            <th width="31%">Restricciones Alimenticias</th>
          </tr>
        </thead>
        <!-- Cuerpo de la tabla -->
        <tbody>
          <tr v-for="passenger in room.passengers" :key="passenger.id">
            <td class="text-center">
              <div class="container-text">{{ passenger.surname }} {{ passenger.name }}</div>
            </td>
            <td class="text-center">
              <div class="container-text">{{ passenger.genre }}</div>
            </td>
            <td class="text-center">
              <div class="container-text">{{ passenger.type }}</div>
            </td>
            <td class="text-center">
              <div class="container-text">{{ passenger.country_iso }}</div>
            </td>
            <td class="text-center">
              <div class="container-text">{{ passenger.doctype_iso }}</div>
            </td>
            <td class="text-center">
              <div class="container-text">{{ passenger.document_number }}</div>
            </td>
            <td class="text-center">
              <div class="container-text">{{ passenger.date_birth }}</div>
            </td>
            <td class="text-justify">
              <a-row :gutter="16" align="top" justify="space-between" class="container-text">
                <a-col :span="2">
                  <font-awesome-icon :icon="['fas', 'heart-pulse']" size="lg" />
                </a-col>
                <a-col :span="22">
                  <div class="text-description">
                    {{ passenger.medical_restrictions }}
                  </div>
                </a-col>
              </a-row>
            </td>
            <td class="text-justify">
              <a-row :gutter="16" align="top" justify="space-between" class="container-text">
                <a-col :span="2">
                  <font-awesome-icon :icon="['fas', 'heart-pulse']" size="lg" />
                </a-col>
                <a-col :span="22">
                  <div class="text-description">
                    {{ passenger.dietary_restrictions }}
                  </div>
                </a-col>
              </a-row>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <a-row :gutter="16" align="middle" justify="space-between">
      <a-col :span="11">
        <div class="box-footer-hotel mt-5">
          <div class="box-footer-hotel-total">
            Total: <span class="number-rooms">1 Hab - {{ room.passengers.length }} pax</span>
          </div>
        </div>
      </a-col>
    </a-row>
  </div>
</template>
<script setup lang="ts">
  import IconLink2 from '@/components/icons/IconLink2.vue';

  defineProps({
    rooms: {
      type: Array,
      default: () => [],
    },
  });
</script>

<style scoped lang="scss">
  /* Estilo general */
  .box-content {
    padding: 30px 40px;
    background-color: #ffffff;
    border-radius: 6px;
    border: 1px solid #e9e9e9;
    margin-top: 10px;
    margin-bottom: 10px;
  }

  .box-header-hotel {
    background-color: #e8edf3;
    padding: 15px 28px;
    border-radius: 6px;

    &-room {
      display: flex;
      justify-content: flex-start;
      gap: 10px;
      font-family: Montserrat, serif;
      font-weight: 700;
      font-size: 14px;
      color: #4f4b4b;
      align-items: center;
    }

    &-room-confirmation {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      font-family: Montserrat, serif;
      font-weight: 400;
      font-size: 12px;
      color: #4f4b4b;
      align-items: center;

      .number {
        font-weight: 700;
        font-size: 14px;
      }
    }
  }

  .box-footer-hotel {
    background-color: #e8edf3;
    padding: 15px 28px;
    border-radius: 6px;

    &-total {
      display: flex;
      justify-content: flex-start;
      gap: 40px;
      font-family: Montserrat, serif;
      font-weight: 500;
      font-size: 14px;
      color: #4f4b4b;
      align-items: center;

      .number-rooms {
        font-weight: 700;
      }
    }
  }

  /* Tabla personalizada */
  .custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 15px;

    thead {
      background-color: #ffffff;
      font-family: Montserrat, serif;
      font-weight: 700;
      font-size: 14px;

      th {
        padding: 25px 10px;
        text-align: center;
        border: none;
        border-radius: 6px;
      }
    }

    tbody {
      font-family: Montserrat, serif;
      font-weight: 400;
      font-size: 14px;
      background-color: #fafafa;

      .container-text {
        padding-top: 10px;
        height: 100%;
      }

      tr {
        border: none;
        background-color: #fafafa;
      }

      .text-description {
        font-family: Montserrat, serif;
        font-weight: 500;
        font-size: 10px;
        color: #4f4b4b;
      }

      /* Bordes redondeados para la primera y última celda */
      tr td:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
      }

      tr td:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
      }

      td {
        padding: 10px;
        text-align: center;
        border: none;
        height: 100px;
      }

      .text-justify {
        text-align: justify;
      }
    }
  }
</style>
