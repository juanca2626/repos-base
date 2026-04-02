<template>
  <div class="module-operations">
    <a-spin :spinning="false" tip="Cargando...">
      <a-title-section
        title="Calendario de bloqueos"
        icon="calendar"
        :btn="{ title: '+ Crear Bloqueo', action: 'showDrawer' }"
        @handlerShowDrawer="handlerShowDrawer($event)"
      />

      <div class="p-5">
        <CreateLockComponent
          :showDrawer="showDrawer"
          @handlerShowDrawer="handlerShowDrawer($event)"
        />

        <FiltersForm />

        <a-row :gutter="8" style="margin-top: 24px">
          <template v-if="locksByMonthList.length > 0">
            <a-col :span="3">
              <GuideListComponent />
            </a-col>
            <transition name="fade">
              <a-col
                :span="locksByProvidersAndSelectedDates.length > 0 ? 4 : 0"
                :hidden="locksByProvidersAndSelectedDates.length === 0"
              >
                <ItemsSeleccionadosComponent />
              </a-col>
            </transition>
            <a-col
              :span="locksByProvidersAndSelectedDates.length > 0 ? 17 : 21"
              style="overflow-x: auto; overflow-y: hidden"
            >
              <PrincipalCalendarComponent />
            </a-col>
          </template>
          <template v-else>
            <a-col :span="24">
              <div class="text-center">
                <p class="text-lg">No hay bloqueos registrados</p>
              </div>
            </a-col>
          </template>
        </a-row>
      </div>
    </a-spin>

    <AReusableModal>
      <template #default="{ type, formUpdateLock, blockingReasonsOptions, selectedLocksSummary }">
        <div v-if="type === 'update' || type === 'deleteOne'">
          <a-flex gap="middle" justify="space-between" align="center" class="mb-3">
            <a-tag class="custom-tag" color="purple">
              {{ formUpdateLock.provider.code }}
            </a-tag>
            <a-typography-paragraph class="my-0" strong>
              {{ formUpdateLock.provider.fullname }}
            </a-typography-paragraph>
          </a-flex>
          <a-flex gap="small" vertical class="my-3">
            <a-typography-text strong> Motivo de bloqueo: </a-typography-text>
            <a-select
              v-model:value="formUpdateLock.blockingReason"
              label-in-value
              placeholder="Seleccione un motivo de bloqueo"
              :options="blockingReasonsOptions"
              :disabled="type === 'deleteOne'"
            />
          </a-flex>
          <a-flex gap="small" vertical class="mt-4">
            <a-typography-paragraph class="my-0">
              <a-flex align="middle">
                <a-typography-text strong style="margin-right: 8px">
                  Fechas y horario:
                </a-typography-text>
                <a-checkbox
                  v-model:checked="formUpdateLock.completeDay"
                  style="margin-left: auto"
                  :disabled="type === 'deleteOne'"
                >
                  Día completo
                </a-checkbox>
              </a-flex>
            </a-typography-paragraph>
            <a-range-picker
              v-model:value="formUpdateLock.timeRange"
              :show-time="formUpdateLock.completeDay ? false : { format: 'HH:mm' }"
              :format="formUpdateLock.completeDay ? 'DD/MM/YYYY' : 'DD/MM/YYYY HH:mm'"
              :placeholder="['Fecha Inicio', 'Fecha Fin']"
              :disabled="type === 'deleteOne'"
            />
          </a-flex>
          <a-flex gap="small" vertical class="my-3">
            <a-typography-text strong>Observaciones: </a-typography-text>
            <a-textarea
              v-model:value="formUpdateLock.observations"
              :disabled="type === 'deleteOne'"
              show-count
              :maxlength="300"
            />
          </a-flex>
        </div>
        <div v-else-if="type === 'deleteMultiple'">
          <p>
            <strong>¿Deseas eliminar el bloqueo seleccionado?</strong><br />
            Estás a un paso de eliminar el (los) bloqueo(s) para:
          </p>

          <table style="width: 100%">
            <thead style="background-color: #2f353a; color: #ffffff; font-size: 14px">
              <tr>
                <th style="padding: 10px; text-align: center">Código</th>
                <th style="padding: 10px; text-align: center">Motivo</th>
                <th style="padding: 10px; text-align: center">Fechas</th>
                <th style="padding: 10px; text-align: center">Horario</th>
              </tr>
            </thead>
            <tbody style="font-size: 14px">
              <tr v-for="(v, index) in selectedLocksSummary" :key="index">
                <td style="padding: 10px; text-align: center">{{ v.code }}</td>
                <td style="padding: 10px; text-align: center">{{ v.motive }}</td>
                <td style="padding: 10px; text-align: center">{{ v.dates }}</td>
                <td style="padding: 10px; text-align: center">{{ v.time }}</td>
              </tr>
            </tbody>
          </table>

          <a-alert
            message="Se deben revisar los usuarios y fechas seleccionados para realizar esta acción."
            type="warning"
            show-icon
            style="margin-top: 16px; line-height: 20px"
          />
        </div>
      </template>
    </AReusableModal>
  </div>
</template>

<script lang="ts" setup>
  import { storeToRefs } from 'pinia';
  import ATitleSection from '@/components/backend/ATitleSection.vue';

  /*** COMPONENTS ***/
  import FiltersForm from '@operations/modules/blackout-calendar/components/FiltersForm.vue';

  import CreateLockComponent from '@operations/modules/blackout-calendar/components/CreateLockComponent.vue';
  import GuideListComponent from '@operations/modules/blackout-calendar/components/GuideListComponent.vue';
  import ItemsSeleccionadosComponent from '@operations/modules/blackout-calendar/components/ItemsSeleccionadosComponent.vue';
  import PrincipalCalendarComponent from '@operations/modules/blackout-calendar/components/PrincipalCalendarComponent.vue';
  import AReusableModal from '@operations/modules/blackout-calendar/components/global/AReusableModal.vue';

  // Composables
  import { useBlockCalendar } from '../composables/useBlockCalendar';
  import { useBlockCalendarStore } from '../store/blockCalendar.store';
  // import { useModalStore } from '@operations/modules/blackout-calendar/store/modal.store';

  const { showDrawer, handlerShowDrawer } = useBlockCalendar();
  const blockCalendarStore = useBlockCalendarStore();
  const { locksByMonthList, locksByProvidersAndSelectedDates } = storeToRefs(blockCalendarStore);

  // const modalStore = useModalStore();
  // const { blockingReasonsOptions } = storeToRefs(modalStore);
</script>

<style lang="scss">
  // @import '@/scss/custom.scss';
</style>
