<template>
    <div class="card">
        <div class="container-fluid col-lg-12">
            <div class="m-0">
                <div class="form-group row">
                    <div class="col-lg-2">
                        <label>Año</label>
                    </div>
                    <div class="col-lg-2">
                        <select name="year" id="" v-model="year" class="form-control">
                                <option v-for="year in years" :value="year">{{ year }}</option>
                        </select> 
                    </div>
                    <div class="col-lg-2">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                        <button @click="downloadMarkups()" class="btn btn-success" type="submit" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            Descargar
                        </button>
                    </div>
                </div>
                <div class="form-group row">                    
                    <div class="col">
                        <label>Margen de protección (%)</label>
                        <div class="col-sm-12 p-0">
                            <input type="number" v-model="form.markup" class="form-control" />
                        </div>
                        <span class="invalid-feedback-select"
                              v-show="errors.has('markup')">
                                <span>{{ errors.first('markup') }}</span>
                            </span>
                    </div>
                    <div class="col" v-if="!edit">
                        <label>{{ $t('hotels.category_name') }}</label>
                        <div class="col-sm-12 p-0">
                            <v-select :options="categories"
									:reduce="category => category.code"
									v-model="form.category_id"
									autocomplete="true"
									data-vv-as="type service"
									data-vv-name="type_service"
									name="type_service"
									v-validate="'required'">
                            </v-select>
                            <span class="invalid-feedback-select"
                                  v-show="errors.has('category_id')">
                                <span>{{ errors.first('category_id') }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="col" v-if="edit">
                        <label>{{ $t('services.typeService') }}</label>
                        <div class="col-sm-12 p-0">
                            <input type="text" readonly="readonly"
                                   v-bind:value="form.hotel_category.translations[0].value"
                                   class="form-control" />
                        </div>
                    </div>
                    <div class="col mt-4">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                        <button @click="saveMarkup()" class="btn btn-success" type="submit" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{$t('global.buttons.submit')}}
                        </button>

                        <template v-if="edit">
                            <button @click="cancel()" class="btn btn-secondary" type="button" v-if="!loading">
                                <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                {{$t('global.buttons.back')}}
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="w-100" v-if="config_markups.length > 0 && !edit">
                <table class="table-bordered">
                    <thead>
                    <tr>
                        <th>Margen de protección</th>
                        <th>{{ $t('hotels.category_name') }}</th>
                        <th>Estado</th>
                        <!-- <th>Fecha última ejecución</th> -->
                        <th><i class="fa fa-cogs"></i></th>
                    </tr>
                    </thead>
                    <tbody>
                      <template v-for="(config_markup, cm) in config_markups">
                        <tr v-bind:key="cm">
                          <td>{{ config_markup.markup }}%</td>
                          <td>{{ config_markup.hotel_category.translations[0].value }}</td>
                          <!-- <td>
                              <template v-if="config_markup.percent < 100 && config_markup.percent > 0">
                                  <div class="progress m-2">
                                      <div class="progress-bar" role="progressbar"
                                           v-bind:style="'width: ' + config_markup.percent + '%;'" aria-valuenow="25"
                                           aria-valuemin="0" aria-valuemax="100">{{ config_markup.percent }}%</div>
                                  </div>
                              </template>
                              <template v-else>
                                  {{ showStatus(config_markup.status) }}
                              </template>
                          </td> -->
                          <td>
                              <template v-if="config_markup.created_at != config_markup.updated_at">
                                {{ config_markup.updated_at | formatDate }}
                              </template>
                          </td>
                          <td>
                              <img src="/images/loading.svg" v-if="loading" width="40px"/>
                              <a v-bind:href="'/translations/config_markups/hotel/' + config_markup.id + '/export'"
                                 v-if="config_markup.status == 2 && !loading"
                                 class="btn btn-white" type="button">
                                  <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                  Descargar afectados
                              </a>
                              <button @click="updateMarkup(config_markup)"
                                      v-bind:disabled="config_markup.status != 2"
                                      class="btn btn-secondary" type="button" v-if="!loading">
                                  <font-awesome-icon :icon="['fas', 'edit']"/>
                              </button>
                              <button @click="deleteMarkup(config_markup)"
                                      v-bind:disabled="config_markup.status != 2"
                                      class="btn btn-danger" type="button" v-if="!loading">
                                  <font-awesome-icon :icon="['fas', 'trash']"/>
                              </button>
                          </td>
                        </tr>
                      </template>
                    </tbody>
                </table>
            </div>
            <!-- div class="row col-lg-12">
                <div class="col-lg-6">
                    <button class="btn btn-success" v-if="cities.prev_page_url !=null" @click="getCitiesByPage(cities.prev_page_url)">Pagina anterior</button>
                </div>
                <div class="col-lg-6">
                    <button class="btn btn-success" v-if="cities.next_page_url !=null" @click="getCitiesByPage(cities.next_page_url)">Pagina siguiente</button>
                </div>
            </div -->
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'

  export default {
    components: {
      vSelect
    },
    data () {
      return {
        loading: false,
        categories: [],
        config_markups: [],
        form: {},
        edit: false,
        years: [],
        year: null
      }
    },
    computed: {

    },
    mounted: function () {
      this.getMarkups()
      this.getCategories()
      this.fillYears()
    },
    methods: {
        downloadMarkups: function () {
            this.loading = true; // Inicia el estado de carga
            const year = this.year;
            window.location.href = '/hotel-markups/download-excel?year=' + year;
            this.loading = false; // Termina el estado de carga
        },
        fillYears() {
            const currentYear = new Date().getFullYear();
            for (let i = 0; i <= 2; i++) {
                this.years.push(currentYear + i);
            }
        },
        cancel: function () {
            this.edit = false
            this.form = {}
        },
        updateMarkup: function (_markup) {
            this.edit = true
            this.form = _markup
        },
      showStatus: function (_state) {
        return (_state > 0) ? ((_state == 1) ? 'Pendiente' : 'Procesado') : 'Inactivo'
      },
      getCategories: function () {
        API.get('/typeclass/selectbox?lang=' + localStorage.getItem('lang'))
          .then((result) => {
            //categories
            let categorias = result.data.data

            /*
            this.categories.push({
                label: 'TODOS',
                code: '0',
            })
            */

            categorias.forEach((category) => {
              this.categories.push({
                label: category.translations[0].value,
                code: category.translations[0].object_id
              })
            })
          })
      },
      getMarkups: function () {

        this.loading = true
        this.edit = false

        API.get('/config_markups?type=2&lang=' + localStorage.getItem('lang'))
          .then((result) => {
            this.loading = false
            this.config_markups = result.data.data
          })
      },
      saveMarkup: function () {
        console.log(this.form)

          if(this.form.markup == undefined)
          {
              this.$notify({
                  group: 'main',
                  type: 'error',
                  title: 'Error',
                  // text: this.$t('cities.error.messages.state_delete')
                  text: 'Ingrese un margen de protección'
              })
              return false
          }

          if(this.form.category_id == undefined)
          {
              this.$notify({
                  group: 'main',
                  type: 'error',
                  title: 'Error',
                  // text: this.$t('cities.error.messages.state_delete')
                  text: 'Ingrese una categoría de hotel'
              })
              return false
          }

        this.loading = true

        API.post('/config_markups', {
          form: this.form,
          type: 2,
        })
          .then((result) => {
            //categories
            this.loading = false

              if(result.data.success)
              {
                  this.getMarkups()
                  this.form = {}
              }

          })
      },
        activateMarkup: function (_markup) {
            this.loading = true

            _markup.status = 1

            API.post('/config_markups', {
                form: _markup,
            })
                .then((result) => {
                    //categories
                    this.loading = false

                    if(result.data.success)
                    {
                        this.getMarkups()
                        this.form = {}
                    }
                })
        },
        deleteMarkup: function (_markup) {
            API({
                method: 'DELETE',
                url: 'config_markups/' + _markup.id
            })
                .then((result) => {
                    if (result.data.success === true) {
                        this.loading = false
                        this.getMarkups()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.states'),
                            // text: this.$t('cities.error.messages.state_delete')
                            text: (result.data.message) ? result.data.message : this.$t('cities.error.messages.state_delete')
                        })
                    }
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('cities.error.messages.name'),
                    text: this.$t('cities.error.messages.connection_error')
                })
            })
        },
    },
  }
</script>

<style>
    .yellow_icon {
        color: #e5c900;
    }
</style>
