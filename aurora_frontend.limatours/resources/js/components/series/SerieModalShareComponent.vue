<template>
    <div class="modal fade modal-general modal-info modal-info_serie" id="modal-compartir">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title">{{ translations.label.share }}</div>
                    <br>
                    <div class="modal-info-editores">
                        <div class="modal-info-descripcion">{{ translations.label.editors }}:</div>
                        <div class="modal-info-avatares">
                            <div class="avatar-content" v-for="share in shares" @click="pulse_user(share)"
                                 data-toggle="tooltip" data-placement="top" :title="share.user.name">
                                <img :src="baseURL + 'images/users/'+ share.user.photo"
                                     :class="{'icon-pulsed':pulse_share_id===share.id}"
                                     onerror="this.src = baseURL + 'images/users/user_default.png'">
                            </div>
                        </div>
                    </div>
                    <div class="modal-info-parrafo">{{ translations.messages.share }}.</div>
                    <div class="modal-info-editar">
                        <div class="input-icono">
                            <multiselect :clear-on-select="false"
                                             :close-on-select="false"
                                             :hide-selected="true"
                                             :searchable="true"
                                             :multiple="true"
                                             :options="users"
                                             :placeholder="translations.label.search_users"
                                             :preserve-search="false"
                                             :tag-placeholder="translations.label.select_users"
                                             :taggable="false"
                                             label="label"
                                             ref="multiselect"
                                             track-by="code"
                                             data-vv-as="usuario"
                                             data-vv-name="usuario"
                                             name="usuario"
                                             @search-change="searchUsers"
                                             @select="pulse_share_id=''"
                                             v-model="users_selected"
                                             :loading="loading"
                                             :internal-search="false"
                                             :show-no-results="false">
                                </multiselect>
<!--                            <div class="icono icon-user"></div>-->
                        </div>
                        <div class="input-icono-editar-textarea">
                            <textarea rows="4" :placeholder="translations.label.comments" v-model="comment"></textarea>
                        </div>
                        <div class="contenido-checkbox">
                            <div class="contenido-checkbox-descripcion">{{ translations.label.permissions }}:</div>
                            <label class="checkbox-general checkbox-primer-item">{{ translations.label.just_visualize }}
                                <input type="radio" checked="checked" name="radio" value="1" v-model="type_permission"><span class="checkmark"></span>
                            </label>
                            <label class="checkbox-general">{{ translations.label.edit }} {{ translations.label.itinerary }}
                                <input type="radio" checked="checked" name="radio" value="2" v-model="type_permission"><span class="checkmark"></span>
                            </label>
                            <label class="checkbox-general right" v-if="pulse_share_id!=='' && !show_delete && (users_selected.length===1)" @click="will_remove()">
                                <i class="icon icon-trash"></i> {{ translations.label.remove }}?
                            </label>
                            <label class="checkbox-general right" v-if="show_delete" @click="remove(pulse_share_id)">
                                <i class="icon icon-trash"></i> {{ translations.label.yes_sure }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="button-cancelar cancelar-info" type="button" :disabled="loading" data-dismiss="modal">{{ translations.label.close }}</button>
                    <button class="button-cancelar button-actualizar" type="button" :disabled="loading" @click="save">{{ translations.label.share }}</button>
                </div>
                <div class="modal-body modal-body_info" style="">
                    <div class="modal-body-enlace">
                        <div class="modal-info-editar" style="margin: 6px;">
                            <div class="enlace-content">
                                <div class="enlace-descripcion">{{ translations.label.direct_link }}:</div>
                                <a :href="baseURL + 'serie/' + link_encode" target="_blank" id="link_for_copy">{{ baseURL }}serie/{{ link_encode }}</a>
                                <button class="enlace-copiar" @click="copy_link()">{{ translations.label.copy }}</button>
                            </div>
                            <div class="enlace-aviso">{{ translations.messages.copy }}.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import Multiselect from 'vue-multiselect'
    export default {
        props: ['data'],
        components: {
            Multiselect
        },
        data: () => {
            return {
                lang: '',
                loading: false,
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                baseExternalURL: window.baseExternalURL,
                baseURL: window.baseURL,
                shares: [],
                users: [],
                users_selected:[],
                type_permission:1,
                comment:"",
                link_encode: '',
                serie_id: '',
                pulse_share_id: '',
                show_delete: false,
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
                this.serie_id = this.data.serie_id
                // btoa - atob
                this.link_encode = btoa(this.data.serie_id)
                this.search()
            },
            will_remove(){
                this.show_delete = true
                setTimeout(()=>{
                    this.show_delete = false
                }, 5000)
            },
            remove(share_id){
                axios.delete(
                    baseExternalURL + 'api/series/users/' + share_id
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.properly_removed, {
                                position: 'top-right'
                            })
                            this.search()
                            this.clear_form()
                            this.$parent.search()
                            this.pulse_share_id = ''
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
            pulse_user(me){
                if( this.pulse_share_id === me.id ){
                    this.pulse_share_id = ''
                    this.clear_form()
                    return
                }
                this.pulse_share_id = me.id
                this.clear_form()
                this.users_selected.push({
                    code : me.user_id,
                    label : me.user.name
                })
                this.type_permission = me.type_permission
                this.comment =  me.comment
            },
            copy_link(){
                var link_for_copy = document.getElementById('link_for_copy')
                var selection = document.createRange()
                selection.selectNodeContents(link_for_copy)
                window.getSelection().removeAllRanges()
                window.getSelection().addRange(selection)
                document.execCommand('copy')
                window.getSelection().removeRange(selection)
                this.$toast.success(this.translations.label.copied_to_clipboard + '!', {
                    position: 'top-right'
                })
            },
            clear_form(){
                this.users_selected = []
                this.type_permission = 1
                this.comment = ""
            },
            save(){

                if( this.users_selected.length === 0 ){
                    this.$toast.warning(this.translations.messages.select_one_or_more_users, {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                let _user_ids = []
                this.users_selected.forEach( u =>{
                    _user_ids.push(u.code)
                } )

                let data = {
                    serie_id: this.serie_id,
                    user_ids: _user_ids,
                    type_permission:  this.type_permission,
                    comment: this.comment
                }

                axios.post(
                    baseExternalURL + 'api/series/users', data
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.saved_correctly, {
                                position: 'top-right'
                            })
                            this.search()
                            this.clear_form()
                            this.$parent.search()
                            this.pulse_share_id = ''
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
            searchUsers(search) {
                let data = { query : search, limit : 10 }
                axios.post(baseExternalURL + 'api/user/search/executiveAndSeller', data)
                    .then((result) => {
                        this.users = result.data.data
                    }).catch(() => {
                    loading(false)
                })
            },
            search() {
                this.loading = true

                axios.get(
                    baseExternalURL + 'api/series/'+this.data.serie_id+'/users'
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.shares = result.data.data
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
            }
        }
    }
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
    .icon-pulsed{
        border: solid 2px #dcff70;
    }
</style>
