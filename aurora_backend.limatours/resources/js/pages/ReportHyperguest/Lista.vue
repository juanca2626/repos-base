<template>
    <div class="container-fluid">
                   
        <div class="row">
            <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
            <div class="row col-8" style="justify-content: flex-end;align-items: end;">
                <div class="col-sm-5 row">
                    <label class="col-sm-2 col-form-label">Mes</label>
                    <v-select class="col-sm-10"
                        type="month"
                        :options="month"
                        :reduce="label => label.code"
                        label="label"
                        v-model="formulario.month"
                        autocomplete="true"></v-select>
                </div>
                <div class="col-sm-5 row">
                    <label class="col-sm-2 col-form-label">Año</label>
                    <v-select class="col-sm-10"
                      type="month"
                      :options="years"
                      :reduce="label => label.code"
                      label="label"
                      v-model="formulario.year"
                      autocomplete="true"></v-select>
                </div>

                <div class="col-sm-2">
                    
                    <button class="btn btn-success" @click="search()" 
                            style="float: right; margin-top: 35px;">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                        Buscar
                    </button>
                </div>
            </div>
            
        </div>

        <div class="card-body">
            <div class="VueTables">
            <table class="VueTables__table table table-striped table-bordered table-hover" v-if="searchs.length>0">
                <thead>
                    <tr>
                        <th colspan="8" style="border-right: 1px solid white!important;border-bottom: 1px solid white!important;">HYPERGUEST</th>
                        <th colspan="3" style="border-bottom: 1px solid white!important;">AURORA</th>
                    </tr>
                    <tr>
                        <th class="vueTable_column_actions" style="width: 2%!important;">#</th> 
                        <th class="vueTable_column_actions" style="width: 5%!important;">Reserva</th>                    
                        <th  class="vueTable_column_actions" style="width: 25%!important;">Hotel</th> 
                        <th class="vueTable_column_actions" style="width: 15%!important;">Pasajero</th>  
                        <th class="vueTable_column_actions" style="width: 8%!important;">Check In</th>
                        <th class="vueTable_column_actions" style="width: 8%!important;">Check Out</th>
                        <th class="vueTable_column_actions" style="width: 11%!important;">Importe</th> 
                        <th  class="vueTable_column_actions" style="border-right: 1px solid white!important;width: 5%!important;">Estado</th>

                        <th class="vueTable_column_actions" style="width: 5%!important;"># File</th>
                        <th class="vueTable_column_actions" style="width: 11%!important;">Importe</th> 
                        <th  class="vueTable_column_actions" style="width: 5%!important;">Estado</th>

                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(reporte, index) of searchs" :class="{ error: !reporte.file_code }"   >
                        <td style="width: 2%!important;">{{ ++index }}</td>  
                        <td style="width: 5%!important;">{{ reporte.booking_id }}</td>  
                        <td style="text-align: left;width: 25%!important;">{{ reporte.property_id }} {{ reporte.property_name }}</td> 
                        <td style="text-align: left;width: 15%!important;">{{ reporte.lead_guest_name }}</td> 
                        <td style="width: 8%!important;">{{ reporte.start_date }}</td>
                        <td style="width: 8%!important;">{{ reporte.end_date }}</td>
                        <td style="text-align: right;width: 11%!important;">USD {{ reporte.price_amount }}</td> 
                        <td style="width: 5%!important;">
                        
                        <font-awesome-icon :icon="['fas', 'check']" style="color: #5bc913;"  v-if="reporte.status=='Confirmed'" />                                               
                        <font-awesome-icon :icon="['fas', 'ban']" style="color: #dc1e1e;" v-else/>

                        </td>                                                    
                        <td style="width: 5%!important;">{{ reporte.file_code }}</td>                      
                        <td style="text-align: right;width: 11%!important;">{{ reporte.file_code ? 'USD' + reporte.price_aurora : '' }}</td>  
                        <td style="width: 5%!important;">      
                            <template v-if="reporte.file_code">
                                <font-awesome-icon :icon="['fas', 'check']" style="color: #5bc913;"  v-if="reporte.status_aurora=='1'" />                                               
                                <font-awesome-icon :icon="['fas', 'ban']" style="color: #dc1e1e;" v-if="reporte.status_aurora=='0'"/>
                            </template>
                        </td>  
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="width: 47%!important; padding-left: 10px!important; vertical-align: middle;text-align: left;" rowspan="3">
                            <button @click="confirmDelete()" class="btn btn-success" type="submit">
                                <font-awesome-icon :icon="['fas', 'dot-circle']"/>  {{$t('global.buttons.delete_records')}}
                            </button>
                            <button @click="openMail()" v-if="btnVisible==true" class="btn btn-success" type="submit">
                                <font-awesome-icon :icon="['fas', 'dot-circle']"/> {{$t('global.buttons.send_hyperguest')}}
                            </button>
                        </td>
                        <td colspan="2" style="font-weight: bold;font-style: 14px;text-align: center;background-color: #a1c6a3;width: 16%!important;">TOTAL</td>
                        <td style="font-weight: bold;font-style: 14px;text-align: right;background-color: #a1c6a3;width: 11%!important;">USD {{ totals.hyperguest }} </td>
                        <td style="width: 5%!important;"></td>
                        <td style="background-color: #a1c6a3;width: 5%!important;"></td>
                        <td style="font-weight: bold;font-style: 14px;text-align: right;background-color: #a1c6a3;width: 11%!important;" :class="{ error_inequality: inequality }" >USD {{ totals.aurora }} </td>
                        <td style="width: 5%!important;"></td>
                    </tr>
                    <tr><td colspan="10" style="height: 15px;"></td></tr>
                    <tr> 
                        <td colspan="2" style="font-weight: bold;font-style: 14px;text-align: center;background-color: #a1c6a3;width: 16%!important;">FEES ({{ fee }}%)</td>
                        <td style="font-weight: bold;font-style: 14px;text-align: right;background-color: #a1c6a3;width: 11%!important;">USD {{ fees.hyperguest }} </td>
                        <td style="width: 5%!important;"></td>
                        <td style="background-color: #a1c6a3;width: 5%!important;"></td>
                        <td style="font-weight: bold;font-style: 14px;text-align: right;background-color: #a1c6a3;width: 11%!important;" :class="{ error_inequality: inequality }">USD {{ fees.aurora }} </td>
                        <td style="width: 5%!important;"></td>
                    </tr>
                </tfoot>    
                
            </table>
            </div>
        </div>
        
        <b-modal title="Eliminar Registros" centered ref="my-modal" size="sm">
            <p class="text-center">
                Se eliminaran todos los registros del {{formulario.month}}/{{formulario.year}}
                <br /><br />
                ¿ {{$t('global.message_delete')}} ?
            </p>

            <div slot="modal-footer">
                <button @click="deleteAll()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal title="Detalle de Reservas no Asociadas" centered ref="my-modal-mail" size="xl">

            <div class="container">
                <div class="row d-flex align-items-center">
                   
                    <div class="col-10 mb-3">
                        <label for="destinatarioEmail" class="form-label">Destinatario:</label>
                        <multiselect :clear-on-select="false"
                                        :close-on-select="false"
                                        :multiple="true"
                                        :options="emails"
                                        placeholder="buscar"
                                        :preserve-search="true"
                                        tag-placeholder="seleccione"
                                        :taggable="true"
                                        @tag="addTag"
                                        label="label"
                                        ref="multiselect"
                                        track-by="code"
                                        v-model="sendForm.destinatarioMail">
                        </multiselect>
                    </div>
                    <div class="col-2 position-relative" style="left: 20px;">
                        <button  @click="openAddEmail()" class="btn btn-success">Administrar Email</button>
                    </div>
                </div>
                
               
                <div class="mb-3">
                    <label for="observaciones" class="form-label">Comentario:</label>
                    <textarea class="form-control"  id="observaciones" rows="3" v-model="sendForm.commentMail"></textarea>
                </div>
                
                <table class="VueTables__table table table-striped table-bordered table-hover" v-if="sendForm.listMail.length>0">
                    <thead>                    
                        <tr>
                            <th class="vueTable_column_actions" style="width: 2%!important;">#</th> 
                            <th class="vueTable_column_actions" style="width: 5%!important;">Reserva</th>                    
                            <th  class="vueTable_column_actions" style="width: 25%!important;">Hotel</th> 
                            <th class="vueTable_column_actions" style="width: 15%!important;">Pasajero</th>  
                            <th class="vueTable_column_actions" style="width: 8%!important;">Check In</th>
                            <th class="vueTable_column_actions" style="width: 8%!important;">Check Out</th>
                            <th class="vueTable_column_actions" style="width: 11%!important;">Importe</th> 
                            
                        
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(reporte, index) of sendForm.listMail" :class="{ error: !reporte.file_code }"   >
                            <td style="width: 2%!important;">{{ ++index }}</td>  
                            <td style="width: 5%!important;">{{ reporte.booking_id }}</td>  
                            <td style="text-align: left;width: 25%!important;">{{ reporte.property_id }} {{ reporte.property_name }}</td> 
                            <td style="text-align: left;width: 15%!important;">{{ reporte.lead_guest_name }}</td> 
                            <td style="width: 8%!important;">{{ reporte.start_date }}</td>
                            <td style="width: 8%!important;">{{ reporte.end_date }}</td>
                            <td style="text-align: right;width: 11%!important;">USD {{ reporte.price_amount }}</td> 
                                                                            
                        
                        </tr>
                    </tbody>
                </table>

            </div>
                <div slot="modal-footer" style="padding-right: 13px;">
                    <button @click="sendMail()" class="btn btn-success">{{$t('global.buttons.send_mail')}}</button>
                    <button @click="hideModalMail()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
        </b-modal>

        <b-modal title="Crear Correo" centered ref="my-modal-add-email" size="lg">
                      
            <div class="row">               
                <div class="col-12" style="text-align: end;">
                    <button @click="openModalCorreo()" class="btn btn-success pl-5 pr-5">Agregar</button>
                </div>
            </div>
            

            <div class="mt-3">
                <table class="VueTables__table table table-striped table-bordered table-hover">
                    <thead>                    
                        <tr>                        
                            <th class="" style="width: 20%">Acción</th>    
                            <th class="" style="width: 80%" >Email</th>           
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) of correos" style="height: 13%;" @click="selectedRow(item)">
                            <td>                                
                                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                                    <template slot="button-content">
                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                    </template>
                                    
                                    <b-dropdown-item-button class="m-0 p-0" @click="openModalCorreo()">
                                        <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                                        {{$t('global.buttons.edit')}}
                                    </b-dropdown-item-button>
                                    
                                    
                                    <b-dropdown-item-button @click.stop="deleteItem(i)" class="m-0 p-0">
                                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                        {{$t('global.buttons.delete')}}
                                    </b-dropdown-item-button>
                                </b-dropdown>                                
                            </td>
                            <td style="text-align: center;width: 80%;">{{ item.label }}</td>  
                        
                        </tr>
                    </tbody>
                </table>
            </div>

            <div slot="modal-footer">        
                
                <button @click="saveEmail()" class="btn btn-success">{{ $t('global.buttons.save')}}</button>
                <button @click="hideModalAddMail()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                
            </div>
        </b-modal>

        <b-modal title="Agregar Correo" centered ref="my-modal-add-correo" size="md">
           
            <label class="col-sm-10 pl-0 col-form-label">Email:</label>
            
            <div class="row">
                <div class="col-sm-12">
                    <input type="email"  class="form-control" required v-model="formEmail.email" 
                    placeholder="Ingrese Email"/>                     
                </div>
                
            </div>

            <div slot="modal-footer">        
                
                <button @click="addEmail()" class="btn btn-success">{{formEmail.estado == 1 ? 'Insertar' : $t('global.buttons.edit')}}</button>
                <button @click="closeModalCorreo()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                
            </div>
            
        </b-modal>
       
    </div>
    
