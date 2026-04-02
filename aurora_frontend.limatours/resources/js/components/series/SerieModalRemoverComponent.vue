<template>
    <div class="modal fade modal-general modal-info" id="modal-borrar-itinerario">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title">Borrar Cotización</div>
                    <br>
                    <div class="modal-info-borrar">
                        Estás a punto de eliminar la cotización <span class="cod-remover">GS-{{ serie_id }}</span> junto con toda la información trabajada en ella: itinerario, servicios, acomodación, etc.
                    </div>
                    <div class="modal-info-pregunta">{{ translations.messages.confirm_continue }}</div>
                </div>
                <div class="modal-footer">
                    <button :disabled="loading" class="button-cancelar cancelar-info" type="button" data-dismiss="modal">{{ translations.label.not }}</button>
                    <button :disabled="loading" type="button" class="button-cancelar button-actualizar" @click="remove()">{{ translations.label.yes }}, {{ translations.label.delete_itinerary }}</button>
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
                this.action_after = this.data.action_after
            },
            remove(){
                this.loading = true

                axios.delete(
                    baseExternalURL + 'api/series/'+this.serie_id
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.properly_removed, {
                                position: 'top-right'
                            })
                            if( this.action_after === 'reload_list' ){
                                window.location = '/series'
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
            }
        }
    }
</script>
