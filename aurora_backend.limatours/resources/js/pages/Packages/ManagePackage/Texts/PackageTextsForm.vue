<template>
    <div class="row">

        <div class="col-md-12">

            <b-tabs>
                <b-tab :key="trans.language.id" :title="trans.language.name" ref="tabtrans"
                       v-for="trans in translations" @click="limpiarFormulario(trans.language.id)">
                    <form @submit.prevent="validateBeforeSubmit( trans.language.id )">
                        <div class="form-row">
                            <label for="name">{{ $t('packagesmanagepackagetexts.name') }}</label>
                            <div class="col-sm-12 p-0">
                                <input :class="{'form-control':true }"
                                       id="name" name="name"
                                       type="text"
                                       class="input-internal"
                                       v-model="form.translations[trans.language.id].name">                              
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="tradename">{{ $t('packagesmanagepackagetexts.tradename') }}</label>
                            <div class="col-sm-12 p-0">
                                <input :class="{'form-control':true }"
                                       id="tradename" name="tradename"
                                       type="text"
                                       class="input-commerce"
                                       v-model="form.translations[trans.language.id].tradename">
                            </div>
                        </div>

                        <div class="form-row" style="padding-left: 0;">
                            <label for="label">{{ $t('packagesmanagepackagetexts.label') }}</label>
                            <div class="col-sm-12 p-0">
                                <input :class="{'form-control':true }"
                                       id="label" name="label"
                                       type="text"
                                       class="input-internal"
                                       v-model="form.translations[trans.language.id].label">
                            </div>
                        </div>
                        <!-- <div class="form-row">
                            <label for="description">{{ $t('packagesmanagepackagetexts.description') }}</label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="description" name="description"
                                          rows="5"
                                          class="input-internal"
                                          v-model="form.translations[trans.language.id].description"></textarea>
                            </div>
                        </div> -->

                        <div class="form-row">
                            <label for="description_commercial">{{ $t('packagesmanagepackagetexts.description') }} 
                            <!-- ({{ $t('service.commercial') }}) -->
                        </label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="description_commercial" name="description_commercial"
                                          rows="5"
                                          class="input-commerce"
                                          v-model="form.translations[trans.language.id].description_commercial"></textarea>
                            </div>
                        </div>

                        <!-- <div class="form-row" style="padding-left: 0;">
                            <label for="label">{{ $t('packagesmanagepackagetexts.itinerary_label') }}</label>
                            <div class="col-sm-12 p-0">
                                <input :class="{'form-control':true }"
                                       id="itinerary_label" name="itinerary_label"
                                       type="text"
                                       class="input-internal"
                                       v-model="form.translations[trans.language.id].itinerary_label">
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="itinerary_description">{{ $t('packagesmanagepackagetexts.itinerary') }}</label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="itinerary_description" name="itinerary_description"
                                          rows="5"
                                          class="input-internal"
                                          v-model="form.translations[trans.language.id].itinerary_description"></textarea>
                            </div>
                        </div> -->

                        <div class="form-row">
                            <label for="itinerary_description_commercial">{{ $t('packagesmanagepackagetexts.skeleton')}} 
                                <!-- ({{ $t('service.commercial') }}) -->
                            </label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="itinerary_description_commercial" name="itinerary_description_commercial"
                                          rows="5"
                                          class="input-commerce"
                                          v-model="form.translations[trans.language.id].itinerary_commercial"></textarea>
                            </div>
                        </div>

                        <!-- <div class="form-row">
                            <label for="itinerary_link">{{ $t('packagesmanagepackagetexts.itinerary_link') }}</label>
                            <div class="col-sm-12 p-0">
                                <input :class="{'form-control':true }"
                                       id="itinerary_link" name="itinerary_link"
                                       type="text"
                                       class="input-internal"
                                       v-model="form.translations[trans.language.id].itinerary_link">
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="itinerary_link_commercial">{{ $t('packagesmanagepackagetexts.itinerary_link') }}
                                ({{ $t('service.commercial') }})</label>
                            <div class="col-sm-12 p-0">
                                <input :class="{'form-control':true }"
                                       id="itinerary_link_commercial" name="itinerary_link_commercial"
                                       type="text"
                                       class="input-commerce"
                                       v-model="form.translations[trans.language.id].itinerary_link_commercial">
                            </div>
                        </div> -->

                        <!-- <div class="form-row">
                            <label for="inclusion">{{ $t('packagesmanagepackagetexts.inclusion') }}</label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="inclusion" name="inclusion"
                                          rows="3"
                                          class="input-internal"
                                          v-model="form.translations[trans.language.id].inclusion"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="restriction">{{ $t('packagesmanagepackagetexts.restriction') }}</label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="restriction" name="restriction"
                                          rows="3"
                                          class="input-internal"
                                          v-model="form.translations[trans.language.id].restriction"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="restriction_commercial">{{ $t('packagesmanagepackagetexts.restriction') }} ({{
                                $t('service.commercial') }})</label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="restriction_commercial" name="restriction_commercial"
                                          rows="3"
                                          class="input-commerce"
                                          v-model="form.translations[trans.language.id].restriction_commercial"></textarea>
                            </div>
                        </div> -->

                        <!-- <div class="form-row">
                            <label for="policies">{{ $t('packagesmanagepackagetexts.policies') }}</label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="policies" name="policies"
                                          rows="3"
                                          class="input-internal"
                                          v-model="form.translations[trans.language.id].policies"></textarea>
                            </div>
                        </div>
                        <div class="form-row mb-3">
                            <label for="policies_commercial">{{ $t('packagesmanagepackagetexts.policies') }} ({{
                                $t('service.commercial') }})</label>
                            <div class="col-sm-12 p-0">
                                <textarea :class="{'form-control':true }"
                                          id="policies_commercial" name="policies_commercial"
                                          rows="3"
                                          class="input-commerce"
                                          v-model="form.translations[trans.language.id].policies_commercial"></textarea>
                            </div>
                        </div> -->
                        
                       
                        <div class="row package_link">
                           
                            <div class="col-12 pr-0 pt-4" style="text-align: end;">
                              
                                <!-- <button  class="btn btn-success mr-2 pl-3 pr-3" @click="openAddYear(trans.language.id,trans.package_id)">
                                    {{$t('global.buttons.addItem')}}
                                </button> -->

                                <input class="btn btn-success mr-2 pl-3 pr-3" @click="openAddYear(trans.language.id)"
                                    type="button" :value="$t('global.buttons.addItem')"/>                                
                            </div>

                            <div class="col-12 pr-0">
                                                                    
                                <div class="card-body pr-2 pl-0">
                                    
                                    <table class="VueTables__table table table-striped table-bordered table-hover">
                                        <thead>
                                            <th class="vueTable_column_actions" style="width: 5%;">Acción</th>
                                            <th class="vueTable_column_actions" style="width: 5%;">{{ $t('packagesmanagepackagetexts.year') }}</th>
                                            <!-- <th class="vueTable_column_actions" style="width: 30%;">{{ $t('packagesmanagepackagetexts.itinerary_link') }}</th> -->
                                            <th class="vueTable_column_actions" style="width: 90%;">{{ $t('packagesmanagepackagetexts.itinerary_link') }} </th> <!-- ({{ $t('service.commercial') }}) -->
                                            <!-- <th class="vueTable_column_actions" style="width: 30%;">{{ $t('packagesmanagepackagetexts.link_itinerary_priceless') }}</th> -->
                                        </thead>
                                        <tbody>                                        
                                            <tr v-for="link in form.translations[trans.language.id].link_itinerary" >     
                                                <td>                                
                                                    <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                                                        <template slot="button-content">
                                                            <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                                        </template>
                                                        
                                                        <b-dropdown-item-button class="m-0 p-0" 
                                                        
                                                        @click="selectedTable(link, trans.language.id)">
                                                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                                                            {{$t('global.buttons.edit')}}
                                                        </b-dropdown-item-button>
                                                        
                                                        
                                                        <b-dropdown-item-button @click.stop="eliminarItemPackage(trans.language.id,link.year)" class="m-0 p-0">
                                                            <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                                            {{$t('global.buttons.delete')}}
                                                        </b-dropdown-item-button>
                                                    </b-dropdown>                                
                                                </td>

                                                <td class="py-2" style="max-width: 200px;"><p>{{ link.year ? link.year : '' }}</p></td>                                   
                                                <!-- <td class="py-2" style="max-width: 200px;width: 30%;"><p style="overflow: hidden;text-overflow: ellipsis;height: 22px;max-width: 87%;word-wrap: normal;">{{ link.itinerary_link ? link.itinerary_link : '' }}</p></td> -->

                                                <!-- style="overflow: hidden;text-overflow: ellipsis;height: 22px;max-width: 87%;word-wrap: normal;" -->
                                                <td class="py-2" style="max-width: 200px;width: 30%;"><p >{{ link.itinerary_link_commercial ? link.itinerary_link_commercial : '' }}</p></td>
                                                <!-- <td class="py-2" style="max-width: 200px;width: 30%;"><p style="overflow: hidden;text-overflow: ellipsis;height: 22px;max-width: 87%;word-wrap: normal;">{{ link.link_itinerary_priceless ? link.link_itinerary_priceless : '' }}</p></td> -->
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>                                    
                            </div>
                        </div>
                               

                        <div class="form-row position-relative justify-content-end" style="margin-top: 20px" >
                            <div slot="footer">
                                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                                <button @click="validateBeforeSubmit(trans.language.id)" class="btn btn-success"
                                        type="button" v-if="!loading">
                                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                    {{ $t('global.buttons.submit') }}
                                </button>
                                <button @click="CancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                                    {{$t('global.buttons.cancel')}}
                                </button>
                            </div>
                        </div>

                    </form>

                    

                </b-tab>
                
            </b-tabs>
            
            <b-modal title="Agregar Año" centered ref="my-modal" size="md">
                <div class="col-12 p-0">
                            <!-- <div v-if="errorPackage" class="col-sm-12 ml-0 p-3 bg-danger text-white mb-3">
                                <span>{{ errorPackage=='1' ? $t('packagesmanagepackagetexts.error_must_fill')  :  $t('packagesmanagepackagetexts.error_year') }}</span>
                            </div> -->
                            <div class="col-sm-12 mb-3 pl-0 pr-1">
                                <label class="col-form-label">{{ $t('packagesmanagepackagetexts.year') }}</label>
                                <v-select :options="years"                                    
                                    :reduce="label => label.code" label="label"
                                    v-model="formLink.year"
                                    :clearable="false"
                                    autocomplete="true"></v-select>
                            </div>
                            <!-- <div class="col-sm-12 mb-3 pl-0 pr-1">                                    
                                <label for="itinerary_link">{{ $t('packagesmanagepackagetexts.itinerary_link') }}</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                        
                                        type="text"
                                        class="input-internal"
                                        v-model="formLink.itinerary_link">
                                </div>                                    
                            </div> -->
                            <div class="col-sm-12 mb-3 pl-0 pr-1">                                    
                                <label for="itinerary_link">{{ $t('packagesmanagepackagetexts.itinerary_link') }}</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                        
                                        type="text"
                                        class="input-internal"
                                        v-model="formLink.itinerary_link_commercial">
                                </div>                                    
                            </div>
                            <!-- <div class="col-sm-12 mb-3 pl-0 pr-1">
                                <label for="itinerary_link_commercial">{{ $t('packagesmanagepackagetexts.link_itinerary_priceless') }}</label>
                                <div class="col-sm-12 p-0">
                                    <input :class="{'form-control':true }"
                                        
                                        type="text"
                                        class="input-commerce"
                                        v-model="formLink.link_itinerary_priceless">
                                </div>
                            </div> -->
                    
                        
                </div>

                <div slot="modal-footer">
                    <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    <button class="btn btn-success pr-4 pl-4" @click="agregarItemClass()"
                            v-if="!loading">{{ editarPackage==false ? $t('global.buttons.addItem') : $t('global.buttons.edit') }}</button>
                    <button @click="closeModalAddYear()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>
            

        </div>
    </div>
