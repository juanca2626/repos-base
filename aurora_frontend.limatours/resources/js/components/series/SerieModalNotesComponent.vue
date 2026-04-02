<template>
    <div class="modal fade modal-general modal-notas" id="modal-notas">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" @click="close_modal()">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title">{{ translations.label.notes }}<span>({{ notes.length }})</span></div>
                    <div class="modal-subtitle"><span>Anotaciones personales de la cotización</span></div>
                    <button @click="set_show_new_note()" :disabled="loading || show_new_note"
                            class="button-cancelar button-actualizar" type="button" style="float: right;margin-top: -40px;">Nueva Nota</button>
                    <br>
                    <transition name="fade">
                        <div class="modal-mensaje-card modal-mensaje-card-comentar series" style="margin-bottom: 20px" v-if="show_new_note">
                            <div class="mensaje-card-contenido">
                                <div class="modal-mensaje-comentar d-flex align-items-center">
                                    <div class="mensaje-card-avatar">
                                        <img style="width: 30px;" :src="baseURL + 'images/users/' + user_photo"
                                             onerror="this.src = baseURL + 'images/users/user_default.png'">
                                    </div>
                                    <div class="mensaje-card-nombre ml-4">Nueva Nota</div>
                                    <div class="mensaje-card-nombre text-counter" v-if="note.length>0" :class="{'error-text':note.length>limit_characters}">
                                        ({{ note.length }}/{{ limit_characters }})
                                    </div>
                                </div>
                                <div class="mensaje-card-textarea">
                                    <textarea class="textarea-notas" rows="5" :placeholder="'Escribe tu nota aquí '+'...'" v-model="note" :disabled="loading"></textarea>
                                </div>
                                <div class="my-2">
                                    Seleccionar un color:
                                    <span v-for="color in colors" @click="set_colors(color)"
                                          :style="'cursor: pointer; border-radius: 50%; border-width: 1.5px; background-color:'+color.secondary_color+'; border-color:'+color.primary_color+'; margin: 5px;'">
                                         <i class="fa fa-check" v-if="color.choose" :style="'color:'+color.primary_color+'; width: 20px; padding: 2px;'"></i>
                                         <i class="fa fa-dot-circle" v-else :style="'color:'+color.secondary_color+'; width: 20px;'"></i>
                                    </span>
                                </div>
                                <div class="mensaje-card-adjunto-editando d-flex justify-content-end">
                                    <button class="btn-file">
                                        <span class="icon-paperclip" style="font-size: 18px;"></span>
                                        <span>
                                            <label style="border-bottom: 1px solid #A71B20; cursor: pointer;" for="file">Adjuntar archivo</label>
                                            <input style="display: none;" type="file" ref="file" id="file" v-on:change="onChangeFileUpload()">
                                        </span>
                                    </button>
                                    <button class="button-cancelar" type="button" :disabled="loading" @click="show_new_note=false">{{ translations.label.cancel }}</button>
                                    <button class="button-cancelar button-actualizar" type="button" :disabled="loading" @click="save()">{{ translations.label.send }}</button>
                                </div>
                            </div>
                        </div>
                    </transition>

                    <div class="modal-card-notas">
