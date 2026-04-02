<template>
    <div class="container">
      <div class="page-hoja-master">
        <div class="contenedor">
          <h1 class="titulo">{{ translations.label.title }}</h1>
        </div>
        <div class="contenedor">
          <div class="conectados">
            <div class="conectados-descripcion">{{ translations.label.connected }}</div>
            <div class="conectados-avatares">
              <div class="avatar-content" data-toggle="tooltip" data-placement="top"
                   v-for="user in users_in_room" :title="user.name">
                <img v-if="user.usertype==='registered'" :src="baseURL + 'images/users/' + user.photo"
                     onerror="this.src = baseURL + 'images/users/user_default.png'">
                <img :src="baseURL + 'images/anonimo.jpg'" v-else>
              </div>
            </div>
          </div>
        </div>
        <div class="contenedor">
          <h2 class="subtitulo">{{ master_sheet.name }}<span>{{ translations.label.by }} {{ master_sheet.user.name }}</span></h2>
        </div>
        <div class="contenedor">
          <div class="tour">
            <div class="tour-info">
              <div class="tour-info-codigo">HM-{{ master_sheet_id_decode }}</div>
              <div class="tour-info-descripcion salida">{{ translations.label.departure }}<span>{{ master_sheet.date_out | reformatDate }}</span></div>
              <div class="tour-info-descripcion pax">{{ translations.label.paxes }}<span>{{ master_sheet.paxes }}</span></div>
              <div class="tour-info-descripcion leader">{{ translations.label.leaders }}<span>{{ master_sheet.leader }}</span></div>
              <div class="tour-info-descripcion leader" v-if="master_sheet.includes_scort!==null">{{ translations.label.scort }}<span>{{ master_sheet.includes_scort }}</span></div>
              <div class="tour-info-modal" data-toggle="modal" data-target="#modal-info">
                <button :disabled="loading" v-if="!(user_invited)" :class="'permission_'+type_permission"
                        @click="toggleModal( 0, 'edit', { translations: translations, master_sheet_selected : master_sheet } )"
                        data-toggle="modal" data-target="#modal-info">
                  <i class="icon-edit"></i><span>{{ translations.label.info }}</span>
                </button>
              </div>
            </div>
            <button id="modal_force_1" data-toggle="modal" data-target="#modal-imports" @click="forceModal()"></button>
            <div class="tour-opciones">
              <div class="descargar dropdown dropdown-importar" data-toggle="tooltip" data-placement="top" :title="translations.label.import" v-if="!(user_invited)">
                <button class="dropdown-toggle dropdown-nota-button icono icon-download" :class="'permission_'+type_permission" data-toggle="dropdown"></button>
                <div class="dropdown-menu dropdown-nota-mensaje">
                  <div class="dropdown-content" :class="'permission_'+type_permission">
                    <a href="javascript:;"
                       @click="toggleModal( 1, 'import', { translations: translations, master_sheet_id : master_sheet.id, tab : 'file' } )">
                      {{ translations.label.import }} {{ translations.label.file }}
                    </a>
                    <a href="javascript:;"
                       @click="toggleModal( 1, 'import', { translations: translations, master_sheet_id : master_sheet.id, tab : 'programming' } )">
                      {{ translations.label.import }} {{ translations.label.programming }}
                    </a>
                    <a href="javascript:;"
                       @click="toggleModal( 1, 'import', { translations: translations, master_sheet_id : master_sheet.id, tab : 'master_sheet' } )">
                      {{ translations.label.import }} {{ translations.label.title }}
                    </a>
                  </div>
                </div>
              </div>
              <button class="descargar" v-if="!(user_invited) && type_permission===0"
                      @click="toggleModal( 0, 'remover', { translations: translations, master_sheet_id : master_sheet.id, action_after : 'reload_list' } )"
                      data-toggle="modal" data-target="#modal-borrar-itinerario" :disabled="loading">
                <span class="icono icon-trash-all" data-toggle="tooltip" data-placement="top" :title="translations.label.delete_itinerary"></span>
              </button>
              <button class="descargar" v-if="!(user_invited)"
                      @click="toggleModal( 0, 'edit', { translations: translations, action_after: 'go_edit' } )"
                      data-toggle="modal" data-target="#modal-info" :disabled="loading">
                <span class="icono icon-new-blank" data-toggle="tooltip" data-placement="top" :title="translations.label.new + ' ' + translations.label.title"></span>
              </button>
              <button class="descargar" v-if="!(user_invited)"
                      data-toggle="tooltip" data-placement="top" :title="translations.label.all_sheets" :disabled="loading">
                <a class="icono icon-archive" href="/master-sheets"></a>
              </button>
              <button class="descargar" v-if="!(user_invited)" :class="'permission_'+type_permission"
                      @click="toggleModal( 0, 'share', { translations: translations, master_sheet_id : master_sheet.id } )"
                      data-toggle="modal" data-target="#modal-compartir" :disabled="loading">
                <div class="descargar-tooltip" data-toggle="tooltip" data-placement="top" :title="translations.label.share">
                  <span class="icono icon-share-2"></span><span class="numero">{{ master_sheet.users_count }}</span>
                </div>
              </button>
              <button class="descargar" v-if="!(user_invited)"
                      @click="toggleModal( 0, 'note', { translations: translations, master_sheet_id : master_sheet.id, type_permission : type_permission } )" data-toggle="modal" data-target="#modal-notas">
                <div class="descargar-tooltip" data-toggle="tooltip" data-placement="top" :title="translations.label.notes" :disabled="loading">
                  <span class="icono icon-file-text"></span>
                  <span class="numero">{{ total_notes }}</span>
                </div>
              </button>
              <button class="descargar mensajes mesajes-nuevo" v-if="!(user_invited)"
                      @click="toggleModal( 0, 'message', { translations: translations, master_sheet_id : master_sheet.id } )"
                      data-toggle="modal" data-target="#modal-mensajes" :disabled="loading">
                <div class="descargar-tooltip" data-toggle="tooltip" data-placement="top" :title="translations.label.messages">
                  <span class="icono icon-mail"></span>
                  <div class="numero" v-if="$store.state.rooms.room.data_messages">
                    {{ $store.state.rooms.room.data_messages.length }}
                  </div>
                  <div class="numero" v-else>
                    {{ master_sheet.messages_count }}
                  </div>
                </div>
              </button>
              <button class="button-red" v-if="!(user_invited)" :class="'permission_'+type_permission"
                      @click="toggleModal( 0, 'day', { translations: translations, master_sheet_id : master_sheet.id } )"
                      data-toggle="modal" data-target="#modal-editar-dia" :disabled="loading">
                <i class="icon-plus-circle"></i><span>{{ translations.label.day }}</span>
              </button>
