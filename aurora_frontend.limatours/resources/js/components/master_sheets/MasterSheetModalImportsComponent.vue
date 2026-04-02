<template>
    <div class="modal fade modal-general modal-info" id="modal-imports">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title">{{ translations.label.import }} {{ translations.label.of }} ...</div>
                    <br>
                    <div class="modal-card-editar-content">
                        <button :disabled="loading || (show_confirm && tab!=='file')" type="button" class="button-cancelar" :class="{'button-actualizar':tab==='file'}"
                                style="float: left; max-width: 75px;" @click="set_tab('file')">
                            {{ translations.label.file }}
                        </button>
                        <button :disabled="loading || (show_confirm && tab!=='programming')" type="button" class="button-cancelar" :class="{'button-actualizar':tab==='programming'}"
                                style="float: left;margin-left: 14px;" @click="set_tab('programming')">
                            {{ translations.label.programming }}
                        </button>
                        <button :disabled="loading || (show_confirm && tab!=='master_sheet')" type="button" class="button-cancelar" :class="{'button-actualizar':tab==='master_sheet'}"
                                style="float: left;margin-left: 14px;" @click="set_tab('master_sheet')">
                            {{ translations.label.title }}
                        </button>
                    </div>

                    <div class="modal-card-editar-content" style="margin-top: 80px">
                        <div class="modal-info-borrar" v-show="tab==='file'">
                            <v-select class="form-control mb-4"
                                      :options="files"
                                      :value="nrofile"
                                      label="descri" :filterable="false" @search="search_files"
                                      :placeholder="translations.label.search_file"
                                      v-model="file_selected" name="file" id="file" style="padding-top: 5px;">
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        {{ option.nroref }} - {{ option.descri }}
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                      {{ option.nroref }} - {{ option.descri }}
                                    </div>
                                </template>
                            </v-select>
                        </div>
                        <div class="modal-info-borrar" v-show="tab==='programming'">
                            <v-select class="form-control mb-4"
                                      :options="s_packages"
                                      :value="s_package_id" @input="set_package_rates"
                                      label="code" :filterable="false" @search="search_packages"
                                      :placeholder="translations.label.search_programming"
                                      v-model="s_package_selected" name="package" id="package" style="padding-top: 5px;">
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        {{ option.id }} - {{ option.translations[0].name }}
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                        {{ option.id }} - {{ option.translations[0].name }}
                                    </div>
                                </template>
                            </v-select>

                            <div class="modal-info-borrar" v-for="rate in s_package_rates">
                                <i class="fa fa-dot-circle-o"></i> {{ rate.name }}
                                <ul>
                                    <li v-for="cat in rate.plan_rate_categories">
                                        <label>
                                            <input type="radio" name="package_category" v-model="package_category" :value="cat.id">
                                            {{ cat.category.translations[0].value }}
                                        </label>
                                    </li>
                                </ul>
                            </div>

                        </div>
                        <div class="modal-info-borrar" v-show="tab==='master_sheet'">
                            <v-select class="form-control mb-4"
                                      :options="m_sheets"
                                      :value="m_sheet_id"
                                      label="name" :filterable="false" @search="search_master_sheets"
                                      :placeholder="translations.label.search_master_sheets"
                                      v-model="m_sheet_selected" name="m_sheet" id="m_sheet" style="padding-top: 5px;">
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        HM-{{ option.id }} - {{ option.name }}
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                        HM-{{ option.id }} - {{ option.name }}
                                    </div>
                                </template>
                            </v-select>
                        </div>
                    </div>

                    <div class="modal-info-borrar" v-show="show_confirm" style="margin-top: 80px;">
                        {{ translations.messages.delete_itinerary }}.
                    </div>
                    <div class="modal-info-pregunta" v-show="show_confirm">{{ translations.messages.confirm_continue }}</div>
                </div>
                <div class="modal-footer" v-show="show_confirm">
                    <button :disabled="loading" class="button-cancelar cancelar-info" type="button" @click="show_confirm=false">{{ translations.label.not }}</button>
                    <button :disabled="loading" type="button" class="button-cancelar button-actualizar" @click="do_import()">{{ translations.label.yes }}, {{ translations.label.remove }}</button>
                </div>
                <div class="modal-footer" v-show="!show_confirm">
                    <button :disabled="loading" class="button-cancelar cancelar-info" type="button" data-dismiss="modal">{{ translations.label.cancel }}</button>
                    <button :disabled="loading" type="button" class="button-cancelar button-actualizar" @click="will_import()">{{ translations.label.import }}</button>
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
                baseExternalURL: window.baseExternalURL,
                baseExpressURL: window.baseExpressURL,
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                master_sheet_id: "",
                tab: "file",
                files:[],
                file_selected:[],
                nrofile:null,
                m_sheets:[],
                m_sheet_selected:[],
                m_sheet_id:null,
                s_packages:[],
                s_package_selected:[],
                s_package_id:null,
                show_confirm:false,
                s_package_rates:[],
                package_category:'',
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
            load() {
                this.translations = this.data.translations
                this.master_sheet_id = this.data.master_sheet_id
                this.tab = ( this.data.tab ) ? this.data.tab : ''
            },
            set_tab(entity){
                this.tab = entity
                this.show_confirm = false
            },
            will_import(){
                if( this.tab === 'file' ){
                    if(this.file_selected.nroref === undefined){
                        this.$toast.warning(this.translations.label.select_file, {
                            position: 'top-right'
                        })
                        return
                    }
                }
                if( this.tab === 'master_sheet' ){
                    if(this.m_sheet_selected.id === undefined){
                        this.$toast.warning(this.translations.label.select_master_sheet, {
                            position: 'top-right'
                        })
                        return
                    }
                }
                if( this.tab === 'programming' ){
                    if(this.s_package_selected.id === undefined){
                        this.$toast.warning(this.translations.label.select_package, {
                            position: 'top-right'
                        })
                        return
                    }
                    if(this.package_category === ''){
                        this.$toast.warning(this.translations.label.select_package_category, {
                            position: 'top-right'
                        })
                        return
                    }
                }
                this.show_confirm = true
            },
            do_import(){
                if( this.tab === 'master_sheet' ){
                    this.import_master_sheet()
                }
                if( this.tab === 'programming' ){
                    this.import_package()
                }
                if( this.tab === 'file' ){
                    this.will_import_file()
                }
            },
            import_master_sheet(){
                this.loading = true
                axios.post('/api/master_sheet/'+this.master_sheet_id+'/clone/master_sheet',
                    {
                        master_sheet_id_for_clone : this.m_sheet_selected.id
                    })
                    .then((result) => {
                        if(result.data.success){
                            this.$toast.success(this.translations.messages.imported_successfully, {
                                position: 'top-right'
                            })
                            let message_ = this.translations.messages.imported_from_another_master_sheet
                            this.$parent.send_notification(message_)
                            this.$parent._closeModal()
                            this.$parent.search_days(true)
                        } else {
                            this.$toast.error(this.translations.messages.internal_error_occurred, {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    }).catch((e) => {
                    this.loading = false
                })
            },
            import_package(){
                this.loading = true
                axios.get('/api/packages/'+this.s_package_selected.id+'/category/'+this.package_category+'/services/equivalences')
                    .then((result) => {
                        if(result.data.success){
                            let package_services = result.data.data
                            let services_ = []
                            package_services.forEach( (service, s)=>{
                                services_.push({
                                    key: s,
                                    type: service.type,
                                    equivalence:
                                        (service.type === 'service')
                                            ? parseInt( service.equivalence )
                                            : service.equivalence,
                                    stela_codes: [],
                                })
                            })
                            this.extract_stela_codes(package_services, services_)
                        } else {
                            this.$toast.error(this.translations.messages.internal_error_occurred, {
                                position: 'top-right'
                            })
                            this.loading = false
                        }
                    }).catch((e) => {
                    this.loading = false
                })
            },
            extract_stela_codes(package_services, services_){

                axios.post(
                    baseExpressURL + 'api/v1/services/services/codes', { equivalences : services_ }
                )
                    .then((result) => {
                        if(result.data.success){

                            result.data.data.forEach( service=>{

                                if( service.type === 'hotel' ){

                                    let checks_ = service.stela_codes[0].checks
                                    package_services[service.key].check_in = ''
                                    package_services[service.key].check_out = ''

                                    if( service.stela_codes[0].checks !== null ){
                                        // Check-out 12:00 hrs
                                        let check_in_ = checks_.split('CHECK IN: ')
                                        if( check_in_.length === 1 ){
                                            check_in_ = checks_.split('CHECK IN ')
                                            check_in_ = check_in_[1]
                                            check_in_ = (check_in_ !== undefined) ? check_in_.substr(0, 2) : '00'
                                            package_services[service.key].check_in = check_in_ + ':00'
                                        }

                                        let check_out_ = checks_.split('CHECK OUT: ')
                                        if( check_out_.length === 1 ){
                                            check_out_ = checks_.split('CHECK OUT ')
                                            check_out_ = (check_out_[1] !== undefined) ? check_out_[1].substr(0, 2) : '00'
                                            package_services[service.key].check_out = check_out_ + ':00'
                                        }
                                    }
                                }

                                package_services[service.key].stela_codes = service.stela_codes
                            })
                            this.import_stela(package_services)
                        }
                        else {
                            this.$toast.error(this.translations.messages.error_extracting_stela, {
                                position: 'top-right'
                            })
                            this.loading = false
                        }
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            },
            import_stela(package_services){

                this.loading = true
                axios.post('/api/master_sheet/'+this.master_sheet_id+'/clone/package_services_stela',
                    {
                        package_services : package_services
                    })
                    .then((result) => {
                        if(result.data.success){
                            this.$toast.success(this.translations.messages.imported_successfully, {
                                position: 'top-right'
                            })
                            let message_ =
                                this.translations.messages.imported_package_services+': ' +
                                this.s_package_selected.translations[0].name
                            this.$parent.send_notification(message_)
                            this.$parent._closeModal()
                            this.$parent.search_days(true)
                        } else {
                            this.$toast.error(this.translations.messages.internal_error_occurred, {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    }).catch((e) => {
                    this.loading = false
                })
            },
            will_import_file(){

                axios.get(
                    baseExpressURL + 'api/v1/files/'+this.file_selected.nroref+'/services'
                )
                    .then((result) => {
                        if(result.data.success){

                            let services = result.data.data
                            services.forEach( (service, s)=>{
                                if( service.clase === 'H' ){
                                    services[s].type = 'hotel'
                                } else {
                                    services[s].type = 'service'
                                }
                            })
                            if( services.length === 0 ){
                                this.$toast.warning(this.translations.messages.file_not_contain_importable_services, {
                                    position: 'top-right'
                                })
                                this.loading = false
                            } else {
                                this.import_file(services)
                            }
                        }
                        else {
                            this.$toast.error(this.translations.messages.error_extracting_stela, {
                                position: 'top-right'
                            })
                            this.loading = false
                        }
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            },
            import_file(services){

                this.loading = true
                axios.post('/api/master_sheet/'+this.master_sheet_id+'/clone/file',
                    {
                        services : services
                    })
                    .then((result) => {
                        if(result.data.success){
                            this.$toast.success(this.translations.messages.imported_successfully, {
                                position: 'top-right'
                            })
                            let message_ =
                                this.translations.messages.imported_services_from_file+': ' +
                                this.file_selected.nroref
                            this.$parent.send_notification(message_)
                            this.$parent._closeModal()
                            this.$parent.search_days(true)
                        } else {
                            this.$toast.error(this.translations.messages.internal_error_occurred, {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    }).catch((e) => {
                    this.loading = false
                })
            },
            search_master_sheets(search, loading) {
                loading(true)
                axios.get('/api/master_sheet?query='+search)
                    .then((result) => {
                        loading(false)
                        this.m_sheets = result.data.data
                    }).catch(() => {
                    loading(false)
                })
            },
            search_packages(search, loading) {
                loading(true)
                axios.get('/api/packages?limit=5&status=1&queryCustom='+search+
                    '&lang='+localStorage.getItem('lang')+'&filter_plan_rates=true')
                    .then((result) => {
                        loading(false)
                        this.s_packages = result.data.data
                    }).catch(() => {
                    loading(false)
                })
            },
            search_files(search, loading) {
                loading(true)
                axios.get(baseExpressURL+'api/v1/files?filter='+search)
                    .then((result) => {
                        loading(false)
                        this.files = result.data.data
                    }).catch(() => {
                    loading(false)
                })
            },
            set_package_rates(){
                this.s_package_rates = this.s_package_selected.plan_rates
            },
        }
    }
</script>
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }
    /*
    .v-select input{
        height: 25px;
    }
    */
    .modal-dialog .modal-body .vs--searchable .vs__selected-options .vs__selected{
        width: 100% !important;
    }
</style>
