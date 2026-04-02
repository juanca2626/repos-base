<template>
    <div class="modal fade modal-general modal-info" id="modal-borrar-nota">
        <div  :class="'modal-dialog ' + modal_size" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" @click="close_modal()">{{ translations.label.close }} X</button>
                <div class="modal-body">
                    <div class="modal-title">Borrar Nota</div>
                    <br>
                    <div class="modal-info-borrar">
                        Estás a punto de eliminar la nota de:
                        <span class="cod-remover">{{ note.note.user.name }} - {{ note.created_at | reformatDate }}.</span>
                    </div>

                  <div class="nota-body">

                    <div class="nota-body-textarea">
                      <textarea class="textarea-notas" rows="3" :placeholder="translations.label.comments"
                                :style="note.style_background + note.style_color"
                                v-model="note.note.note" disabled>{{ note.note.note }}</textarea>
                    </div>

                    <div class="nota-body-adjunto" v-if="note.note.attached!=='' && note.note.attached!==null">
                      <a target="_blank" :href="baseURL + 'uploads/' + note.note.attached">
                        <i class="icon-paperclip"></i>
                        <span>{{translations.label.see_attached}}</span>
                      </a>
                    </div>
                  </div>

                    <div class="modal-info-pregunta">{{ translations.messages.confirm_continue }}</div>
                </div>
                <div class="modal-footer">
                    <button :disabled="loading" class="button-cancelar cancelar-info" type="button" @click="close_modal()">{{ translations.label.not }}</button>
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
                modal_size: '',
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                baseExternalURL: window.baseExternalURL,
                baseURL: window.baseURL,
                note: {
                    note: {
                        user:{}
                    }
                },
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
                this.note = this.data.note
                this.modal_size = 'modal-size-small'
            },
            close_modal(){
                this.modal = ''
                document.getElementById('modal-borrar-nota').removeAttribute('style')
            },
            remove(){

                this.loading = true

                axios.delete(
                    baseExternalURL + 'api/notes/series/'+this.note.id
                )
                    .then((result) => {
                        if(result.data.success){
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.$parent.search()
                            this.$parent.$parent.get_total_notes()
                            this.close_modal()
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
  .textarea-notas{
    resize: none;
    border-radius: 9px;
    margin: 12px 0;
    font-style: italic;
    opacity: 0.8;
  }
</style>
