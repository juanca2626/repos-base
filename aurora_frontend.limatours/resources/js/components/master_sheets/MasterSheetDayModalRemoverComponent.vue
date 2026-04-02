<template>
    <div class="modal fade modal-general modal-info" id="modal-borrar-dia">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title">{{ translations.label.remove_day }}</div>
                    <br>
                    <div class="modal-info-borrar">
                        {{ translations.messages.remove_day }}.
                    </div>
                    <div class="modal-info-pregunta">{{ translations.messages.confirm_continue }}</div>
                </div>
                <div class="modal-footer">
                    <button :disabled="loading" class="button-cancelar cancelar-info" type="button" data-dismiss="modal">{{ translations.label.not }}</button>
                    <button :disabled="loading" type="button" class="button-cancelar button-actualizar" @click="remove()">{{ translations.label.yes }}, {{ translations.label.remove_day }}</button>
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
                master_sheet_day_id: "",
                day_number:""
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
                this.master_sheet_day_id = this.data.master_sheet_day_id
                this.day_number = this.data.day_number
            },
            remove(){
                this.loading = true

                axios.delete(
                    baseExternalURL + 'api/master_sheet/day/'+this.master_sheet_day_id
                )
                    .then((result) => {
                        if( result.data.success ){
                            // this.$toast.success(this.translations.messages.properly_removed, {
                            //     position: 'top-right'
                            // })
                            let message_ = this.translations.messages.has_deleted_day + ': ' + this.day_number
                            this.$parent.send_notification(message_)
                            this.$parent._closeModal()
                            this.$parent.search_days(true)
                            this.$parent.get_total_notes()
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
