<template>
  <div>
    <a-modal v-model:open="modal.open" :width="800" :closable="true" :maskClosable="false">
      <template #title>
        <p class="title-add-emails">Correos adicionales para solicitud de reserva:</p>
        <hr class="hr-add-emails" />
      </template>
      <div class="email-modal">
        <a-row :gutter="24" align="middle" justify="space-between" class="mt-2">
          <a-col :span="24">
            <div class="box-info-supplier">
              <div class="box-label-supplier">Correo asociado a reservas:</div>
              <div class="boxes-email-supplier">
                <div class="box-email-supplier" v-if="emails.length > 0">
                  {{ emails[0] }}
                  <font-awesome-icon :icon="['fas', 'xmark']" @click="$emit('deleteEmail', 0)" />
                </div>
              </div>
            </div>
          </a-col>
          <a-col :span="24" class="mt-2">
            <div class="box-info-supplier">
              <div class="boxes-email-supplier">
                <div
                  class="box-email-supplier"
                  v-for="(email, index) in emails.slice(1)"
                  :key="index"
                >
                  {{ email }}
                  <font-awesome-icon
                    :icon="['fas', 'xmark']"
                    @click="$emit('deleteEmail', '', index)"
                  />
                </div>
              </div>
            </div>
          </a-col>
        </a-row>
        <a-form layout="vertical">
          <a-row :gutter="24" align="middle" justify="space-between" class="mt-3">
            <a-col :span="24">
              <BaseSelectTags
                label="Agregar correos adicionales"
                v-model:modelValue="emailInput"
                placeholder="Agregar correos adicionales"
                :filterOption="false"
                :allowClear="true"
              />
            </a-col>
          </a-row>
        </a-form>
      </div>
      <template #footer>
        <a-row :gutter="24" align="middle" justify="end">
          <a-col>
            <a-button key="button" type="default" size="large" @click="$emit('close')"
              >Cerrar
            </a-button>
            <a-button key="button" type="primary" size="large" @click="$emit('save', emailInput)"
              >Guardar
            </a-button>
          </a-col>
        </a-row>
      </template>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import BaseSelectTags from '@/components/files/reusables/BaseSelectTags.vue';

  defineProps({
    modal: Object,
    emails: Array,
  });

  const emailInput = ref([]);
</script>

<style scoped lang="scss">
  .title-add-emails {
    font-family: Montserrat, serif;
    font-weight: 700;
    font-size: 18px;
    line-height: 25px;
    letter-spacing: -0.015em;
    text-align: center;
    color: #3d3d3d;
    margin-top: 20px;
    margin-bottom: 0px;
  }

  .hr-add-emails {
    border: 0;
    height: 1px;
    background-color: #e9e9e9;
  }

  .email-modal {
    .box-info-supplier {
      display: flex;
      flex-direction: row;
      gap: 10px;
      justify-content: flex-end;
      align-items: flex-start;
      flex-wrap: wrap;

      .box-label-supplier {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 500;
        line-height: 21px;
        letter-spacing: 0.015em;
        text-align: right;
        color: #4f4b4b;
      }

      .boxes-email-supplier {
        display: flex;
        flex-direction: row;
        gap: 2px;
        justify-content: flex-end;
        align-items: flex-start;
        flex-wrap: wrap;

        .box-email-supplier {
          font-family: Montserrat, serif;
          font-size: 14px;
          font-weight: 400;
          line-height: 21px;
          letter-spacing: 0.015em;
          text-align: left;
          color: #4f4b4b;
          border: 1px dashed #bbbdbf;
          padding: 1px 8px 1px 8px;
          border-radius: 4px;

          svg {
            margin-left: 5px;
            color: #c4c4c4;
            cursor: pointer;
          }
        }
      }
    }
  }
</style>
