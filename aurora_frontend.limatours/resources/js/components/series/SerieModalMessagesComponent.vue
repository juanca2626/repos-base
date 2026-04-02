<template>
    <div class="modal fade modal-general modal-mensajes" id="modal-mensajes">
        <div class="modal-dialog" role="document">
            <div class="modal-content" v-if="messages">
                <button class="modal-cerrar" type="button" @click="close_modal()">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title">{{ translations.label.messages }}<span>({{messages.length + messages_olds.length}})</span></div>
                    <div class="modal-subtitle d-flex align-items-center">
                        <span>{{translations.label.details_communication}}.</span>
                        <select class="select-modal" name="see_select" v-model="see_by" v-if="data.see_previous_messages">
                            <option value="all" selected>Ver todos los mensajes</option>
                            <option value="quote">Cotización</option>
                            <option value="pre-quote">Pre-Cotización(Importados)</option>
                        </select>
                        <button @click="set_show_new_message()" v-if="!show_new_message" :disabled="loading"
                            class="button-cancelar button-actualizar" type="button">{{ translations.label.new_message }}</button>
                    </div>
                    <br>
                    <div v-show="(see_by==='all' || see_by==='quote') && data.see_previous_messages">
                        <p class="subtitle-modal">Cotización</p>
                        <hr>
                    </div>
                    <transition name="fade">
                        <div class="modal-mensaje-card modal-mensaje-card-comentar" v-if="show_new_message">
                        <div class="mensaje-card-contenido">
                            <div class="modal-mensaje-comentar ">
                                <div class="mensaje-card-avatar">
                                    <img :src="baseURL + 'images/users/' + user_photo"
                                         onerror="this.src = baseURL + 'images/users/user_default.png'">
                                </div>
                                <div class="mensaje-card-nombre">{{ translations.label.new_message }}</div>
                                <div class="mensaje-card-nombre text-counter" v-if="message.length>0" :class="{'error-text':message.length>limit_characters}">
                                    ({{ message.length }}/{{ limit_characters }})
                                </div>
                            </div>
                            <div class="mensaje-card-textarea">
                                <textarea rows="5" :placeholder="translations.messages.write_message_here+'...'" v-model="message" :disabled="loading"></textarea>
                            </div>
                            <div class="mensaje-card-adjunto-editando">
                                <button><i class="icon-paperclip"></i>
                                    <span>
                                        <input type="file" ref="file" id="file" v-on:change="onChangeFileUpload()">
                                    </span>
                                </button>
                                <button class="button-cancelar" type="button" :disabled="loading" @click="show_new_message=false">{{ translations.label.cancel }}</button>
                                <button class="button-cancelar button-actualizar" type="button" :disabled="loading" @click="save()">{{ translations.label.send }}</button>
                            </div>
                        </div>
                    </div>
                    </transition>

                    <div class="modal-mensaje-card" :class="{'modal-mensaje-card-nuevo':verify_recent(m.updated_at)}"  v-show="see_by==='all' || see_by==='quote'"
                         v-for="m in messages" v-if="m.reply_id===null || m.reply_id===''">
                        <div class="mensaje-card-avatar">
                            <img :src="baseURL + 'images/users/' + m.user.photo"
                                      onerror="this.src = baseURL + 'images/users/user_default.png'">
                        </div>
                        <div class="mensaje-card-contenido modal-mensaje-card-respondiendo">
                            <div class="mensaje-card-nombre">{{ m.user.name }}</div>
                            <div class="icon-remove" v-if="user_id==m.user_id">
                                <span @click="will_remove(m)" v-if="!m.show_delete">
                                    <i class="fa fa-times"></i>
                                </span>
                                <span @click="remove(m.id)" v-if="m.show_delete">
                                    <i class="fa fa-times"></i> {{ translations.label.yes }}, {{ translations.label.remove }}
                                </span>
                            </div>
                            <div class="mensaje-card-info">
                                <div class="mensaje-card-info_hora">{{ m.updated_at | reformatDate }}</div>
                                <div class="mensaje-card-info_editar" v-if="user_id==m.user_id">
                                    <button class="descargar" @click="set_show_edit(m)">
                                        <span data-toggle="tooltip" data-placement="top" :title="translations.label.edit">
                                            <i class="icon-edit"></i>
                                        </span>
                                    </button>
                                </div>
                                <div class="mensaje-card-info_responder">
                                    <button class="descargar" style="margin-left: 10px" @click="set_show_reply(m)">
                                        <span data-toggle="tooltip" data-placement="top" :title="translations.label.answer">
                                            <i class="icon-corner-down-right"></i>
                                        </span>
                                    </button>
                                </div>
                                <span v-if="m.show_edit" class="text-counter" :class="{'error-text':m.message_edit.length>limit_characters}">
                                    ({{ m.message_edit.length }}/{{ limit_characters }})
                                </span>
                            </div>
                            <div class="mensaje-card-textarea" style="padding-top: 11px;">
                                <textarea rows="5" :placeholder="translations.label.comments" v-model="m.message" disabled v-if="!m.show_edit">{{ m.message }}</textarea>
                                <textarea rows="5" :placeholder="translations.label.comments" v-model="m.message_edit" v-if="m.show_edit">{{ m.message_edit }}</textarea>
                            </div>
                            <div class="mensaje-card-adjunto" v-if="m.attached!=='' && m.attached!==null" style="margin-bottom: 10px;">
                                <button>
                                    <i class="icon-paperclip"></i>
                                    <a target="_blank" :href="baseURL + 'uploads/' + m.attached">{{ translations.label.see_attached }}</a>
                                </button>
                            </div>

                            <div class="mensaje-card-respuesta mensaje-card-respondiendo" v-show="m.show_reply">
                                <div class="mensaje-card-contenido">
                                    <div class="mensaje-card-nombre" v-if="message_reply.length>0">
                                        <div class="mensaje-card-avatar">
                                            <img style="float: left; height: 22px; width: 22px; margin-right: 8px;"
                                                 :src="baseURL + 'images/users/' + user_photo"
                                                 onerror="this.src = baseURL + 'images/users/user_default.png'">
                                        </div> {{ translations.label.answering }} ...
                                        <span class="text-counter" :class="{'error-text':message_reply.length>limit_characters}">
                                            ({{ message_reply.length }}/{{ limit_characters }})
                                        </span>
                                    </div>
                                    <div class="mensaje-card-textarea" style="padding-right: 15px; margin-bottom: 12px; margin-top: 10px;">
                                        <textarea rows="5" v-model="message_reply" :placeholder="translations.messages.write_message_here+' ...'"></textarea>
                                    </div>
                                    <div class="mensaje-card-adjunto-editando">
                                        <button>
                                            <i class="icon-paperclip"></i>
                                            <span>
                                                <input type="file" :ref="'file_reply_'+m.id" :id="'file_reply_'+m.id"
                                                       v-on:change="onChangeFileUploadExistsReply(m.id)">
                                            </span>
                                        </button>
                                        <button class="button-cancelar" type="button" :disabled="loading"
                                                @click="m.show_reply=false;">
                                            {{ translations.label.cancel }}
                                        </button>
                                        <button class="button-cancelar button-actualizar" type="button" :disabled="loading" @click="reply(m)">{{ translations.label.send }}</button>
                                    </div>
                                </div>
                            </div>

                            <div class="mensaje-card-adjunto-editando" v-show="m.show_edit">
                                <button><i class="icon-paperclip"></i>
                                    <span>
                                        <input type="file" :ref="'file_'+m.id" :id="'file_'+m.id"
                                           v-on:change="onChangeFileUploadExists(m.id)">
                                    </span>
                                </button>
                                <button class="button-cancelar" type="button" :disabled="loading" @click="m.show_edit=false;">
                                    {{ translations.label.cancel }}
                                </button>
                                <button class="button-cancelar button-actualizar" type="button" :disabled="loading" @click="update(m)">{{ translations.label.update }}</button>
                            </div>

