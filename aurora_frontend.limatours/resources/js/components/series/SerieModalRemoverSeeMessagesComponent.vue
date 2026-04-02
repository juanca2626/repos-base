<template>
    <div class="modal fade modal-general modal-info" id="modal-borrar-see-message">
        <div  :class="'modal-dialog ' + modal_size" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" @click="close_modal()">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title">Borrar Mensajes</div>
                    <br>
                    <div class="modal-info-borrar">
                        Estás a punto de eliminar todos los mensajes incluidos en:
                        <span class="cod-remover">Pre-cotización - Importados de HM-{{ data.master_sheet_id }}.</span>
                    </div>
                    <div class="modal-info-pregunta">{{ translations.messages.confirm_continue }}</div>
                </div>
                <div class="modal-footer">
                    <button :disabled="loading" class="button-cancelar cancelar-info" type="button" @click="close_modal()">{{ translations.label.not }}</button>
                    <button :disabled="loading" type="button" class="button-cancelar button-actualizar" @click="update_see_previous_messages()">{{ translations.label.yes }}, {{ translations.label.delete_itinerary }}</button>
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
                modal_size: '',
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                baseExternalURL: window.baseExternalURL,
                serie_id: "",
                action_after: "",
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
                this.modal_size = 'modal-size-small'
            },
            close_modal(){
                this.modal = ''
                document.getElementById('modal-borrar-see-message').removeAttribute('style')
            },
            update_see_previous_messages(){
                this.loading = true
                axios.put(
                    baseExternalURL + 'api/series/' + this.serie_id + '/see_previous_messages', { see_previous_messages : 0 }
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.properly_removed, {
                                position: 'top-right'
                            })
                            this.$parent.data.see_previous_messages = false
                            this.$parent.$parent.search()
                            this.close_modal()
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
        }
    }
</script>
