<template>
    <div class="col-12">
        <div class="col-4 text-left" style="padding-top: 20px;">
            <b-form-checkbox v-model="showCalendar" name="check-button" button button-variant="primary">
                <span v-if="!showCalendar">
                    <i class="far fa-calendar-alt"></i> Ver tarifas en calendario
                </span>
                <span v-if="showCalendar">
                    <i class="fas fa-list"></i> Ver lista de tarifas
                </span>
            </b-form-checkbox>
        </div>
        <div class="col-12 mt-3">
            <div class="col-12" v-if="!showCalendar">
                <div class="row">
                    <div class="offset-8  col-4 text-right">
                        <router-link
                            :to="'/hotels/'+$route.params.hotel_id+'/manage_hotel/rates/rates/cost/add'"
                            v-if="$can('create', 'rates')"
                            >
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-plus"></i> {{$t('global.buttons.add')}}
                            </button>
                        </router-link>
                        <button class="btn btn-primary" @click="showModalDuplicateRate">
                            <i class="far fa-copy"></i> Duplicar Tarifas
                        </button>
                    </div>
                    <div class="col-12">
                        <table-client :columns="table.columns"
                                      :data="rates"
                                      :options="tableOptions"
                                      id="dataTable"
                                      ref="dataTable"
                                      theme="bootstrap4">
                            <div slot="name" slot-scope="props">
                                {{ props.row.name }}
                            </div>
                            <div class="table-meal" slot="meal" slot-scope="props">
                                {{ props.row.meal.translations[0].value }}
                            </div>
                            <div class="table-rates_plan_type" slot="rates_plan_type" slot-scope="props">
                                {{
                                    props.row.rates_plan_type.name
                                }}
                            </div>
                            <div class="table-charge_type" slot="charge_type" slot-scope="props">
                                {{ props.row.charge_type.name}}
                            </div>
                            <div class="table-channel" slot="channel" slot-scope="props">
                                <span v-if="props.row.type_channel == 2">HYPERGUEST PULL</span>
                                <span v-else-if="props.row.type_channel == 1">HYPERGUEST PUSH</span>
                                <span v-else-if="props.row.type_channel == null">AURORA</span>
                            </div>
                            <div class="table-charge_type" slot="status" slot-scope="props">
                                <span v-if="props.row.status === 1" class="badge badge-success" style="font-size: 14px;">{{ checkboxChecked(props.row.status) }}</span>
                                <span v-if="props.row.status === 0" class="badge badge-danger" style="font-size: 14px;">{{ checkboxChecked(props.row.status) }}</span>
                            </div>