<!--                        :style="'background-color: '+note.note.secondary_color" -->
                        <div class="nota" v-for="note in notes" :style="note.style_background">
                            <div class="nota-header">
                                <div class="nota-header-title">
                                    <div class="nota-body-editando d-flex">
                                        <div class="avatar-content mr-2">
                                            <img :src="baseURL + 'images/users/' + note.note.user.photo" style="width: 20px; height: 20px;"
                                                 onerror="this.src = baseURL + 'images/users/user_default.png'">
                                        </div>
                                        <div class="mr-3" :style="note.style_color">{{ note.note.user.name }}</div>
                                        <div class="editando mr-4" style="font-size: 14px; font-weight: 400" :style="note.style_color">{{ note.created_at | reformatDate }} <span class="editado" v-if="note.note.created_at!==note.note.updated_at">(editado)</span> </div>
                                    </div>
                                    <div class="nota-header-editar" v-if="type_permission!==1":style="note.style_color">
                                        <button @click="will_show_edit(note)" :disabled="loading">
                                            <i class="icon-edit f-20" data-toggle="tooltip" data-placement="top" :title="translations.label.edit"></i>
                                        </button>
                                    </div>
                                    <div class="nota-header-borrar" v-if="type_permission!==1" style="margin-left: 10px" :style="note.style_color"
                                         @click="toggleModal( 0, 'remover-note', { translations: translations, note : note } )"
                                         data-toggle="modal" data-target="#modal-borrar-nota" :disabled="loading">
                                        <button><i class="icon-trash-2 f-20" data-toggle="tooltip" data-placement="top" :title="translations.label.remove"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="nota-body">

                                <div class="nota-body-textarea">
                                    <textarea class="textarea-notas" rows="3" :placeholder="translations.label.comments" :style="note.style_background + note.style_color"
                                              v-model="note.note.note" v-if="!note.show_edit" disabled>{{ note.note.note }}</textarea>

                                    <textarea class="textarea-notas" rows="3" :placeholder="translations.label.comments"
                                              v-model="note.note.note" v-else>{{ note.note.note }}</textarea>
                                </div>

                                <div v-if="note.show_edit" class="my-2">
                                    Seleccionar un color:

                                    <span v-for="color in colors" @click="set_colors(color)"
                                          :style="'border-radius: 50%; border-width: 1.5px; background-color:'+color.secondary_color+'; border-color:'+color.primary_color+'; margin: 5px;'">
                                        <i class="fa fa-check" v-if="color.choose" :style="'color:'+color.primary_color+'; width: 20px; padding: 2px;'"></i>
                                         <i class="fa fa-dot-circle" v-else :style="'color:'+color.secondary_color+'; width: 20px;'"></i>
                                    </span>
                                </div>

                                <div class="nota-body-adjunto" v-if="note.note.attached!=='' && note.note.attached!==null && !(note.show_edit)">
                                    <a target="_blank" :href="baseURL + 'uploads/' + note.note.attached">
                                        <i class="icon-paperclip"></i>
                                        <span>{{translations.label.see_attached}}</span>
                                    </a>
                                </div>
                                <div class="nota-body-adjunto-editando" v-if="note.show_edit">
                                    <button class="btn-file">
                                        <span class="icon-paperclip" style="font-size: 18px;"></span>
                                        <span>
                                            <label style="border-bottom: 1px solid #A71B20; cursor: pointer;" :for="'note_file_'+note.note.id">Adjuntar archivo</label>
                                            <input style="display: none;" type="file" :ref="'note_file_'+note.note.id" :id="'note_file_'+note.note.id">
                                        </span>
                                    </button>