<!--                            REPLY REPLY REPLY REPLY REPLY REPLY REPLY REPLY REPLY REPLY-->
                            <div v-for="mm in messages" v-if="mm.reply_id===m.id">
                                <div class="mensaje-card-separador"></div>
                                <div class="mensaje-card-respuesta" :class="{'modal-mensaje-card-nuevo':verify_recent(mm.updated_at)}">
                                    <div class="mensaje-card-icon-responder">
                                        <i class="icon-corner-down-right"></i>
                                    </div>
                                    <div class="mensaje-card-avatar">
                                        <img :src="baseURL + 'images/users/' + mm.user.photo"
                                             onerror="this.src = baseURL + 'images/users/user_default.png'">
                                    </div>
                                    <div class="mensaje-card-contenido">
                                        <div class="mensaje-card-nombre">{{ mm.user.name }}</div>
                                        <div class="icon-remove" v-if="user_id==mm.user_id">
                                            <span @click="will_remove(mm)" v-if="!mm.show_delete">
                                                <i class="fa fa-times"></i>
                                            </span>
                                            <span @click="remove(mm.id)" v-if="mm.show_delete">
                                                <i class="fa fa-times"></i> {{translations.label.yes}}, {{translations.label.remove}}
                                            </span>
                                        </div>
                                        <div class="mensaje-card-info">
                                            <div class="mensaje-card-info_hora">{{ mm.updated_at | reformatDate }}</div>

                                            <div class="mensaje-card-info_editar" v-if="user_id==mm.user_id">
                                                <button class="descargar" @click="set_show_edit(mm)">
                                                    <span data-toggle="tooltip" data-placement="top" :title="translations.label.edit">
                                                        <i class="icon-edit"></i>
                                                    </span>
                                                </button>
                                            </div>
                                            <div class="mensaje-card-info_responder" style="margin-left: 10px;">
                                                <button class="descargar" @click="set_show_reply(m)">
                                                    <span data-toggle="tooltip" data-placement="top" :title="translations.label.answer">
                                                        <i class="icon-corner-down-right"></i>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                        <span v-if="mm.show_edit" class="text-counter" :class="{'error-text':mm.message_edit.length>limit_characters}">
                                            ({{ mm.message_edit.length }}/{{limit_characters}})
                                        </span>
                                        <div class="mensaje-card-textarea" style="padding-top: 11px;">
                                            <textarea rows="5" :placeholder="translations.label.comments" v-model="mm.message" disabled v-if="!mm.show_edit">{{ mm.message }}</textarea>
                                            <textarea rows="5" :placeholder="translations.label.comments" v-model="mm.message_edit" v-if="mm.show_edit">{{ mm.message_edit }}</textarea>
                                        </div>
                                    </div>

                                </div>

                                <div class="mensaje-card-adjunto" v-if="mm.attached!=='' && mm.attached!==null" style="margin-bottom: 10px;">
                                    <button>
                                        <i class="icon-paperclip"></i>
                                        <a target="_blank" :href="baseURL + 'uploads/' + mm.attached">{{translations.label.see_attached}}</a>
                                    </button>
                                </div>

                                <div class="mensaje-card-adjunto-editando" v-show="mm.show_edit">
                                    <button><i class="icon-paperclip"></i>
                                        <span>
                                                <input type="file" :ref="'file_'+mm.id" :id="'file_'+mm.id"
                                                       v-on:change="onChangeFileUploadExists(mm.id)">
                                            </span>
                                    </button>
                                    <button class="button-cancelar" type="button" :disabled="loading" @click="mm.show_edit=false;">{{translations.label.cancel}}</button>
                                    <button class="button-cancelar button-actualizar" type="button" :disabled="loading" @click="update(mm)">{{translations.label.update}}</button>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div>
                        <p class="subtitle-modal" v-if="messages_olds.length>0 && data.see_previous_messages" v-show="see_by==='all' || see_by==='pre-quote'">
                            Pre-Cotización - Importados de HM-{{ data.master_sheet_id }} <span class="icon-trash-2" style="font-size: 20px; margin-left: 10px; cursor: pointer;"
                                @click="toggleModal( 0, 'remover-see-messages', { translations: translations, serie_id : serie_id, master_sheet_id : data.master_sheet_id } )"
                                data-toggle="modal" data-target="#modal-borrar-see-message"></span>
                        </p>
                        <hr>
                    </div>

                    <div class="modal-mensaje-card" v-for="m in messages_olds" v-if="m.reply_id===null || m.reply_id===''"
                         v-show="(see_by==='all' || see_by==='pre-quote') && data.see_previous_messages">
                        <div class="mensaje-card-avatar">
                            <img :src="baseURL + 'images/users/' + m.user.photo"
                                 onerror="this.src = baseURL + 'images/users/user_default.png'">
                        </div>
                        <div class="mensaje-card-contenido modal-mensaje-card-respondiendo">
                            <div class="mensaje-card-nombre">{{ m.user.name }}</div>
                            <div class="mensaje-card-info">
                                <div class="mensaje-card-info_hora">{{ m.updated_at | reformatDate }}</div>
                            </div>
                            <div class="mensaje-card-textarea" style="padding-top: 11px;">
                                <textarea rows="5" :placeholder="translations.label.comments" v-model="m.message" disabled>{{ m.message }}</textarea>
                            </div>
                            <div class="mensaje-card-adjunto" v-if="m.attached!=='' && m.attached!==null" style="margin-bottom: 10px;">
                                <button>
                                    <i class="icon-paperclip"></i>
                                    <a target="_blank" :href="baseURL + 'uploads/' + m.attached">{{ translations.label.see_attached }}</a>
                                </button>
                            </div>

