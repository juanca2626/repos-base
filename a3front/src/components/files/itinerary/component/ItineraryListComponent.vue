<template>
  <div>
    <div class="box-content">
      <!-- Detalles del servicio -->
      <div class="box-service-container">
        <div class="box-service itinerary" v-for="(item, index) in itineraries" :key="index">
          <a-row :gutter="16" align="middle" justify="space-between">
            <a-col :span="4">
              <div class="day">Day {{ item.day }} | {{ item.city_in_name }}</div>
            </a-col>
            <!--            <a-col :span="10" align="right">-->
            <!--              <div class="icon" @click="toggleEditMode(item)">-->
            <!--                <IconEdit color="#EB5757" v-if="!item.isEditing" />-->
            <!--                <IconSave color="#EB5757" width="1.75em" height="1.75em" v-else />-->
            <!--              </div>-->
            <!--            </a-col>-->
            <a-col :span="24">
              <div class="date">{{ item.date }}</div>
            </a-col>
          </a-row>
          <div v-for="itinerary in item.itineraries">
            <a-row
              :gutter="16"
              align="top"
              justify="space-between"
              :key="index"
              class="mt-3"
              v-if="itinerary.entity === 'service'"
            >
              <a-col :span="2">
                <div class="hours">{{ itinerary.start_time }}</div>
              </a-col>
              <a-col :span="22">
                <div
                  v-if="!item.isEditing"
                  class="description"
                  v-html="itinerary.description"
                ></div>
                <a-textarea
                  v-else
                  v-model:value="itinerary.description"
                  :auto-size="{ minRows: 2, maxRows: 5 }"
                />
              </a-col>
            </a-row>
            <a-row
              :gutter="16"
              align="top"
              justify="space-between"
              :key="index"
              class="mt-3"
              v-if="itinerary.entity === 'hotel'"
            >
              <a-col :span="2"></a-col>
              <a-col :span="22">
                <a-row>
                  <a-col :span="24">
                    <div class="accommodation">
                      <span>Acomodación: {{ itinerary.name }}</span>
                      <a :href="itinerary.hotel_detail.url" target="_blank">
                        {{ itinerary.hotel_detail.url }}
                      </a>
                    </div>
                  </a-col>
                  <a-col :span="24">
                    <div class="check-in-out">
                      Check in: <span>{{ itinerary.start_time }}</span> Check out:
                      <span>{{ itinerary.departure_time }}</span>
                    </div>
                  </a-col>
                  <a-col :span="24">
                    <div class="meals">
                      Comidas incluidas: <span>{{ itinerary.hotel_detail.meal }}</span>
                    </div>
                  </a-col>
                </a-row>
              </a-col>
            </a-row>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
  defineProps({
    language: {
      type: String,
      required: true,
    },
    itineraries: {
      type: Array,
      required: true,
    },
  });

  // const toggleEditMode = (item: any) => {
  //   item.isEditing = !item.isEditing;
  // };
</script>

<style scoped lang="scss">
  .itinerary {
    font-family: Montserrat, serif;
    color: #0d0d0d;

    .accommodation {
      font-family: Montserrat, serif;
      font-weight: 400;
      font-size: 12px;
      color: #575757;

      span {
        font-weight: 500;
        margin-right: 10px;
      }

      a {
        color: #80baff;
        text-decoration: underline;
        text-underline-offset: 2px;
      }
    }

    .check-in-out {
      font-family: Montserrat, serif;
      font-weight: 400;
      font-size: 12px;
      color: #575757;

      span {
        font-weight: 500;
        margin-left: 10px;
        margin-right: 10px;
      }
    }

    .meals {
      font-family: Montserrat, serif;
      font-weight: 400;
      font-size: 12px;
      color: #575757;

      span {
        font-weight: 500;
        margin-left: 10px;
        margin-right: 10px;
      }
    }

    .day {
      font-weight: 700;
      font-size: 14px;
      margin-bottom: 5px;
    }

    .date {
      font-weight: 600;
      font-size: 12px;
      margin-bottom: 5px;
    }

    .hours {
      font-weight: 500;
      font-size: 14px;
      margin-bottom: 5px;
    }

    .description {
      font-weight: 400;
      font-size: 12px;
      margin-bottom: 5px;
      text-align: justify;
    }

    .icon {
      svg {
        cursor: pointer;
        color: #eb5757;
      }
    }
  }

  /* Detalles del servicio */
  .box-service-container {
    .box-service {
      border-radius: 8px;
      background-color: #fafafa;
      padding: 20px 25px;
      margin-bottom: 30px;
    }
  }

  /* Estilo para contenido de tab */
  .box-content {
    padding: 30px 40px;
    background-color: #ffffff;
    border-radius: 6px;
    border: 1px solid #e9e9e9;
    margin-bottom: 10px;
  }
</style>