<!--              <button class="button-red">Guardar</button>-->
            </div>
          </div>
        </div>
        <div class="contenedor contenedor-cards">
          <div class="card" v-for="ms_day in master_sheet_days">
            <div class="card-header-master row-free" v-if="ms_day.row_type==='free'">
              <div class="card-header-dia" :class="{'min-w-110':ms_day.number_out>ms_day.number_in}">{{ translations.label.day }} {{ ms_day.number_in }}
                <span v-if="ms_day.number_out>ms_day.number_in"> {{ translations.label.to }} {{ ms_day.number_out }}</span>
              </div>
              <div class="card-header-ruta">
                <div class="descripcion">{{ translations.label.free }} ...</div>
                <div class="info">{{ ms_day.date_in | reformatDate }}
                  <span v-if="ms_day.number_out>ms_day.number_in"> - {{ ms_day.date_out | reformatDate }}</span>
                </div>
              </div>
            </div>

            <div :class="'card-header-master row-'+ms_day.row_class"
                 v-if="ms_day.row_type==='regular'">
              <div class="card-header-dia">{{ translations.label.day }} {{ ms_day.number }}</div>
              <div class="card-header-ruta">
                <div class="descripcion">{{ ms_day.name }}
                  <span v-if="ms_day.destinations!==null && ms_day.name!==null">/</span> {{ ms_day.destinations }}
                </div>
                <div class="info">{{ ms_day.date_in | reformatDate }}</div>
              </div>
              <div class="card-header-opciones card-header-flecha">
                <div class="content-icon" v-if="!(user_invited)">
                  <button class="icon icon-arrow-up" :class="'permission_'+type_permission" :disabled="loading" @click="move_day(ms_day,'up')"></button>
                  <button class="icon icon-arrow-down" :class="'permission_'+type_permission" :disabled="loading" @click="move_day(ms_day,'down')" style="margin-left: 15px;"></button>
                </div>
              </div>
              <div class="card-header-opciones">
                <div class="content-icon" v-if="!(user_invited)">
                  <button class="icon icon-trash-2" :class="'permission_'+type_permission"
                          @click="toggleModal( 0, 'day-remover', { translations: translations, master_sheet_day_id : ms_day.id, day_number : ms_day.number } )"
                          data-toggle="modal" data-target="#modal-borrar-dia"></button>
                  <button class="icon icon-edit" :class="'permission_'+type_permission"
                      @click="toggleModal( 0, 'day', { translations: translations, master_sheet_id : master_sheet.id, master_sheet_day_id : ms_day.id } )"
                      data-toggle="modal" data-target="#modal-editar-dia" style="margin-left: 15px;"></button>
                </div>
              </div>
            </div>
            <div class="card-body-master" v-if="ms_day.row_type==='regular'">
              <div class="card-body-itinerario" v-for="service in ms_day.services" v-if="ms_day.services.length>0">
                <div class="card-body-itinerario-nota">
                  <div class="dropdown dropdown-nota" v-if="service.comment_status">
                    <button class="dropdown-toggle dropdown-nota-button icon-file-text" data-toggle="dropdown"></button>
                    <form class="dropdown-menu dropdown-nota-mensaje">
                      <div class="dropdown-content">
                        <h3 class="dropdown-titulo">{{ translations.label.day }} {{ ms_day.number }} - {{ ms_day.destinations }}</h3>
                        <div class="dropdown-info">
                          <div class="hora">{{ service.check_in | formatHours }}</div>
                          <div class="codigo codigo-azul" v-if="service.service_code_stela!=='' && service.service_code_stela!==null">
                            {{ service.service_code_stela }}
                          </div>
                          <div class="codigo" v-else>{{ max_width( translations.label.without_code, 7 ) | upper }}</div>
                          <div class="separacion">-</div>
                          <div class="lugar" v-if="lang === 'es'">{{ service.description_ES | filter_text }}</div>
                          <div class="lugar" v-if="lang === 'en'">{{ service.description_EN | filter_text }}</div>
                          <div class="lugar" v-if="lang === 'pt'">{{ service.description_PT | filter_text }}</div>
                          <div class="lugar" v-if="lang === 'it'">{{ service.description_IT | filter_text }}</div>
                          <button type="button" class="editar icon-edit fa-2x" v-if="!(user_invited) && type_permission!==1" @click="service.show_edit=!(service.show_edit)" :disabled="loading"></button>
                          <button type="button" class="borrar icon-trash-2 fa-2x" v-if="!(user_invited) && type_permission!==1" @click="remove_note(service)" :disabled="loading"></button>
                        </div>
                        <div class="dropdown-input">
                          <textarea class="dropdown-textarea" v-model="service.comment" rows="7" :disabled="!(service.show_edit)">{{ service.comment }}</textarea>
                        </div>

                        <button style="float: right; width: 100%; padding: 10px;" v-show="service.show_edit">
                          <i class="icon-paperclip"></i>
                          <span>
                              <input type="file" :ref="'list_file_'+service.id" :id="'list_file_'+service.id">
                          </span>
                        </button>

                        <div class="mensaje-card-adjunto-editando" v-show="service.show_edit">
                          <button class="button-red" style="float: right" type="button" @click="will_update_note(service)" :disabled="loading">{{translations.label.update}}</button>
                          <button class="button-gris" style="float: right; margin-right: 10px" @click="service.show_edit=false" type="button" :disabled="loading">{{ translations.label.cancel }}</button>
                        </div>

                        <div class="dropdown-adjuntar" v-if="service.attached!=='' && service.attached!==null && !(service.show_edit)">
                          <button>
                            <i class="icon-paperclip"></i>
                            <a target="_blank" :href="baseURL + 'uploads/' + service.attached">{{translations.label.see_attached}}</a>
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
                <div class="card-body-itinerario-hora" v-if="(service.check_in!==null && service.check_in!=='')">
                  {{ service.check_in | formatHours}}
                </div>
                <div class="card-body-itinerario-hora" v-else>
                  -
                </div>
                <div class="card-body-itinerario-codigo card-body-itinerario-codigo-azul"
                     v-if="(service.service_code_stela!==null && service.service_code_stela!=='')">
                    {{ service.service_code_stela }}
                </div>
                <div class="card-body-itinerario-codigo"
                     v-else>
                    {{ max_width( translations.label.without_code, 7 ) | upper }}
                </div>
                <div class="card-body-itinerario-separacion">-</div>

                <div class="card-body-itinerario-info" v-if="lang === 'es'">{{ service.description_ES | filter_text }}</div>
                <div class="card-body-itinerario-info" v-if="lang === 'en'">{{ service.description_EN | filter_text }}</div>
                <div class="card-body-itinerario-info" v-if="lang === 'pt'">{{ service.description_PT | filter_text }}</div>
                <div class="card-body-itinerario-info" v-if="lang === 'it'">{{ service.description_IT | filter_text }}</div>
              </div>
              <div class="card-body-general card-body-alternativa" v-if="ms_day.services.length===0">
                <div class="title" style="color: #a7a7a7;">{{ translations.label.without_services }} ...</div>
              </div>
              <div class="card-body-separador" v-if="ms_day.services.length>0"></div>
              <div class="card-body-general card-body-acomodacion" v-for="hotel in ms_day.services"
                   v-if="hotel.status===1 && hotel.type_service==='hotel'">
                <div class="title">{{ translations.label.accommodation }}</div>
                <div class="web" v-if="lang === 'es'">{{ hotel.description_ES | filter_text }}</div>
                <div class="web" v-if="lang === 'en'">{{ hotel.description_EN | filter_text }}</div>
                <div class="web" v-if="lang === 'pt'">{{ hotel.description_PT | filter_text }}</div>
                <div class="web" v-if="lang === 'it'">{{ hotel.description_IT | filter_text }}</div>
                <div class="info-content">
                  <div class="info-general checkin"><span>{{ translations.label.check_in }}</span><span>{{ hotel.check_in }} {{translations.label.hrs}}</span></div>
                  <div class="info-general checkout"><span>{{ translations.label.check_out }}</span><span>{{ hotel.check_out }} {{translations.label.hrs}}</span></div>
                  <div class="info-general incluye"><span>{{translations.label.includes_breakfast}}</span></div>
                </div>
              </div>
              <div class="card-body-general card-body-alternativa">
                <div class="title" v-if="ms_day.services.length>0">Alternativa (s)</div>
                <div v-for="hotel in ms_day.services" v-if="hotel.status===0 && hotel.type_service==='hotel'">
                  <div class="web" v-if="lang === 'es'">{{ hotel.description_ES | filter_text }}</div>
                  <div class="web" v-if="lang === 'en'">{{ hotel.description_EN | filter_text }}</div>
                  <div class="web" v-if="lang === 'pt'">{{ hotel.description_PT | filter_text }}</div>
                  <div class="web" v-if="lang === 'it'">{{ hotel.description_IT | filter_text }}</div>
                  <div class="info-content">
                    <div class="info-general checkin"><span>{{ translations.label.check_in }}</span><span>{{ hotel.check_in }} hrs</span></div>
                    <div class="info-general checkout"><span>{{ translations.label.check_out }}</span><span>{{ hotel.check_out }} hrs</span></div>
                    <div class="info-general incluye"><span>{{translations.label.includes_breakfast}}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <component ref="template" v-bind:is="modal" v-bind:data="dataModal"></component>

    </div>