<!--                            REPLY REPLY REPLY REPLY REPLY REPLY REPLY REPLY REPLY REPLY-->
                            <div v-for="mm in messages_olds" v-if="mm.reply_id===m.id">
                                <div class="mensaje-card-separador"></div>
                                <div class="mensaje-card-respuesta" :class="{'modal-mensaje-card-nuevo':verify_recent(mm.updated_at)}">
                                    <div class="mensaje-card-icon-responder">
                                        <i class="icon-corner-down-right"></i>
                                    </div>
                                    <div class="mensaje-card-avatar">
                                        <img :src="baseURL + 'images/users/' + mm.user.photo"
                                             onerror="this.src = baseURL + 'images/users/user_default.png'">
                                    </div>
                                    <div class="mensaje-card-contenido">
                                        <div class="mensaje-card-nombre">{{ mm.user.name }}</div>
                                        <div class="mensaje-card-info">
                                            <div class="mensaje-card-info_hora">{{ mm.updated_at | reformatDate }}</div>
                                        </div>
                                        <div class="mensaje-card-textarea" style="padding-top: 11px;">
                                            <textarea rows="5" :placeholder="translations.label.comments" v-model="mm.message" disabled>{{ mm.message }}</textarea>
                                        </div>
                                    </div>

                                </div>

                                <div class="mensaje-card-adjunto" v-if="mm.attached!=='' && mm.attached!==null" style="margin-bottom: 10px;">
                                    <button>
                                        <i class="icon-paperclip"></i>
                                        <a target="_blank" :href="baseURL + 'uploads/' + mm.attached">{{translations.label.see_attached}}</a>
                                    </button>
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
                modal: '',
                dataModal: {},
                baseExternalURL: window.baseExternalURL,
                baseURL: window.baseURL,
                user_photo: '',
                user_id: '',
                serie_id: '',
                messages: [],
                messages_olds: [],
                optionsR: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    minDate: moment().format('YYYY-MM-DD')
                },
                show_new_message : false,
                entity : 'serie',
                message_reply : '',
                message : '',
                file : '',
                sub_file : '',
                sub_sub_file : '',
                attached : '',
                reply_id : null,
                action_socket : '',
                limit_characters : 600,
                see_by : "all",
            }
        },
        created: function () {
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.user_photo = localStorage.getItem('photo')
            this.user_id = localStorage.getItem('user_id')
        },
        computed: {
        },
        methods: {
            load() {
                this.translations = this.data.translations
                this.serie_id = this.data.serie_id
                this.search()
            },
            close_modal(){
                this.$parent._closeModal()
            },
            toggleModal(index, _modal, _data) {

                this.index = index
                this.dataModal = _data
                this.modal = 'serie-modal-' + _modal

                let vm = this
                setTimeout(function() {
                    vm.$refs.template.load()
                }, 100)
            },
            will_remove(message){
                message.show_delete = true
                setTimeout(()=>{
                    message.show_delete = false
                }, 5000)
            },
            remove(message_id){
                axios.delete(
                    baseExternalURL + 'api/message/' + message_id
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.properly_removed, {
                                position: 'top-right'
                            })
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
                    })
            },
            set_show_new_message(){
                this.show_new_message = true
                this.messages.forEach( m=>{
                    m.show_edit = false
                    m.message_edit = m.message
                    m.show_reply = false
                } )
            },
            set_show_reply(message){
                this.message_reply = ''
                if( message.show_reply ){
                    message.show_reply = false
                } else {
                    this.messages.forEach( m=>{
                        m.show_edit = false
                        m.show_reply = false
                        if( m.id === message.id ){
                            m.message_edit = m.message
                        }
                    } )
                    message.show_reply=true
                }
            },
            set_show_edit(message){
                if( message.show_edit ){
                    message.show_edit = false
                    message.message_edit = message.message
                } else {
                    this.messages.forEach( m=>{
                        m.show_edit = false
                        m.show_reply = false
                        if( m.id === message.id ){
                            m.message_edit = m.message
                        }
                    } )

                    message.show_edit=true
                }
            },
            verify_recent(_datetime){
                let date = moment(_datetime)
                let _diff = moment().diff(date, 'minutes')
                if( _diff <= 15 )
                    return true
                return false
            },
            search(){
                this.loading = true
                axios.get(
                    baseExternalURL + 'api/message?entity='+this.entity+'&object_id='
                                +this.serie_id + '&limit=100'
                )
                    .then((result) => {
                        this.loading = false
                        let _messages = []
                        let _messages_olds = []
                        result.data.data.forEach( m =>{
                            if( m.created_at < this.data.serie_created_at ){
                                _messages_olds.push(m)
                            } else {
                                _messages.push(m)
                            }
                        })
                        this.messages_olds = _messages_olds
                        this.messages = _messages
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            onChangeFileUpload(){
                this.file = this.$refs.file.files[0];
            },
            onChangeFileUploadExists(message_id){
                let el = document.getElementById('file_'+message_id)
                this.sub_file = el.files[0];
            },
            onChangeFileUploadExistsReply(message_id){
                let el = document.getElementById('file_reply_'+message_id)
                this.sub_sub_file = el.files[0];
            },
            reply(message){
                if( this.message_reply === '' ){
                    this.$toast.warning(this.translations.validations.message, {
                        position: 'top-right'
                    })
                    return
                }
                if( this.message_reply.length > this.limit_characters ){
                    this.$toast.warning(this.translations.validations.message_exceed +'. '+'('+this.limit_characters+')', {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true
                if(this.sub_sub_file !== '' && this.sub_sub_file !== null && this.sub_sub_file !== undefined)
                {
                    let formData = new FormData();
                    formData.append('file', this.sub_sub_file);

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
                            this.attached = result.data.name
                            this.message = this.message_reply
                            this.reply_id = message.id
                            message.show_reply = false
                            this.save_message()
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
                } else {
                    this.message = this.message_reply
                    this.reply_id = message.id
                    message.show_reply = false
                    this.save_message()
                }
            },
            update(message){

                if( message.message_edit === '' ){
                    this.$toast.warning(this.translations.validations.message, {
                        position: 'top-right'
                    })
                    return
                }
                if( message.message_edit.length > this.limit_characters ){
                    this.$toast.warning(this.translations.validations.message_exceed+'. '+'('+this.limit_characters+')', {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true
                if(this.sub_file !== '' && this.sub_file !== null && this.sub_file !== undefined)
                {
                    let formData = new FormData();
                    formData.append('file', this.sub_file);

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
                            message.attached = result.data.name
                            message.message = message.message_edit
                            this.updateMessage(message)
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
                } else {
                    message.message = message.message_edit
                    this.updateMessage(message)
                }

            },
            updateMessage(message){
                this.loading = true
                let data = {
                    message: message.message,
                    attached: message.attached,
                }

                axios.put(
                    baseExternalURL + 'api/message/' + message.id, data
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.sub_file = ''
                            message.show_edit = false
                            this.search()
                            let message_ = this.translations.messages.has_modified_his_message
                            this.$toast.success(message_, {
                                position: 'top-right'
                            })
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
            save(){

                if( this.message === '' ){
                    this.$toast.warning(this.translations.validations.message, {
                        position: 'top-right'
                    })
                    return
                }

                if( this.message.length > this.limit_characters ){
                    this.$toast.warning(this.translations.validations.message_exceed+'. '+'('+this.limit_characters+')', {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true
                if(this.file !== '' && this.file !== null && this.file !== undefined)
                {
                    let formData = new FormData();
                    formData.append('file', this.file);

                    axios.post(baseURL + 'account/upload_file',
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }
                    ).then((result) => {
                        this.loading = false

                        console.log(result)

                        if(result.data.success)
                        {
                            this.attached = result.data.name
                            this.save_message()
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
                } else {
                    this.save_message()
                }

            },
            save_message(){
                this.loading = true
                let data = {
                    entity: this.entity,
                    object_id: this.serie_id,
                    message: this.message,
                    reply_id: this.reply_id,
                    attached: this.attached,
                }

                axios.post(
                    baseExternalURL + 'api/message', data
                )
                    .then((result) => {
                        if( result.data.success ){
                            let message_ = ''
                            if( this.reply_id !== '' && this.reply_id !==null ){
                                message_ = this.translations.messages.has_answered_a_message
                            } else {
                                message_ = this.translations.messages.has_sent_new_message
                            }
                            this.message = ''
                            this.file = ''
                            this.sub_sub_file = ''
                            this.attached = ''
                            this.reply_id = ''
                            this.show_new_message = false
                            this.$toast.success(message_, {
                                position: 'top-right'
                            })
                            this.search()
                            this.$parent.serie.messages_count+=1
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
        },
        filters : {
            reformatDate: function(_date) {
                if (_date == undefined) {
                    return;
                }
                _date = moment(_date).format('ddd D MMM YYYY hh:mm');
                return _date;
            },
        }
    }
</script>
<style>
    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0
    }
    .icon-remove{
        position: relative;
        float: right;
        cursor: pointer;
    }
    .error-text{
        color: #f10000 !important;
    }
    .text-counter{
        margin-left: 6px;
        font-size: 13px;
    }
</style>
