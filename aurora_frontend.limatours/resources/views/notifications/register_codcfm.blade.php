@extends('layouts.app')

@section('content')

    <section>
        <button class="btn btn-success force" v-show="false" @click="get_hotels_force()">Download Force</button>
    </section>

    <section class="page-central" v-if="!final_message && !loading && message===''">
        <div class="container">
            <div class="d-block">
                <h1 class="mr-0 ml-0 mb-3 mt-2 providers-title">@{{ translations.label.confirmation_codes }}</h1>
                <section class="d-flex justify-content-between">
                    <h3 class="providers-subtitle">Hotel @{{ hotel.name }}</h3>
                    <div class="d-flex align-items-center providers-file">
                        <h4 class="mr-2">@{{ translations.label.file_number }}:</h4>
                        <h4 class="providers-code">@{{ nrofile }}</h4>
                    </div>
                </section>

                <hr/>
                <section class="container mt-4 mb-3 p-0">
                    <div class="d-flex">
                        <p class="providers-inf">@{{ translations.th.group_name }}:</p>
                        <p class="ml-4">@{{ group_name }}</p>
                    </div>
                    <div class="d-flex">
                        <p class="providers-inf">@{{ translations.label.quantity_of_passengers }}:</p>
                        <p class="ml-4">@{{ q_adults }} @{{ translations.label.adults }}</p>
                        <p class="ml-4" v-if="q_children>0">@{{ q_children }} @{{ translations.label.kids }}</p>
                    </div>
                    <div class="d-flex">
                        <p class="providers-inf">@{{ translations.label.nationality }}:</p>
                        <p class="ml-4">@{{ client_nationality }}</p>
                    </div>
                    </section>
                    <hr/>


                <table class="table mt-4 table-xs table-striped table-hover" v-if="!loading && message!==''">
                    <thead>
                    <tr>
                        <td colspan="5">
                                <span class="alert alert-warning" style="text-align:center;color: #cd0000 !important; margin: 12px 0px;">
                                    <i class="fa fa-info-circle"></i> @{{ message }}
                                </span>
                        </td>
                    </tr>
                    </thead>
                </table>

                <table class="table mt-5 text-center table-hover" v-if="message===''">
                    <thead class="bg-table">
                        <tr>
                            <th>@{{ translations.label.status.toUpperCase() }}</th>
                            <th style="min-width: 190px;">CHECK-IN</th>
                            <th style="min-width: 190px;">CHECK-OUT</th>
                            <th style="min-width: 105px;">N° @{{ translations.label.rooms_abbreviated.toUpperCase() }}</th>
                            <th>@{{ translations.label.type.toUpperCase() }}</th>
                            <th style="min-width: 95px;">N° PAXS</th>
                            <th>
                                @{{ translations.label.confirmation_codes.toUpperCase() }}
                            </th>
                        </tr>
                    </thead>

                    <tr v-if="loading">
                        <td colspan="5">
                            <h3 style="text-align:center;color: #cd0000 !important; margin: 12px 0px;">
                                <i class="fa fa-cog fa-spin"></i>
                            </h3>
                        </td>
                    </tr>

                    <tbody class="bg-color" v-for="(service, s) in services_hotels" v-if="!loading">
                    <tr class="mt-3" v-for="(variation, v) in service.variations">

                        <td v-if="(variation.number_annulments == 0)">
                            <span class="providers-state-confirmed" v-if="(variation.status_hotel.trim() === 'OK')">
                                <i class="icon-check-circle mr-2"></i><strong>Confirmed</strong>
                            </span>
                            <span class="providers-state-onRequest" v-else>
                                <i class="icon-alert-circle mr-2"></i><strong>On request</strong>
                            </span>
                        </td>
                        <td v-else>
                            <span class="providers-state-onRequest">
                                <i class="icon-alert-circle mr-2"></i><strong>@{{ 'CXL ' + translations.label.with_penalty.toUpperCase() }}</strong>
                            </span>
                        </td>
                        <!--                         <td>@{{ (variation.number_annulments == 0)
                                                    ? ((variation.status_hotel.trim() === 'OK')
                                                        ? translations.label.confirmed.toUpperCase()
                                                        : variation.status_hotel )
                                                    : 'CXL ' + translations.label.with_penalty.toUpperCase() }}
                                                </td> -->

                        <td>@{{ convert_date_custom( service.date_in ) }} <br> @{{ hotel_check_in_hour | format_hour }} h</td>
                        <td>@{{ convert_date_custom( service.date_out ) }} <br> @{{ hotel_check_out_hour | format_hour}} h</td>
                        <td>@{{ variation.total_rooms }}</td>
                        <td>@{{ variation.base_name_initial }}</td>
                        <td>@{{ variation.total_paxs }}</td>
                        <td style="text-align: center">
{{--                            && variation.status_hotel.trim() !== 'RQ'--}}
                            <input style="min-width: 200px;" type="text" class="form-control" maxlength="15" :disabled="!(variation.number_annulments == 0)"
                                   :placeholder="translations.label.provider_code" v-model="variation.confirmation_code"
                                   v-on:keyup="duplicate_codcfm_sames(s, v)">
                        </td>
                    </tr>
                    </tbody>

                </table>

                <div class="group-btn" v-if="message===''">

                    <button type="button" class="btn btn-primary mt-5 mb-5 float-right" :disabled="btn_disabled"
                            @click="will_confirm_save()" v-show="!view_confirm_save">
                        @{{ translations.btn.save }}
                    </button>

                    <button type="button" class="btn btn-primary right mt-5 mb-5 float-right" v-show="view_confirm_save"
                            @click="save_confirmation_codes()">
                        @{{ translations.btn.confirm }}
                    </button>
                    <div class="alert alert-warning mt-5 float-left" v-show="view_confirm_save">
                        <i class="fa fa-info-circle"></i> @{{ translations.messages.confirm_confirmation_codes }}
                    </div>

                </div>

            </div>

        </div>
    </section>

    <section class="page-message py-5" v-if="!final_message && loading">
        <div class="container py-5">
            <div class="row py-5">
                <div class="col text-center d-block py-5">
                    <h2 class="color-provider" style="font-size: 4rem!important;">@{{ translations.label.loading }}</h2>
                    <p>
                    </p>
                    <br>
                    <br>
                    <i class="fas fa-spin fa-spinner" style="font-size: 8rem;"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="page-message py-5" v-if="final_message">
        <div class="container py-5">
            <div class="row py-5">
                <div class="col text-center d-block py-5">
                    <h2 class="color-provider" style="font-size: 4rem!important;">@{{ translations.messages.successful_commit_record }}</h2>
                    <p class="my-5" style="font-size: 2.5rem;">@{{ translations.label.confirmation_codes }} <strong v-for="s_saved in services_for_save">- @{{ s_saved.confirmation_code }}</strong></p>
                    <i class="fas fa-check-circle" style="font-size: 8rem; color:#67D399;"></i>
                    <p class="my-5">¡@{{ translations.messages.thanks }}!</p>
                </div>
            </div>
        </div>
    </section>

    <section class="page-message py-5" v-if="no_file_message">
        <div class="container py-5">
            <div class="row py-5">
                <div class="col text-center d-block py-5">
                    <h2 class="color-provider" style="font-size: 4rem!important;">El File aún está siendo procesado</h2>
{{--                    <h2 class="color-provider" style="font-size: 4rem!important;">@{{ translations.messages.successful_commit_record }}</h2>--}}
                    <p class="my-5" style="font-size: 2.5rem;">N° <strong>@{{ nrofile }}</strong></p>
                    <i class="fas fa-info-circle" style="font-size: 8rem; color:#2c9dff;"></i>
{{--                    <p class="my-5">¡@{{ translations.messages.thanks }}!</p>--}}
                </div>
            </div>
        </div>
    </section>

