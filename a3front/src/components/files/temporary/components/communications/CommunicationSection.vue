<template>
  <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
    <div class="box-in-communications">
      <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
        <a-col :span="24">
          <div class="box-title">
            <IconPullRequest color="#212529" width="1.2em" height="1.2em" />
            {{ title }}
          </div>
        </a-col>
      </a-row>
      <div class="box-communications" v-for="(item, index) in items" :key="index">
        <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
          <a-col :span="10">
            <div class="box-title-supplier">
              <font-awesome-icon :icon="['fas', 'users-gear']" />
              {{ item.supplier_name }}
            </div>
          </a-col>
          <a-col :span="14">
            <div class="box-info-supplier">
              <div class="box-label-supplier">Correo asociado a reservas:</div>
              <div class="boxes-email-supplier">
                <div v-if="item.supplier_emails.length > 0" class="box-email-supplier">
                  {{ item.supplier_emails[0] }}
                </div>
              </div>
              <div
                class="box-action-supplier"
                v-show="!fileServiceStore.isLoading"
                @click="$emit('showModalEmails', item.supplier_emails, index, type, null, null)"
              >
                <IconCirclePlus color="#EB5757" width="20px" height="20px" />
              </div>
            </div>
          </a-col>
          <a-col :span="24" class="mt-2">
            <div v-if="item.supplier_emails.length > 1" class="box-info-supplier">
              <div
                class="box-email-supplier"
                v-for="(email, index) in item.supplier_emails.slice(1)"
                :key="index"
              >
                {{ email }}
              </div>
            </div>
          </a-col>
        </a-row>
        <!-- Componentes del servicio -->
        <a-row :gutter="24" align="middle" justify="space-between" class="mt-3">
          <div
            :class="[
              type == 'reservations' ? 'box-new-service-master' : 'box-cancel-service-master',
            ]"
            v-for="(component, index) in item.components"
            :key="index"
          >
            <a-col :span="19">
              <div class="box-name-service-master">{{ component.name }}</div>
            </a-col>
            <a-col :span="5">
              <div class="box-cost-service-master">
                <div class="box-icon-service-master">
                  <IconCircleCheck
                    color="#1ED790"
                    width="20px"
                    height="20px"
                    v-if="component.penality == undefined && component.penality == null"
                  />
                </div>
                <div class="box-icon-service-master-penality">
                  <i
                    class="bi bi-exclamation-triangle"
                    style="font-size: 1.2rem"
                    v-if="
                      component.penality !== undefined &&
                      component.penality !== null &&
                      component.penality > 0
                    "
                  ></i>
                </div>
                <div
                  class="box-label-cost-service-master"
                  v-if="component.penality == undefined && component.penality == null"
                >
                  Costo
                </div>
                <div
                  class="box-label-penality-service-master"
                  v-if="
                    component.penality !== undefined &&
                    component.penality !== null &&
                    component.penality > 0
                  "
                >
                  Penalidad
                </div>
                <div
                  class="box-amount-service-master"
                  v-if="component.penality == undefined && component.penality == null"
                >
                  $. {{ component.amount_cost || 0 }}
                </div>
                <div
                  class="box-amount-penality-service-master"
                  v-if="
                    component.penality !== undefined &&
                    component.penality !== null &&
                    component.penality > 0
                  "
                >
                  $. {{ component.penality || 0 }}
                </div>
              </div>
            </a-col>
          </div>
        </a-row>
        <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
          <a-col :span="12">
            <div
              v-bind:class="[
                'd-flex cursor-pointer',
                !item.showNotes ? 'text-dark-gray' : 'text-danger',
              ]"
              @click="toggleNotes(item)"
            >
              <template v-if="item.showNotes">
                <i class="bi bi-check-square-fill text-danger d-flex" style="font-size: 1.5rem"></i>
              </template>
              <template v-else>
                <i class="bi bi-square d-flex" style="font-size: 1.5rem"></i>
              </template>
              <span class="mx-2">Agregar nota al proveedor</span>
            </div>
          </a-col>
          <a-col :span="12" class="col-end">
            <a-button
              v-if="!item.showNotes"
              danger
              :loading="fileServiceStore.isLoading"
              type="default"
              @click="$emit('showCommunicationFrom', item, type)"
              class="btn-communications d-flex ant-row-middle text-600"
            >
              <i class="bi bi-search"></i>
              <span class="mx-2">Ver comunicación</span>
            </a-button>
          </a-col>
        </a-row>
        <a-row
          v-if="item.showNotes"
          :gutter="24"
          align="middle"
          justify="space-between"
          class="mt-3"
        >
          <a-col :span="24">
            <p class="text-danger my-2">
              <IconPencilLinear color="#EB5757" width="1.4em" height="1.4em" />
              Nota para el proveedor:
            </p>
            <a-row align="top" justify="space-between">
              <a-col flex="auto">
                <a-textarea
                  :class="{ 'error-border': noteError }"
                  v-model:value="item.notas"
                  :disabled="fileServiceStore.isLoading"
                  placeholder="Escribe una nota para el proveedor que podrás visualizar en la comunicación"
                  :auto-size="{ minRows: 3 }"
                />
                <p v-if="noteError && !item.notas?.trim()" class="text-danger mt-2">
                  La nota es requerida.
                </p>
              </a-col>
            </a-row>
            <a-row align="middle" justify="end" class="mt-4">
              <a-col :offset="17" :span="7">
                <a-button
                  danger
                  type="default"
                  size="large"
                  class="text-600 ms-2"
                  style="float: right"
                  :loading="fileServiceStore.isLoading"
                  @click="handleSaveNotes(item, index)"
                >
                  <i v-bind:class="['bi bi-floppy', fileServiceStore.isLoading ? 'mx-2' : '']"></i>
                </a-button>
                <div style="float: right" v-show="!fileServiceStore.isLoading">
                  <file-upload
                    type="default"
                    v-bind:folder="'communications'"
                    @onResponseFiles="responseFilesFrom"
                  />
                </div>
                <a-button
                  danger
                  type="default"
                  style="height: 40px"
                  :loading="fileServiceStore.isLoading"
                  @click="$emit('showCommunicationFrom', item, type)"
                  class="btn-communications d-flex ant-row-middle text-600"
                >
                  <i class="bi bi-search"></i>
                  <span class="mx-2">Ver comunicación</span>
                </a-button>
              </a-col>
            </a-row>
          </a-col>
        </a-row>
      </div>
    </div>
  </a-row>
