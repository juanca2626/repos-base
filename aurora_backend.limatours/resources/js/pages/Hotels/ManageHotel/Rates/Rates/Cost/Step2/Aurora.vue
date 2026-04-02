<template>
    <div class="container mt-3">

        <div class="col-12" style="text-align: right;margin-top: -67px;margin-bottom: 39px;">

            <c-switch
                :uncheckedValue="false"
                :value="true"
                class="mx-1"
                color="primary"
                v-model="changeStatusRooms"
                variant="pill"
            />

        </div>

        <div class="col-12" v-if="changeStatusRooms" style="margin-bottom: 20px;">

            <div class="row mt-3 add">
                <div class="col-12">
                    <div class="rooms-table row rooms-table-headers">
                        <div class="col-10 my-auto">
                            {{ $t('hotelsmanagehotelratesratescost.room') }}
                        </div>
                        <div class="col-2">
                            Estado
                        </div>
                    </div>
                    <div :key="room.id" class="rooms-table row" v-for="(room, index) in formRooms.rooms">
                        <div class="col-10 my-auto">
                            {{ room.name }}
                        </div>
                        <div class="col-2" style="text-align: center;">
                            <c-switch
                                :uncheckedValue="false"
                                :value="true"
                                class="mx-1"
                                color="primary"
                                v-model="formRooms.rooms[index].selected"
                                variant="pill"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div :class="{'col-8': !loading, 'col-12': loading}">
                    <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    <button @click="submitRoomDisabled" class="btn btn-success" type="button" v-if="!loading">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        Guardar
                    </button>
                    <router-link to="../" v-if="!loading">
                        <button class="btn btn-danger" type="button">
                            {{ $t('global.buttons.cancel') }}
                        </button>
                    </router-link>
                </div>
            </div>



        </div>



        <div class="col-12" v-if="!changeStatusRooms">
            <div class="row" v-if="newOrEditRatePlan">
                <div class="b-form-group form-group col-6">
                    <div class="form-row" style="margin-bottom: 5px;">
                        <label class="col-sm-2 col-form-label" for="policy">{{
                            $t('hotelsmanagehotelratesratescost.policy_name') }}</label>
                        <div class="col-sm-10">
                            <v-select
                                :options="policies"
                                @input="setPolicy"
                                autocomplete="true"
                                id="policy"
                                label="value"
                                ref="policyTypeahead"
                                v-model="policy">
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="customErrors.policy">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('hotelsmanagehotelratesratescost.error.required') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group col-2">
                    <label class="col-form-label">{{ $t('hotelsmanagehotelratesratescost.date_range')}}</label>
                </div>
                <div class="b-form-group form-group col-4">
                    <div class="row">
                        <div class="input-group col-6">
                            <date-picker
                                :config="datePickerFromOptions"
                                @dp-change="setDateFrom"
                                id="dates_from"
                                name="dates_from"
                                placeholder="inicio: DD/MM/YYYY"
                                ref="datePickerFrom"
                                v-model="form.dates_from"
                                v-validate="{ required: true }"
                            >
                            </date-picker>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon
                                    :icon="['fas', 'exclamation-circle']"
                                    style="margin-left: 5px;"
                                    v-show="errors.has('dates_from')"/>
                                <span v-show="errors.has('dates_from')">{{ errors.first('dates_from') }}</span>
                            </div>
                        </div>
                        <div class="input-group col-6">
                            <date-picker
                                :config="datePickerToOptions"
                                id="dates_to"
                                name="dates_to"
                                placeholder="fin: DD/MM/YYYY"
                                ref="datePickerTo"
                                v-model="form.dates_to"
                                v-validate="{ required: true }"
                            >
                            </date-picker>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon
                                    :icon="['fas', 'exclamation-circle']"
                                    style="margin-left: 5px;"
                                    v-show="errors.has('dates_to')"/>
                                <span v-show="errors.has('dates_to')">{{ errors.first('dates_to') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" v-if="newOrEditRatePlan">
                <div class="col-12">
                    <table class="table table-days">
                        <thead>
                        <tr>
                            <th>{{$t('global.days.all')}}</th>
                            <th>{{$t('global.days.monday')}}</th>
                            <th>{{$t('global.days.tuesday')}}</th>
                            <th>{{$t('global.days.wednesday')}}</th>
                            <th>{{$t('global.days.thursday')}}</th>
                            <th>{{$t('global.days.friday')}}</th>
                            <th>{{$t('global.days.saturday')}}</th>
                            <th>{{$t('global.days.sunday')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <font-awesome-icon
                                    :icon="['fas', 'check-circle']"
                                    class="success fa-2x"
                                    v-if="policyDays.all"/>
                                <font-awesome-icon
                                    :icon="['fas', 'times-circle']"
                                    class="danger fa-2x"
                                    v-if="!policyDays.all"/>
                            </td>
                            <td>
                                <font-awesome-icon
                                    :icon="['fas', 'check-circle']"
                                    class="success fa-2x"
                                    v-if="policyDays.all || policyDays[1]"/>
                                <font-awesome-icon
                                    :icon="['fas', 'times-circle']"
                                    class="danger fa-2x"
                                    v-if="!policyDays[1] && !policyDays.all"/>
                            </td>
                            <td>
                                <font-awesome-icon
                                    :icon="['fas', 'check-circle']"
                                    class="success fa-2x"
                                    v-if="policyDays.all || policyDays[2]"/>
                                <font-awesome-icon
                                    :icon="['fas', 'times-circle']"
                                    class="danger fa-2x"
                                    v-if="!policyDays[2] && !policyDays.all"/>
                            </td>
                            <td>
                                <font-awesome-icon
                                    :icon="['fas', 'check-circle']"
                                    class="success fa-2x"
                                    v-if="policyDays.all || policyDays[3]"/>
                                <font-awesome-icon
                                    :icon="['fas', 'times-circle']"
                                    class="danger fa-2x"
                                    v-if="!policyDays[3] && !policyDays.all"/>
                            </td>
                            <td>
                                <font-awesome-icon
                                    :icon="['fas', 'check-circle']"
                                    class="success fa-2x"
                                    v-if="policyDays.all || policyDays[4]"/>
                                <font-awesome-icon
                                    :icon="['fas', 'times-circle']"
                                    class="danger fa-2x"
                                    v-if="!policyDays[4] && !policyDays.all"/>
                            </td>
                            <td>
                                <font-awesome-icon
                                    :icon="['fas', 'check-circle']"
                                    class="success fa-2x"
                                    v-if="policyDays.all || policyDays[5]"/>
                                <font-awesome-icon
                                    :icon="['fas', 'times-circle']"
                                    class="danger fa-2x"
                                    v-if="!policyDays[5] && !policyDays.all"/>
                            </td>
                            <td>
                                <font-awesome-icon
                                    :icon="['fas', 'check-circle']"
                                    class="success fa-2x"
                                    v-if="policyDays.all || policyDays[6]"/>
                                <font-awesome-icon
                                    :icon="['fas', 'times-circle']"
                                    class="danger fa-2x"
                                    v-if="!policyDays[6] && !policyDays.all"/>
                            </td>
                            <td>
                                <font-awesome-icon
                                    :icon="['fas', 'check-circle']"
                                    class="success fa-2x"
                                    v-if="policyDays.all || policyDays[7]"/>
                                <font-awesome-icon
                                    :icon="['fas', 'times-circle']"
                                    class="danger fa-2x"
                                    v-if="!policyDays[7] && !policyDays.all"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row mt-3 add" v-if="newOrEditRatePlan">
                <div class="col-12">
                    <div class="rooms-table row rooms-table-headers">
                        <div :class="{'my-auto': true, 'col-8': true}">
                            {{$t('hotelsmanagehotelratesratescost.room')}}
                        </div>
                        <div class="col-1">
                            {{$t('hotelsmanagehotelratesratescost.adult')}} US$
                        </div>
                        <div class="col-1" :class="{'ninos' : permiteNinos}">
                            {{$t('hotelsmanagehotelratesratescost.child')}} US$
                        </div>
                        <div class="col-1 " :class="{'infantes' : permiteInfante}">
                            {{$t('hotelsmanagehotelratesratescost.infant')}} US$
                        </div>
                        <div class="col-1">
                            {{$t('hotelsmanagehotelratesratescost.extra')}} US$
                        </div>
                    </div>
                    <div :key="index" class="rooms-table row" v-for="(room, index) in currentRooms"
                         :class="{'updated':room.edit}">
                        <div class="col-8 my-auto">
                            {{room.room_name }}
                        </div>
                        <div class="col-1">
                            <input :id="'room-'+room.room_id+'-adult'"
                                   :name="'room-'+room.room_id+'-adult'"
                                   class="form-control"
                                   :class="{'updated':room.edit}"
                                   step="0.01"
                                   type="number"
                                   v-model="currentRooms[index].price_adult" @keyup="setEditRoom(index)"/>
                        </div>
                        <div class="col-1" :class="{'ninos' : permiteNinos}">
                            <input :id="'room-'+room.room_id+'-child'"
                                   :name="'room-'+room.room_id+'-child'"
                                   class="form-control"
                                   :class="{'updated':room.edit}"
                                   step="0.01"
                                   type="number"
                                   v-model="currentRooms[index].price_child" @keyup="setEditRoom(index)"/>
                        </div>
                        <div class="col-1" :class="{'infantes' : permiteInfante}">
                            <input :id="'room-'+room.room_id+'-infant'"
                                   :name="'room-'+room.room_id+'-infant'"
                                   class="form-control"
                                   :class="{'updated':room.edit}"
                                   step="0.01"
                                   type="number"
                                   v-model="currentRooms[index].price_infant" @keyup="setEditRoom(index)"/>
                        </div>
                        <div class="col-1">
                            <input :id="'room-'+room.room_id+'-extra'"
                                   :name="'room-'+room.room_id+'-extra'"
                                   class="form-control"
                                   :class="{'updated':room.edit}"
                                   step="0.01"
                                   type="number"
                                   v-model="currentRooms[index].price_extra " @keyup="setEditRoom(index)"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row text-right my-3" v-if="newOrEditRatePlan">
                <div class="col-12">
                    <button @click="cleanCurrentRooms" class="btn btn-danger mr-2" type="button">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        Limpiar
                    </button>
                    <button @click="submit" class="btn btn-success" type="button">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        Agregar
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-2">
                    <div class="b-form-group form-group">
                        <div class="input-group">
                            <div class="form-row">
                                <label class="col-form-label" for="period">{{
                                    $t('clientsmanageclienthotel.period') }}</label>
                                <div class="col-sm-12">
                                    <select @change="getDateRanges" ref="period" class="form-control" id="period"
                                            required
                                            size="0" v-model="selectPeriod">
                                        <option :value="year.value" v-for="year in years">
                                            {{ year.text}}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="b-form-group form-group">
                        <div class="input-group">
                            <div class="form-row">
                                <label class="col-form-label" for="room_type">Tipo de habitación</label>
                                <div class="col-sm-12">
                                    <select @change="getDateRanges" ref="period" class="form-control" id="room_type"
                                            required
                                            size="0" v-model="selectRoom">
                                        <option value="">Seleccione...</option>
                                        <option :value="room.room_id" v-for="room in rooms_selector">
                                            {{ room.room_name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-7">
                    <div class="b-form-group form-group float-right">
                        <div class="input-group">
                            <div class="form-row">
                                <label class="col-form-label" for="room_type">&ensp;</label>
                                <div class="col-sm-12 text-right">
                                    <button @click="generateRatesInCalendar" class="btn btn-success" type="button">
                                        <font-awesome-icon :icon="['fas', 'fa-bell']"/>
                                        Procesar tarifario en el calendario
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row" v-if="notUpdatedRates">
                <span title="Editar Grupo" class="badge badge-primary" style="cursor: pointer;padding: 20px;width: 100%;font-size: 18px;"><i class="fas fa-bell"></i> Tiene cambios en el tarifario que no se han aplicado al calendario</span>
            </div>
            <div class="row">
                <table class="VueTables__table table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                    <th scope="col" style="padding: 10px">Opción</th>
                    <th scope="col" style="padding: 10px">Habitacion</th>
                    <th scope="col" style="padding: 10px">Periodo</th>
                    <th scope="col" style="padding: 10px">Politica</th>
                    <th scope="col" style="padding: 10px">Habitacion US$</th>
                    <th scope="col" style="padding: 10px">Niño US$</th>
                    <th scope="col" style="padding: 10px">Infante US$</th>
                    <th scope="col" style="padding: 10px">Extra US$</th>
                    <th></th>
                    </thead>
                    <tbody>
                    <tr v-for="date_range in date_ranges">
                        <td style="width: 90px">
                            <button type="button" class="btn btn-success btn-sm" @click="editRoom(date_range.group)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                        <td>
                        <span style="cursor: pointer; padding: 5px">
                            <!-- <i v-if="date_range.flag_migrate === 0" class="fa fa-check-circle"></i> -->
                            <i v-if="date_range.flag_migrate !== 1" class="fa fa-check-circle"></i>
                            {{date_range.room_id}} - {{ date_range.room_name }}
                        </span>
                            -
                            <span class="badge badge-primary" style="cursor: pointer" title="Editar Grupo" @click="setNewGroup(date_range)">
                            ( {{ date_range.group }} ) <i class="far fa-edit"></i>
                            </span>
                        </td>
                        <td style="padding: 5px;width: 180px">
                            {{ getDateFormat(date_range.date_from) }} <i class="fas fa-angle-right"></i> {{getDateFormat(date_range.date_to) }}
                        </td>
                        <td style="padding: 5px">{{ date_range.policy_name }}</td>
                        <td style="padding: 5px">{{ date_range.price_adult }}</td>
                        <td style="padding: 5px">{{ date_range.price_child }}</td>
                        <td style="padding: 5px">{{ date_range.price_infant }}</td>
                        <td style="padding: 5px">{{ date_range.price_extra }}</td>
                        <td style="padding: 5px">
                         <button type="button" @click="editRoomDelete(date_range.id)" title="Eliminar" class="btn btn-sm btn-danger" style="cursor: pointer">
                             <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                         </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <b-modal :title="'Tarifas'" centered ref="modal_date_range_group" size="md" :no-close-on-backdrop=true
                     :no-close-on-esc=true>
                <div>
                    <p>Introduzca por favor el grupo al cual desea afiliar este rango de fechas</p>
                    <input type="text" v-model="new_group">
                </div>
                <div slot="modal-footer">
                    <button class="btn btn-success" @click="updateDateRangeGroup">{{$t('global.buttons.accept')}}
                    </button>
                    <button @click="closeModalDateRangeGroup" class="btn btn-danger">{{$t('global.buttons.cancel')}}
                    </button>
                </div>
            </b-modal>

            <b-modal :title="'Files afectados'"  ref="modal_files" :no-close-on-backdrop=true size="xl" >
                <div class="row">
                    <div class="col-12">
                        <h3>{{ informations.hotel }}</h3>
                        <h6>Tarifa: {{ informations.rate }} </h6>
                        <h6>Habitaciones Afectadas: {{ informations.rooms }}</h6>
                    </div>                                         
                </div>
              
                <div class="row mt-2">
 
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                            <tr>
                                <th style="text-align: center"></th>
                                <th style="text-align: center">File</th>
                                <th style="text-align: center">Name</th>
                                <th style="text-align: center">Client Code</th>
                                <th style="text-align: center">Client Name</th>
                                <th style="text-align: center">Executive</th>
                                <th style="text-align: center">Room Name</th>
                                <th style="text-align: center">Date In</th>
                                <th style="text-align: center">Date Out</th>                                
                                <th style="text-align: center">Cost</th>
                                <th style="text-align: center">                                                      
                                    <i class="fa fa-lock"  v-if="padlock == 2" @click="onCheck_full(1)" style="font-size: 17px;"></i>
                                    <i class="fas fa-unlock" v-if="padlock == 1" @click="onCheck_full(2)" style="font-size: 17px;" ></i>                                                              
                                </th> 
                            </tr>
                        </thead>
                        <tbody>

                            <template v-for="(tb, index) in filtrar">
                                <tr>
                                    <td>
                                        <i class="fa fa-plus"  v-if="!tb.open" @click="onOpen(tb, true)" style="font-size: 17px;"></i>
                                        <i class="fas fa-minus" v-if="tb.open" @click="onOpen(tb, false)" style="font-size: 17px;" ></i> 
                                    </td>
                                    <td style="text-align: center">{{ tb.file_number }}</td>
                                    <td style="text-align: center">{{ tb.file_name }}</td>
                                    <td style="text-align: left">{{ tb.client_code }}</td>
                                    <td style="text-align: left">{{ tb.client_name }}</td>
                                    <td style="text-align: center">{{ tb.executive_code }}</td>
                                    <td style="text-align: left">{{ tb.room_name }}</td>
                                    <td style="text-align: left">{{ tb.date_in }}</td>
                                    <td style="text-align: left">{{ tb.date_out }}</td>                                    
                                    <td style="text-align: left">{{ tb.amount_cost }}</td>
                                    <td style="text-align: center;padding:0" > 
                                        <i class="fa fa-lock"  v-if="tb.file_amount_type_flag_id == 2" @click="onCheck(tb, 1)" style="font-size: 17px;"></i>
                                        <i class="fas fa-unlock" v-if="tb.file_amount_type_flag_id == 1" @click="onCheck(tb, 2)" style="font-size: 17px;" ></i> 
                                    </td>  
                                </tr>
                                <template v-if="tb.open">
                                    <tr v-for="(night, index) in tb.nights">
                                        <td style="text-align: center"></td>
                                        <td style="text-align: center"></td>
                                        <td style="text-align: center"></td>
                                        <td style="text-align: left"></td>
                                        <td style="text-align: left"></td>
                                        <td style="text-align: center"></td>
                                        <td style="text-align: left"></td>
                                        <td style="text-align: center" colspan="2">{{ night.date }}</td> 
                                        <td style="text-align: left">{{ night.total_amount_cost }}</td>
                                        <td style="text-align: center;padding:0"> 
                                            <i class="fa fa-lock"  v-if="night.file_amount_type_flag_id == 2" @click="onCheck_interna(night, 1)" style="font-size: 17px;"></i>
                                            <i class="fas fa-unlock" v-if="night.file_amount_type_flag_id == 1" @click="onCheck_interna(night, 2)" style="font-size: 17px;" ></i> 
                                        </td>  
                                    </tr>                                
                                </template>

                            </template>

                        </tbody>
                        </table>
                    </div>

                    <div class="col-sm-12" style="margin-top: 10px">
                        <b-pagination v-model="currentPage" :total-rows="rows"
                            :per-page="perPage" aria-controls="my-table" align="right"
                            size="sm" limit="8"></b-pagination>
                    </div>

                </div>
                <div class="w-100" slot="modal-footer">
                    <div class="row">
                        <div class="offset-6 col-6 text-right">
                            <button @click="closeDetail" class="btn btn-danger" type="button" v-if="!loading">
                                {{$t('global.buttons.cancel')}}
                            </button>

                            <button @click="procesarRates" class="btn btn-success" type="button" v-if="!loading">
                                {{$t('global.buttons.accept')}}
                            </button>                            
                        </div>
                    </div>
                </div>
            </b-modal>


            <block-page></block-page>
        </div>
    </div>
</template>

<script>
    import { API,APISERVICE  } from './../../../../../../../api'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
    import Progress from 'bootstrap-vue/src/components/progress/progress'
    import CSwitch from '@coreui/vue/src/components/Switch/Switch'
    import TableClient from './../../../../../../../components/TableClient'
    import moment from 'moment'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import BlockPage from '../../../../../../../components/BlockPage'

    export default {
        components: {
            'table-client': TableClient,
            VueBootstrapTypeahead,
            datePicker,
            'b-progress': Progress,
            CSwitch,
            vSelect,
            BModal,
            BlockPage
        },
        props: {
            newOrEditRatePlan: Boolean,
            hotelID: Number,
            ratePlanID: Number,
            formAction: String,
            options: Object,
            channelID: Number
        },
        data: () => {
            return {
                files:[],
                changeStatusRooms: false,
                notUpdatedRates:false,
                operationValidate: 'new',
                permiteNinos: false,
                permiteInfante: false,
                loading: false,
                policies: [],
                policy: {
                    value: null
                },
                policySearch: null,
                policyDays: {
                    all: false,
                    1: false,
                    2: false,
                    3: false,
                    4: false,
                    5: false,
                    6: false,
                    7: false
                },
                datePickerFromOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                datePickerToOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                currentRooms: [],
                defaultRooms: [],
                rooms_selector: [],
                customErrors: {
                    meals: false,
                    policy: false
                },
                form: {
                    policy_id: '',
                    dates_from: '',
                    dates_to: ''
                },
                selectPeriod: '',
                selectRoom: '',
                bag_room_check: false,
                date_ranges: [],
                date_range_selected: null,
                new_group: null,
                filter: {
                    date: ''
                },
                formRooms : {
                    channel_id: '',
                    rooms: []
                },
                table: {
                    columns: ['file_number', 'file_name',  'client_code',  'client_name', 'executive_code' , 'date_in', 'date_out','room_name', 'amount_cost', 'file_amount_type_flag_id'],
                },
                perPage: 15, 
                currentPage: 1,  
                padlock : 1,
                informations : {
                    hotel: '',
                    rate: '',
                    rooms: ''
                }
            }
        },
        mounted(){

            this.formRooms.channel_id = this.channelID
            API.get('rates/cost/' + this.hotelID + '/' + this.ratePlanID + '/channels' +
                '/?lang=' + localStorage.getItem('lang') + '&channel=' + this.channelID)
                .then((result) => {
                    this.formRooms.rooms = result.data.data

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.name'),
                    text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                })
            })

        },
        computed: {

            filtrar() {
 
                let tblpr_filtrada =  this.files;
                
                // tblpr_filtrada = this.files.filter( pr => { 
                //     return !this.searchPr || pr.nomcob.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").indexOf(this.searchPr.toLowerCase()) > -1
                // }) 

                return tblpr_filtrada.slice(
                    (this.currentPage - 1) * this.perPage,
                    this.currentPage * this.perPage
                )
            },

            rows() {

                let tblpr_filtrada =  this.files;
                
                // tblpr_filtrada = this.files.filter( pr => { 
                //     return !this.searchPr || pr.nomcob.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").indexOf(this.searchPr.toLowerCase()) > -1
                // }) 

                return tblpr_filtrada.length
            },
           

            tableOptions: function () {
                return {
                    headings: { 
                        file_number: 'File',
                        file_name: 'Name',
                        client_code: 'Client Code',
                        client_name: 'Client Name',
                        executive_code: 'Executive',
                        date_in: 'Date In',
                        date_out: 'Date Out',
                        room_name: 'Room Name',
                        amount_cost: 'Cost',
                        file_amount_type_flag_id: 'Padlock'
                    },
                    sortable: [],
                    filterable: false,
                }
            },
            years () {
                let previousYear = moment().subtract(2, 'years').year()
                let currentYear = moment().add(5, 'years').year()

                let years = []

                do {
                    years.push({ value: previousYear, text: previousYear })
                    previousYear++
                } while (currentYear > previousYear)

                return years
            }
        },
        created () {
            let currentDate = new Date()
            this.selectPeriod = currentDate.getFullYear()
            this.getPolicies()
            this.getRooms()
            this.getDateRanges()
            this.getStatusUpdatedRates()
        },
        methods: {
            setEditRoom: function (index) {
                if (isNaN(this.currentRooms[index].price_adult) || this.currentRooms[index].price_adult == '') {
                    this.currentRooms[index].price_adult = 0
                }
                if (isNaN(this.currentRooms[index].price_child) || this.currentRooms[index].price_child == '') {
                    this.currentRooms[index].price_child = 0
                }
                if (isNaN(this.currentRooms[index].price_infant) || this.currentRooms[index].price_infant == '') {
                    this.currentRooms[index].price_infant = 0
                }
                if (isNaN(this.currentRooms[index].price_extra) || this.currentRooms[index].price_extra == '') {
                    this.currentRooms[index].price_extra = 0
                }

                if (this.currentRooms[index].price_adult > 0 && this.currentRooms[index].price_adult !== '') {
                    this.currentRooms[index].edit = true
                } else {
                    this.currentRooms[index].edit = false
                }
            },
            updateDateRangeGroup: function () {
                if (this.new_group === null) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Tarifas',
                        text: 'El grupo nuevo no puede estar vacio'
                    })
                } else {
                    let validate_update_group = false
                    this.date_ranges.forEach((date_range) => {
                        if (date_range.group == this.new_group) {

                            if (date_range.date_from == this.date_range_selected.date_from && date_range.date_to == this.date_range_selected.date_to && date_range.policy_id == this.date_range_selected.policy_id) {
                                validate_update_group = true
                                this.$root.$emit('blockPage', { message: 'Por favor espere Actulizando grupo Rango de Fecha..' })
                                API.put('date_ranges/hotels/group', {
                                    date_range_id: this.date_range_selected.id,
                                    group: this.new_group
                                })
                                    .then((response) => {
                                        this.$root.$emit('unlockPage')
                                        this.getDateRanges()
                                    }).catch((e) => {
                                    console.log(e)
                                    this.$root.$emit('unlockPage')
                                })
                            }
                        }
                    })
                    if (!validate_update_group) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Tarifas',
                            text: 'No puede asociarse este rango de fechas a ese grupo'
                        })
                    }
                }
            },
            closeModalDateRangeGroup: function () {
                this.cleanCurrentRooms()
                this.date_range_selected = null
                this.new_group = null
                this.$refs['modal_date_range_group'].hide()
            },
            setNewGroup: function (date_range) {
                this.cleanCurrentRooms()
                this.$refs['modal_date_range_group'].show()
                this.date_range_selected = date_range
            },
            cleanPolicies: function () {
                this.policyDays = {
                    all: false,
                    1: false,
                    2: false,
                    3: false,
                    4: false,
                    5: false,
                    6: false,
                    7: false
                }
                this.policySearch = null
                this.policy = { value: null }
            },
            cleanCurrentRooms: function () {
                this.operationValidate = 'new'
                this.form = {
                    policy_id: '',
                    dates_from: '',
                    dates_to: ''
                }
                this.currentRooms = JSON.parse(JSON.stringify(this.defaultRooms))
                this.cleanPolicies()
            },
            async generateRatesInCalendar(){

                this.$root.$emit('blockPage', { message: 'Por favor Espere..' });

                const { data } = await API.get('/generate-rates-in-calendar',{
                    params: {
                        'hotel_id' : this.hotelID,
                        'rates_plans_id' : this.ratePlanID
                    }
                }) 
                
                if(data.success == false){
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error',
                        text: data.message
                    })
                    this.$root.$emit('unlockPage')
                    return false;
                }
              
                /*const result = await APISERVICE.post('/search-file-hotel-rates',{                    
                    'hotel_id' : this.hotelID,
                    'rates_plans_id' : this.ratePlanID,
                    'rangos' : data.rangos                     
                }) 

                console.log(result);

                if(result.data.data.files.length>0){

                    this.files = result.data.data.files
                    this.informations.hotel = data.hotel.name
                    this.informations.rate = data.hotel.rates_plans[0].name
                    this.informations.rooms = result.data.data.modify
                    this.$refs['modal_files'].show()
                    this.$root.$emit('unlockPage')
                }else{
                    this.procesarRates()
                }    */
                
                this.procesarRates();

            }, 
            procesarRates(){
                this.$root.$emit('blockPage', { message: 'Por favor Espere..' });
                API.post('/generate-rates-in-calendar',{
                    'perido' : this.selectPeriod,
                    'hotel_id' : this.hotelID,
                    'rates_plans_id' : this.ratePlanID,
                    'files' : this.files
                }).then((result) => {

                    if(result.data.success == false){
                        this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error',
                                text: result.data.message
                            })
                    }else{
                        this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Success',
                                text: result.data.message
                            })
                        this.notUpdatedRates = false
                    }
                    this.$refs['modal_files'].hide()
                    this.$root.$emit('unlockPage')
                }).catch((e) => {                    
                    this.$root.$emit('unlockPage')
                })
            },
            getStatusUpdatedRates: function(){
                API.get('/generate-rates-in-calendar-status',{
                   params: {
                    'hotel_id' : this.hotelID,
                    'rates_plans_id' : this.ratePlanID
                   }
                }).then((result) => {
                    this.notUpdatedRates = result.data.status
                }).catch((e) => {
                    console.log(e)
                })
            },
            getDateFormat: function (date) {
                return moment(date).format('L')
            },
            getPolicies: function () {
                API.get('/policies_rates/selectBox?hotel_id=' + this.hotelID)
                    .then((result) => {
                        let policies = []
                        result.data.data.forEach((item) => {
                            policies.push({
                                id: item.id,
                                value: item.name,
                                days_apply: item.days_apply
                            })
                        })

                        this.policies = policies
                    }).catch((e) => {
                    console.log(e)
                })
            },
            getRooms: function () {
                API.get('/rooms/selectBox?hotel_id=' + this.hotelID + '&lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        let rooms_alt = []
                        let rooms = []
                        let allRooms = result.data.data
                        allRooms.forEach((room) => {
                            if (room.state === 1) {
                                rooms.push({
                                    date_range_id: null,
                                    room_id: parseInt(room.id),
                                    room_name: room.translations[0].value,
                                    price_adult: 0,
                                    price_child: 0,
                                    price_infant: 0,
                                    price_extra: 0,
                                    edit: false
                                })
                                rooms_alt.push({
                                    room_id: parseInt(room.id),
                                    room_name: room.translations[0].value
                                })
                            }
                        })

                        this.rooms_selector = rooms_alt
                        this.defaultRooms = JSON.parse(JSON.stringify(rooms))
                        this.currentRooms = JSON.parse(JSON.stringify(rooms))

                    }).catch((e) => {
                    console.log(e)
                })
            },
            getDateRanges: function () {
                this.cleanCurrentRooms()
                API.get('/date_ranges/hotels/rate_plan/' + this.ratePlanID + '?year=' + this.selectPeriod + '&room_id=' + this.selectRoom)
                    .then((response) => {
                        this.date_ranges = response.data
                    }).catch((e) => {
                    console.log(e)
                })
            },
            setPolicy (event) {
                if (event) {
                    this.policy = event
                    let days = event.days_apply.split('|')

                    Object.keys(this.policyDays).forEach((key) => {
                        this.policyDays[key] = days.indexOf(key) > -1
                    })
                    this.form.policy_id = event.id
                }
            },
            setDateFrom (e) {
                this.$refs.datePickerTo.dp.minDate(e.date)
            },
            editRoom (date_range_group) {
                this.currentRooms = JSON.parse(JSON.stringify(this.defaultRooms))
                this.operationValidate = 'put'
                for (let i = 0; i < this.date_ranges.length; i++) {
                    if (this.date_ranges[i].group === date_range_group) {
                        for (let j = 0; j < this.currentRooms.length; j++) {
                            if (this.currentRooms[j].room_id === this.date_ranges[i].room_id) {
                                this.form.dates_from = moment(JSON.parse(JSON.stringify(this.date_ranges[i].date_from))).format('L')
                                this.form.dates_to = moment(JSON.parse(JSON.stringify(this.date_ranges[i].date_to))).format('L')
                                this.form.policy_id = this.date_ranges[i].policy_id

                                this.currentRooms[j].date_range_id = this.date_ranges[i].id
                                this.currentRooms[j].room_id = this.date_ranges[i].room_id
                                this.currentRooms[j].room_name = this.date_ranges[i].room_name
                                this.currentRooms[j].price_adult = this.date_ranges[i].price_adult
                                this.currentRooms[j].price_child = this.date_ranges[i].price_child
                                this.currentRooms[j].price_infant = this.date_ranges[i].price_infant
                                this.currentRooms[j].price_extra = this.date_ranges[i].price_extra
                                this.currentRooms[j].edit = true
                            }
                        }
                    }
                }

                for (let i = 0; i < this.policies.length; i++) {
                    if (this.policies[i].id === this.form.policy_id) {
                        this.policy = this.policies[i]
                        this.policySearch = this.policies[i].value
                        this.setPolicy(this.policies[i])
                        this.$refs.policyTypeahead.inputValue = this.policies[i].value

                        break
                    }
                }
            },
            editRoomDelete (date_range_id) {
                this.$root.$emit('blockPage', { message: 'Por favor espere Eliminando Rango de Fecha..' })
                this.cleanCurrentRooms()
                API.delete('date_ranges/hotels/' + date_range_id).then((response) => {
                    this.notUpdatedRates = true
                    this.$root.$emit('unlockPage')
                    this.getDateRanges()
                }).catch((e) => {
                    this.$root.$emit('unlockPage')
                    console.log(e)
                })
            },
            submit () {
                this.$validator.validateAll().then(isValid => {
                    if (this.policy.value == null) {
                        this.customErrors.policy = true
                        return false
                    }
                    this.customErrors.policy = false

                    if (isValid) {
                        this.$root.$emit('blockPage', { message: 'Por favor Espere..' })
                        API.post('/date_ranges/hotels/rate_plan/' + this.ratePlanID, {
                            hotel_id: this.hotelID,
                            date_from: this.form.dates_from,
                            date_to: this.form.dates_to,
                            policy_id: this.form.policy_id,
                            rooms: this.currentRooms,
                            operation: this.operationValidate
                        })
                            .then((response) => {
                                this.notUpdatedRates = true
                                this.getDateRanges()
                                this.$root.$emit('unlockPage')
                            }).catch((e) => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$i18n.t('global.modules.ratescost'),
                                text: e.response.data.message
                            })
                            console.log(e)
                            this.$root.$emit('unlockPage')
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$i18n.t('global.modules.ratescost'),
                            text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            cancelForm (){
                // this.$parent.newOrEditRatePlan = false
            },
            submitRoomDisabled () {
                this.loading = true
                API({
                    method: this.formAction,
                    url: 'rates/cost/' + this.hotelID + '/' + this.ratePlanID + '/channels',
                    data: this.formRooms
                }).then((result) => {
                    this.loading = false
                    if (result.data.success) {
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$i18n.t('hotelsmanagehotelratesratescost.channel'),
                            text: this.$i18n.t('hotelsmanagehotelratesratescost.messages.saveok')
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$i18n.t('hotelsmanagehotelratesratescost.channel'),
                            text: this.$i18n.t('hotelsmanagehotelratesratescost.messages.savefailed')
                        })
                    }
                })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.name'),
                            text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                        })
                    })
            },   
            closeDetail() { 
                this.$refs['modal_files'].hide()
            },
            onCheck(check,status){
                check.file_amount_type_flag_id = status  
                check.nights.forEach(element => {
                    element.file_amount_type_flag_id = status
                });
            },
            onCheck_interna(check,status){
                check.file_amount_type_flag_id = status  
            },                        
            onCheck_full(status){
                this.padlock = status  
                this.files.forEach(element => {
                    element.file_amount_type_flag_id = status                    
                    element.nights.forEach(element_night => {
                        element_night.file_amount_type_flag_id = status
                    });                    
                });

            },            
            onOpen(check,status){
                check.open = status  
            }             
        }
    }
</script>

<style lang="stylus">
    .ninos {
        display: none;
    }

    .infantes {
        display: none;
    }

    .updated {
        background-color: grey;
        color: white;
    }
</style>