@endsection

@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                services_for_save: [],
                services_hotels: [
                    {
                        variations: []
                    }
                ],
                loading: true,
                btn_disabled: true,
                view_confirm_save: false,
                nrofile: '{{ $nrofile }}',
                group_name: "",
                codsvs: '{{ $codsvs }}',
                translations: {
                    label:{
                        type:"",
                        confirmation_codes:"",
                        status:"",
                        rooms_abbreviated:"",
                        confirmed:"",
                        with_penalty:"",
                    },
                    messages:{},
                    btn:{}
                },
                message:"",
                final_message: false,
                no_file_message: false,
                hotel: {
                    name: "",
                },
                q_adults: 0,
                q_children: 0,
                client_nationality: "",
                hotel_check_in_hour: "00:00",
                hotel_check_out_hour: "00:00",
            },
            created: function () {
                localStorage.setItem('lang', '{{ $lang }}')
            },
            mounted() {
                this.set_translations()
                this.get_hotels()
            },
            computed: {},
            methods: {
                capitalize: function (value) {
                    if (!value) return ''
                    value = value.toString().toLowerCase()
                    return value.charAt(0).toUpperCase() + value.slice(1)
                },
                convert_date_custom(date_){
                    let date_format = moment(date_)
                    return date_format.format("DD") + '-' + this.capitalize( date_format.format("MMMM") ) + '-' + date_format.format("YYYY")
                },
                set_translations() {
                    axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/board').then((data) => {
                        this.translations = data.data
                        console.log(this.translations)
                    })
                },
                will_confirm_save(){
                    this.view_confirm_save = true

                    let me = this

                    setTimeout(function(){
                        me.view_confirm_save = false
                    }, 30000)

                },
                save_confirmation_codes() {
                    this.view_confirm_save = false
                    this.btn_disabled = true

                    this.services_for_save = []

                    this.services_hotels.forEach((s) => {
                        s.variations.forEach((v) => {
                            // if (v.status_hotel !== 'RQ') {
                                if (v.confirmation_code != 0 && v.confirmation_code !== null &&
                                    v.confirmation_code.trim() !== '') {
                                    this.services_for_save.push({
                                        id: v.id,
                                        item_number: v.item_number,
                                        confirmation_code: v.confirmation_code,
                                        preview_status: v.status_hotel
                                    })
                                }
                            // }
                        })
                    })

                    console.log(this.services_for_save)

                    if( this.services_for_save.length === 0 ){
                        this.$toast.warning(this.translations.message.error, {
                            position: 'top-right'
                        })
                        return
                    }

                    let data = {
                        services: this.services_for_save
                    }
                    axios.post(baseExternalURL + 'api/files/' + this.nrofile + '/services/confirmation_codes', data)
                        .then((response) => {
                            if (response.data.success) {
                                this.get_hotels()
                                this.final_message = true
                            } else {
                                this.$toast.error(this.translations.message.error, {
                                    position: 'top-right'
                                })
                            }
                            this.btn_disabled = false
                        })
                },
                verify_code_inputs() {
                    let emptys = 0
                    let no_emptys = 0
                    this.services_hotels.forEach((s) => {
                        s.variations.forEach((v) => {
                            // if (v.status_hotel !== 'RQ') {
                                if (v.confirmation_code == 0 || v.confirmation_code === null ||
                                    v.confirmation_code.trim() === '') {
                                    emptys++
                                } else {
                                    no_emptys++
                                }
                            // }
                        })
                    })
                    if (no_emptys > 0) {
                        this.btn_disabled = false
                    } else {
                        this.btn_disabled = true
                    }
                },
                get_hotels() {
                    this.loading = true
                    this.message = ""
                    axios.get(baseExternalURL + 'api/files/' + this.nrofile + '/services/code/' + this.codsvs).then((response) => {
                        if( response.data.success ) {
                            this.services_hotels = this.reformat_data(response.data)
                            // console.log("this.services_hotels")
                            // console.log(this.services_hotels)
                            if(response.data.hotel!=''){
                                this.hotel = response.data.hotel
                                this.hotel_check_in_hour = response.data.hotel.check_in_time
                                this.hotel_check_out_hour = response.data.hotel.check_out_time
                            }
                            this.verify_code_inputs()
                            this.message = ""
                        } else {
                            console.log(response.data)
                            if( response.data.file.length === 0 ){
                                this.no_file_message = true
                                this.message = this.translations.messages.files_not_found
                            } else {
                                this.message = response.data.message
                            }
                        }

                        this.loading = false
                    })
                },
                get_hotels_force() {
                    this.loading = true
                    this.message = ""
                    axios.get(baseExternalURL + 'api/files/' + this.nrofile + '/services/code/' + this.codsvs + '/force').then((response) => {
                        if( response.data.success ) {
                            this.services_hotels = this.reformat_data(response.data)
                            // console.log("this.services_hotels")
                            // console.log(this.services_hotels)
                            if(response.data.hotel!=''){
                                this.hotel = response.data.hotel
                                this.hotel_check_in_hour = response.data.hotel.check_in_time
                                this.hotel_check_out_hour = response.data.hotel.check_out_time
                            }
                            this.verify_code_inputs()
                            this.message = ""
                        } else {
                            console.log(response.data)
                            if( response.data.file.length === 0 ){
                                this.no_file_message = true
                                this.message = this.translations.messages.files_not_found
                            } else {
                                this.message = response.data.message
                            }
                        }

                        this.loading = false
                    })
                },
                reformat_data(data) {

                    this.group_name = ""
                    this.q_adults = 0
                    this.q_children = 0
                    this.client_nationality = ""
                    if(data.file){
                        this.group_name = data.file.description
                        this.q_adults = data.file.adults
                        this.q_children = data.file.children
                        if(data.file.reservation){
                            if(data.file.reservation.client){
                                if(data.file.reservation.client.countries){
                                    if(data.file.reservation.client.countries.translations.length){
                                        this.client_nationality = data.file.reservation.client.countries.translations[0].value
                                    }
                                }
                            }
                        }

                    }

                    let data_ = []
                    let data_array = []
                    data.data.forEach((s) => {
                        if (data_array[s.date_in + s.date_out] === undefined) {
                            data_array[s.date_in + s.date_out] = data_.length
                            data_.push({
                                date_in: s.date_in,
                                date_out: s.date_out,
                                variations: []
                            })
                            data.data.forEach((s_) => {
                                if (s_.date_in === s.date_in && s_.date_out === s.date_out) {
                                    data_[data_.length - 1].variations.push(s_)
                                }
                            })
                        }
                    })

                    return data_
                },
                duplicate_codcfm_sames(index_service, index_variation) {
                    if (index_service === 0 && this.services_hotels.length > 1) {
                        this.services_hotels.forEach((service, key_s) => {
                            if (key_s > 0) {
                                service.variations.forEach((v) => {
                                    if (v.base_code === this.services_hotels[0].variations[index_variation].base_code &&
                                        v.total_rooms === this.services_hotels[0].variations[index_variation].total_rooms
                                    ) {
                                        v.confirmation_code = this.services_hotels[0].variations[index_variation].confirmation_code
                                    }
                                })
                            }
                        })
                    }
                    this.verify_code_inputs()
                },
            },
            filters: {
                format_date : function (_date) {
                    if( _date == undefined ){
                        // console.log('fecha no parseada: ' + _date)
                        return;
                    }
                    let secondPartDate = ''

                    if( _date.length > 10 ){
                        secondPartDate = _date.substr(10, _date.length )
                        _date = _date.substr(0,10)
                    }

                    _date = _date.split('-')
                    _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                    return _date + secondPartDate
                },
                format_hour(value){
                    if( value == undefined ){
                        return;
                    }
                    let hour_ = value.substr(0, 5)
                    return hour_
                }
            }
        })
    </script>
@endsection

<style>
    .td-title {
        background: white;
        border-bottom: solid 1px #952216;
    }
    table td{
        padding: 1.5rem 1.563rem !important;
        vertical-align: middle !important;
    }
    .providers-title {
    margin: 60px 0 0 0 !important;
    font-size: 3.5em !important;
    font-weight: 500 !important;
    color: #EB5757 !important;
  }
  .color-provider {
    color: #EB5757 !important;
  }
  .providers-file {
    font-size: 1.5em !important;
    font-weight: 500 !important;
  }
  .providers-code {
    color: #EB5757;
  }
  .providers-inf {
    color: #2E2B9E;
  }
  .providers-subtitle {
    font-size: 2.25em !important;
    font-weight: 500 !important;
    color: #2E2E2E !important;
  }
  .providers-state-onRequest{
    color: #FFCC00;
  }
  .providers-state-confirmed{
    color: #1ED790;
  }
  .bg-table{
    background: #EB5757;
    color: #FFFFFF;
    font-size: 1em;
    line-height: 2em;
  }
  .bg-color {
    font-size: 1.125em;
    background: #E2E8F0;
    }
</style>