</template>
<script>
    // Node socket.io
    import store from "../../store"
    // Node socket.io

    export default {
        props: ['translations','master_sheet_id'],
        store,
        data: () => {
            return {
                lang: '',
                loading: false,
                baseExternalURL: window.baseExternalURL,
                baseSocketURL: window.baseSocketURL,
                baseURL: window.baseURL,
                master_sheet_id_decode:'',
                master_sheet: {
                    user : {}
                },
                modal: '',
                dataModal: {},
                total_sockets_in_room: 0,
                total_notes: 0,
                code: "",
                user_id: "",
                user_invited: false,
                time_for_guest: 0,
                type_permission: 1, // lectura
            }
        },
        computed: {
            total_sockets(){
                return this.$store.state.live.countLiveUsers
            },
            master_sheet_days(){
                return this.$store.state.rooms.room.data_days
            },
            users_in_room(){
                return this.$store.state.rooms.room.users
            },
            messages_alert(){
                return this.$store.state.notification
            },
            notifications(){
                return this.$store.state.rooms.room.notifications
            },
        },
        watch:{
            notifications(notifications){
                if( notifications !== undefined ){
                    let notification = notifications[notifications.length-1]
                    this.$toast.success( notification.user + ': ' + notification.message, {
                        position: 'top-right'
                    })
                }
            },
            messages_alert(message_obj){
                if( message_obj !== undefined ){
                    console.log(message_obj)
                    this.$toast.success(message_obj.message, {
                        position: 'top-right'
                    })
                }
            },
            users_in_room(){ // Para botarlo al usuario invitado si no hay moderador
                // if( this.user_invited && this.$store.state.rooms.room.users !== undefined ){
                //     setTimeout( ()=>{
                //         let moderator = 0
                //         this.$store.state.rooms.room.users.forEach(u=>{
                //             if( u.usertype === 'registered' ){
                //                 moderator++
                //             }
                //         })
                //         if( moderator === 0 ){
                //             window.location = '/error?type=expired&lang='+this.lang+'&redirect=' + window.location.href
                //         } else {
                //             this.time_for_guest = 5000
                //         }
                //     }, this.time_for_guest )
                // }
            }
        },
        mounted: function() {

            this.lang = localStorage.getItem('lang')
            this.code = localStorage.getItem('code')
            this.user_id = parseInt( localStorage.getItem('user_id') )
            this.user_invited = ( this.code === 'guest' )

            try {
                this.master_sheet_id_decode = atob(this.master_sheet_id)
            } catch(e){
                window.location = '/error'
            }

            this.search()

            // Node socket.io
            axios.post(
                baseSocketURL + 'api/login',
                {
                    code : (this.user_invited) ? localStorage.getItem('code_guest') : localStorage.getItem('code'),
                    photo : localStorage.getItem('photo'),
                    name : localStorage.getItem('name'),
                    usertype : (this.user_invited) ? 'invited' : 'registered'
                }
            )
                .then((result) => {
                    this.$store.state.auth.token = result.data.token
                    if(!Vue.prototype.$socket){
                        require("../../plugins/socket-io")
                    }
                    this.$socket.connect()
                    this.$store.commit("rooms/set_room", {id:this.master_sheet_id_decode}, {root:true})
                    this.$socket.emit('rooms/join', this.master_sheet_id_decode)
                    this.total_sockets_in_room = this.$store.state.rooms.countUsersForLive[this.master_sheet_id_decode]
                    console.log(this.$store.state.rooms.room)
                })
                .catch((e) => {
                    console.log(e)
                })

            // Node socket.io

            this.search_days()
            this.get_total_notes()

        },
        methods: {
            send_notification(message_){
                this.$socket.emit('rooms/notification', {room: this.master_sheet_id_decode, message:message_})
            },
            get_total_notes(){

                axios.get(
                    baseExternalURL + 'api/master_sheet/'+this.master_sheet_id_decode+'/days/services/comments/total'
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.total_notes = result.data.data
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            will_update_note( service ){
                let el = document.getElementById('list_file_'+service.id)
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
                        if(result.data.success)
                        {
                            service.attached = result.data.name
                            this.update_note(service)
                        }
                        else
                        {
                            this.$toast.error(result.data.content, {
                                // override the global option
                                position: 'top-right'
                            })
                            this.loading = false
                        }
                    })
                        .catch((e) => {
                            this.loading = false
                            console.log(e)
                        })
                } else {
                    this.update_note(service)
                }
            },
            update_note(service){

                this.loading = true

                axios.put(
                    baseExternalURL + 'api/master_sheet/day/service/'+service.id+'/comment',
                    service
                )
                    .then((result) => {
                        if(result.data.success){
                            service.show_edit = false
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.get_total_notes()
                        } else {
                            this.$toast.error("Error", {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })

                this.loading = false
            },
            remove_note(service){
                service.comment_status = false
                this.update_note( service )
            },
            move_day(day, direction){

                console.log(day)

                if( this.type_permission===1 ){
                    this.$toast.warning(this.translations.messages.permissions, {
                        position: 'top-right'
                    })
                    return
                }

                let symbol_ = '▼'
                if( direction === 'up' ){
                    symbol_ = '▲'
                }
                let message_ = this.translations.messages.day_has_moved + ' ' + day.number + ' ' + symbol_

                this.loading = true
                axios.put(
                    baseExternalURL + 'api/master_sheet/'+this.master_sheet_id_decode+'/day/'+day.id+'/day',
                    { direction : direction }
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.search_days(true)
                            this.send_notification(message_)
                        } else{
                            console.log(result.data)
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            },
            search_days(socket=false){
                this.loading = true
                axios.get(
                    baseExternalURL + 'api/master_sheet/'+this.master_sheet_id_decode+'/days'
                )
                .then((result) => {
                    this.loading = false
                    this.$store.commit("rooms/set_data", ['data_days', result.data.data], {root:true})
                    if(socket){
                        this.$socket.emit('rooms/refresh', this.master_sheet_id_decode, 'data_days', result.data.data)
                    }
                })
                .catch((e) => {
                    this.loading = false
                    console.log(e)
                })
            },
            search(){
                this.loading = true

                axios.get(
                    baseExternalURL + 'api/master_sheet/'+this.master_sheet_id_decode
                )
                    .then((result) => {
                        this.loading = false
                        if( result.data.data === null ){
                            window.location = '/error'
                        }
                        this.master_sheet = result.data.data

                        if( this.master_sheet.user_id === this.user_id ){
                            this.type_permission = 0
                        } else {
                            this.master_sheet.users.forEach(ms_u=>{
                                if( ms_u.user_id === this.user_id ){
                                    this.type_permission = ms_u.type_permission
                                }
                            })
                        }

                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            toggleModal(index, _modal, _data) {
                console.log(_data)
                if( this.type_permission===1 ){
                    if( (_modal === 'edit' && _data.master_sheet_selected) || _modal === 'import' ||
                          _modal === 'share' || _modal === 'day' || _modal === 'day-remover'){
                          this.$toast.warning(this.translations.messages.permissions, {
                              position: 'top-right'
                          })
                          return
                    }
                }

                this.index = index
                this.dataModal = _data
                this.modal = 'master-sheet-modal-' + _modal

                let vm = this
                if( this.index === 0 ){
                  setTimeout(function() {
                      vm.$refs.template.load()
                  }, 100)
                } else {
                    let el = document.getElementById('modal_force_'+this.index)
                    setTimeout(function() {
                        el.click()
                    }, 100)
                }
            },
            forceModal(){
                let vm = this
                setTimeout(function() {
                    vm.$refs.template.load()
                }, 100)
            },
            _closeModal() {
                this.modal = ''
                document.getElementsByClassName('modal-backdrop')[0].remove()
            },
            formatDate (_date, charFrom, charTo, orientation) {
                _date = _date.split(charFrom)
                _date =
                    (orientation)
                        ? _date[2] + charTo + _date[1] + charTo + _date[0]
                        : _date[0] + charTo + _date[1] + charTo + _date[2]
                return _date
            },
            max_width(string_, long_){
                if( string_ !== undefined && string_.length > long_){
                    return string_.substr(0, long_) + '.'
                }
                return string_
            }
        },
        filters : {
            reformatDate(_date) {
                if (_date == undefined) {
                    return;
                }
                _date = moment(_date).format('ddd D MMM YYYY');
                return _date;
            },
            formatHours(_hour) {
                if( _hour === null || _hour === '' ){
                    return _hour
                }
                let hour_split = _hour.split(':')
                let hh = parseInt( hour_split[0] )
                let mm = hour_split[1]
                let _hh
                if (hh >= 12) {
                    hh = (hh!==12) ? (hh-12) : 12
                    _hh = (hh <= 9 ) ? '0'+hh : hh
                    return _hh + ':' + mm + ' PM';
                } else {
                    _hh = (hh <= 9 ) ? '0'+hh : hh
                    return _hh + ':' + mm + ' AM';
                }
            },
            filter_text(value){
                return ( value !== null ) ? value.split('�').join("") : ""
            },
            upper (value){
                return (value!==undefined) ? value.toUpperCase() : value
            }
        }
    }
</script>
<style>
    .icon-disabled{
        color: #b4b4b4;
    }
    .row-warning{
      background-color: #ffe4ec !important;
    }
    .row-free{
      background-color: #feffdc !important;
    }
    .min-w-110{
      min-width: 110px;
    }
  .card-recent{
    background: #F3F9FF !important;
  }
  .card-recent .card-header-master{
    background: #e4f1ff !important;
  }
  .permission_1{
    opacity: 0.5;
  }

</style>