<!--                            <div class="table-actions" slot="actions" slot-scope="props">-->
<!--                                <menu-edit :id="props.row.id" :options="options" @remove="remove(props.row)"-->
<!--                                           @clonar="clonar(props.row.id)" @notifytarifa="notifytarifa(props.row)"-->
<!--                                           @updatestatus="updatestatus(props.row)"/>-->
<!--                            </div>-->

                            <div class="table-actions" slot="actions" slot-scope="props">
                                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                                    <template slot="button-content">
                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                    </template>

                                    <li @click="to_manage_rate(props.row)" class="nav-link m-0 p-0">
                                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'rates')">
                                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                                            {{$t('global.buttons.edit')}}
                                        </b-dropdown-item-button>
                                    </li>

                                    <li @click="to_supplements(props.row)" class="nav-link m-0 p-0">
                                        <b-dropdown-item-button class="m-0 p-0">
                                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                                            Suplementos
                                        </b-dropdown-item-button>
                                    </li>

                                    <li @click="will_update_status(props.row)" class="nav-link m-0 p-0">
                                        <b-dropdown-item-button class="m-0 p-0">
                                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                                            <span v-if="props.row.status">Desactivar </span>
                                            <span v-else>Activar</span>
                                        </b-dropdown-item-button>
                                    </li>

                                    <li @click="will_notify_promotional_rate(props.row)" class="nav-link m-0 p-0">
                                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('notifynew', 'rates')">
                                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                                            Notificar Tarifa Promocional
                                        </b-dropdown-item-button>
                                    </li>

                                    <li @click="will_remove_rate(props.row)" class="nav-link m-0 p-0">
                                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('delete', 'rates')">
                                            <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                            Eliminar
                                        </b-dropdown-item-button>
                                    </li>

                                </b-dropdown>
                            </div>

                        </table-client>
                    </div>
                </div>
            </div>
            <div v-else>
                <Calendar></Calendar>
            </div>
            <b-modal :title="'Duplicar Tarifas'" centered ref="my-modal-duplicate-rates" size="md" :no-close-on-backdrop=true
                     :no-close-on-esc=true>
                <div>
                    <div class="row text-left" style="padding-top: 20px;" v-if="!showCalendar">
                        <div class="col-12 mb-4">
                            <multiselect :clear-on-select="false"
                                         :close-on-select="false"
                                         :multiple="true"
                                         :options="rates_select"
                                         placeholder="Elegir Tarifas"
                                         :preserve-search="true"
                                         :taggable="true"
                                         v-model="rate_plan_ids"
                                         label="name"
                                         ref="multiselect"
                                         track-by="id">
                            </multiselect>
                        </div>
                        <div class="col-12 mb-4">
                            <select ref="period" class="form-control" id="year_from" required size="0" v-model="year_from">
                                <option :value="year.value" v-for="year in years">
                                    {{ year.text}}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 mb-4">
                            <select ref="period" class="form-control" id="year_to" required size="0" v-model="year_to">
                                <option :value="year.value" v-for="year in years">
                                    {{ year.text}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div slot="modal-footer">
                    <button class="btn btn-success" @click="validateFieldsDuplicate">{{$t('global.buttons.accept')}}</button>
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>
            <b-modal :title="'Tarifas'" centered ref="my-modal" size="md" :no-close-on-backdrop=true
                     :no-close-on-esc=true>
                <div>
                    <ol>
                        <li v-for="rate in rate_plans_selected" v-if="rate._selected">
                            <p> {{ rate.name }} | <b>Desea Duplicar inventario?</b> <br>
                                Marcar si su respuesta es "SI" <input class="" type="checkbox" v-model="rate.duplicate_inventory">
                            </p>
                        </li>
                    </ol>
                </div>
                <div slot="modal-footer">
                    <button class="btn btn-success" @click="duplicateRate">{{$t('global.buttons.accept')}}</button>
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>
            <b-modal title="Coincidencias de la Tarifa" ref="my-modal-uses" hide-footer size="lg">

                <h4>Se encontró que la tarifa está siendo utilizada en los siguientes paquetes.</h4>

                <div class="left">
                    <button v-if="!see_confirm" class="btn btn-success" :disabled="loading" type="button" @click="will_send_report()">
                        <span>Reportar</span>
                    </button>
                    <div class="alert alert-warning" v-if="see_message">
                        <i class="fa fa-info-circle"></i> La tarifa ya fué reportada por favor espere las 48 hrs y será desactivada automáticamente.
                    </div>
                    <div class="alert alert-warning" v-if="see_confirm">
                        <i class="fa fa-info-circle"></i> Notificará a los usuarios correspondientes y el hotel se desactivará automáticamente en 48 hrs.
                    </div>
                    <button v-if="see_confirm" class="btn btn-success" :disabled="loading" type="button" @click="send_report()">
                        <span>Confirmar</span>
                    </button>
                    <br>
                    <br>
                </div>

                <table-client :columns="table_used_packages.columns" :data="used_rate_plans.packages"
                              :options="table_options_used_packages" id="dataTable"
                              theme="bootstrap4">
                    <div class="table-supplement" slot="package" slot-scope="props">
                    <span v-if="props.row.code">
                                {{ props.row.id }} - [{{ props.row.code }}] -
                            </span>
                        <span v-if="!(props.row.code)">
                                <span v-if="props.row.extension=='1'">[E{{ props.row.id }}] - </span>
                                <span v-if="props.row.extension=='0'">[P{{ props.row.id }}] - </span>
                            </span>
                        <span v-html="props.row.translations[0].name"></span>
                    </div>
                    <div class="table-supplement" slot="plan_rate" slot-scope="props">
                        [{{ props.row.plan_rates[0].service_type.abbreviation}}] - {{props.row.plan_rates[0].name}}
                    </div>
                    <div class="table-supplement" slot="categories" slot-scope="props">
                        <div class="badge badge-primary bag-category mr-1" v-if="category.services.length>0"
                             v-for="category in props.row.plan_rates[0].plan_rate_categories">
                            {{category.category.translations[0].value}} ({{ category.services.length }})
                        </div>
                    </div>
                    <div class="table-supplement" slot="period" slot-scope="props">
                        {{props.row.plan_rates[0].date_from | formatDate}} - {{props.row.plan_rates[0].date_to |
                        formatDate}}
                    </div>
                </table-client>

            </b-modal>


            <b-modal :title="'Actualizar Estado'" centered ref="my-modal-status" size="md" :no-close-on-backdrop=true
                     :no-close-on-esc=true>
                <div>
                    <p> <b>¿Desea <span v-if="rate_selected.status">desactivar </span>
                        <span v-else>activar</span> la tarifa?</b> <br><br>
                        <label>
                            <input class="" type="checkbox" v-model="check_send_to_mkt"> Notificar a Marketing que revise las notas generales
                        </label>
                    </p>
                </div>
                <div slot="modal-footer">
                    <button class="btn btn-success" @click="updatestatus">{{$t('global.buttons.accept')}}</button>
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>

            <b-modal :title="'Mejora de Tarifa Promocional'" centered ref="my-modal-notify-promotional-rate" size="md" :no-close-on-backdrop=true
                     :no-close-on-esc=true>
                <div>
                    <p>
                        <b>
                            ¿Desea notificar la Mejora de Tarifa Promocional?
                        </b>
                    </p>
                </div>
                <div slot="modal-footer">
                    <button class="btn btn-success" @click="notifytarifa">{{$t('global.buttons.accept')}}</button>
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>

            <b-modal :title="'Confirmar Eliminación'" centered ref="my-modal-remove-rate" size="md" :no-close-on-backdrop=true
                     :no-close-on-esc=true>
                <div>
                    <p>
                        <b>
                            ¿Desea eliminar la Tarifa?
                        </b>
                    </p>
                </div>
                <div slot="modal-footer">
                    <button class="btn btn-success" @click="remove(false)">{{$t('global.buttons.accept')}}</button>
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>

        </div>
        <block-page></block-page>
    </div>

</template>

<script>
    import {API} from './../../../../../../api'
    import TableClient from './../../../../../../components/TableClient'
    import MenuEdit from './../../../../../../components/MenuEdit'
    import Calendar from './Calendar'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import BlockPage from '../../../../../../components/BlockPage'
    import Multiselect from 'vue-multiselect'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import moment from 'moment'

    export default {
        components: {
            Calendar,
            datePicker,
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            BModal,
            BlockPage,
            Multiselect,
            BFormCheckbox
        },
        data: () => {
            return {
                rates: [],
                year_from: moment().format('YYYY'),
                year_to: moment().add(1, 'year').format('YYYY'),
                rate_plan_ids: [],
                rate_plans_selected: [],
                currentRate: '',
                showCalendar: false,
                table: {
                    columns: ['id', 'code', 'name', 'meal', 'rates_plan_type', 'channel', 'status', 'actions']
                },
                rate_plan_choose : "",
                table_used_packages: {
                    columns: ['package', 'plan_rate', 'categories', 'period']
                },
                used_rate_plans: {
                    rate_plan_id: "",
                    rate_plan_name: "",
                    packages: []
                },
                see_confirm:false,
                see_message:false,
                loading:false,
                check_send_to_mkt:false,
                rate_selected:"",
                blocked_add_new: false
            }
        },
        mounted() {
            this.fetchData(this.$i18n.locale)
        },
        computed: {
            years () {
                let previousYear = moment().subtract(2, 'years').year()
                let currentYear = moment().add(5, 'years').year()

                let years = []

                do {
                    years.push({ value: previousYear, text: previousYear })
                    previousYear++
                } while (currentYear > previousYear)

                return years
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        name: this.$i18n.t('global.name'),
                        meal: this.$i18n.t('hotelsmanagehotelratesratescost.meal_name'),
                        rates_plan_type: this.$i18n.t('hotelsmanagehotelratesratescost.rates_plan_type'),
                        channel: 'Channel',
                        status: 'Status',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['name']
                }
            },
            table_options_used_packages: function () {
                return {
                    headings: {
                        package: "Paquete",
                        plan_rate: "Plan Tarifario",
                        categories: "Categorías",
                        period: "Periodo"
                    },
                    sortable: [],
                    filterable: []
                }
            },
        },
        created() {

            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {

            will_remove_rate: function (me) {
                this.rate_selected = me
                this.$refs['my-modal-remove-rate'].show()
            },
            will_notify_promotional_rate: function (me) {
                this.rate_selected = me
                this.$refs['my-modal-notify-promotional-rate'].show()
            },
            will_update_status: function (me) {
                this.rate_selected = me
                this.check_send_to_mkt = false
                this.$refs['my-modal-status'].show()
            },
            to_manage_rate: function (me) {
                this.$router.push('/hotels/' + me.hotel_id + '/manage_hotel/rates/rates/cost/edit/' + me.id)
            },
            to_supplements: function (me) {
                this.$router.push('/hotels/' + me.hotel_id + '/manage_hotel/rates/rates/cost/supplements/' + me.id)
            },
            will_send_report(){
                this.see_message = false
                this.loading = true

                API({
                    method: 'get',
                    url: 'deactivatable/entity?entity=App/RatesPlans&object_id='+ this.used_rate_plans.rate_plan_id
                })
                    .then((result) => {
                        if (result.data.success) {
                            if( result.data.data.length > 0 ){
                                this.see_message = true
                                let me = this
                                setTimeout(function(){
                                    me.see_message = false
                                }, 5000)
                            } else{
                                this.see_confirm = true
                            }
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.hotels'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                        this.loading = false
                    })
            },
            send_report() {

                this.loading = true

                let packages_ = []

                this.used_rate_plans.packages.forEach(p => {
                    let obj = {}
                    if (p.code) {
                        obj.package = p.id + ' - [' + p.code + '] - '
                    } else {
                        if (p.extension === '1') {
                            obj.package = '[E' + p.id + '] - '
                        } else {
                            obj.package = '[P' + p.id + '] - '
                        }
                    }
                    obj.package += p.translations[0].name

                    obj.plan_rate = '[' + p.plan_rates[0].service_type.abbreviation + '] - ' + p.plan_rates[0].name

                    obj.categories = []
                    p.plan_rates[0].plan_rate_categories.forEach(c => {
                        if (c.services.length > 0) {
                            obj.categories.push(c.category.translations[0].value + ' (' + c.services.length + ')')
                        }
                    })

                    obj.period = this.formatDate(p.plan_rates[0].date_from) + ' - ' + this.formatDate(p.plan_rates[0].date_to)

                    packages_.push(obj)

                })

                let data = {
                    rate_plan_id: this.used_rate_plans.rate_plan_id,
                    rate_plan_name: this.used_rate_plans.rate_plan_name,
                    packages: packages_
                }

                API({
                    method: 'post',
                    url: 'rates/cost/' + data.rate_plan_id + '/uses/report',
                    data: {data: data}
                })
                    .then((result) => {
                        if (result.data.success) {
                            this.see_confirm = false
                            this.hideModal();
                            this.used_rate_plans.packages = []
                            this.used_rate_plans.rate_plan_id = ""
                            this.used_rate_plans.rate_plan_name = ""
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.rooms'),
                                text: "Enviado correctamente"
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                        this.loading = false
                    })

            },
            checkboxChecked: function (status) {
                if (status == 1) {
                    return 'Activada'
                } else {
                    return 'Desactivada'
                }
            },
            updatestatus: function () {
                this.rate_plan_choose = this.rate_selected
                let rate_plan = this.rate_selected

                let action_text = (this.rate_selected.status) ? "Desactivando" : "Activando"

                this.$root.$emit('blockPage', {message: action_text + " tarifa"})
                API.post('update/status/rate_plan',
                    {
                            rate_plan_id: rate_plan.id,
                            status: !rate_plan.status,
                            send_to_mkt: (this.check_send_to_mkt) ? 1 : 0
                    })
                    .then((result) => {
                        this.$refs['my-modal-status'].hide()
                        if (result.data.success !== true) {
                            this.$refs['my-modal-uses'].show()
                            this.used_rate_plans.packages = result.data.uses
                            this.used_rate_plans.rate_plan_id = rate_plan.id
                            this.used_rate_plans.rate_plan_name = '[' + rate_plan.id + '] ' + rate_plan.name
                        }
                        this.$root.$emit('unlockPage')
                        this.fetchData(this.$i18n.locale)
                    }).catch((error) => {
                        this.$root.$emit('unlockPage')
                        console.log(error)
                    })
            },
            createRoomsAdditional: function () {
                this.$root.$emit('blockPage', {message: "Creando Habitaciones"})
                API.post('add/rooms/additional', {
                    hotel_id: this.$route.params.hotel_id,
                    rate_plan_ids: this.rate_plan_ids
                })
                    .then((result) => {
                        this.$root.$emit('unlockPage')
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Rooms',
                            text: result.data.message
                        })
                    }).catch((error) => {
                    this.$root.$emit('unlockPage')
                    console.log(error)
                })
            },
            fetchData: function (lang) {
                API.get('rates/cost/' + this.$route.params.hotel_id + '/?lang=' + lang)
                    .then((result) => {
                        if (result.data.success === true) {
                            this.rates = result.data.data

                            this.rates_select = result.data.data.filter(function (e){
                                return e.type_channel != 2
                            });

                            for (let i = 0; i < this.rates.length; i++) {
                                if (!(isHyperguest && isType2)) {
                                    this.rate_plans_selected.push({
                                        id: this.rates[i].id,
                                        name: this.rates[i].name,
                                        duplicate_inventory: false,
                                        _selected: false,
                                        channel: this.rates[i].channel
                                    })
                                }
                            }
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Fetch Error',
                                text: result.data.message
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                        text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                })
            },
          notifytarifa: function () {

              let rate_plan = this.rate_selected
              if(rate_plan.promotions == 1 || rate_plan.promotions == true)
              {
                API({
                  method: 'post',
                  url: 'rates/cost/' + this.$route.params.hotel_id + '/notify_new_rate/' + rate_plan.id,
                  data: {rate: rate_plan}
                })
                  .then((result) => {

                      this.$refs['my-modal-notify-promotional-rate'].hide()

                    this.$notify({
                      group: 'main',
                      type: 'success',
                      title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                      text: 'Enviado Correctamente'
                    })
                  })
                  .catch(() => {
                    this.$notify({
                      group: 'main',
                      type: 'error',
                      title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                      text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                  })
              }
              else
              {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                  text: 'No es una tarifa promocional'
                })
              }
          },
            remove(force) {
                let rate_plan = this.rate_selected
                this.rate_plan_choose = rate_plan
                this.loading = true
                API({
                    method: 'DELETE',
                    url: 'rates/cost/' + this.$route.params.hotel_id + '/' + rate_plan.id,
                    data: {force: force}
                })
                    .then((result) => {
                        this.loading = false

                        this.$refs['my-modal-remove-rate'].hide()

                        if (result.data.success === true) {
                            this.fetchData(localStorage.getItem('lang'))

                        } else {

                            if (result.data.success !== true && result.data.uses.length>0) {
                                this.$refs['my-modal-uses'].show();
                                this.used_rate_plans.packages = result.data.uses
                                this.used_rate_plans.rate_plan_id = rate_plan.id
                                this.used_rate_plans.rate_plan_name = '[' + rate_plan.id + '] ' + rate_plan.name
                                return
                            }

                            if (result.data.code === 'related_rooms') {
                                this.$bvModal.msgBoxConfirm(this.$t(result.data.message), {
                                    title: 'Alert',
                                    size: 'sm',
                                    buttonSize: 'sm',
                                    okVariant: 'danger',
                                    okTitle: 'YES',
                                    cancelTitle: 'Cancel',
                                    footerClass: 'p-2',
                                    hideHeaderClose: false,
                                    centered: true
                                })
                                    .then(value => {
                                        if (value) {
                                            this.remove(true)
                                        }
                                    })
                                    .catch(err => {
                                        // An error occurred
                                    })
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.rooms'),
                                    text: this.$t(result.data.message)
                                })
                            }
                        }
                    })
            },
            clonar(id) {
                this.loading = true
                API.post('rates/cost/' + this.$route.params.hotel_id + '/' + id + '/clone')
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {

                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Exito',
                                text: 'Se clonó la tarifa correctamente'
                            })

                            this.fetchData(localStorage.getItem('lang'))

                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.rooms'),
                                text: this.$t(result.data.message)
                            })
                        }

                    })
            },
            validateFieldsDuplicate() {
                this.rate_plans_selected.forEach(r_p_s => {
                    r_p_s._selected = false
                })
                this.rate_plan_ids.forEach(r_p_multiple => {
                    this.rate_plans_selected.forEach(r_p_s => {
                        if (r_p_s.id == r_p_multiple.id) {
                            r_p_s._selected = true
                        }
                    })
                })

                if (this.year_from === '' || this.year_to === '' || this.rate_plan_ids.length == 0) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: 'Faltan datos por llenar'
                    })
                } else {
                    this.showModal()
                }
            },
            showModal() {
                this.$refs['my-modal'].show()
            },
            showModalDuplicateRate() {
                this.$refs['my-modal-duplicate-rates'].show()
            },
            hideModal() {
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-uses'].hide()
                this.$refs['my-modal-duplicate-rates'].hide()

                this.$refs['my-modal-status'].hide()
                this.$refs['my-modal-notify-promotional-rate'].hide()
                this.$refs['my-modal-remove-rate'].hide()
            },
            duplicateRate: function () {
                this.$root.$emit('blockPage', {message: "Cargando.."})
                let data = {
                    rate_plans: [],
                    year_from: '',
                    year_to: '',
                }
                for (let i = 0; i < this.rate_plans_selected.length; i++) {
                    if (this.rate_plans_selected[i]._selected) {
                        data.rate_plans.push(this.rate_plans_selected[i])
                    }
                }

                data.year_from = this.year_from
                data.year_to = this.year_to
                API.post('validate/super_position/duplicate/rates', data).then((response) => {
                    if (response.data == false) {
                        API.post('duplicate/rates', data)
                            .then((result) => {
                                this.$root.$emit('unlockPage')
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: 'Tarifas',
                                    text: result.data
                                })
                                this.hideModal()

                            }).catch((e) => {
                            console.log(e)
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                                text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                            })
                            this.$root.$emit('unlockPage')
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                            text: response.data.message
                        })
                        this.$root.$emit('unlockPage')
                    }
                }).catch((e) => {
                    console.log(e)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                        text: this.$t('hotelsmanagehotelratesratescost.error.messages.name')
                    })
                    this.$root.$emit('unlockPage')
                })
            },
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            }
        },

        filters: {
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            }
        }
    }
</script>




