<template>

    <div class="row">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="col-sm-12">
            <form @submit.prevent="submit()">
              <div class="col-12 row">

                  <div class="col-sm-2">
                    <label class="col-sm-12 col-form-label">Mes</label>
                  </div>

                  <div class="col-sm-6 mb-3">
                    <v-select class=""
                      type="month"
                      :options="month"
                      :reduce="label => label.code"
                      label="label"
                      v-model="formulario.month" clearable
                      autocomplete="true"></v-select>
                  </div>

              </div>

              <div class="col-12 mb-3 row">
                  <div class="col-sm-2">
                      <label class="col-sm-12 col-form-label">Año</label>
                  </div>

                  <div class="col-sm-6">

                    <v-select class=""
                          type="month"
                          :options="years"
                          :reduce="label => label.code"
                          label="label"
                          v-model="formulario.year" clearable
                          autocomplete="true"></v-select>
                  </div>

              </div>

              <div class="col-12 mb-3 row">
                  <div class="col-sm-2">
                      <label class="col-sm-12 col-form-label">Fee</label>
                  </div>

                  <div class="col-sm-6">
                    <input type="text" class="form-control" required v-model="formulario.fee" disabled/>
                  </div>

                  <div class="col-sm-4">
                    <input type="button" class="btn btn-warning" value="Editar Fee" @click="openModalFee()"/>
                  </div>

              </div>

              <div class="col-12 mb-3 row">

                <div class="col-sm-2">
                    <label class="col-sm-12 col-form-label"></label>
                </div>

                <div class="col-sm-6">
                  <input type="file" name="file_import" id="file_import" class="form-control" v-on:change="onChangeFileUpload" ref="file" required />
                </div>

              </div>

            </form>

        </div>

        <div class="col-sm-6" style="margin-left: 28px;">
            <div slot="footer">
                <button @click="validateBeforeSubmit()" class="btn btn-success" type="submit" :disabled="hasActiveImport">
                  <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}}
                </button>
                <button @click="CancelForm" class="btn btn-danger" type="reset">
                  {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>

        <!-- Últimos 3 reportes cargados -->
        <div class="col-sm-12 mt-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Últimos reportes cargados</strong>
                    <small class="text-muted" v-if="hasActiveImport"><span class="badge badge-warning">Actualizando...</span></small>
                </div>
                <div class="card-body p-0">
                    <div v-if="recentReports.length === 0" class="text-center text-muted py-3">
                        No hay reportes cargados aún.
                    </div>
                    <table class="table table-sm table-hover mb-0" v-else>
                        <thead class="thead-light">
                            <tr>
                                <th>Período</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Progreso</th>
                                <th>Filas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="report in recentReports" :key="report.id">
                                <td>{{ report.month }}/{{ report.year }}</td>
                                <td>{{ report.created_at }}</td>
                                <td>
                                    <span :class="statusBadgeClass(report.status)">
                                        {{ statusLabel(report.status) }}
                                    </span>
                                </td>
                                <td style="min-width: 140px;">
                                    <div v-if="report.status === 'FAILED'" class="text-danger small" :title="report.error_message">
                                        <font-awesome-icon :icon="['fas', 'exclamation-triangle']"/> Error
                                    </div>
                                    <div v-else>
                                        <b-progress
                                            :value="report.percentage"
                                            :variant="progressVariant(report.status)"
                                            :striped="report.status === 'PROCESSING'"
                                            :animated="report.status === 'PROCESSING'"
                                            show-value
                                            class="mt-1"
                                            style="height: 16px; font-size: 11px;"
                                        ></b-progress>
                                    </div>
                                </td>
                                <td>
                                    <span v-if="report.total_rows > 0">
                                        {{ report.processed_rows }} / {{ report.total_rows }}
                                    </span>
                                    <span v-else class="text-muted">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Error detalle si hay error -->
                    <div v-for="report in recentReports" :key="'err-' + report.id">
                        <div v-if="report.status === 'FAILED' && report.error_message" class="alert alert-danger mx-3 mb-2 py-1 small">
                            <strong>{{ report.month }}/{{ report.year }}:</strong> {{ report.error_message }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <b-modal title="Editar Fee" centered ref="my-modal-fee" size="sm">
            <input type="number" class="form-control" v-model="formFee.fee">

            <div slot="modal-footer">
                <button @click="saveFee()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModalFee()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

    </div>



</template>

<script>
  import { API } from './../../api'
  import vSelect from 'vue-select';
  import 'vue-select/dist/vue-select.css';
  import Loading from 'vue-loading-overlay';
  import 'vue-loading-overlay/dist/vue-loading.css';

  export default {

    components: {
      vSelect,
      Loading
    },
    data: () => {
      return {
        month: [
                {code: '01', label: 'Enero'},
                {code: '02', label: 'Febrero'},
                {code: '03', label: 'Marzo'},
                {code: '04', label: 'Abril'},
                {code: '05', label: 'Mayo'},
                {code: '06', label: 'Junio'},
                {code: '07', label: 'Julio'},
                {code: '08', label: 'Agosto'},
                {code: '09', label: 'Setiembre'},
                {code: '10', label: 'Octubre'},
                {code: '11', label: 'Noviembre'},
                {code: '12', label: 'Diciembre'},
              ],
        formulario:{
            month:'',
            year:'',
            fee:'',
        },
        formAction: 'post',
        loading: false,
        reporte: null,
        file:'',
        years:[],
        formFee:{
          fee:'',
        },
        recentReports: [],
        pollingInterval: null,
      }
    },
    computed: {
      hasActiveImport() {
        return this.recentReports.some(r =>
            (r.status === 'PENDING' || r.status === 'PROCESSING') &&
            String(r.month) === String(this.formulario.month) &&
            String(r.year) === String(this.formulario.year)
        );
      }
    },
    mounted () {
      this.comboAno();
      this.fechaActual();
      this.getEmail();
      this.loadRecentReports();
    },
    beforeDestroy() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
      }
    },
    methods: {
      comboAno(){
           let n = (new Date()).getFullYear();
           this.years = []
           for(let i = n; i >= n - 1; i--) {
            this.years.push({code: i, label: i})
           }
      },

      fechaActual(){
            let fecha = (new Date()).getMonth();
            this.formulario.month = ('0'+(fecha + 1)).slice(-2);
            let anio =(new Date()).getFullYear();
            this.formulario.year = anio;
      },

      CancelForm () {
        this.$router.push('/report-hyperguest')
      },

      validateBeforeSubmit () {
        if (this.hasActiveImport) {
            this.$notify({
                group: 'main',
                type: 'warn',
                title: 'Atención',
                text: 'Ya hay una importación en curso. Espere que termine antes de subir otro archivo.'
            });
            return;
        }
        this.$validator.validateAll().then((result) => {
          if (result) {
            this.importReport()
          }
        })
      },

      onChangeFileUpload: function () {
        this.file = this.$refs.file.files[0]
      },

      async importReport() {
        if(!this.file){
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Error',
            text: 'Seleccione un archivo'
          })
          return false
        }

        let formData = new FormData()
        formData.append('file', this.file)
        formData.append('month', this.formulario.month)
        formData.append('year', this.formulario.year)
        formData.append('fee', this.formulario.fee)

        this.loading = true

        try {
            const { data } = await API({
                method: 'POST',
                url: 'report-hyperguest/import',
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            this.loading = false;

            if (data.type === 'success') {
                this.$notify({
                    group: 'main',
                    type: 'success',
                    title: 'Éxito',
                    text: data.message
                });
                // Refresh list and start polling since job is now queued
                await this.loadRecentReports();
                this.startPolling();
            } else {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        } catch (error) {
            this.loading = false;
            this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error',
                text: 'Error al iniciar la importación.'
            });
        }
      },

      async loadRecentReports() {
        try {
            const { data } = await API.get('report-hyperguest/recent');
            this.recentReports = data;
        } catch (error) {
            console.error('Error loading recent reports:', error);
        }
      },

      // Only called explicitly after clicking Grabar
      startPolling() {
        if (this.pollingInterval) return; // ya hay polling activo

        this.pollingInterval = setInterval(async () => {
            await this.loadRecentReports();

            // Detener cuando ya no haya importaciones activas
            if (!this.hasActiveImport) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        }, 90000); // cada 60 segundos
      },

      statusBadgeClass(status) {
        const map = {
            'COMPLETED':  'badge badge-success',
            'FAILED':     'badge badge-danger',
            'PROCESSING': 'badge badge-info',
            'PENDING':    'badge badge-warning',
        };
        return map[status] || 'badge badge-secondary';
      },

      statusLabel(status) {
        const map = {
            'COMPLETED':  'Completado',
            'FAILED':     'Error',
            'PROCESSING': 'Procesando',
            'PENDING':    'Pendiente',
        };
        return map[status] || status;
      },

      progressVariant(status) {
        if (status === 'COMPLETED') return 'success';
        if (status === 'FAILED') return 'danger';
        if (status === 'PROCESSING') return 'info';
        return 'warning';
      },

      openModalFee(){
        this.$refs['my-modal-fee'].show()
        this.formFee.fee = this.formulario.fee;
      },

      getEmail(){
        API.get('report-hyperguest/list-email').then((result) => {
            this.formulario.fee = result.data.fee
        })
      },

      saveFee(){
        this.loading = true
        API({
            method: 'put',
            url: 'report-hyperguest/updateFee',
            data: this.formFee,
          })
          .then((result) => {
            this.loading = false
            this.getEmail();
            this.$notify({
                group: 'main',
                type: 'success',
                title: this.$t('hyperguest.notification'),
                text: "El proceso se realizo correctamente!!",
            })
            this.$refs['my-modal-fee'].hide()
          })
          .catch((error) => {
            this.loading = false
            this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error',
                text: error.response.data.message
              })
          })
      },

      hideModalFee(){
        this.$refs['my-modal-fee'].hide()
      },

    }
  }
</script>

<style lang="stylus">

</style>