</template>

<script setup lang="ts">
  import { defineEmits, defineProps, ref, watch } from 'vue';
  import IconPullRequest from '@/components/icons/IconPullRequest.vue';
  import IconCirclePlus from '@/components/icons/IconCirclePlus.vue';
  import IconCircleCheck from '@/components/icons/IconCircleCheck.vue';
  import FileUpload from '@/components/global/FileUploadComponent.vue';
  import { useFileServiceStore } from '@/components/files/temporary/store/useFileServiceStore';
  import { useRouter } from 'vue-router';
  import IconPencilLinear from '@/components/icons/IconPencilLinear.vue';
  import { notification } from 'ant-design-vue';

  const fileServiceStore = useFileServiceStore();
  const router = useRouter();
  const props = defineProps({
    title: String,
    items: Array,
    type: String,
  });

  defineEmits(['showModalEmails', 'showCommunicationFrom']);
  const filesFrom = ref([]);
  const noteError = ref(false); // Estado de error para la nota

  const responseFilesFrom = (files) => {
    filesFrom.value = files.map((item) => item.link);
  };

  const toggleNotes = (item) => {
    item.showNotes = !item.showNotes;
  };

  const handleSaveNotes = async (item, index) => {
    // Validar si el campo de nota está vacío
    if (!item.notas.trim()) {
      noteError.value = true;
      return; // Detener la ejecución si está vacío
    }

    noteError.value = false;

    item.attachments = filesFrom.value;

    const data = { ...item, html: '' };
    try {
      const partialPayload = {
        [props.type]: [data],
      };

      const response = await fileServiceStore.updateCommunicationsTemporary(
        router.currentRoute.value.params.id,
        router.currentRoute.value.params.service_id,
        partialPayload
      );
      if (fileServiceStore.isSuccess) {
        if (response && response.data && response.data[props.type]?.length) {
          props.items[index] = { ...response.data[props.type][0] };

          notification.success({
            message: 'Éxito',
            description: 'Se guardaron las notas correctamente.',
          });
        }
      }

      console.log('Respuesta updateCommunicationsTemporary: ', response);
    } catch (error) {
      console.error('Error al guardar las notas:', error);
    }
  };

  const handleNotSaveNotes = async (item) => {
    try {
      // Reinicia los datos de notas y adjuntos
      noteError.value = false;
      item.notas = '';
      item.attachments = [];

      // Prepara el payload
      const data = { ...item, html: '' };
      console.log('handleNotSaveNotes data', data);

      const partialPayload = {
        [props.type]: [data],
      };

      // Realiza la petición al backend
      const response = await fileServiceStore.updateCommunicationsTemporary(
        router.currentRoute.value.params.id,
        router.currentRoute.value.params.service_id,
        partialPayload
      );

      // Si la operación fue exitosa
      if (fileServiceStore.isSuccess && response?.data?.[props.type]?.length) {
        const updatedItem = response.data[props.type][0];

        // Actualiza el objeto reactivo
        Object.assign(item, updatedItem); // Mantén la referencia reactiva

        // Notificación de éxito
        notification.success({
          message: 'Éxito',
          description: 'Se quitaron las notas correctamente.',
        });
      }
    } catch (error) {
      console.error('Error al guardar las notas:', error);
      // Notificación de error
      notification.error({
        message: 'Error',
        description: 'No se pudieron guardar los cambios.',
      });
    }
  };

  // Configuración de `watch` para monitorear cambios en `showNotes`
  watch(
    () => props.items,
    (items) => {
      items.forEach((item) => {
        watch(
          () => item.showNotes,
          (newValue) => {
            if (!newValue) {
              handleNotSaveNotes(item);
            }
          }
        );
      });
    },
    { deep: true } // Necesario para observar cambios en objetos anidados
  );