</template>

<script>
    import { API } from '../../../../api'
    import { Switch as cSwitch } from '@coreui/vue'
    import BTab from 'bootstrap-vue/es/components/tabs/tab'
    import BInputNumber from 'bootstrap-vue/es/components/form-input/form-input'
    import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BFormCheckboxGroup from 'bootstrap-vue/es/components/form-checkbox/form-checkbox-group'
    import vSelect from 'vue-select';
    import 'vue-select/dist/vue-select.css';

    export default {
        components: {
            BTabs,
            BTab,
            cSwitch,
            VueBootstrapTypeahead,
            BFormCheckbox,
            BFormCheckboxGroup,
            BInputNumber,
            vSelect,
        },
        data: () => {
            return {
                loading: false,
                translations: [],
                form: {
                    package_id: '',
                    translations: [],
                   
                },
                formLink:{
                    year:'',
                    link_itinerary_price:'',
                    itinerary_link:'',
                    itinerary_link_commercial:''
                },
                years:[],                
                editarPackage:false,
                errorPackage:'',
                language_selected:'', 
            }
        },
        mounted: function () {

            this.$i18n.locale = localStorage.getItem('lang')

            API.get('/package/' + this.form.package_id + '/translations/').then((result) => {
                this.translations = result.data.data
                let _data = result.data.data
               
                _data.forEach((value) => {
                    this.form.translations[value.language.id] = value
                    this.form.translations[value.language.id].name = (value.name) ? value.name.trim() : ''
                    this.form.translations[value.language.id].tradename = (value.tradename) ? value.tradename.trim() : ((value.tradename) ? value.tradename.trim() : '')
                    this.form.translations[value.language.id].label = (value.label) ? value.label.trim() : ''
                    this.form.translations[value.language.id].description = (value.description) ? value.description.trim() : ''
                    this.form.translations[value.language.id].itinerary_label = (value.itinerary_label) ? value.itinerary_label.trim() : ''
                    this.form.translations[value.language.id].itinerary_description = (value.itinerary_description) ? value.itinerary_description.trim() : ''
                    this.form.translations[value.language.id].itinerary_link = (value.itinerary_link) ? value.itinerary_link.trim() : ''
                    this.form.translations[value.language.id].inclusion = (value.inclusion) ? value.inclusion.trim() : ''
                    this.form.translations[value.language.id].restriction = (value.restriction) ? value.restriction.trim() : ''
                    this.form.translations[value.language.id].policies = (value.policies) ? value.policies.trim() : ''
                    //commercial text
                    this.form.translations[value.language.id].itinerary_commercial = (value.itinerary_commercial) ? value.itinerary_commercial.trim() : ''
                    this.form.translations[value.language.id].itinerary_link_commercial = (value.itinerary_link_commercial) ? value.itinerary_link_commercial.trim() : ''
                    this.form.translations[value.language.id].description_commercial = (value.description_commercial) ? value.description_commercial.trim() : ''
                    this.form.translations[value.language.id].restriction = (value.restriction_commercial) ? value.restriction_commercial.trim() : ''
                    this.form.translations[value.language.id].policies = (value.policies_commercial) ? value.policies_commercial.trim() : ''
                    this.form.translations[value.language.id].link_itinerary = value.link_itinerary

                    
                })

            }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('packagesmanagepackagetexts.error.messages.name'),
                    text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
                })
            })

            this.selectYears();
        },
       
        created: function () {
            this.form.package_id = this.$route.params.package_id
        },
        methods: {
            CancelForm () {
                this.id_image = ''
                this.$router.push({ path: '/packages/list' })
            },
            validateBeforeSubmit: function (lang_id) {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.form.package_id = this.$route.params.package_id
                        
                        this.submit(lang_id)

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.packages'),
                            text: this.$t('packagesmanagepackagetexts.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            submit: function (lang_id) {
                        
                API({
                    method: 'put',
                    url: 'package/' + this.form.package_id + '/translations/' + this.form.translations[lang_id].id,
                    data: this.form.translations[lang_id]
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: "Notificación",
                                text: this.$t('packagesmanagepackagetexts.messages.successfully')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Notificación",
                                text: this.$t('packagesmanagepackagetexts.messages.information_error')
                            })

                            this.loading = false
                        }
                    })
            },

            selectYears(){

                const year = new Date();
                const n = year.getFullYear() + 2;
                this.years = []
                for (let i = n; i >= n - 3; i--) {
                    
                    this.years.push({code:i,label:i})
                }
            },


            agregarItemClass(){

                this.translations.forEach(e => {
                    
                });
                
                let acum = 0;  
                let value2= false         
                let value = false;
                if(this.formLink.year == ''  || this.formLink.itinerary_link_commercial == ''){
                                    
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: "Notificación",
                        text: this.$t('packagesmanagepackagetexts.error_must_fill')
                    })
                
                }else{                 
                    if(this.editarPackage == true){
                        this.form.translations[this.language_selected].link_itinerary.forEach((e, i) => {
                            if(this.formLink.year == e.year && this.formLink.id != i){
                                acum = acum + 1
                                i=0;
                            }
                        });                    
                        
                        if(acum==1){
                                                    
                            acum = 0;
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Notificación",
                                text: this.$t('packagesmanagepackagetexts.error_year')
                            })
                            
                        }else{
                            this.form.translations[this.language_selected].link_itinerary.forEach((e,i) => {
                                if(this.formLink.id == i ){
                                    e.id='';         
                                    e.year = '';
                                    e.itinerary_link = '';
                                    e.link_itinerary_priceless = '';

                                    e.year = this.formLink.year;
                                    e.itinerary_link = this.formLink.itinerary_link;
                                    e.itinerary_link_commercial = this.formLink.itinerary_link_commercial;
                                    e.link_itinerary_priceless = this.formLink.link_itinerary_priceless;
                                    e.lang= this.language_selected;
                                    e.package_id=this.form.package_id
                                    this.editarPackage = false;
                                    this.limpiarFormulario(this.language_selected);
                                    acum = 0;
                                    i=0;
                                }                  
                            });   
                            this.$refs['my-modal'].hide()           
                        }            
                            
                    }else{
                    
                        value = this.form.translations[this.language_selected].link_itinerary.some(clase => clase.year == this.formLink.year);
                        
                        if(value){
                                                    
                            value2 = false;

                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: "Notificación",
                                text: this.$t('packagesmanagepackagetexts.error_year')
                            })                     
                        
                        }else{
                            this.form.translations[this.language_selected].link_itinerary.push({
                                
                                year:this.formLink.year,
                                itinerary_link:this.formLink.itinerary_link,
                                itinerary_link_commercial:this.formLink.itinerary_link_commercial,
                                link_itinerary_priceless: this.formLink.link_itinerary_priceless,
                                language_id:this.language_selected,
                                package_id:this.form.package_id,
                            });
                            
                            this.$refs['my-modal'].hide()
                            this.editarPackage = false;
                            this.limpiarFormulario(this.language_selected);
                        }

                        value = false;
                    }                 
                }       
            },

            CancelarItemPackage(lang_id){
                this.editarPackage=false;
                this.limpiarFormulario(lang_id);
            },

            eliminarItemPackage(lang_id,year){
                const data = this.form.translations[lang_id].link_itinerary.filter((clase, i) => clase.year != year);
                this.form.translations[lang_id].link_itinerary = data;
                this.limpiarFormulario(lang_id);
                this.editarPackage = false;
            },

            limpiarFormulario(lang_id){
                
                this.form.translations[lang_id].link_itinerary.filter(clase => {            
                    if(clase.selected == true){
                        clase.selected = false;
                    }               
                });
                this.language_selected = lang_id
                this.editarPackage = false
                this.formLink.id= '';
                this.formLink.year = '';
                this.formLink.itinerary_link='';
                this.formLink.itinerary_link_commercial='';
                this.formLink.link_itinerary_priceless='';
            },

            selectedTable(tbl,lang_id){
                this.editarPackage = true
                this.language_selected = lang_id;
                this.$refs['my-modal'].show()
                this.form.translations[lang_id].link_itinerary.forEach((data,i) => {                
                    data.selected = false
                    if(tbl.year == data.year){
                        data.selected = true
                    
                        this.formLink.id = i;
                        this.formLink.year = data.year;
                        this.formLink.itinerary_link= data.itinerary_link;
                        this.formLink.itinerary_link_commercial= data.itinerary_link_commercial;
                        this.formLink.link_itinerary_priceless = data.link_itinerary_priceless;
                    }                
                }); 
            },

            openAddYear(lang_id){
                this.limpiarFormulario(lang_id)
                this.$refs['my-modal'].show()
                this.language_selected = lang_id;  
            },

            closeModalAddYear(){
                this.$refs['my-modal'].hide()
            }
        }
    }
</script>

<style scoped>
    .input-commerce {
        background-color: antiquewhite !important;
    }

    .input-internal {
        background-color: #f0f3f5 !important;
    }

    .active_selected {
        color: white;
        background-color: #BD0D12 !important;
        
      }

      @media only screen and (max-width: 945px) {
        .package_link {
            display: block;
            padding: 9px;
        }

        .package_table{
            max-width: 100%;
            padding:0;
        }

        .package_input {
            max-width: 100%;
        }

}

    
</style>