</template>

<script>
import { API } from './../../api' 
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import Multiselect from 'vue-multiselect'
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    props: [],
    components: { 
        vSelect,
        Loading,
        Multiselect,
    },
    data: () => {
        return {
            id: '',
            propertyId:'',
            propertyName:'',
            propertyCountry:'',
            propertyCity:'',
            leadGuestName:'',
            adults:'',
            children:'',

            infants:'',
            startDate:'',
            endDate:'',
            priceAmount:'',
            priceCurrency:'',
            status:'',
            partnerId:'',

            partnerName:'',
            agencyReference:'',
            bookingDate:'',
            cancellationDeadline:'',
            total:0,
            reportes:[],
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
                id: '',
                month:'',
                year:'',
            },   
            years:[],
            searchs:[],
            totals:[],
            fees: [],            
            fee: '',
            loading: false,

            sendForm:{
                destinatarioMail: [],
                commentMail:'',
                listMail:[],
            },
            alerta:'',
            btnVisible:false,
            inequality:false,
            emails:[],
            notification:false,
            formEmail:{
                id:'',
                email:'',
                estado:1,
                codigo:'',
            },
            correos:[],
            sms:"",
        }
    },
    computed: {
        
        
    },
    mounted: function () {         
        this.comboAno();
        this.fechaActual();
       
        this.search()
        this.getEmail()
        
    },
    methods: {
      
        comboAno(){                     
                       
           let n = (new Date()).getFullYear();
           this.years = []
           for(let i = n; i >= n - 1; i--) {
               
            this.years.push({code: i, label: i},)
           }
        },

        fechaActual(){
            let fecha = (new Date()).getMonth();
            this.formulario.month = '0'+(fecha + 1)
            this.formulario.month = this.formulario.month.slice(-2);
            let anio =(new Date()).getFullYear();
            this.formulario.year = anio;
            console.log(this.formulario.month);
        },

        async search(){ 
            this.btnVisible = false
            this.inequality = false
            this.loading = true
            await API({
                method: 'get',
                url: 'report-hyperguest/search',
                
                params: {
                    month:this.formulario.month,
                    year:this.formulario.year
                    }
                
            }).then((result) => {
               this.searchs = result.data.results
               this.totals = result.data.totals
               this.fees = result.data.fees
               this.fee = result.data.fee 
               this.inequality = result.data.inequality
               this.loading = false
               this.formulario.id = result.data.reportHyperguest_id                
               this.searchs.forEach(e => {
                if(e.file_code==''){
                    this.btnVisible = true
                }
            });

            }).catch((error) => { 
                this.loading = false 
                this.searchs = []
                this.totals = []
                this.fees = []
                this.fee = 0                
            })

          



        },

        confirmDelete(){
            this.$refs['my-modal'].show()             
        },
        deleteAll(){
    
            this.loading = true
            API({
                method: 'post',
                url: 'report-hyperguest/delete-all',
                data: {
                    id: this.formulario.id
                }

            }).then((result) => {
                this.searchs = []
                this.totals = []
                this.fees = []
                this.fee = 0
                this.loading = false    
                this.$refs['my-modal'].hide()                
            }).catch((error) => { 
                this.loading = false 
                this.$refs['my-modal'].hide()                
            })
        },        
        hideModal() {
            this.$refs['my-modal'].hide()
            this.$refs['my-modal-uses'].hide()
        },

        hideModalMail() {
            this.$refs['my-modal-mail'].hide()
            this.limpiarData()
        },

        openMail(){

            this.limpiarData()
            this.sendForm.commentMail = 'Por favor revisar estas reservas que no las tenemos asociadas en nuestro sistema.';
            this.searchs.forEach(e => {
                if(e.file_code==''){
                    this.sendForm.listMail.push(e);
                }
            });           
            this.$refs['my-modal-mail'].show()
        },

        sendMail(){
            
            if(this.sendForm.commentMail == '' || this.sendForm.destinatarioMail.length == 0){
                
                this.$notify({
                    group: 'main',
                    type: 'success',
                    title: this.$t('hyperguest.notification'),
                    text: 'Debe llenar todos los campos',
                })   
            }else{
                this.loading = true
                API({
                    method: 'post',
                    url: 'report-hyperguest/send-mail',
                    data: {
                        dataMail: this.sendForm
                    }

                }).then((result) => {
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('hyperguest.notification'),
                        text: this.$t('hyperguest.notification_mensaje'),
                    })   
                    this.$refs['my-modal.mail'].hide()    
                    this.loading = false
                   
                }).catch((error) => {                 
                    this.$refs['my-modal-mail'].hide() 
                                   
                    this.loading = false              
                });
            }           
        },

        limpiarData(){           
            
            this.sendForm.commentMail='';
            this.sendForm.listMail = [];
            this.notification = false;
        },

       async getEmail(){           

           await API.get('report-hyperguest/list-email').then((result) => {
                this.emails = result.data.result
                this.correos = [... this.emails]
                this.formEmail.id = result.data.id
                this.sendForm.destinatarioMail = this.emails;
            })
        },

        addTag (newTag) {
            const tag = {
            label: newTag,
            code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
            }
            this.emails.push(tag)
           
        },

        openAddEmail(){
            this.formEmail.estado = 1;
            this.formEmail.email = "";
            this.correos.forEach(e => {
                if(e.selected==true){
                    e.selected = false
                }
            });
            this.$refs['my-modal-add-email'].show()
        },

        hideModalAddMail(){
            this.$refs['my-modal-add-email'].hide()
        },

        saveEmail(){
            if(this.correos.length == 0){
                this.$notify({
                    group: 'main',
                    type: 'success',
                    title: this.$t('hyperguest.notification'),
                    text: 'Debe haber al menos un email',
                }) 
                
            }else{
                this.loading = true
                this.formEmail.emails = this.correos
                API({
                    method: 'put',
                    url: 'report-hyperguest/add-mail/'+ this.formEmail.id,
                    data:  this.formEmail                    

                }).then((result) => {
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('hyperguest.notification'),
                        text: "El proceso se realizo correctamente!!",
                    })   
                    this.getEmail();
                    
                    this.$refs['my-modal-add-email'].hide()    
                    this.loading = false
                    
                   
                }).catch((error) => {                 
                    this.$refs['my-modal-add-email'].hide() 
                                   
                    this.loading = false              
                });
            }
            
        },

        limpiarEmail(){
            this.formEmail.email = '';         
            
        },

        selectedRow(row){
            this.formEmail.estado = 2;
            this.correos.filter((item, i) => {
                item.selected=false;
                if(item.code == row.code){
                    this.formEmail.email = item.label;
                    item.selected=true;
                    this.formEmail.codigo = item.code;
                }
            });
            
        },

        addEmail(){

            if(this.formEmail.estado == 1){
                if(this.formEmail.email == ''){                   
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('hyperguest.notification'),
                        text: 'El campo Email es requerido !!!!',
                    }) 
                    return
                }
                let validar = this.correos.some(e => e.label.toLowerCase().trim() === this.formEmail.email.toLowerCase().trim());
                if(validar){                  
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('hyperguest.notification'),
                        text: 'El email ya existe !!!!',
                    }) 
                    
                }else{
                    let items = 0
                    if(this.correos.length>0){
                        this.correos.forEach(e => {
                            items = e.code
                        });
                    }else{
                        items = 0
                    }
                    
                    let simbolo = this.formEmail.email.includes("@");
                    if(simbolo){
                        this.correos.push({code:items + 1,label:this.formEmail.email.toLowerCase(), selected:false})
                        this.$refs['my-modal-add-correo'].hide()
                        this.limpiarEmail()
                    }else{                       
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('hyperguest.notification'),
                            text: 'Debe incluir un signo de "@" en el correo electrónico !!!!',
                        })
                    }                              
                   
                }
               
            }else{
                if(this.formEmail.email == ''){                    
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('hyperguest.notification'),
                        text: 'El campo Email es requerio !!!!',
                    })
                    return
                }
                
                let simbolo = this.formEmail.email.includes("@");
                if(!simbolo){
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('hyperguest.notification'),
                        text: 'Debe incluir un signo de "@" en el correo electrónico !!!!',
                    })
                    return
                }     

                let validar = this.correos.some(e => e.label.toLowerCase().trim() === this.formEmail.email.toLowerCase().trim()); 
                let valor = this.correos.find((element) =>element.label.toLowerCase().trim() === this.formEmail.email.toLowerCase().trim()); 
                console.log(valor)
                if(validar && valor.code == this.formEmail.codigo){

                    this.correos.forEach(e => {
                        if(e.selected == true ){                        
                            e.label = this.formEmail.email                            
                        }                        
                    });
                    this.$refs['my-modal-add-correo'].hide()
                    this.limpiarEmail()

                }else if(validar && valor.code != this.formEmail.codigo){

                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('hyperguest.notification'),
                        text: 'El email ya existe !!!!',
                    })
                    
                }else{
                    this.correos.forEach(e => {
                        if(e.selected == true ){                        
                            e.label = this.formEmail.email                            
                        }                        
                    });
                    this.$refs['my-modal-add-correo'].hide()
                }
                        
            }
            
        },

        deleteItem(e){
            
            this.formEmail.email = '';
            this.correos.splice(e,1);
            this.formEmail.estado = 1;
            this.limpiarEmail()
        },

        openModalCorreo(){
            this.formEmail.estado = 1;
            this.limpiarEmail()
            this.$refs['my-modal-add-correo'].show()
        },

        closeModalCorreo(){
            this.$refs['my-modal-add-correo'].hide()
        },
    }
}
</script>

<style scoped >

.error_inequality{
    color: red!important;
}
.table th, .table td {
     padding:0px!important;
}
.vueTable_column_actions {
    width: initial !important;
}

.error {  
    color: red;
    font-weight: bold;
}

table {
	max-width:100%;
	table-layout:fixed;
	margin:auto;
}
th, td {
	padding:0px!important;
	border:1px solid #000;
}
thead, tfoot {
	background:#f9f9f9;
	display:table;
	width:100%; 
}
tbody {
	height:300px;
	overflow:auto;
	overflow-x:hidden;
	display:block; 
}
tbody tr {
	display:table;
	width:100%;
	table-layout:fixed;
}
.btnEliminar{
    margin: auto;
    width: 30px;
    padding: 5px 10px;
    border-radius: 4px;
    background-color: red;
    cursor: pointer;
}


</style>
