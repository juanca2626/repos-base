<template>
    <div class="modal fade modal-general modal-info" id="modal-info">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title" v-if="serie_selected!==''">{{ translations.label.edit }} {{ translations.label.info }}</div>
                    <div class="modal-title" v-else>{{ translations.label.new }} {{ translations.label.title }}</div>
                    <div class="modal-codigo" v-if="serie_selected!==''">Cotización:<span>GS-{{ serie_selected.id }}</span></div>
                    <br>
                    <div class="modal-info-editar">
                        <div class="d-flex">
                            <div class="input-icono " style="margin-right: 1rem;">
                                <input style="width: 305px;" type="text" :placeholder="translations.label.file_name+'...'" v-model="form.name">
                                <div class="icono icon-edit"></div>
                            </div>
                            <div class="modal-info-editar-inputs">
                                <div class="input-icono input-icono-fecha">
                                    <date-picker style="width: 200px;" type="text" placeholder="Fecha de Inicio" :config="optionsR" v-model="form.date_start"></date-picker>
                                    <div class="icono icon-calendar"></div>
                                </div>
                            </div>
                        </div>

                        <div class="input-icono-editar-textarea">
                            <textarea rows="4" :placeholder="translations.label.comments" v-model="form.comment"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button :disabled="loading" class="button-cancelar cancelar-info" type="button" data-dismiss="modal">{{ translations.label.cancel }}</button>
                    <button :disabled="loading" class="button-cancelar button-actualizar" @click="update" type="button" v-if="serie_selected!==''">{{ translations.label.update }}</button>
                    <button :disabled="loading" class="button-cancelar button-actualizar" @click="save" type="button" v-else>{{ translations.label.create }}</button>
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
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                serie_selected: '',
                form : {},
                optionsR: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    minDate: moment().format('YYYY-MM-DD')
                },
                action_after : '',
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
                if( this.data.serie_selected === undefined ) {
                    this.action_after = this.data.action_after
                    this.serie_selected = ''
                    this.form.id = ''
                    this.form.name = ''
                    this.form.date_start = ''
                    this.form.comment = ''
                } else {
                    this.action_after = ''
                    this.serie_selected = this.data.serie_selected
                    this.form.id = this.data.serie_selected.id
                    this.form.name = this.data.serie_selected.name
                    this.form.date_start = this.$parent.formatDate( this.data.serie_selected.date_start, '-', '-', 1 )
                    this.form.comment = this.data.serie_selected.comment
                }
            },
            save(){

                if( this.form.name === '' || this.form.date_start === '' ){
                    this.$toast.warning(this.translations.messages.complete_info, {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                axios.post(
                    baseExternalURL + 'api/series', this.form
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.saved_correctly, {
                                position: 'top-right'
                            })
                            if( this.action_after === 'go_edit' ){
                                window.location = '/serie/' + btoa( result.data.serie_id )
                            } else {
                                this.$parent._closeModal()
                                this.$parent.search()
                            }
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
            update(){
                this.loading = true

                axios.put(
                    baseExternalURL + 'api/series/' + this.form.id, this.form
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.updated_successfully, {
                                position: 'top-right'
                            })
                            this.$parent._closeModal()
                            this.$parent.search()
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
        }
    }
</script>
