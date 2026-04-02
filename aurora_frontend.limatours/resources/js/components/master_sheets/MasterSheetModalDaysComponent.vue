<template>
    <div class="modal fade modal-general modal-editar-dia" id="modal-editar-dia">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title" v-if="master_sheet_day_id===''">{{ translations.label.add }} {{ translations.label.day }}</div>
                    <div class="modal-title" v-else>{{ translations.label.edit }} {{ translations.label.day }}</div>
                    <br>
                    <div class="modal-card-editar-content">
                        <div class="modal-card-editar-numero">
                            <input type="text" placeholder="#" v-model="day_number" :disabled="true">
                        </div>
                        <div class="input-sin-icono modal-card-editar-titulo">
                            <input type="text" :placeholder="translations.label.description_day" v-model="form.name">
                        </div>
                        <div class="input-icono modal-card-editar-fecha">
                            <date-picker type="text" :placeholder="translations.label.start_date" :config="optionsR" v-model="form.date_in"></date-picker>
                            <i class="icono icon-calendar"></i>
                        </div>
                    </div>

                    <div class="modal-card-subtitle" v-if="master_sheet_day_id!==''">{{ translations.label.itinerary | upper }}</div>
                    <div class="modal-card-itinerario" v-if="master_sheet_day_id!==''">
                        <div class="modal-card-itinerario-header">
                            <div class="drag"></div>
                            <div class="hora">{{ translations.label.hour | upper }}</div>
                            <div class="codigo">{{ translations.label.code | upper }}</div>
                            <div class="descripcion">{{ translations.label.description | upper }}</div>
                            <div class="nota">{{ translations.label.paxes | upper }}</div>
                            <div class="nota">{{ translations.label.note | upper }}</div>
                            <div class="borrar"></div>
                        </div>
                        <div class="modal-card-itinerario-content">
                            <div class="app-draggable">
                                <draggable v-model="services" group="people" @start="drag=true" @end="drag=false" handle=".handle">
                                    <transition-group name="slide-fade" tag="div">
                                        <div v-for="(service, s_i) in services" v-show="service.show_tr" :key="service.id_front">
                                        <div class="celda-content">
                                            <div class="celda">
                                                <div class="drag handle"><i class="icon-align-justify"></i></div>
                                                <div class="input-sin-icono hora">
                                                    <input type="time" min="00:00" max="23:59" :placeholder="translations.label.hour" v-model="service.check_in">
                                                </div>
                                                <div class="input-sin-icono codigo"
                                                     :class="{'codigo-azul':(service.service_code_stela!==null && service.service_code_stela!=='')}">
                                                    <input type="text" :placeholder="translations.label.code" v-model="service.service_code_stela" disabled>
                                                </div>
                                                <div class="input-sin-icono descripcion" :class="{'descripcion-azul': service.comment_status}">
                                                    <input type="text" :placeholder="translations.label.description" v-on:keyup="filter_services(service)" v-model="service.description">
                                                    <button class="icon-link" data-toggle="tooltip" data-placement="top" :title="translations.label.messages"></button>
                                                </div>
                                                <div class="nota">
                                                    <label class="checkbox-general-2" @click="toggle_paxs(service)">
                                                        <input type="checkbox" v-model="service.pax_status"><span class="checkmark"></span>
                                                    </label>
                                                </div>
                                                <div class="nota">
                                                    <label class="checkbox-general-2" @click="toggle_comment(service)">
                                                        <input type="checkbox" v-model="service.comment_status"><span class="checkmark"></span>
                                                    </label>
                                                </div>
                                                <div class="borrar">
                                                    <button @click="remove_service(s_i)" :disabled="loading">
                                                        <i class="icon-trash-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="celda-nota-sin-resultado" v-if="service.results">

                                                <div style="float: right; cursor: pointer; margin-bottom: -20px;" @click="service.results=false">
                                                    <i class="fa fa-times"></i>
                                                </div>
                                                <div class="aviso" v-if="service.results.length===0">
                                                    {{ translations.messages.without_results }}
                                                </div>
                                                <div v-else>
                                                    <div class="modal-card-select-content ws_results">
                                                        <div class="celda" v-for="result in service.results" @click="choose_service(service, result)">
                                                            <div class="nombre">
                                                                ({{ result.codigo }}) - {{ max_width( result.descri, 55 ) }} - ({{ result.ciudes }} - {{ result.ciuhas }})
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="celda-nota-trabajo" v-if="service.show_paxs">

                                                <button class="btn btn-message-save" :disabled="loading || service.paxs===0" @click="close_pax(service)">
                                                    <i class="fa fa-save"></i> {{ translations.label.save }}
                                                </button>
                                                <button class="btn btn-message-close" :disabled="loading || service.paxs===0" @click="close_pax(service)">
                                                    {{ translations.label.close }}
                                                </button>
                                                <input class="form-control" type="number" min="0" step="1" v-model="service.paxs">

                                            </div>
                                            <div class="celda-nota-trabajo" v-if="service.show_comment">
                                                <textarea rows="5" :placeholder="translations.label.comments" v-model="service.comment"></textarea>

                                                <button>
                                                    <i class="icon-paperclip"></i>
                                                    <span>
                                                        <input type="file" :ref="'file_note_'+service.id_front" :id="'file_note_'+service.id_front">
                                                    </span>
                                                </button>

                                                <button v-if="service.attached!=='' && service.attached!==null">
                                                    <i class="icon-paperclip"></i>
                                                    <a target="_blank" :href="baseURL + 'uploads/' + service.attached">{{ translations.label.see_attached }}</a>
                                                </button>

                                                <button class="btn btn-message-save" :disabled="loading || service.comment.length===0" @click="save_comment(service)">
                                                    <i class="fa fa-save"></i> {{ translations.label.save }}
                                                </button>
                                                <button class="btn btn-message-close" :disabled="loading || service.comment.length===0" @click="close_comment(service)">
                                                    Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    </transition-group>
                                </draggable>
                            </div>
                        </div>
                    </div>
                    <div class="modal-card-subtitle" v-if="master_sheet_day_id!==''">{{ translations.label.main_accommodation | upper }}</div>
                    <div class="modal-card-select" v-if="master_sheet_day_id!==''">
                        <div class="modal-card-select-header">
                            <div class="select">{{ max_width( translations.label.select ) | upper }}</div>
                            <div class="nombre">{{ translations.label.name | upper }}</div>
                            <div class="checkin">{{ translations.label.check_in | upper }}</div>
                            <div class="checkout">{{ translations.label.check_out | upper }}</div>
                            <div class="incluye">{{ translations.label.it_includes | upper }}</div>
                        </div>
                        <div class="modal-card-select-content">
                            <div class="celda" v-for="hotel in services" v-if="hotel.type_service === 'hotel'">
                                <div class="select" @click="put_status_hotel(hotel)">
                                    <label class="checkbox-general checkbox-primer-item">
                                        <input type="radio" name="radio" v-model="radio_accommodation" :value="'checked_'+hotel.status">
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                                <div class="nombre">{{ hotel.description }}</div>
                                <div class="checkin">{{ hotel.check_in }} {{ translations.label.hrs }}</div>
                                <div class="checkout">{{ hotel.check_out }} {{ translations.label.hrs }}</div>
                                <div class="incluye">{{ translations.label.breakfast }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" v-if="master_sheet_day_id!==''">
                    <button class="button-cancelar button-modal-gris" type="button" :disabled="loading" @click="add_service()">
                        <i class="icon-drag"></i><span style="margin-left: 10px;">{{ translations.label.service }}</span>
                    </button>
                    <button class="button-cancelar button-actualizar" type="button" @click="update()" :disabled="loading">{{ translations.label.save }}</button>
                </div>
                <div class="modal-footer" v-else>
                    <button class="button-cancelar button-actualizar" type="button" @click="save()" :disabled="loading">{{ translations.label.create }}</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['data'],
        data: () => {
            return {
                lang: '',
                loading: false,
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                baseURL: window.baseURL,
                baseExternalURL: window.baseExternalURL,
                baseExpressURL: window.baseExpressURL,
                master_sheet_id: '',
                master_sheet_day_id: '',
                day_number: '?',
                form : {},
                optionsR: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    minDate: moment().format('YYYY-MM-DD')
                },
                services:[],
                radio_accommodation: 'checked_1',
            }
        },
        created: function () {
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
        },
        computed: {

        },
        methods: {
            put_status_hotel(hotel){
                this.services.forEach( s=>{
                    if( s.type_service === 'hotel' ){
                        s.status = 0
                    }
                } )
                hotel.status = 1
            },
            choose_service( service, result ){
                service.results = false
                service.service_code_stela = result.codigo
                service.description = result.descri
                service.description_ES = result.descri_es
                service.description_EN = result.descri_en
                service.description_PT = result.descri_pt
                service.description_IT = result.descri_it
                service.origin_city = result.ciudes
                service.destination_city = result.ciuhas
                if( result.clasvs === 'H' ){
                    service.status = 0
                    service.type_service = 'hotel'
                    if( result.checks !== '' && result.checks !== null ){
                        // CHECK IN: 15 HRS     CHECK OUT: 12 HRS
                        let checks_ = result.checks
                        let check_in_ = checks_.split('CHECK IN: ')
                        if( check_in_.length === 1 ){
                            check_in_ = checks_.split('CHECK IN ')
                        }
                        check_in_ = check_in_[1]
                        check_in_ = check_in_.substr(0, 2)
                        service.check_in = check_in_ + ':00'

                        let check_out_ = checks_.split('CHECK OUT: ')
                        if( check_out_.length === 1 ){
                            check_out_ = checks_.split('CHECK OUT ')
                        }
                        check_out_ = check_out_[1].substr(0, 2)
                        service.check_out = check_out_ + ':00'
                    }
                } else {
                    service.status = 1
                    service.type_service = 'service'
                }
            },
            max_width(string_, long_, text_after = '...'){
                if(string_ !== undefined && string_.length > long_){
                    return string_.substr(0, long_) + text_after
                }
                return string_
            },
            toggle_comment(service){
                service.show_comment = !(service.comment_status)
            },
            toggle_paxs(service){
                service.show_paxs = !(service.pax_status)
            },
            save_comment(service){
                this.close_comment(service)
                let el = document.getElementById('file_note_'+service.id_front)
                let sub_file = el.files[0]
                if(sub_file !== '' && sub_file !== null && sub_file !== undefined)
                {
                    let formData = new FormData();
                    formData.append('file', sub_file);

                    axios.post(baseURL + 'account/upload_file',
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }
                    ).then((result) => {
                        this.loading = false
                        if(result.data.success)
                        {
                            service.attached = result.data.name
                        }
                        else
                        {
                            this.$toast.error(result.data.content, {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                    })
                        .catch((e) => {
                            this.loading = false
                            console.log(e)
                        })
                }
            },
            close_pax(service){
                service.show_paxs = false
            },
            close_comment(service){
                service.show_comment = false
            },
            load() {
                this.translations = this.data.translations
                this.master_sheet_id = this.data.master_sheet_id
                this.form.master_sheet_id = this.data.master_sheet_id
                if( this.data.master_sheet_day_id === undefined ) {
                    this.master_sheet_day_id = ''
                    this.form.id = ''
                    this.form.name = ''
                    this.day_number = '?'
                    this.services = []
                    this.add_service()
                } else {
                    this.master_sheet_day_id = this.data.master_sheet_day_id
                    this.form.id = this.data.master_sheet_day_id
                    this.search()
                }
            },
            filter_services(service){

                if( service.description.length <= 2 ){
                    service.results = false
                    return
                }

                this.loading = true

                axios.get(
                    baseExpressURL + 'api/v1/services/services?filter='+service.description+'&limit=5'
                )
                    .then((result) => {
                        if(result.data.success){
                            service.results = result.data.data
                        } else {
                            service.results = false
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })

            },
            search(){
                this.loading = true

                axios.get(
                    baseExternalURL + 'api/master_sheet/day/'+this.form.id
                )
                    .then((result) => {
                        this.loading = false
                        this.form.name = result.data.data.name
                        this.day_number = result.data.data.number
                        this.form.date_in = this.$parent.formatDate( result.data.data.date_in, '-', '/', 1 )
                        this.services = result.data.data.services
                        if( this.services.length === 0 ){
                            this.add_service()
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            save(){

                if( this.form.name === '' || this.form.date_in === null ){
                    this.$toast.warning(this.translations.messages.complete_info, {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true
                axios.post(
                    baseExternalURL + 'api/master_sheet/day', this.form
                )
                    .then((result) => {
                        if( result.data.success ){
                            // this.$toast.success(this.translations.messages.saved_correctly, {
                            //     position: 'top-right'
                            // })
                            this.form.id = result.data.data.id
                            this.master_sheet_day_id = result.data.data.id
                            this.day_number = result.data.data.number
                            let message_ = this.translations.messages.has_created_day + ': ' + this.day_number
                            this.$parent.send_notification(message_)
                            this.$parent.search_days(true)
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            add_service(){

                var d = new Date();
                var n = d.getMilliseconds();

                let data_ = {
                    id : "",
                    service_code_stela : "",
                    check_in : "",
                    check_out : "",
                    destination_city : "",
                    origin_city : "",
                    includes : "",
                    description : "",
                    description_ES : "",
                    description_EN : "",
                    description_IT : "",
                    description_PT : "",
                    type_service : "service",
                    status : 1,
                    pax_status : false,
                    paxs : "",
                    comment_status : false,
                    comment : "",
                    attached : "",
                    show_paxs : false,
                    show_comment : false,
                    show_tr : true,
                    results : false,
                    id_front : n,
                }
                this.services.push(data_)
            },
            remove_service(index){
                this.services[index].show_tr = false
                let me = this
                setTimeout( ()=>{
                    me.services.splice(index, 1)
                }, 1300 )
            },
            update(){
                this.loading = true

                axios.put(
                    baseExternalURL + 'api/master_sheet/day/' + this.form.id, this.form
                )
                    .then((result) => {
                        if( result.data.success ){

                            this.day_number = result.data.data.number
                            this.update_services()
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                            this.loading = false
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            update_services(){

                axios.post(
                    baseExternalURL + 'api/master_sheet/day/' + this.form.id + '/services', { services : this.services }
                )
                    .then((result) => {
                        if( result.data.success ){
                            // this.$toast.success(this.translations.messages.updated_successfully, {
                            //     position: 'top-right'
                            // })
                            let message_ = this.translations.messages.has_updated_services_day + ': ' + this.day_number
                            this.$parent.send_notification(message_)
                            this.$parent.search_days(true)
                            this.$parent.get_total_notes()
                            this.search()
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
        },
        filters:{
            upper (value){
                return (value!==undefined) ? value.toUpperCase() : value
            }
        }
    }
</script>
<style>
    .btn-message-save{
        float: right;
        border: solid 1px #781c53 !important;
    }
    .btn-message-close{
        float: right;
        border: solid 1px #848484 !important;
        background-color: #b0b0b0 !important;
        margin-right: 5px;
        color: white !important;
    }
    .slide-fade-enter-active {
        transition: all .1s ease;
    }
    .slide-fade-leave-active {
        transition: all .3s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    }
    .slide-fade-enter, .slide-fade-leave-to
        /* .slide-fade-leave-active below version 2.1.8 */ {
        transform: translateX(10px);
        opacity: 0;
    }
    .ws_results{
        background: #effaff;
        padding: 15px;
    }
    .ws_results .celda{
        cursor: pointer;
    }
    .ws_results .celda:hover{
        background-color: #faebff;
    }
</style>