<!--                                    <button class="button-adjuntar">-->
<!--                                        <i class="icon-paperclip"></i>-->
<!--                                        <span><input type="file" :ref="'note_file_'+note.id" :id="'note_file_'+note.id"></span>-->
<!--                                    </button>-->
                                    <button class="button-cancelar" type="button" @click="note.show_edit=false" :disabled="loading">{{ translations.label.cancel }}</button>
                                    <button class="button-cancelar button-actualizar" type="button" @click="will_update(note.note)":disabled="loading">{{ translations.label.update }}</button>
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
                baseExternalURL: window.baseExternalURL,
                baseURL: window.baseURL,
                serie_id: '',
                type_permission: '',
                modal: '',
                dataModal: {},
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                file: '',
                note: '',
                notes: [],
                limit_characters: 500,
                user_photo: '',
                show_new_note: false,
                color_choose:{
                    primary_color: "#979797",
                    secondary_color: "#fafafa",
                    default:true,
                    choose:true,
                },
                colors:[
                    {
                        primary_color: "#979797",
                        secondary_color: "#fafafa",
                        default:true,
                        choose:true,
                    },
                    {
                        primary_color: "#714d38",
                        secondary_color: "#fff0a0",
                        default:false,
                        choose:false,
                    },
                    {
                        primary_color: "#51cbc3",
                        secondary_color: "#ebffe2",
                        default:false,
                        choose:false,
                    },
                    {
                        primary_color: "#5b9ce5",
                        secondary_color: "#e2fdff",
                        default:false,
                        choose:false,
                    },
                    {
                        primary_color: "#a71b20",
                        secondary_color: "#ffe2e2",
                        default:false,
                        choose:false,
                    },
                ]
            }
        },
        created: function () {
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.user_photo = localStorage.getItem('photo')
        },
        computed: {
        },
        methods: {
            load() {
                this.translations = this.data.translations
                this.serie_id = this.data.serie_id
                this.type_permission = this.data.type_permission
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
            onChangeFileUpload(){
                this.file = this.$refs.file.files[0];
            },
            will_show_edit(note){
                this.notes.forEach( n=>{
                    n.show_edit = false
                } )
                note.show_edit = !(note.show_edit)
                this.colors.forEach( c =>{
                    if( note.note.primary_color === c.primary_color ){
                        this.color_choose = c
                        c.choose = true
                    } else {
                        c.choose = false
                    }
                })
            },
            set_colors(color){
                this.color_choose = color
                this.colors.forEach( c=>{
                    c.choose = false
                } )
                color.choose = true
            },
            set_show_new_note(){
                this.show_new_note = true
                this.notes.forEach( m=>{
                    m.show_edit = false
                } )
                this.colors.forEach( c =>{
                    if( c.default ){
                        c.choose = true
                        this.color_choose = c
                    } else {
                        c.choose = false
                    }
                })
            },
            search(){

                this.loading = true

                axios.get(
                    baseExternalURL + 'api/notes/series/'+this.serie_id
                )
                    .then((result) => {
                        if( result.data.success ){
                            result.data.data.forEach( n=>{
                                n.style_background = ''
                                n.style_color = ''
                                if( n.note.secondary_color !== null && n.note.secondary_color !== '' ){
                                    n.style_background = 'background-color : ' + n.note.secondary_color
                                    n.style_background += '; border-color : ' + n.note.secondary_color + ';'
                                    n.style_color += 'color : ' + n.note.primary_color + ';'
                                }
                            } )
                            this.notes = result.data.data
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
            will_update(note){
                let el = document.getElementById('note_file_'+note.id)
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
                            note.attached = result.data.name
                            this.update(note)
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
                    this.update(note)
                }
            },
            save(){

                if( this.note === '' ){
                    this.$toast.warning("Por favor ingrese su nota", {
                        position: 'top-right'
                    })
                    return
                }

                if( this.note.length > this.limit_characters ){
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
                            this.save_note()
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
                    this.save_note()
                }

            },
            save_note(){

                this.loading = true
                let data = {
                    note: this.note,
                    attached: this.attached,
                    primary_color: (!(this.color_choose.default)) ? this.color_choose.primary_color : '',
                    secondary_color: (!(this.color_choose.default)) ? this.color_choose.secondary_color : ''
                }

                axios.post(
                    baseExternalURL + 'api/notes/series/'+this.serie_id, data
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.note = ''
                            this.file = ''
                            this.attached = ''
                            this.color_choose = {}
                            this.show_new_note = false
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
            update(note){

                this.loading = true

                note.primary_color = (!(this.color_choose.default)) ? this.color_choose.primary_color : ''
                note.secondary_color = (!(this.color_choose.default)) ? this.color_choose.secondary_color : ''

                axios.put(
                    baseExternalURL + 'api/notes/' + note.id,
                    note
                )
                    .then((result) => {
                        if(result.data.success){
                            note.show_edit = false
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.search()
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

            }
        },
        filters:{

            formatHours(_hour) {
                if( _hour === null || _hour === '' ){
                    return _hour
                }
                let hour_split = _hour.split(':')
                let hh = parseInt( hour_split[0] )
                let mm = hour_split[1]
                let _hh
                if (hh > 12) {
                    hh = (hh!==12) ? (hh-12) : 12
                    _hh = (hh <= 9 ) ? '0'+hh : hh
                    return _hh + ':' + mm + ' PM';
                } else {
                    _hh = (hh <= 9 ) ? '0'+hh : hh
                    return _hh + ':' + mm + ' AM';
                }
            },
            upper (value){
                return (value!==undefined) ? value.toUpperCase() : value
            },
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
