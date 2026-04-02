<template>
    <div class="modal fade modal-general modal-info" id="modal-info">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title" v-if="master_sheet_selected!==''">{{ translations.label.edit }} {{ translations.label.info }}</div>
                    <div class="modal-title" v-else>{{ translations.label.new }} {{ translations.label.title }}</div>
                    <div class="modal-codigo" v-if="master_sheet_selected!==''">{{ translations.label.pre_quote }}:<span>HM-{{ master_sheet_selected.id }}</span></div>
                    <br>
                    <div class="modal-info-editar">
                        <div class="input-icono">
                            <input type="text" :placeholder="translations.label.file_name+'...'" v-model="form.name">
                            <div class="icono icon-edit"></div>
                        </div>
                        <div class="modal-info-editar-inputs">
                            <div class="input-icono input-icono-fecha">
                                <date-picker type="text" :placeholder="translations.label.departure_date" :config="optionsR" v-model="form.date_out"></date-picker>
                                <div class="icono icon-calendar"></div>
                            </div>
                            <div class="input-icono input-icono-numero">
                                <input type="number" min="0" step="1" :placeholder="translations.label.paxes" v-model="form.paxes">
                                <div class="icono icon-users"></div>
                            </div>
                            <div class="input-icono input-icono-agregar">
                                <input type="number" min="0" step="1" :placeholder="translations.label.leaders" v-model="form.leader">
                                <div class="icono icon-user-plus"></div>
                            </div>
                            <div class="input-icono input-icono-scort">
                                <input type="number" min="0" step="1" :placeholder="translations.label.scort" v-model="form.includes_scort"><i class="icono icon-user-plus"></i>
                            </div>
                        </div>
                        <div class="input-icono-editar-textarea">
                            <textarea rows="4" :placeholder="translations.label.comments" v-model="form.comment"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button :disabled="loading" class="button-cancelar cancelar-info" type="button" data-dismiss="modal">{{ translations.label.cancel }}</button>
                    <button :disabled="loading" class="button-cancelar button-actualizar" @click="update" type="button" v-if="master_sheet_selected!==''">{{ translations.label.update }}</button>
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
                master_sheet_selected: '',
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
                if( this.data.master_sheet_selected === undefined ) {
                    this.action_after = this.data.action_after
                    this.master_sheet_selected = ''
                    this.form.id = ''
                    this.form.name = ''
                    this.form.date_out = ''
                    this.form.paxes = ''
                    this.form.includes_scort = ''
                    this.form.comment = ''
                } else {
                    this.action_after = ''
                    this.master_sheet_selected = this.data.master_sheet_selected
                    this.form.id = this.data.master_sheet_selected.id
                    this.form.name = this.data.master_sheet_selected.name
                    console.log(this.data.master_sheet_selected.date_out)
                    this.form.date_out = this.$parent.formatDate( this.data.master_sheet_selected.date_out, '-', '-', 1 )
                    this.form.paxes = this.data.master_sheet_selected.paxes
                    this.form.includes_scort = this.data.master_sheet_selected.includes_scort
                    this.form.comment = this.data.master_sheet_selected.comment
                }
            },
            save(){

                if( this.form.name === '' || this.form.date_out === '' || this.form.paxes === '' ){
                    this.$toast.warning(this.translations.messages.complete_info, {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                axios.post(
                    baseExternalURL + 'api/master_sheet', this.form
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.saved_correctly, {
                                position: 'top-right'
                            })
                            if( this.action_after === 'go_edit' ){
                                window.location = '/master-sheet/' + btoa( result.data.master_sheet_id )
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
                    baseExternalURL + 'api/master_sheet/' + this.form.id, this.form
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.updated_successfully, {
                                position: 'top-right'
                            })
                            let message_ = this.translations.messages.updated_the_master_sheet
                            this.$parent.send_notification(message_)
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
