<template>
    <div class="modal fade modal-general modal-notas" id="modal-notas">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title">{{ translations.label.notes }}<span>({{ notes.length }})</span></div>
                    <div class="modal-subtitle"><span>{{ translations.label.private_annotations }}</span></div>
                    <br>
                    <div class="modal-card-notas">
                        <div class="nota" v-for="note in notes">
                            <div class="nota-header">
                                <div class="nota-header-title"><span>{{ translations.label.day }} {{ note.day.number }} </span><span>- </span><span>{{ note.day.destinations }}</span></div>
                                <div class="nota-header-subtitle">
                                    <div class="nota-header-hora" v-if="note.check_in!=='' && note.check_in!==null">{{ note.check_in | formatHours }}</div>
                                    <div class="nota-header-hora nota-header-sin-codigo" v-else>{{ translations.label.without_hour | upper }}</div>
                                    <div class="nota-header-codigo" v-if="note.service_code_stela!=='' && note.service_code_stela!==null">{{ note.service_code_stela }} </div>
                                    <div class="nota-header-codigo nota-header-sin-codigo" v-else>{{ max_width( translations.label.without_code, 7 ) | upper }}</div>
                                    <div class="nota-header-info"><span class="separador">-</span><span>{{ note.description }}</span></div>
                                    <div class="nota-header-editar" v-if="type_permission!==1">
                                        <button @click="note.show_edit=!(note.show_edit)" :disabled="loading">
                                            <i class="icon-edit" data-toggle="tooltip" data-placement="top" :title="translations.label.edit"></i>
                                        </button>
                                    </div>
                                    <div class="nota-header-borrar" v-if="type_permission!==1" style="margin-left: 10px" @click="remove(note)" :disabled="loading">
                                        <button><i class="icon-trash-2" data-toggle="tooltip" data-placement="top" :title="translations.label.remove"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="nota-body">

                                <div class="nota-body-editando" v-if="note.show_edit">
                                    <div class="avatar-content">
                                        <img :src="baseURL + 'images/users/' + user_photo"
                                             onerror="this.src = baseURL + 'images/users/user_default.png'">
                                    </div>
                                    <div class="editando">{{ translations.label.editing }} ...</div>
                                </div>
                                <div class="nota-body-textarea">
                                    <textarea rows="3" :placeholder="translations.label.comments" v-model="note.comment" :disabled="!(note.show_edit)">{{ note.comment }}</textarea>
                                </div>
                                <div class="nota-body-adjunto" v-if="note.attached!=='' && note.attached!==null && !(note.show_edit)">
                                    <a target="_blank" :href="baseURL + 'uploads/' + note.attached">
                                        <i class="icon-paperclip"></i>
                                        <span>{{translations.label.see_attached}}</span>
                                    </a>
                                </div>
                                <div class="nota-body-adjunto-editando" v-if="note.show_edit">
                                    <button class="button-adjuntar">
                                        <i class="icon-paperclip"></i>
                                        <span><input type="file" :ref="'note_file_'+note.id" :id="'note_file_'+note.id"></span>
                                    </button>
                                    <button class="button-cancelar" type="button" @click="note.show_edit=false" :disabled="loading">{{ translations.label.cancel }}</button>
                                    <button class="button-cancelar button-actualizar" type="button" @click="will_update(note)":disabled="loading">{{ translations.label.update }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
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
                baseURL: window.baseURL,
                master_sheet_id: '',
                type_permission: '',
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                notes: [],
                user_photo: '',
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
                this.master_sheet_id = this.data.master_sheet_id
                this.type_permission = this.data.type_permission
                this.search()
            },
            search(){

                this.loading = true

                axios.get(
                    baseExternalURL + 'api/master_sheet/'+this.master_sheet_id+'/days/services/comments'
                )
                    .then((result) => {
                        if( result.data.success ){
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
            update(note){

                this.loading = true

                axios.put(
                    baseExternalURL + 'api/master_sheet/day/service/'+note.id+'/comment',
                    note
                )
                    .then((result) => {
                        if(result.data.success){
                            note.show_edit = false
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.$parent.get_total_notes()
                            this.$parent.search_days(true)
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

            },

            remove(note){
                note.comment_status = false
                this.update( note )
            },
            max_width(string_, long_){
                if( string_ !== undefined && string_.length > long_){
                    return string_.substr(0, long_) + '.'
                }
                return string_
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
            }
        }
    }
</script>
