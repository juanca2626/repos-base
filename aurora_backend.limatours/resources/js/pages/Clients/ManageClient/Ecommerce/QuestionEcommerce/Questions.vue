<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div>
            <div class="row">
                <div class="col-md-10">
                    <label>Categoria - Pregunta</label>
                    <div class="col-sm-12 p-0">
                        <v-select :options="questions"
                                  multiple
                                  label="question"
                                  autocomplete="true"
                                  data-vv-as="questions"
                                  data-vv-name="questions"
                                  v-model="questionsSelected"
                                  :reduce="question => question.id"
                                  v-validate="'required'">
                            <template v-slot:option="question">
                                <span class="font-weight-bold mb-2">
                                    <i class="fas fa-tags"></i> {{ question.category }}
                                </span>
                                <br>
                                <span>¿{{ question.question }}?</span>
                            </template>
                            <template #selected-option-container="{ option, deselect, multiple, disabled }">
                                <div class="vs__selected">
                                    <div class="">
                                        <p class="font-weight-bold mb-1">
                                            <i class="fas fa-tags"></i> {{ option.category }}
                                        </p>
                                        <p class="text-left mb-1">¿{{ option.question }}?</p>
                                    </div>
                                </div>
                            </template>
                        </v-select>
                        <span class="invalid-feedback-select" v-show="errors.has('questions')">
                        <span>{{ errors.first('questions') }}</span>
                    </span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label>.</label>
                    <div class="col-sm-12 p-0">
                        <button type="button" class="btn btn-success" @click="validateBeforeSubmit">Guardar</button>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <table-client :columns="table.columns" :data="data_questions" :loading="loading" :options="tableOptions"
                              id="dataTable"
                              theme="bootstrap4">
                    <div class="table-actions" slot="actions" slot-scope="props">
                        <menu-edit :id="props.row.id" :name="(props.row.question.translations_question.length > 0) ? props.row.question.translations_question[0].value : ''"
                                   :options="menuOptions"
                                   @remove="remove(props.row.id)"/>
                    </div>
                    <div class="table-question" slot="question" slot-scope="props">
                        <p class="font-weight-bold mb-0">
                            <span v-if="props.row.question.category.translations.length > 0">
                                {{ props.row.question.category.translations[0].value }}
                            </span>
                        </p>
                        <p class="mb-2">
                            <span v-if="props.row.question.translations_question.length > 0">
                                {{ props.row.question.translations_question[0].value }}
                            </span>
                        </p>
                    </div>


                    <div class="table-loading text-center" slot="loading">
                        <img alt="loading" height="51px" src="/images/loading.svg"/>
                    </div>
                </table-client>
            </div>
        </div>

    </div>
</template>
<script>
    import { API } from '../../../../../api'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import TableClient from '../../../../../components/TableClient'
    import MenuEdit from '../../../../../components/MenuEdit'

    export default {
        components: {
            Loading,
            vSelect,
            'menu-edit': MenuEdit,
            'table-client': TableClient,
        },
        data () {
            return {
                loading: false,
                question_id: '',
                data_questions: [],
                questions: [],
                questionsSelected: [],
                table: {
                    columns: ['actions', 'id', 'question']
                }
            }
        },
        mounted: function () {
            this.fetchData()
            this.getFrequentQuestions()
        },
        computed: {
            menuOptions: function () {
                let options = []
                if (this.$can('delete', 'clientecommerce')) {
                    options.push({
                        type: 'delete',
                        text: '',
                        link: '',
                        icon: 'trash',
                        type_action: 'button',
                        callback_delete: 'remove'
                    })
                }
                return options
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        question: 'Categoria - Pregunta',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['id']
                }
            }
        },

        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            getFrequentQuestions: function () {
                API.get('frequent_questions/selectBox?lang=' + localStorage.getItem('lang')).then((result) => {
                    this.questions = result.data.data
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Ecommerce - Preguntas frecuentes',
                        text: this.$t('error.messages.connection_error'),
                    })
                })
            },
            validateBeforeSubmit: function () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Ecommerce - Preguntas frecuentes',
                            text: this.$t('packages.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {

                this.loading = true

                API({
                    method: 'post',
                    url: 'client/ecommerce/question',
                    data: {
                        client_id: this.$route.params.client_id,
                        questions_ids: this.questionsSelected,
                    }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.fetchData()
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Ecommerce - Preguntas frecuentes',
                                text: 'Guardado correctamente'
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Ecommerce - Preguntas frecuentes',
                                text: result.data.message
                            })

                        }
                    })
            },
            fetchData: function () {
                this.loading = true
                API.get('client/' + this.$route.params.client_id + '/ecommerce/question?lang=' + localStorage.getItem('lang')).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.data_questions = result.data.data
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                }).catch((error) => {
                        this.loading = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: error.response.data.title,
                            text: error.response.data.message
                        })
                    })
            },
            remove (id) {
                API({
                    method: 'DELETE',
                    url: 'client/ecommerce/question/' + id
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Ecommerce - Preguntas frecuentes',
                                text: result.data.message
                            })
                        } else {
                            this.fetchData()
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Ecommerce - Preguntas frecuentes',
                                text: 'Eliminado correctamente'
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Ecommerce - Preguntas frecuentes',
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            }
        },

    }
</script>

<style>
    body {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }


</style>

<i18n src="./services.json"></i18n>