</script>

<style scoped lang="scss">
  .error-border {
    border-color: red !important;
  }

  .box-communications {
    padding: 40px;
    border: 1px solid #e9e9e9;
    border-radius: 6px;
    margin-bottom: 20px;

    .box-title-service {
      font-family: Montserrat, serif;
      font-size: 18px;
      font-weight: 700;
      line-height: 25px;
      letter-spacing: -0.01em;
      text-align: left;
      color: #575757;
    }

    .flex-row-service {
      display: flex;
      justify-content: left;
      align-items: center;
      gap: 50px;

      .date-service {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 5px;
        font-size: 14px;
        color: #979797;

        .label-date {
          color: #979797;
          font-family: 'Montserrat', sans-serif !important;
          font-weight: 700;
        }
      }

      .pax-service {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #979797;

        .label-pax,
        .label-adult,
        .label-child {
          color: #979797;
          font-size: 14px;
          font-family: 'Montserrat', sans-serif !important;
          font-weight: 700;
        }
      }
    }

    .box-in-communications {
      padding: 0 20px 20px 20px;
      border: 0;
      border-radius: 6px;
      background-color: #fafafa91;
      width: 100%;

      .box-title {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 600;
        line-height: 21px;
        letter-spacing: 0.015em;
        text-align: left;
        color: #212529;
        margin-bottom: 20px;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;

        svg {
          margin-right: 10px;
        }
      }

      .box-info-supplier {
        display: flex;
        flex-direction: row;
        gap: 2px;
        justify-content: flex-end;
        align-items: flex-start;
        flex-wrap: wrap;

        .box-title-supplier {
          font-family: Montserrat, serif;
          font-size: 14px;
          font-weight: 400;
          line-height: 21px;
          letter-spacing: 0.015em;
          text-align: left;
        }

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
          flex-direction: column;
          gap: 5px;
        }

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
        }

        .box-emails-supplier-others {
        }

        .box-action-supplier {
          cursor: pointer;
        }
      }

      .box-cancel-service-master {
        background-color: #fff2f2;
        padding: 10px 20px;
        border-radius: 6px;
        display: flex;
        flex-direction: row;
        width: 100%;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 10px;
      }

      .box-new-service-master {
        background-color: #ededff;
        padding: 10px 20px;
        border-radius: 6px;
        display: flex;
        flex-direction: row;
        width: 100%;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 10px;
      }

      .box-name-service-master {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 600;
        line-height: 21px;
        letter-spacing: 0.015em;
        text-align: left;
        color: #3d3d3d;
        width: 100%;
      }

      .box-cost-service-master {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-end;

        .box-label-cost-service-master {
          font-family: Montserrat, serif;
          font-size: 12px;
          font-weight: 500;
          line-height: 21px;
          letter-spacing: 0.015em;
          color: #4f4b4b;
        }

        .box-icon-service-master-penality {
          color: #ffcc00;
        }

        .box-label-penality-service-master {
          font-size: 12px;
          font-weight: 500;
          line-height: 21px;
          letter-spacing: 0.015em;
          color: #4f4b4b;
        }

        .box-amount-penality-service-master {
          font-family: Montserrat, serif;
          font-size: 16px;
          font-weight: 700;
          line-height: 23px;
          letter-spacing: 0.015em;
          color: #e4b804;
          margin-left: 10px;
        }

        .box-amount-service-master {
          font-family: Montserrat, serif;
          font-size: 16px;
          font-weight: 700;
          line-height: 23px;
          letter-spacing: 0.015em;
          color: #4f4b4b;
          margin-left: 10px;
        }
      }

      .btn-communications {
        height: 45px;
        color: #eb5757;
        border: 1px solid #eb5757;

        &:hover {
          color: #c63838;
          background-color: #fff6f6;
          border: 1px solid #c63838;
        }
      }

      .col-end {
        display: flex;
        justify-content: end;
      }

      .d-flex {
        align-items: center;
      }

      .check-notes {
        color: #c4c4c4;
      }

      .notes {
        color: #979797;
      }

      .box-communications {
        padding: 5px 20px 20px 20px;
        border: 1px solid #e9e9e9;
      }
    }
  }

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

  .box-reservations-communications {
    padding: 20px;
    border: 1px solid #2e2b9e;
    border-radius: 6px;
    margin-bottom: 20px;
    background-color: #ededff;
    height: 100%;

    .box-cancel-title-service {
      font-family: Montserrat, serif;
      font-size: 14px;
      font-weight: 400;
      line-height: 25px;
      letter-spacing: -0.01em;
      text-align: left;
      color: #575757;
      background-color: #ffffff;
      padding: 5px;
      border-radius: 6px;
    }

    .box-info-supplier {
      justify-content: flex-start !important;
      gap: 1px !important;

      .box-label-supplier {
        font-size: 14px !important;
      }
    }

    .box-new-service-master {
      margin-right: 0 !important;
      margin-left: 0 !important;
      margin-top: 0 !important;
      padding: 0 !important;
    }
  }

  .ant-btn-dangerous {
    background: rgb(0 0 0 / 0%);
  }

  .box-icon {
    display: flex;
    flex-direction: row;
    justify-content: center;
    height: 100%;
    align-items: center;
  }

  .box-cancel-communications {
    padding: 20px;
    border: 1px solid #c63838;
    border-radius: 6px;
    margin-bottom: 20px;
    background-color: #fff2f2;
    height: 100%;

    .box-cancel-title-service {
      font-family: Montserrat, serif;
      font-size: 14px;
      font-weight: 400;
      line-height: 25px;
      letter-spacing: -0.01em;
      text-align: left;
      color: #575757;
      background-color: #ffffff;
      padding: 5px;
      border-radius: 6px;
    }

    .box-info-supplier {
      justify-content: flex-start !important;
      gap: 1px !important;

      .box-label-supplier {
        font-size: 14px !important;
      }
    }

    .box-cancel-service-master {
      margin-right: 0 !important;
      margin-left: 0 !important;
      margin-top: 0 !important;
      padding: 0 !important;
    }
  }

  .email-modal {
    .box-info-supplier {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 10px;
      justify-content: flex-end;

      .box-label-supplier {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 500;
        line-height: 21px;
        letter-spacing: 0.015em;
        text-align: right;
        color: #4f4b4b;
      }

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
</style>
